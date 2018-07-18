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
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
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

   /**
    * {@inheritDoc}
    * @see CommonTreeDropdown::getAdditionalFields()
    */
   public function getAdditionalFields() {
      return [
            [
                  'name'      => 'knowbaseitemcategories_id',
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
      $cat_table  = getTableForItemType('PluginFormcreatorCategory');
      $form_table = getTableForItemType('PluginFormcreatorForm');
      $table_fp   = getTableForItemType('PluginFormcreatorForm_Profile');
      if ($helpdeskHome) {
         $helpdesk   = "AND $form_table.`helpdesk_home` = 1";
      } else {
         $helpdesk   = '';
      }

      $query_faqs = KnowbaseItem::getListRequest([
            'faq'      => '1',
            'contains' => ''
      ]);

      // Selects categories containing forms or sub-categories
      $where      = "(SELECT COUNT($form_table.id)
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
      ) > 0
      OR (SELECT COUNT(*)
         FROM `$cat_table` AS `cat2`
         WHERE `cat2`.`plugin_formcreator_categories_id`=`$cat_table`.`id`
      ) > 0
      OR (SELECT COUNT(*)
         FROM ($query_faqs) AS `faqs`
         WHERE `faqs`.`knowbaseitemcategories_id` = `$cat_table`.`knowbaseitemcategories_id`
         AND `faqs`.`knowbaseitemcategories_id` <> '0'
      ) > 0";

      $formCategory = new self();
      if ($rootId == 0) {
         $items = $formCategory->find("`level`='1' AND ($where)", '`name`');
         $name = '';
         $parent = 0;
      } else {
         $items = $formCategory->find("`plugin_formcreator_categories_id`='$rootId' AND ($where)",
                                      '`name`');
         $formCategory = new self();
         $formCategory->getFromDB($rootId);
         $name = $formCategory->getField('name');
         $parent = $formCategory->getField('plugin_formcreator_categories_id');
      }

      // No sub-categories, then return
      if (count($items) == 0) {
         return [
            'name'            => $name,
            'parent'          => $parent,
            'id'              => $rootId,
            'subcategories'   => new stdClass()
         ];
      }

      // Generate sub categories
      $children = [
         'name'            => $name,
         'parent'          => $parent,
         'id'              => $rootId,
         'subcategories'   => []
      ];
      foreach ($items as $categoryItem) {
         $children['subcategories'][] = self::getCategoryTree($categoryItem['id']);
      }

      return $children;
   }
}
