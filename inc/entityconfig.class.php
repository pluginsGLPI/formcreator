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

/**
 *
 * @since 0.1.0
 */
class PluginFormcreatorEntityconfig extends CommonDBTM {

   const CONFIG_PARENT = -2;

   const CONFIG_GLPI_HELPDSK = 0;
   const CONFIG_SIMPLIFIED_SERVICE_CATALOG = 1;
   const CONFIG_EXTENDED_SERVICE_CATALOG = 2;

   const CONFIG_DEFAULT_FORM_LIST_ALL = 0;
   const CONFIG_DEFAULT_FORM_LIST_DEFAULT = 1;

   const CONFIG_SORT_POPULARITY   = 0;
   const CONFIG_SORT_ALPHABETICAL = 1;

   const CONFIG_KB_MERGED = 0;
   const CONFIG_KB_DISTINCT = 1;

   const CONFIG_SEARCH_HIDDEN = 0;
   const CONFIG_SEARCH_VISIBLE = 1;

   const CONFIG_HEADER_HIDDEN = 0;
   const CONFIG_HEADER_VISIBLE = 1;

   const CONFIG_DASHBOARD_HIDDEN = 0;
   const CONFIG_DASHBOARD_VISIBLE = 1;

   const CONFIG_SEARCH_ISSUE_HIDDEN = 0;
   const CONFIG_SEARCH_ISSUE_VISIBLE = 1;

   const CONFIG_UI_FORM_MASONRY = 0;
   const CONFIG_UI_FORM_UNIFORM_HEIGHT = 1;

   const CONFIG_SERVICE_CATALOG_HOME_SEARCH = 0;
   const CONFIG_SERVICE_CATALOG_HOME_ISSUE = 1;

