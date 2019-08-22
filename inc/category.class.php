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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2019 Teclib'
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

   public static function getTypeName($nb = 1) {
      return _n('Form category', 'Form categories', $nb, 'formcreator');
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $env       = new self;
      $found_env = $env->find();
      $nb        = count($found_env);
      return self::createTabEntry(self::getTypeName($nb), $nb);
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType()==__CLASS__) {
         $item->showChildren();
      }
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
    * @param integer $rootId id of the subtree root
    * @param boolean $helpdeskHome
    * @return array Tree of form categories as nested array
    */
   public static function getCategoryTree($rootId = 0, $helpdeskHome = false) {
      global $DB;

      $cat_table  = PluginFormcreatorCategory::getTable();
      $form_table = PluginFormcreatorForm::getTable();
      $table_fp   = PluginFormcreatorForm_Profile::getTable();
      $helpdesk   = '';
      if ($helpdeskHome) {
         $helpdesk   = "AND $form_table.`helpdesk_home` = 1";
      }

      $query_faqs = KnowbaseItem::getListRequest([
         'faq'      => '1',
         'contains' => ''
      ]);

      $categoryFk = self::getForeignKeyField();
      $query = "SELECT `id`, `name`, `$categoryFk` as `parent`, `level`, (
         (
            SELECT COUNT($form_table.id)
            FROM $form_table
            WHERE $form_table.`plugin_formcreator_categories_id` = $cat_table.`id`
            AND $form_table.`is_active` = 1
            AND $form_table.`is_deleted` = 0
            $helpdesk
            AND $form_table.`language` IN ('".$_SESSION['glpilanguage']."', '', NULL, '0')
            AND ".getEntitiesRestrictRequest("", $form_table, "", "", true, false)."
            AND ($form_table.`access_rights` != ".PluginFormcreatorForm::ACCESS_RESTRICTED." OR $form_table.`id` IN (
            SELECT plugin_formcreator_forms_id
            FROM $table_fp
            WHERE profiles_id = ".$_SESSION['glpiactiveprofile']['id']."))
         )
         + (
            SELECT COUNT(*)
            FROM ($query_faqs) AS `faqs`
            WHERE `faqs`.`knowbaseitemcategories_id` = `$cat_table`.`knowbaseitemcategories_id`
            AND `faqs`.`knowbaseitemcategories_id` <> '0'
         )
      ) as `items_count`
      FROM $cat_table
      ORDER BY `level` DESC, `name` DESC";

      $categories = [];
      if ($result = $DB->query($query)) {
         while ($category = $DB->fetch_assoc($result)) {
            $categories[$category['id']] = $category;
         }
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
      foreach ($categories as $category) {
         $flat[$category['id']] = $category;
         $flat[$category['parent']]['subcategories'][] = &$flat[$category['id']];
      }

      return $nodes;
   }
}
