<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorCategory extends CommonTreeDropdown
{
   // Activate translation on GLPI 0.85
   var $can_be_translated = true;

   static function canView() {
      if (isAPI()) {
         return true;
      }

      return parent::canView();
   }

   public static function getTypeName($nb = 1) {
      return _n('Form category', 'Form categories', $nb, 'formcreator');
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $env       = new self;
      $found_env = $env->find([static::getForeignKeyField() => $item->getID()]);
      $nb        = $_SESSION['glpishow_count_on_tabs'] ? count($found_env) : 0;
      return self::createTabEntry(self::getTypeName($nb), $nb);
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() != __CLASS__) {
         return;
      }
      $item->showChildren();
   }

   public function getAdditionalFields() {
      return [
         [
            'name'      => KnowbaseItemCategory::getForeignKeyField(),
            'type'      => 'dropdownValue',
            'label'     => __('Knowbase category', 'formcreator'),
            'list'      => false
         ],
         [
            'name'      => $this->getForeignKeyField(),
            'type'      => 'parent',
            'label'     => __('As child of'),
            'list'      => false
         ]
      ];
   }

   /**
    * Get the tree of categories
    *
    * @return array Tree of form categories as nested array
    */
   public static function getCategoryTree(): array {
      global $DB;

      $cat_table  = PluginFormcreatorCategory::getTable();
      $form_table = PluginFormcreatorForm::getTable();

      if (version_compare(GLPI_VERSION, '10.0.6') > 0) {
         $knowbase_category = KnowbaseItemCategory::SEEALL;
      } else {
         $knowbase_category = 0;
      }

      $query_faqs = KnowbaseItem::getListRequest([
         'faq'      => '1',
         'contains' => '',
         'knowbaseitemcategories_id' => $knowbase_category,
      ]);
      $query_faqs['SELECT'] = [$query_faqs['FROM'] . '.' . 'id'];

      $dbUtils = new DbUtils();
      $entityRestrict = $dbUtils->getEntitiesRestrictCriteria($form_table, "", "", true, false);
      if (count($entityRestrict)) {
         $entityRestrict = [$entityRestrict];
      }

      // Selects categories containing forms or sub-categories
      $categoryFk = PluginFormcreatorCategory::getForeignKeyField();

      // Get base query, add count and category condition
      $count_forms_criteria = PluginFormcreatorForm::getFormListQuery();
      $count_forms_criteria['COUNT'] = 'count';
      $count_forms_criteria['WHERE']["`$form_table`.`$categoryFk`"] = new QueryExpression("`$cat_table`.`id`");

      $count1 = new QuerySubQuery($count_forms_criteria);
      $count2 = new QuerySubQuery([
         'COUNT' => 'count',
         'FROM' => 'glpi_knowbaseitems_knowbaseitemcategories',
         'WHERE' => [
            'knowbaseitems_id' => new QuerySubQuery($query_faqs),
            [(new QueryExpression("`glpi_knowbaseitems_knowbaseitemcategories`.`knowbaseitemcategories_id` = `$cat_table`.`knowbaseitemcategories_id`"))],
         ]
      ]);
      $request = [
         'SELECT' => [
            self::getTableField('id'),
            "$categoryFk as parent",
            'level',
            new QueryExpression(
               $count1->getQuery() . " + " . $count2->getQuery() . " as items_count"
            ),
         ],
         'FROM' => $cat_table,
         'LEFT JOIN' => [],
         'ORDER' => ["level DESC", "name DESC"],
      ];
      $translation_table = DropdownTranslation::getTable();
      if (Session::haveTranslations(self::getType(), 'name')) {
         $request['LEFT JOIN']["$translation_table as namet"] = [
            'FKEY' => [
               $cat_table => 'id',
               'namet' => 'items_id',
               ['AND' => [
                  'namet.language' => $_SESSION['glpilanguage'],
                  'namet.itemtype' => self::getType(),
                  'namet.field' => 'name',
               ]],
            ],
         ];
         $request['SELECT'][] = 'namet.value as name';
      } else {
         $request['SELECT'][] = 'name';
         $request['SELECT'][] = 'comment';
      }
      if (Session::haveTranslations(self::getType(), 'comment')) {
         $request['LEFT JOIN']["$translation_table as commentt"] = [
            'FKEY' => [
               $cat_table => 'id',
               'commentt' => 'items_id',
               ['AND' => [
                  'namet.language' => $_SESSION['glpilanguage'],
                  'namet.itemtype' => self::getType(),
                  'namet.field' => 'comment',
               ]],
            ],
         ];
         $request['SELECT'][] = 'commentt.value as comment';
      } else {
         $request['SELECT'][] = 'comment';
      }
      $result = $DB->request($request);

      $categories = [];
      foreach ($result as $category) {
         $category['name'] = Dropdown::getDropdownName($cat_table, $category['id'], 0, true, false);
         // Keep the short name only
         // If a symbol > exists in a name, it is saved as an html entity, making the following reliable
         $split = explode(' &#62; ', $category['name']);
         $category['name'] = array_pop($split);
         $categories[$category['id']] = $category;
      }

      // Remove categories that have no items and no children
      // Requires category list to be sorted by level DESC
      foreach ($categories as $index => $category) {
         $children = array_filter(
            $categories,
            function ($element) use ($category) {
               return $category['id'] == $element['parent'];
            }
         );
         if (empty($children) && 0 == $category['items_count']) {
            unset($categories[$index]);
            continue;
         }
         $categories[$index]['subcategories'] = [];
      }

      // Create root node
      $nodes = [
         'name'            => '',
         'id'              => 0,
         'parent'          => 0,
         'subcategories'   => [],
      ];
      $flat = [
         0 => &$nodes,
      ];

      // Build from root node to leaves
      $categories = array_reverse($categories);
      foreach ($categories as $item) {
         $flat[$item['id']] = $item;
         $flat[$item['parent']]['subcategories'][] = &$flat[$item['id']];
      }

      return $nodes;
   }

   /**
    * Get available categories
    *
    * @param int $helpdeskHome
    * @return DBmysqlIterator
    */
   public static function getAvailableCategories($helpdeskHome = 1) : DBmysqlIterator {
      global $DB;

      $result = $DB->request(self::getAvailableCategoriesCriterias($helpdeskHome));

      return $result;
   }

   /**
    * Get available categories
    *
    * @param int $helpdeskHome
    * @return array
    */
   public static function getAvailableCategoriesCriterias($helpdeskHome = 1) : array {
      $cat_table   = PluginFormcreatorCategory::getTable();
      $category_fk = PluginFormcreatorCategory::getForeignKeyField();
      $form_table  = PluginFormcreatorForm::getTable();
      $form_ids    = PluginFormcreatorForm::getFormRestrictionSubQuery($helpdeskHome);

      return [
         'SELECT' => [
            $cat_table => [
              'name', 'id'
            ]
         ],
         'FROM' => $cat_table,
         'INNER JOIN' => [
            $form_table => [
               'FKEY' => [
                  $cat_table => 'id',
                  $form_table => $category_fk
               ]
            ]
         ],
         'WHERE' => ["$form_table.id" => $form_ids],
         'GROUPBY' => [
            "$cat_table.id"
         ]
      ];
   }
}