   /**
    * @var bool $dohistory maintain history
    */
   public $dohistory                   = true;

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $tabNames = [];
      if (!$withtemplate) {
         if ($item->getType() == 'Entity') {
            $tabNames[1] = _n('Form', 'Forms', 2, 'formcreator');
         }
      }
      return $tabNames;
   }

   public static function getEnumHelpdeskMode() : array {
      return [
         self::CONFIG_PARENT                     => __('Inheritance of the parent entity'),
         self::CONFIG_GLPI_HELPDSK               => __('GLPi\'s helpdesk', 'formcreator'),
         self::CONFIG_SIMPLIFIED_SERVICE_CATALOG => __('Service catalog simplified', 'formcreator'),
         self::CONFIG_EXTENDED_SERVICE_CATALOG   => __('Service catalog extended', 'formcreator'),
      ];
   }

   public static function getEnumDefaultFormList(): array {
      return [
         self::CONFIG_PARENT                    => __('Inheritance of the parent entity'),
         self::CONFIG_DEFAULT_FORM_LIST_ALL     => __('All available forms', 'formcreator'),
         self::CONFIG_DEFAULT_FORM_LIST_DEFAULT => __('Only default forms', 'formcreator'),
      ];
   }

   public static function getEnumSort() : array {
      return [
         self::CONFIG_PARENT            => __('Inheritance of the parent entity'),
         self::CONFIG_SORT_POPULARITY   => __('Popularity sort', 'formcreator'),
         self::CONFIG_SORT_ALPHABETICAL => __('Alphabetic sort', 'formcreator'),
      ];
   }

   public static function getEnumKbMode() : array {
      return [
         self::CONFIG_PARENT      => __('Inheritance of the parent entity'),
         self::CONFIG_KB_MERGED   => __('Merged with Forms', 'formcreator'),
         self::CONFIG_KB_DISTINCT => __('Distinct menu entry', 'formcreator'),
      ];
   }

   public static function getEnumSearchVisibility() : array {
      return [
         self::CONFIG_PARENT         => __('Inheritance of the parent entity'),
         self::CONFIG_SEARCH_VISIBLE => __('Visible', 'formcreator'),
         self::CONFIG_SEARCH_HIDDEN  => __('Hidden', 'formcreator'),
      ];
   }

   public static function getEnumHeaderVisibility() : array {
      return [
         self::CONFIG_PARENT         => __('Inheritance of the parent entity'),
         self::CONFIG_HEADER_VISIBLE => __('Visible', 'formcreator'),
         self::CONFIG_HEADER_HIDDEN  => __('Hidden', 'formcreator'),
      ];
   }

   public static function getEnumDashboardVisibility() : array {
      return [
         self::CONFIG_PARENT            => __('Inheritance of the parent entity'),
         self::CONFIG_DASHBOARD_VISIBLE => __('Visible', 'formcreator'),
         self::CONFIG_DASHBOARD_HIDDEN  => __('Hidden', 'formcreator'),
      ];
   }

   public static function getEnumSearchIssueVisibility() : array {
      return [
         self::CONFIG_PARENT            => __('Inheritance of the parent entity'),
         self::CONFIG_SEARCH_ISSUE_VISIBLE => __('Visible', 'formcreator'),
         self::CONFIG_SEARCH_ISSUE_HIDDEN  => __('Hidden', 'formcreator'),
      ];
   }

   public static function getEnumUIForm() : array {
      return [
         self::CONFIG_PARENT                 => __('Inheritance of the parent entity'),
         self::CONFIG_UI_FORM_MASONRY        => __('Variable height', 'formcreator'),
         self::CONFIG_UI_FORM_UNIFORM_HEIGHT => __('Uniform height', 'formcreator'),
      ];
   }
   public static function getEnumServiceCatalogHome() : array {
      return [
         self::CONFIG_SERVICE_CATALOG_HOME_SEARCH => __('Search for assistance', 'formcreator'),
         self::CONFIG_SERVICE_CATALOG_HOME_ISSUE => __('User\'s assistance requests', 'formcreator'),
      ];
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'Entity') {
         $config = new self();
         $config->showFormForEntity($item);
      }
   }

   public function prepareInputForAdd($input) {
      if (!isset($input['entities_id'])) {
         return false;
      }
      $entity = new Entity();
      if (!$entity->getFromDB($input['entities_id'])) {
         return false;
      }
      $input['header'] = $input['header'] ?? '';

      $config = Toolbox::getHtmLawedSafeConfig();
      $input['header'] = htmLawed($input['header'], $config);

      return $input;
   }

   public function prepareInputForUpdate($input) {
      // Disallow changing the linked entity
      unset($input['entities_id']);

      $input['header'] = $input['header'] ?? '';

      $config = Toolbox::getHtmLawedSafeConfig();
      $input['header'] = htmLawed($input['header'], $config);

      return $input;
   }

   protected static function createDefaultsForEntity($entityId): self {
      $entityConfig = new self();
      if ($entityConfig->getFromDbByCrit(['entities_id' => $entityId])) {
         return $entityConfig;
      }

      $entityConfig->add([
         'entities_id'            => $entityId,
         'replace_helpdesk'       => self::CONFIG_PARENT,
         'default_form_list_mode' => self::CONFIG_PARENT,
         'sort_order'             => self::CONFIG_PARENT,
         'is_kb_separated'        => self::CONFIG_PARENT,
         'is_search_visible'      => self::CONFIG_PARENT,
         'is_dashboard_visible'   => self::CONFIG_PARENT,
         'is_header_visible'      => self::CONFIG_PARENT,
      ]);

      return $entityConfig;
   }

   public function post_addItem() {
      $this->input = $this->addFiles(
         $this->input,
         [
            'force_update'  => true,
            'content_field' => 'header',
            'name'          => 'header',
         ]
      );
   }

   public function post_updateItem($history = 1) {
      $this->input = $this->addFiles(
         $this->input,
         [
            'force_update'  => true,
            'content_field' => 'header',
            'name'          => 'header',
         ]
      );
   }

   public function showFormForEntity(Entity $entity) {
      $entityId = $entity->getID();
      if (!$entity->can($entityId, READ)) {
         return false;
      }

      $this->getFromDB(self::createDefaultsForEntity($entityId)->getID());

      $canedit = Entity::canUpdate() && $entity->canUpdateItem();
      echo "<div class='spaced'>";
      if ($canedit) {
         echo "<form method='post' name=form action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      }

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".__('Helpdesk', 'formcreator')."</th></tr>";

      $elements = self::getEnumHelpdeskMode();
      if ($entityId == 0) {
         unset($elements[self::CONFIG_PARENT]);
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Helpdesk mode', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('replace_helpdesk', $elements, ['value' => $this->fields['replace_helpdesk']]);
      if ($this->fields['replace_helpdesk'] == self::CONFIG_PARENT) {
         $tid = self::getUsedConfig('replace_helpdesk', $entityId);
         echo '<br>';
         Entity::inheritedValue($elements[$tid], true);
      }
      echo '</td></tr>';

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Default Form list mode', 'formcreator')."</td>";
      echo "<td>";
      $elements = self::getEnumDefaultFormList();
      $options = ['value' => $this->fields['default_form_list_mode']];
      $options['no_parent'] = ($entityId == 0);
      self::dropdownDefaultFormList('default_form_list_mode', $options);
      if (!$options['no_parent'] && $this->fields['default_form_list_mode'] == self::CONFIG_PARENT) {
         $tid = self::getUsedConfig('default_form_list_mode', $entityId);
         echo '<br>';
         Entity::inheritedValue($elements[$tid], true);
      }
      echo '</td></tr>';

      $elements = self::getEnumSort();
      if ($entityId == 0) {
         unset($elements[self::CONFIG_PARENT]);
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Sort order', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('sort_order', $elements, ['value' => $this->fields['sort_order']]);
      if ($this->fields['replace_helpdesk'] == self::CONFIG_PARENT) {
         $tid = self::getUsedConfig('sort_order', $entityId);
         echo '<br>';
         Entity::inheritedValue($elements[$tid], true);
      }
      echo '</td></tr>';

      // Knowledge base settiing : merged with forms (legacy) separated menu on the left
      $elements = self::getEnumKbMode();
      if ($entityId == 0) {
         unset($elements[self::CONFIG_PARENT]);
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Knowledge base', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('is_kb_separated', $elements, ['value' => $this->fields['is_kb_separated']]);
      if ($this->fields['is_kb_separated'] == self::CONFIG_PARENT) {
         $tid = self::getUsedConfig('is_kb_separated', $entityId);
         echo '<br>';
         Entity::inheritedValue($elements[$tid], true);
      }
      echo '</td></tr>';

      $elements = self::getEnumSearchVisibility();
      if ($entityId == 0) {
         unset($elements[self::CONFIG_PARENT]);
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Search', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('is_search_visible', $elements, ['value' => $this->fields['is_search_visible']]);
      if ($this->fields['is_search_visible'] == self::CONFIG_PARENT) {
         $tid = self::getUsedConfig('is_search_visible', $entityId);
         echo '<br>';
         Entity::inheritedValue($elements[$tid], true);
      }
      echo '</td></tr>';

      // Dashboard visibility
      $elements = self::getEnumDashboardVisibility();
      if ($entityId == 0) {
         unset($elements[self::CONFIG_PARENT]);
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Counters dashboard', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('is_dashboard_visible', $elements, ['value' => $this->fields['is_dashboard_visible']]);
      if ($this->fields['is_dashboard_visible'] == self::CONFIG_PARENT) {
         $tid = self::getUsedConfig('is_dashboard_visible', $entityId);
         echo '<br>';
         Entity::inheritedValue($elements[$tid], true);
      }
      echo '</td></tr>';

      // header visibility
      $elements = self::getEnumHeaderVisibility();
      if ($entityId == 0) {
         unset($elements[self::CONFIG_PARENT]);
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Header message', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('is_header_visible', $elements, ['value' => $this->fields['is_header_visible']]);
      if ($this->fields['is_header_visible'] == self::CONFIG_PARENT) {
         $tid = self::getUsedConfig('is_header_visible', $entityId);
         echo '<br>';
         Entity::inheritedValue($elements[$tid], true);
      }
      echo '</td></tr>';

      // Search issue visibility
      $elements = self::getEnumSearchIssueVisibility();
      if ($entityId == 0) {
         unset($elements[self::CONFIG_PARENT]);
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Search issue', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('is_search_issue_visible', $elements, ['value' => $this->fields['is_search_issue_visible']]);
      if ($this->fields['is_search_issue_visible'] == self::CONFIG_PARENT) {
         $tid = self::getUsedConfig('is_search_issue_visible', $entityId);
         echo '<br>';
         Entity::inheritedValue($elements[$tid], true);
      }
      echo '</td></tr>';

      // Service catalog home page
      $elements = self::getEnumServiceCatalogHome();
      if ($entityId == 0) {
         unset($elements[self::CONFIG_PARENT]);
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Service catalog home page', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('service_catalog_home', $elements, ['value' => $this->fields['service_catalog_home']]);
      if ($this->fields['service_catalog_home'] == self::CONFIG_PARENT) {
         $tid = self::getUsedConfig('service_catalog_home', $entityId);
         echo '<br>';
         Entity::inheritedValue($elements[$tid], true);
      }
      echo '</td></tr>';

      // Tiles Design
      $elements = self::getEnumUIForm();
      if ($entityId == 0) {
         unset($elements[self::CONFIG_PARENT]);
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Tile design', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('tile_design', $elements, ['value' => $this->fields['tile_design']]);
      if ($this->fields['tile_design'] == self::CONFIG_PARENT) {
         $tid = self::getUsedConfig('tile_design', $entityId);
         echo '<br>';
         Entity::inheritedValue($elements[$tid], true);
      }
      echo '</td></tr>';

      // header
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . _n('Header', 'Headers', 1, 'formcreator') . "</td>";
      echo "<td>";
      echo Html::textarea([
         'name'            => 'header',
         'value'           => $this->fields['header'],
         'enable_richtext' => true,
         'display'         => false,
      ]);
      echo '</td></tr>';

      if ($canedit) {
         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='4'>";
         echo Html::hidden('entities_id', ['value' => $this->fields["entities_id"]]);
         echo Html::submit(_x('button', 'Save'), ['name' => 'update']);
         echo "</td></tr>";
         Html::closeForm();
      }
      echo "</table>";
      echo "</div>";
   }

   public function rawSearchOptions() {
      $tab = [];

      $tab[] = [
         'id'              => '3',
         'table'           => self::getTable(),
         'name'            => __('Helpdesk mode', 'formcreator'),
         'field'           => 'replace_helpdesk',
         'datatype'        => 'integer',
         'nosearch'        => true,
         'massiveaction'   => false,
      ];

      $tab[] = [
         'id'              => '4',
         'table'           => self::getTable(),
         'name'            => __('Sort order', 'formcreator'),
         'field'           => 'sort_order',
         'datatype'        => 'integer',
         'nosearch'        => true,
         'massiveaction'   => false,
      ];

      $tab[] = [
         'id'              => '5',
         'table'           => self::getTable(),
         'name'            => __('Knowledge base', 'formcreator'),
         'field'           => 'is_kb_separated',
         'datatype'        => 'integer',
         'nosearch'        => true,
         'massiveaction'   => false,
      ];

      $tab[] = [
         'id'              => '6',
         'table'           => self::getTable(),
         'name'            => __('Display search field', 'formcreator'),
         'field'           => 'is_search_visible',
         'datatype'        => 'integer',
         'nosearch'        => true,
         'massiveaction'   => false,
      ];

      $tab[] = [
         'id'              => '7',
         'table'           => self::getTable(),
         'name'            => __('Display header', 'formcreator'),
         'field'           => 'is_header_visible',
         'datatype'        => 'integer',
         'nosearch'        => true,
         'massiveaction'   => false,
      ];

      $tab[] = [
         'id'              => '8',
         'table'           => self::getTable(),
         'name'            => _n('Header', 'Headers', 1, 'formcreator'),
         'field'           => 'header',
         'datatype'        => 'text',
         'nosearch'        => true,
         'massiveaction'   => true,
      ];

      $tab[] = [
         'id'              => '9',
         'table'           => self::getTable(),
         'name'            => __('Service catalog home page', 'formcreator'),
         'field'           => 'service_catalog_home',
         'datatype'        => 'integer',
         'nosearch'        => true,
         'massiveaction'   => false,
      ];

      $tab[] = [
         'id'              => '10',
         'table'           => self::getTable(),
         'name'            => __('Default Form list mode', 'formcreator'),
         'field'           => 'default_form_list_mode',
         'datatype'        => 'integer',
         'nosearch'        => true,
         'massiveaction'   => false,
      ];

      $tab[] = [
         'id'              => '11',
         'table'           => self::getTable(),
         'name'            => __('Counters dashboard', 'formcreator'),
         'field'           => 'is_dashboard_visible',
         'datatype'        => 'integer',
         'nosearch'        => true,
         'massiveaction'   => false,
      ];

      $tab[] = [
         'id'              => '12',
         'table'           => self::getTable(),
         'name'            => __('Search issue', 'formcreator'),
         'field'           => 'is_search_issue_visible',
         'datatype'        => 'integer',
         'nosearch'        => true,
         'massiveaction'   => false,
      ];

      $tab[] = [
         'id'              => '13',
         'table'           => self::getTable(),
         'name'            => __('Tile design', 'formcreator'),
         'field'           => 'tile_design',
         'datatype'        => 'integer',
         'nosearch'        => true,
         'massiveaction'   => false,
      ];

      return $tab;
   }

   /**
    * Retrieve data of current entity or parent entity
    *
    * @since version 0.84 (before in entitydata.class)
    *
    * @param $fieldref        string   name of the referent field to know if we look at parent entity
    * @param $entities_id
    * @param $fieldval        string   name of the field that we want value (default '')
    * @param $default_value   integer  value to return (default -2)
    */
   static function getUsedConfig($fieldref, $entities_id, $fieldval = '', $default_value = -2) {

      // for calendar
      if (empty($fieldval)) {
         $fieldval = $fieldref;
      }

      $entity = new Entity();
      $entityConfig = new self();
      // Search in entity data of the current entity
      if ($entity->getFromDB($entities_id)) {
         // Value is defined : use it
         self::createDefaultsForEntity($entities_id);
         if ($entityConfig->getFromDBByCrit(['entities_id' => $entities_id])) {
            if (is_numeric($default_value)
                  && ($entityConfig->fields[$fieldref] != self::CONFIG_PARENT)) {
                     return $entityConfig->fields[$fieldval];
            }
            if (!is_numeric($default_value)) {
               return $entityConfig->fields[$fieldval];
            }

         }
      }

      // Entity data not found or not defined : search in parent one
      if ($entities_id > 0) {

         if ($entity->getFromDB($entities_id)) {
            $ret = self::getUsedConfig($fieldref, $entity->fields['entities_id'], $fieldval,
                  $default_value);
            return $ret;

         }
      }

      return $default_value;
   }

   /**
    * Show a dropdown to select default form list mode
    *
    * @param string $name name if the input field
    * @param array $options options
    * @return void
    */
   public static function dropdownDefaultFormList(string $name, array $options = []): void {
      $items = self::getEnumDefaultFormList();
      if (isset($options['no_parent']) && $options['no_parent']) {
         unset($items[self::CONFIG_PARENT]);
      }
      Dropdown::showFromArray($name, $items, $options);
   }
}
