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

class PluginFormcreatorForm extends CommonDBTM
{
   static $rightname = 'entity';

   public $dohistory         = true;

   const ACCESS_PUBLIC       = 0;
   const ACCESS_PRIVATE      = 1;
   const ACCESS_RESTRICTED   = 2;

   /**
    * Check if current user have the right to create and modify requests
    *
    * @return boolean True if he can create and modify requests
    */
   public static function canCreate() {
      return Session::haveRight("entity", UPDATE);
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView() {
      return Session::haveRight('entity', UPDATE);
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canDelete() {
      return Session::haveRight('entity', UPDATE);
   }

   public function canPurgeItem() {
      $DbUtil = new DbUtils();

      $criteria = [
         PluginFormcreatorForm::getForeignKeyField() => $this->getID(),
      ];
      if ($DbUtil->countElementsInTable(PluginFormcreatorForm_Answer::getTable(), $criteria) > 0) {
         return false;
      }
      return Session::haveRight('entity', UPDATE);
   }

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return _n('Form', 'Forms', $nb, 'formcreator');
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu  = parent::getMenuContent();
      $validation_image = '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/check.png"
                                title="' . __('Forms waiting for validation', 'formcreator') . '">';
      $import_image     = '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/import.png"
                                title="' . __('Import forms', 'formcreator') . '">';
      $menu['links']['search']          = PluginFormcreatorFormList::getSearchURL(false);
      $menu['links']['config']          = PluginFormcreatorForm::getSearchURL(false);
      $menu['links'][$validation_image] = PluginFormcreatorForm_Answer::getSearchURL(false);
      $menu['links'][$import_image]     = PluginFormcreatorForm::getFormURL(false)."?import_form=1";

      return $menu;
   }

   /**
    * Define search options for forms
    *
    * @return Array Array of fields to show in search engine and options for each fields
    */
   public function getSearchOptionsNew() {
      return $this->rawSearchOptions();
   }

   public function rawSearchOptions() {
      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => __('Characteristics')
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'description',
         'name'               => __('Description'),
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __('Entity'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __('Recursive'),
         'datatype'           => 'bool',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'language',
         'name'               => __('Language'),
         'datatype'           => 'specific',
         'searchtype'         => [
            '0'                  => 'equals'
         ],
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'helpdesk_home',
         'name'               => __('Homepage', 'formcreator'),
         'datatype'           => 'bool',
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '9',
         'table'              => $this->getTable(),
         'field'              => 'access_rights',
         'name'               => __('Access', 'formcreator'),
         'datatype'           => 'specific',
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '10',
         'table'              => 'glpi_plugin_formcreator_categories',
         'field'              => 'name',
         'name'               => __('Form category', 'formcreator'),
         'datatype'           => 'dropdown',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '30',
         'table'              => $this->getTable(),
         'field'              => 'is_active',
         'name'               => __('Active'),
         'datatype'           => 'specific',
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'massiveaction'      => true
      ];

      return $tab;
   }

   /**
    * Define how to display search field for a specific type
    *
    * @since version 0.84
    *
    * @param String $field           Name of the field as define in $this->getSearchOptions()
    * @param String $name            Name attribute for the field to be posted (default '')
    * @param Array  $values          Array of all values to display in search engine (default '')
    * @param Array  $options         Options (optional)
    *
    * @return String                 Html string to be displayed for the form field
    **/
   public static function getSpecificValueToSelect($field, $name='', $values='', array $options=[]) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'is_active' :
            return Dropdown::showFromArray($name, [
               '0' => __('Inactive'),
               '1' => __('Active'),
            ], [
               'value'               => $values[$field],
               'display_emptychoice' => false,
               'display'             => false
            ]);
            break;

         case 'access_rights' :
            return Dropdown::showFromArray($name, [
               self::ACCESS_PUBLIC => __('Public access', 'formcreator'),
               self::ACCESS_PRIVATE => __('Private access', 'formcreator'),
               self::ACCESS_RESTRICTED => __('Restricted access', 'formcreator'),
            ], [
               'value'               => $values[$field],
               'display_emptychoice' => false,
               'display'             => false
            ]);
            break;

         case 'language' :
            return Dropdown::showLanguages($name, [
               'value'               => $values[$field],
               'display_emptychoice' => true,
               'emptylabel'          => '--- ' . __('All langages', 'formcreator') . ' ---',
               'display'             => false
            ]);
            break;
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }


   /**
    * Define how to display a specific value in search result table
    *
    * @param  String $field   Name of the field as define in $this->getSearchOptions()
    * @param  Mixed  $values  The value as it is stored in DB
    * @param  Array  $options Options (optional)
    * @return Mixed           Value to be displayed
    */
   public static function getSpecificValueToDisplay($field, $values, array $options = []) {
      global $CFG_GLPI;
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'is_active':
            if ($values[$field] == 0) {
               $output = '<div style="text-align: center"><img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/inactive.png"
                           height="16" width="16"
                           alt="' . __('Inactive') . '"
                           title="' . __('Inactive') . '" /></div>';
            } else {
               $output = '<div style="text-align: center"><img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/active.png"
                           height="16" width="16"
                           alt="' . __('Active') . '"
                           title="' . __('Active') . '" /></div>';
            }
            return $output;
            break;

         case 'access_rights':
            switch ($values[$field]) {
               case self::ACCESS_PUBLIC :
                  return __('Public access', 'formcreator');
                  break;

               case self::ACCESS_PRIVATE :
                  return __('Private access', 'formcreator');
                  break;

               case self::ACCESS_RESTRICTED :
                  return __('Restricted access', 'formcreator');
                  break;
            }
            return '';
            break;

         case 'language' :
            if (empty($values[$field])) {
               return __('All langages', 'formcreator');
            } else {
               return Dropdown::getLanguageName($values[$field]);
            }
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * Show the Form for the adminsitrator to edit in the config page
    *
    * @param  Array  $options Optional options
    *
    * @return NULL   Nothing, just display the form
    */
   public function showForm($ID, $options=[]) {
      global $DB;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo '<tr class="tab_bg_1">';
      echo '<td width="20%"><strong>' . __('Name') . ' <span class="red">*</span></strong></td>';
      echo '<td width="30%"><input type="text" name="name" value="' . $this->fields["name"] . '" size="35"/></td>';
      echo '<td width="20%"><strong>' . __('Active') . ' <span class="red">*</span></strong></td>';
      echo '<td width="30%">';
      Dropdown::showYesNo("is_active", $this->fields["is_active"]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_2">';
      echo '<td>' . __('Category') . '</td>';
      echo '<td>';
      PluginFormcreatorCategory::dropdown([
         'name'  => 'plugin_formcreator_categories_id',
         'value' => ($ID != 0) ? $this->fields["plugin_formcreator_categories_id"] : 0,
      ]);
      echo '</td>';
      echo '<td>' . __('Direct access on homepage', 'formcreator') . '</td>';
      echo '<td>';
      Dropdown::showYesNo("helpdesk_home", $this->fields["helpdesk_home"]);
      echo '</td>';

      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td>' . __('Description') . '</td>';
      echo '<td><input type="text" name="description" value="' . $this->fields['description'] . '" size="35" /></td>';
      echo '<td>' . __('Language') . '</td>';
      echo '<td>';
      Dropdown::showLanguages('language', [
         'value'               => ($ID != 0) ? $this->fields['language'] : $_SESSION['glpilanguage'],
         'display_emptychoice' => true,
         'emptylabel'          => '--- ' . __('All langages', 'formcreator') . ' ---',
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td>' . _n('Header', 'Headers', 1, 'formcreator') . '</td>';
      echo '<td colspan="3"><textarea name="content" cols="124" rows="10">' . $this->fields["content"] . '</textarea></td>';
      Html::initEditorSystem('content');
      echo '</tr>';

      echo '<tr class="tab_bg_2">';
      echo '<td>' . __('Need to be validate?', 'formcreator') . '</td>';
      echo '<td colspan="3" class="validators_bloc">';

      Dropdown::showFromArray('validation_required', [
         0 => Dropdown::EMPTY_VALUE,
         1 => _n('User', 'Users', 1),
         2 => _n('Group', 'Groups', 1),
      ], [
         'value'     =>  $this->fields["validation_required"],
         'on_change' => 'changeValidators(this.value)'
      ]);

      // Validators users
      $validators = [];
      $formId = $this->getID();
      $form_validator = new PluginFormcreatorForm_Validator();
      $rows = $form_validator->find("`plugin_formcreator_forms_id` = '$formId'");
      foreach ($rows as $row) {
         $validators[] = $row['items_id'];
      }

      // If the form is recursive, authorize the validators in sub-entities
      // If it isn't, only the validators of the entity of the form
      if ($this->isRecursive()) {
         $entites = getSonsOf('glpi_entities', $this->getEntityID());
      } else {
         $entites = $this->getEntityID();
      }
      $subentities = getEntitiesRestrictRequest("", 'pu', "", $entites, true, true);

      // Select all users with ticket validation right and the groups
      $query = "SELECT DISTINCT u.`id`, u.`name`, u.`realname`, u.`firstname`, g.`id` AS groups_id, g.`completename` AS groups_name
                FROM `glpi_users` u
                INNER JOIN `glpi_profiles_users` pu ON u.`id` = pu.`users_id`
                INNER JOIN `glpi_profiles` p ON p.`id` = pu.`profiles_id`
                INNER JOIN `glpi_profilerights` pr ON p.`id` = pr.`profiles_id`
                LEFT JOIN `glpi_groups_users` gu ON u.`id` = gu.`users_id`
                LEFT JOIN `glpi_groups` g ON g.`id` = gu.`groups_id`
                WHERE pr.`name` = 'ticketvalidation'
                AND (
                  pr.`rights` & " . TicketValidation::VALIDATEREQUEST . " = " . TicketValidation::VALIDATEREQUEST . "
                  OR pr.`rights` & " . TicketValidation::VALIDATEINCIDENT . " = " . TicketValidation::VALIDATEINCIDENT . ")
                AND $subentities
                AND u.`is_active` = '1'
                GROUP BY u.`id`
                ORDER BY u.`name`";
      $result = $DB->query($query);
      $groups_users = [];

      echo '<div id="validators_users" style="width: 100%">';
      echo '<select name="_validator_users[]" size="4" style="width: 100%" multiple id="validator_users">';
      while ($user = $DB->fetch_assoc($result)) {
         $groups_users[] = $user['id'];
         if (!empty($user['realname']) && !empty($user['firstname'])) {
            $displayName = $user['realname'] . ' ' .$user['firstname'];
         } else {
            $displayName = $user['name'];
         }
         echo '<option value="' . $user['id'] . '"';
         if (in_array($user['id'], $validators)) {
            echo ' selected="selected"';
         }
         echo '>' . $displayName . '</option>';
      }
      echo '</select>';
      echo '</div>';

      // Validators groups
      echo '<div id="validators_groups" style="width: 100%">';
      echo '<select name="_validator_groups[]" size="4" style="width: 100%" multiple id="validator_groups">';
      if (!empty($groups_users)) {
         $query = "SELECT DISTINCT g.`id`, g.`completename`
                   FROM `glpi_groups` g
                   INNER JOIN `glpi_groups_users` gu
                     ON g.`id` = gu.`groups_id`
                     AND gu.`users_id` IN (" . implode(',', $groups_users) . ")
                   ORDER BY g.`completename`";
         $result = $DB->query($query);
         while ($group = $DB->fetch_assoc($result)) {
            echo '<option value="' . $group['id'] . '"';
            if (in_array($group['id'], $validators)) {
               echo ' selected="selected"';
            }
            echo '>' . $group['completename'] . '</option>';
         }
      }
      echo '</select>';
      echo '</div>';

      $script = 'function changeValidators(value) {
                  if (value == 1) {
                     document.getElementById("validators_users").style.display  = "block";
                     document.getElementById("validators_groups").style.display = "none";
                  } else if (value == 2) {
                     document.getElementById("validators_users").style.display  = "none";
                     document.getElementById("validators_groups").style.display = "block";
                  } else {
                     document.getElementById("validators_users").style.display  = "none";
                     document.getElementById("validators_groups").style.display = "none";
                  }
               }
               $(document).ready(function() {changeValidators(' . $this->fields["validation_required"] . ');});';
      echo Html::scriptBlock($script);

      echo '</td>';
      echo '</tr>';

      echo '<td>'.__('Default form in service catalog', 'formcreator').'</td>';
      echo '<td>';
      Dropdown::showYesNo("is_default", $this->fields["is_default"]);
      echo '</td>';
      echo '</tr>';

      $this->showFormButtons($options);
   }

   /**
    * Return the name of the tab for item including forms like the config page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Config Item)
    * @param  integer    $withtemplate
    *
    * @return String                   Name to be displayed
    */
   public function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      switch ($item->getType()) {
         case "PluginFormcreatorConfig":
            $object  = new self;
            $found = $object->find();
            $number  = count($found);
            return self::createTabEntry(self::getTypeName($number), $number);
            break;
         case "PluginFormcreatorForm":
            return __('Preview');
            break;
         case 'Central':
            return _n('Form', 'Forms', 2, 'formcreator');
            break;
      }
      return '';
   }

   /**
    * Display a list of all forms on the configuration page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Config Item)
    * @param  integer    $tabnum       Number of the current tab
    * @param  integer    $withtemplate
    *
    * @see CommonDBTM::displayTabContentForItem
    *
    * @return null                     Nothing, just display the list
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      $uri = strrchr($_SERVER['HTTP_REFERER'], '/');
      if (strpos($uri, '?')) {
         $uri = substr($uri, 0, strpos($uri, '?'));
      }
      $uri = trim($uri, '/');

      switch ($uri) {
         case "form.form.php":
            echo '<div style="text-align: left">';
            $item->displayUserForm($item);
            echo '</div>';
            break;
         case 'central.php':
            $form = new static();
            $form->showForCentral();
            break;
      }
   }


   public function defineTabs($options=[]) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginFormcreatorQuestion', $ong, $options);
      $this->addStandardTab('PluginFormcreatorForm_Profile', $ong, $options);
      $this->addStandardTab('PluginFormcreatorTarget', $ong, $options);
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab('PluginFormcreatorForm_Answer', $ong, $options);
      return $ong;
   }

   /**
    * Show the list of forms to be displayed to the end-user
    */
   public function showList() {
      echo '<div class="center" id="plugin_formcreator_wizard">';

      echo '<div class="plugin_formcreator_marginRight plugin_formcreator_card">';
      $this->showWizard();
      // echo '<hr style="clear:both; height:0; background: transparent; border:none" />';
      echo '</div>';

      echo '<div id="plugin_formcreator_lastForms">';
      $this->showMyLastForms();
      echo '</div>';

      echo '</div>';
   }

   public function showServiceCatalog() {
      echo "<div id='formcreator_servicecatalogue'>";

      // show wizard
      echo '<div id="plugin_formcreator_wizard" class="plugin_formcreator_menuplaceholder">';
      $this->showWizard(true);
      echo '</div>';

      echo '</div>'; // formcreator_servicecatalogue
   }

   public function showWizard($service_catalog = false) {
      echo '<div id="plugin_formcreator_wizard_categories">';
      echo '<div><h2>'._n("Category", "Categories", 2, 'formcreator').'</h2></div>';
      echo '<div><a href="#" id="wizard_seeall">' . __('see all', 'formcreator') . '</a></div>';
      echo '</div>';

      echo '<div id="plugin_formcreator_wizard_right">';

      // hook display central (for alert plugin)
      if ($service_catalog) {
         echo "<div id='plugin_formcreator_display_central'>";
         Plugin::doHook('display_central');
         echo "</div>";
      }

      echo '<div id="plugin_formcreator_searchBar">';
      $this->showSearchBar();
      echo '</div>';
      echo '<div class="plugin_formcreator_sort">';
      echo '<span class="formcreator_radios">';
      echo '<input type="radio" class="form-control" id="plugin_formcreator_mostPopular" name="sort" value="mostPopularSort" />';
      echo '<label for="plugin_formcreator_mostPopular">'.__('Popularity sort', 'formcreator').'</label>';
      echo '</span>';
      echo '<span class="formcreator_radios">';
      echo '<input type="radio" class="form-control" id="plugin_formcreator_alphabetic" name="sort" value="alphabeticSort" />';
      echo '<label for="plugin_formcreator_alphabetic">'.__('Alphabetic sort', 'formcreator').'</label>';
      echo '</span>';
      echo '</div>';
      echo '<div id="plugin_formcreator_wizard_forms">';
      echo '</div>';
      echo '</div>';
   }

   /**
    * Show form and FAQ items
    * @param number $rootCategory Items of this subtree only. 0 = no filtering
    * @param string $keywords Filter items with keywords
    * @param string $helpdeskHome show items for helpdesk only
    */
   public function showFormList($rootCategory = 0, $keywords = '', $helpdeskHome = false) {
      global $DB;

      $table_cat     = getTableForItemType('PluginFormcreatorCategory');
      $table_form    = getTableForItemType('PluginFormcreatorForm');
      $table_fp      = getTableForItemType('PluginFormcreatorForm_Profile');
      $table_target  = getTableForItemType('PluginFormcreatorTargets');
      $table_section = getTableForItemType('PluginFormcreatorSections');
      $table_question= getTableForItemType('PluginFormcreatorQuestions');

      $order         = "$table_form.name ASC";

      $where_form    = "$table_form.`is_active` = 1 AND $table_form.`is_deleted` = 0 ";
      $where_form    .= getEntitiesRestrictRequest("AND", $table_form, "", "", true, false);
      $where_form    .= " AND $table_form.`language` IN ('".$_SESSION['glpilanguage']."', '', NULL, '0')";

      if ($helpdeskHome) {
         $where_form    .= "AND $table_form.`helpdesk_home` = '1'";
      }

      if ($rootCategory != 0) {
         $selectedCategories = getSonsOf($table_cat, $rootCategory);
         $selectedCategories = implode(', ', array_keys($selectedCategories));
         $where_form .= " AND $table_form.`plugin_formcreator_categories_id` IN ($selectedCategories)";
      } else {
         $selectedCategories = '';
      }

      // Find forms accessible by the current user
      $keywords = preg_replace("/[^A-Za-z0-9 ]/", '', $keywords);
      if (!empty($keywords)) {
         // Determine the optimal search mode
         $searchMode = "BOOLEAN MODE";
         $query = "SHOW TABLE STATUS WHERE `Name` = '$table_form'";
         $result = $DB->query($query);
         if ($result) {
            $row = $DB->fetch_assoc($result);
            if ($row['Rows'] > 20) {
               $searchMode = "NATURAL LANGUAGE MODE";
            }
         }
         $keywords = $DB->escape($keywords);
         $highWeightedMatch = " MATCH($table_form.`name`, $table_form.`description`)
               AGAINST('$keywords*' IN $searchMode)";
         $lowWeightedMatch = " MATCH($table_question.`name`, $table_question.`description`)
               AGAINST('$keywords*' IN $searchMode)";
         $where_form .= " AND ($highWeightedMatch OR $lowWeightedMatch)";
      }
      $query_forms = "SELECT
         $table_form.id,
         $table_form.name,
         $table_form.description,
         $table_form.usage_count,
         $table_form.is_default
      FROM $table_form
      LEFT JOIN $table_cat ON ($table_cat.id = $table_form.`plugin_formcreator_categories_id`)
      LEFT JOIN $table_target ON ($table_target.`plugin_formcreator_forms_id` = $table_form.`id`)
      LEFT JOIN $table_section ON ($table_section.`plugin_formcreator_forms_id` = $table_form.`id`)
      LEFT JOIN $table_question ON ($table_question.`plugin_formcreator_sections_id` = $table_section.`id`)
      WHERE $where_form
      AND (`access_rights` != ".PluginFormcreatorForm::ACCESS_RESTRICTED." OR $table_form.`id` IN (
         SELECT plugin_formcreator_forms_id
         FROM $table_fp
         WHERE `profiles_id` = ".$_SESSION['glpiactiveprofile']['id']."))
      GROUP BY `$table_target`.`plugin_formcreator_forms_id`,
               $table_form.id,
               $table_form.name,
               $table_form.description,
               $table_form.usage_count,
               $table_form.is_default
      ORDER BY $order";
      $result_forms = $DB->query($query_forms);

      $formList = [];
      if ($DB->numrows($result_forms) > 0) {
         while ($form = $DB->fetch_array($result_forms)) {
            $formList[] = [
                  'id'           => $form['id'],
                  'name'         => $form['name'],
                  'description'  => $form['description'],
                  'type'         => 'form',
                  'usage_count'  => $form['usage_count'],
                  'is_default'   => $form['is_default'] ? "true" : "false"
            ];
         }
      }

      // Find FAQ entries
      $query_faqs = KnowbaseItem::getListRequest([
            'faq'      => '1',
            'contains' => $keywords
      ]);
      if ($selectedCategories != '') {
         $query_faqs = "SELECT `faqs`.* FROM ($query_faqs)  AS `faqs`
         WHERE `faqs`.`knowbaseitemcategories_id` IN (SELECT `knowbaseitemcategories_id` FROM `$table_cat` WHERE `id` IN ($selectedCategories) AND `knowbaseitemcategories_id` <> '0')";
      } else {
         $query_faqs = "SELECT `faqs`.* FROM ($query_faqs)  AS `faqs`
         INNER JOIN `$table_cat` ON (`faqs`.`knowbaseitemcategories_id` = `$table_cat`.`knowbaseitemcategories_id`)
         WHERE `faqs`.`knowbaseitemcategories_id` <> '0'";
      }
      $result_faqs = $DB->query($query_faqs);
      if ($DB->numrows($result_faqs) > 0) {
         while ($faq = $DB->fetch_array($result_faqs)) {
            $formList[] = [
                  'id'           => $faq['id'],
                  'name'         => $faq['name'],
                  'description'  => '',
                  'type'         => 'faq',
                  'usage_count'  => $faq['view'],
                  'is_default'   => false
            ];
         }
      }

      if (count($formList) == 0) {
         $defaultForms = true;
         // No form nor FAQ have been selected
         // Fallback to default forms
         $where_form       = "$table_form.`is_active` = 1 AND $table_form.`is_deleted` = 0 ";
         $where_form       .= getEntitiesRestrictRequest("AND", $table_form, "", "", true, false);
         $where_form       .= " AND $table_form.`language` IN ('".$_SESSION['glpilanguage']."', '', NULL, '0')";
         $where_form       .= " AND `is_default` <> '0'";
         $query_forms = "SELECT $table_form.id, $table_form.name, $table_form.description, $table_form.usage_count
         FROM $table_form
         LEFT JOIN $table_cat ON ($table_cat.id = $table_form.`plugin_formcreator_categories_id`)
         WHERE $where_form
         AND (`access_rights` != ".PluginFormcreatorForm::ACCESS_RESTRICTED." OR $table_form.`id` IN (
         SELECT plugin_formcreator_forms_id
         FROM $table_fp
         WHERE profiles_id = ".$_SESSION['glpiactiveprofile']['id']."))
         ORDER BY $order";
         $result_forms = $DB->query($query_forms);

         if ($DB->numrows($result_forms) > 0) {
            while ($form = $DB->fetch_array($result_forms)) {
               $formDescription = plugin_formcreator_encode($form['description']);
               $formList[] = [
                     'id'           => $form['id'],
                     'name'         => $form['name'],
                     'description'  => $formDescription,
                     'type'         => 'form',
                     'usage_count'  => $form['usage_count'],
                     'is_default'   => true
               ];
            }
         }
      } else {
         $defaultForms = false;
      }
      return ['default' => $defaultForms, 'forms' => $formList];
   }

   protected function showSearchBar() {
      echo '<form name="formcreator_search" onsubmit="javascript: return false;" >';
      echo '<input type="text" name="words" id="formcreator_search_input" required/>';
      echo '<span id="formcreator_search_input_bar"></span>';
      echo '<label for="formcreator_search_input">'.__('Please, describe your need here', 'formcreator').'</label>';
      echo '</form>';
   }

   protected function showMyLastForms() {
      global $DB;

      $userId = $_SESSION['glpiID'];
      echo '<div class="plugin_formcreator_card">';
      echo '<div class="plugin_formcreator_heading">'.__('My last forms (requester)', 'formcreator').'</div>';
      $query = "SELECT fa.`id`, f.`name`, fa.`status`, fa.`request_date`
                      FROM glpi_plugin_formcreator_forms f
                      INNER JOIN glpi_plugin_formcreator_forms_answers fa ON f.`id` = fa.`plugin_formcreator_forms_id`
                      WHERE fa.`requester_id` = '$userId'
                      AND f.is_deleted = 0
                      ORDER BY fa.`status` ASC, fa.`request_date` DESC
                      LIMIT 0, 5";
      $result = $DB->query($query);
      if ($DB->numrows($result) == 0) {
         echo '<div class="line1" align="center">'.__('No form posted yet', 'formcreator').'</div>';
         echo "<ul>";
      } else {
         while ($form = $DB->fetch_assoc($result)) {
               echo '<li class="plugin_formcreator_answer">';
               echo ' <a class="plugin_formcreator_'.$form['status'].'" href="form_answer.form.php?id='.$form['id'].'">'.$form['name'].'</a>';
               echo '<span class="plugin_formcreator_date">'.Html::convDateTime($form['request_date']).'</span>';
               echo '</li>';
         }
         echo "</ul>";
         echo '<div align="center">';
         echo '<a href="form_answer.php?criteria[0][field]=4&criteria[0][searchtype]=equals&criteria[0][value]='.$userId.'">';
         echo __('All my forms (requester)', 'formcreator');
         echo '</a>';
         echo '</div>';
      }
      echo '</div>';

      if (Session::haveRight('ticketvalidation', TicketValidation::VALIDATEINCIDENT)
            || Session::haveRight('ticketvalidation', TicketValidation::VALIDATEREQUEST)) {

         echo '<div class="plugin_formcreator_card">';
         echo '<div class="plugin_formcreator_heading">'.__('My last forms (validator)', 'formcreator').'</div>';
         $groupList = Group_User::getUserGroups($userId);
         $groupIdList = [];
         foreach ($groupList as $group) {
            $groupIdList[] = $group['id'];
         }
         $groupIdListString = $groupIdList + ['NULL', '0', ''];
         $groupIdListString = "'" . implode("', '", $groupIdList) . "'";
         $query = "SELECT fa.`id`, f.`name`, fa.`status`, fa.`request_date`
                FROM glpi_plugin_formcreator_forms f
                INNER JOIN glpi_plugin_formcreator_forms_validators fv ON fv.`plugin_formcreator_forms_id`=f.`id`
                INNER JOIN glpi_plugin_formcreator_forms_answers fa ON f.`id` = fa.`plugin_formcreator_forms_id`
                WHERE (f.`validation_required` = 1 AND fv.`items_id` = '$userId' AND fv.`itemtype` = 'User' AND `fa`.`users_id_validator` = '$userId'
                   OR f.`validation_required` = 2 AND fv.`items_id` IN ($groupIdListString) AND fv.`itemtype` = 'Group' AND `fa`.`groups_id_validator` IN ($groupIdListString)
                )
                AND f.is_deleted = 0
                ORDER BY fa.`status` ASC, fa.`request_date` DESC
                LIMIT 0, 5";
         $result = $DB->query($query);
         if ($DB->numrows($result) == 0) {
            echo '<div class="line1" align="center">'.__('No form waiting for validation', 'formcreator').'</div>';
         } else {
            echo "<ul>";
            while ($form = $DB->fetch_assoc($result)) {
               echo '<li class="plugin_formcreator_answer">';
               echo ' <a class="plugin_formcreator_'.$form['status'].'" href="form_answer.form.php?id='.$form['id'].'">'.$form['name'].'</a>';
               echo '<span class="plugin_formcreator_date">'.Html::convDateTime($form['request_date']).'</span>';
               echo '</li>';
            }
            echo "</ul>";
            echo '<div align="center">';
            $criteria = 'criteria[0][field]=5&criteria[0][searchtype]=equals&criteria[0][value]=' . $_SESSION['glpiID'];
            $criteria.= "&criteria[1][link]=OR"
                      . "&criteria[1][field]=7"
                      . "&criteria[1][searchtype]=equals"
                      . "&criteria[1][value]=mygroups";

            echo '<a href="form_answer.php?' . $criteria . '">';
            echo __('All my forms (validator)', 'formcreator');
            echo '</a>';
            echo '</div>';
         }
         echo '</div>';
      }
   }

   /**
    * Display the Form end-user form to be filled
    *
    * @param  CommonGLPI   $item       Instance of the Form to be displayed
    *
    * @return Null                     Nothing, just display the form
    */
   public function displayUserForm(CommonGLPI $item) {
      global $CFG_GLPI, $DB;

      if (isset($_SESSION['formcreator']['data'])) {
         $data = $_SESSION['formcreator']['data'];
         unset($_SESSION['formcreator']['data']);
      } else {
         $data = null;
      }

      // Print css media
      echo Html::css("plugins/formcreator/css/print_form.css", ['media' => 'print']);

      // Display form
      echo "<form name='formcreator_form'".$item->getID()."' method='post' role='form' enctype='multipart/form-data'
               action='". $CFG_GLPI['root_doc']."/plugins/formcreator/front/form.form.php'
               class='formcreator_form form_horizontal'>";
      echo "<h1 class='form-title'>";
      echo $item->fields['name']."&nbsp;";
      echo "<img src='".FORMCREATOR_ROOTDOC."/pics/print.png' class='pointer print_button'
                 title='".__("Print this form", 'formcreator')."' onclick='window.print();'>";
      echo '</h1>';

      // Form Header
      if (!empty($item->fields['content'])) {
         echo '<div class="form_header">';
         echo html_entity_decode($item->fields['content']);
         echo '</div>';
      }
      // Get and display sections of the form
      $question      = new PluginFormcreatorQuestion();
      $questions     = [];

      $section_class = new PluginFormcreatorSection();
      $find_sections = $section_class->find('plugin_formcreator_forms_id = ' . $item->getID(), '`order` ASC');
      echo '<div class="form_section">';
      foreach ($find_sections as $section_line) {
         echo '<h2>' . $section_line['name'] . '</h2>';

         // Display all fields of the section
         $questions = $question->find('plugin_formcreator_sections_id = ' . $section_line['id'], '`order` ASC');
         foreach ($questions as $question_line) {
            if (isset($data['formcreator_field_' . $question_line['id']])) {
               // multiple choice question are saved as JSON and needs to be decoded
               $answer = (in_array($question_line['fieldtype'], ['checkboxes', 'multiselect']))
                       ? json_decode($data['formcreator_field_' . $question_line['id']])
                       : $data['formcreator_field_' . $question_line['id']];
            } else {
               $answer = null;
            }
            PluginFormcreatorFields::showField($question_line, $answer);
         }
      }
      echo Html::scriptBlock('$(function() {
         formcreatorShowFields();
      })');

      // Show validator selector
      if ($item->fields['validation_required'] > 0) {
         $table_form_validator = PluginFormcreatorForm_Validator::getTable();
         $validators = [0 => Dropdown::EMPTY_VALUE];

         // Groups
         if ($item->fields['validation_required'] == 2) {
            $query = "SELECT g.`id`, g.`completename`
                      FROM `glpi_groups` g
                      LEFT JOIN `$table_form_validator` fv ON fv.`items_id` = g.`id` AND fv.itemtype = 'Group'
                      WHERE fv.`plugin_formcreator_forms_id` = " . $this->getID();
            $result = $DB->query($query);
            while ($validator = $DB->fetch_assoc($result)) {
               $validators[$validator['id']] = $validator['completename'];
            }

            // Users
         } else {
            $query = "SELECT u.`id`, u.`name`, u.`realname`, u.`firstname`
                      FROM `glpi_users` u
                      LEFT JOIN `$table_form_validator` fv ON fv.`items_id` = u.`id` AND fv.itemtype = 'User'
                      WHERE fv.`plugin_formcreator_forms_id` = " . $this->getID();
            $result = $DB->query($query);
            while ($validator = $DB->fetch_assoc($result)) {
               $validators[$validator['id']] = formatUserName($validator['id'], $validator['name'], $validator['realname'], $validator['firstname']);
            }
         }

         echo '<div class="form-group required liste line' . (count($questions) + 1) % 2 . '" id="form-validator">';
         echo '<label>' . __('Choose a validator', 'formcreator') . ' <span class="red">*</span></label>';
         Dropdown::showFromArray('formcreator_validator', $validators);
         echo '</div>';
      }

      echo '</div>';

      // Display submit button
      echo '<div class="center">';
      echo '<input type="submit" name="submit_formcreator" class="submit_button" value="' . __('Send') . '" />';
      echo '</div>';

      echo '<input type="hidden" name="formcreator_form" value="' . $item->getID() . '">';
      echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
      echo '<input type="hidden" name="uuid" value="' .$item->fields['uuid'] . '">';
      echo '</form>';
   }

   /**
    * Prepare input data for adding the form
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
    */
   public function prepareInputForAdd($input) {
      // Decode (if already encoded) and encode strings to avoid problems with quotes
      foreach ($input as $key => $value) {
         if (!is_array($value)) {
            $input[$key] = plugin_formcreator_encode($value);
         }
      }

      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      // Control fields values :
      // - name is required
      if (isset($input['name'])) {
         if (empty($input['name'])) {
            Session::addMessageAfterRedirect(__('The name cannot be empty!', 'formcreator'), false, ERROR);
            return [];
         }
         $input['name'] = addslashes($input['name']);
      }

      if (isset($input['description'])) {
         $input['description'] = addslashes($input['description']);
      }

      if (isset($input['content'])) {
         $input['content'] = addslashes($input['content']);
      }

      if (!isset($input['requesttype'])) {
         $requestType = new RequestType();
         $requestType->getFromDBByCrit(['name' => ['LIKE' => 'Formcreator']]);
         $input['requesttype'] = $requestType->getID();
      }

      return $input;
   }

   /**
    * Actions done after the ADD of the item in the database
    *
    * @return void
   **/
   public function post_addItem() {
      $this->updateValidators();
      return true;
   }

   /**
    * Prepare input data for updating the form
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
   **/
   public function prepareInputForUpdate($input) {
      if (isset($input['access_rights'])
            || isset($_POST['massiveaction'])
            || isset($input['usage_count'])) {
         return $input;
      } else {
         $this->updateValidators();
         return $this->prepareInputForAdd($input);
      }
   }

   /**
    * Actions done after the PURGE of the item in the database
    *
    * @return void
   **/
   public function post_purgeItem() {
      $target = new PluginFormcreatorTarget();
      $target->deleteByCriteria(['plugin_formcreator_forms_id' => $this->getID()]);

      $section = new PluginFormcreatorSection();
      $section->deleteByCriteria(['plugin_formcreator_forms_id' => $this->getID()]);

      $form_validator = new PluginFormcreatorForm_Validator();
      $form_validator->deleteByCriteria(['plugin_formcreator_forms_id' => $this->getID()]);
   }

   /**
    * Save form validators
    *
    * @return void
    */
   private function updateValidators() {
      if (!isset($this->input['validation_required'])) {
         return true;
      }

      $form_validator = new PluginFormcreatorForm_Validator();
      $form_validator->deleteByCriteria(['plugin_formcreator_forms_id' => $this->getID()]);

      if ($this->input['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_USER
          && !empty($this->input['_validator_users'])
          || $this->input['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_GROUP
          && !empty($this->input['_validator_groups'])) {

         switch ($this->input['validation_required']) {
            case PluginFormcreatorForm_Validator::VALIDATION_USER:
               $validators = $this->input['_validator_users'];
               $validatorItemtype = 'User';
               break;
            case PluginFormcreatorForm_Validator::VALIDATION_GROUP:
               $validators = $this->input['_validator_groups'];
               $validatorItemtype = 'Group';
               break;
         }
         foreach ($validators as $itemId) {
            $form_validator = new PluginFormcreatorForm_Validator();
            $form_validator->add([
               'plugin_formcreator_forms_id' => $this->getID(),
               'itemtype'                    => $validatorItemtype,
               'items_id'                    => $itemId
            ]);
         }
      }
   }

   /**
    * Validates answers of a form and saves them in database
    *
    * @param array $input
    *
    * @return boolean true if the form is valid, false otherwise
    */
   public function saveForm($input) {
      $valid = true;
      $data  = [];

      // Prepare form fields for validation
      $question = new PluginFormcreatorQuestion();
      $found_questions = $question->getQuestionsFromForm($this->getID());
      foreach ($found_questions as $id => $question) {
         // If field was not post, it's value is empty
         if (isset($input['formcreator_field_' . $id])) {
            $data['formcreator_field_' . $id] = is_array($input['formcreator_field_' . $id])
                           ? json_encode($input['formcreator_field_' . $id], JSON_UNESCAPED_UNICODE)
                           : $input['formcreator_field_' . $id];

            // Replace "," by "." if field is a float field and remove spaces
            if ($question->getField('fieldtype') == 'float') {
               $data['formcreator_field_' . $id] = str_replace(',', '.', $data['formcreator_field_' . $id]);
               $data['formcreator_field_' . $id] = str_replace(' ', '', $data['formcreator_field_' . $id]);
            }
            unset($input['formcreator_field_' . $id]);
         } else {
            $data['formcreator_field_' . $id] = '';
         }
      }

      // Validate form fields
      foreach ($found_questions as $id => $question) {
         if (!($obj = PluginFormcreatorFields::getFieldInstance($question->getField('fieldtype'), $question, $data))) {
            $valid = false;
            break;
         }
         if (PluginFormcreatorFields::isVisible($id, $data) && !$obj->isValid($data['formcreator_field_' . $id])) {
            $valid = false;
            break;
         }
      }
      if (isset($input) && is_array($input)) {
         $data = $data + $input;
      }

      // Check required_validator
      if ($this->fields['validation_required'] && empty($data['formcreator_validator'])) {
         Session::addMessageAfterRedirect(__('You must select validator !', 'formcreator'), false, ERROR);
         $valid = false;
      }

      // If not valid back to form
      if (!$valid) {
         foreach ($data as $key => $value) {
            if (is_array($value)) {
               foreach ($value as $key2 => $value2) {
                  $data[$key][$key2] = plugin_formcreator_encode($value2);
               }
            } else if (is_array(json_decode($value))) {
               $value = json_decode($value);
               foreach ($value as $key2 => $value2) {
                  $value[$key2] = plugin_formcreator_encode($value2);
               }
               $data[$key] = json_encode($value);
            } else {
               $data[$key] = plugin_formcreator_encode($value);
            }
         }

         $_SESSION['formcreator']['data'] = $data;
         // Save form
      } else {
         $formanswer = new PluginFormcreatorForm_Answer();
         $formanswer->saveAnswers($data);
      }

      return $valid;
   }

   public function increaseUsageCount() {
      // Increase usage count of the form
      $this->update([
            'id' => $this->getID(),
            'usage_count' => $this->getField('usage_count') + 1,
      ]);
   }

   public  function getByQuestionId($questionId) {
      $table_sections = PluginFormcreatorSection::getTable();
      $table_questions = PluginFormcreatorQuestion::getTable();
      if (!method_exists($this, 'getFromDBByRequest')) {
         $this->getFromDBByQuery(
            "WHERE `id` = (
                SELECT `plugin_formcreator_forms_id` FROM `$table_sections`
                INNER JOIN `$table_questions`
                   ON `$table_questions`.`plugin_formcreator_sections_id` = `$table_sections`.`id`
                WHERE `$table_questions`.`id` = '$questionId'
            )"
         );
      } else {
         $this->getFromDBByRequest([
            'INNER JOIN' => [
               PluginFormcreatorSection::getTable() => [
                  'FKEY' => [
                     PluginFormcreatorForm::getTable()    => 'id',
                     PluginFormcreatorSection::getTable() => PluginFormcreatorSection::getForeignKeyField(),
                  ]
               ],
               PluginFormcreatorQuestion::getTable() => [
                  'FKEY' => [
                     PluginFormcreatorQuestion::getTable() => PluginFormcreatorSection::getForeignKeyField(),
                     PluginFormcreatorSection::getTable()  => 'id'
                  ]
               ]
            ],
            'WHERE' => [
               PluginFormcreatorQuestion::getTable() . '.id' => $questionId,
            ]
         ]);
      }
   }

   /**
    * Duplicate a from. Execute duplicate action for massive action.
    *
    * NB: Queries are made directly in SQL without GLPI's API to avoid controls made by Add(), prepareInputForAdd(), etc.
    *
    * @return Boolean true if success, false otherwise.
    */
   public function duplicate() {
      $target              = new PluginFormcreatorTarget();
      $target_ticket       = new PluginFormcreatorTargetTicket();
      $target_change       = new PluginFormcreatorTargetChange();
      $target_ticket_actor = new PluginFormcreatorTargetTicket_Actor();
      $target_change_actor = new PluginFormcreatorTargetChange_Actor();
      $form_section        = new PluginFormcreatorSection();
      $section_question    = new PluginFormcreatorQuestion();
      $question_condition  = new PluginFormcreatorQuestion_Condition();
      $form_validator      = new PluginFormcreatorForm_Validator();
      $form_profile        = new PluginFormcreatorForm_Profile();
      $tab_questions       = [];

      // Form data
      $form_datas              = $this->fields;
      $form_datas['name']     .= ' [' . __('Duplicate', 'formcreator') . ']';
      $form_datas['is_active'] = 0;

      unset($form_datas['id'], $form_datas['uuid']);

      $old_form_id             = $this->getID();
      $new_form_id             = $this->add($form_datas);
      if ($new_form_id === false) {
         return false;
      }

      // Form profiles
      $rows = $form_profile->find("`plugin_formcreator_forms_id` = '$old_form_id'");
      foreach ($rows as $row) {
         unset($row['id'],
               $row['uuid']);
         $row['plugin_formcreator_forms_id'] = $new_form_id;
         if (!$form_profile->add($row)) {
            return false;
         }
      }

      // Form validators
      $rows = $form_validator->find("`plugin_formcreator_forms_id` = '$old_form_id'");
      foreach ($rows as $row) {
         unset($row['id'],
               $row['uuid']);
         $row['plugin_formcreator_forms_id'] = $new_form_id;
         if (!$form_validator->add($row)) {
            return false;
         }
      }

      // Form sections
      $sectionRows = $form_section->find("`plugin_formcreator_forms_id` = '$old_form_id'");
      foreach ($sectionRows as $sections_id => $sectionRow) {
         unset($sectionRow['id'],
               $sectionRow['uuid']);
         $sectionRow['plugin_formcreator_forms_id'] = $new_form_id;
         if (!$new_sections_id = $form_section->add($sectionRow)) {
            return false;
         }

         // Form questions
         $questionRows = $section_question->find("`plugin_formcreator_sections_id` = '$sections_id'");
         foreach ($questionRows as $questions_id => $questionRow) {
            unset($questionRow['id'],
                  $questionRow['uuid']);
            $questionRow['plugin_formcreator_sections_id'] = $new_sections_id;
            if (!$new_questions_id = $section_question->add($questionRow)) {
               return false;
            }

            // Map old question ID to new question ID
            $tab_questions[$questions_id] = $new_questions_id;
         }
      }

      // Form questions conditions
      $questionIds = implode("', '", array_keys($tab_questions));
      $rows = $question_condition->find("`plugin_formcreator_questions_id` IN  ('$questionIds')");
      foreach ($rows as $row) {
         unset($row['id'],
               $row['uuid']);
         $row['show_field'] = $tab_questions[$row['show_field']];
         $row['plugin_formcreator_questions_id'] = $tab_questions[$row['plugin_formcreator_questions_id']];
         if (!$question_condition->add($row)) {
            return false;
         }
      }

      // Form targets
      $rows = $target->find("`plugin_formcreator_forms_id` = '$old_form_id'");
      foreach ($rows as $target_values) {
         unset($target_values['id'],
               $target_values['uuid']);
         $target_values['plugin_formcreator_forms_id'] = $new_form_id;

         if (!$target->add($target_values)) {
            return false;
         }

         $new_target_item_id = $target->getField('items_id');
         switch ($target_values['itemtype']) {
            case PluginFormcreatorTargetTicket::class:
               // Get the original target ticket
               if (!$target_ticket->getFromDB($target_values['items_id'])) {
                  return false;
               }

               // Update the target ticket created while cloning the target
               $update_target_ticket = $target_ticket->fields;
               $update_target_ticket['id'] = $new_target_item_id;
               unset($update_target_ticket['uuid']);
               foreach ($tab_questions as $id => $value) {
                  $update_target_ticket['name']    = str_replace('##question_' . $id . '##', '##question_' . $value . '##', $update_target_ticket['name']);
                  $update_target_ticket['name']    = str_replace('##answer_' . $id . '##', '##answer_' . $value . '##', $update_target_ticket['name']);
                  $update_target_ticket['comment'] = str_replace('##question_' . $id . '##', '##question_' . $value . '##', $update_target_ticket['comment']);
                  $update_target_ticket['comment'] = str_replace('##answer_' . $id . '##', '##answer_' . $value . '##', $update_target_ticket['comment']);
               }

               // update time to resolve rule
               if ($update_target_ticket['due_date_rule'] == 'answer'
                   || $update_target_ticket['due_date_rule'] == 'calcul') {
                  $update_target_ticket['due_date_question'] = $tab_questions[$update_target_ticket['due_date_question']];
               }

               // update urgency rule
               if ($update_target_ticket['urgency_rule'] == 'answer') {
                  $update_target_ticket['urgency_question'] = $tab_questions[$update_target_ticket['urgency_question']];
               }

               // update destination entity
               if ($update_target_ticket['destination_entity'] == 'user'
                   || $update_target_ticket['destination_entity'] == 'entity') {
                  $update_target_ticket['destination_entity_value'] = $tab_questions[$update_target_ticket['destination_entity_value']];
               }

               //update category
               if ($update_target_ticket['category_rule'] == 'answer') {
                  $update_target_ticket['category_question'] = $tab_questions[$update_target_ticket['category_question']];
               }

               //update location
               if ($update_target_ticket['location_rule'] == 'answer') {
                  $update_target_ticket['location_question'] = $tab_questions[$update_target_ticket['location_question']];
               }

               $new_target_ticket = new PluginFormcreatorTargetTicket();
               $update_target_ticket['title'] = Toolbox::addslashes_deep($update_target_ticket['name']);
               $update_target_ticket['comment'] = Toolbox::addslashes_deep($update_target_ticket['comment']);
               if (!$new_target_ticket->update($update_target_ticket)) {
                  return false;
               }
               break;

            case PluginFormcreatorTargetChange::class:
               // Get the original target change
               if (!$target_change->getFromDB($target_values['items_id'])) {
                  return false;
               }

               // Update the target change created while cloning the target
               $update_target_change = $target_change->fields;
               $update_target_change['id'] = $new_target_item_id;
               unset($update_target_change['uuid']);
               foreach ($tab_questions as $id => $value) {
                  $changeFields = [
                     'name',
                     'comment',
                     'impactcontent',
                     'controlistcontent',
                     'rolloutplancontent',
                     'backoutplancontent',
                     'checklistcontent'
                  ];
                  foreach ($changeFields as $changeField) {
                     $update_target_change[$changeField] = str_replace(
                        '##question_' . $id . '##',
                        '##question_' . $value . '##',
                        $update_target_change[$changeField]
                     );
                     $update_target_change[$changeField] = str_replace(
                        '##answer_' . $id . '##',
                        '##answer_' . $value . '##',
                        $update_target_change[$changeField]
                     );
                  }
               }

               // update time to resolve rule
               if ($update_target_change['due_date_rule'] == 'answer'
                   || $update_target_change['due_date_rule'] == 'calcul') {
                  $update_target_change['due_date_question'] = $tab_questions[$update_target_change['due_date_question']];
               }

               // update urgency rule
               if ($update_target_change['urgency_rule'] == 'answer') {
                  $update_target_change['urgency_question'] = $tab_questions[$update_target_change['urgency_question']];
               }

               // update destination entity
               if ($update_target_change['destination_entity'] == 'user'
                   || $update_target_change['destination_entity'] == 'entity') {
                  $update_target_change['destination_entity_value'] = $tab_questions[$update_target_change['destination_entity_value']];
               }

               //update category
               if ($update_target_change['category_rule'] == 'answer') {
                  $update_target_change['category_question'] = $tab_questions[$update_target_change['category_question']];
               }

               $new_target_change = new PluginFormcreatorTargetChange();
               $update_target_change['title'] = Toolbox::addslashes_deep($update_target_change['name']);
               $update_target_change['comment'] = Toolbox::addslashes_deep($update_target_change['comment']);
               if (!$new_target_change->update($update_target_change)) {
                  return false;
               }
               break;
         }

         switch ($target_values['itemtype']) {
            case PluginFormcreatorTargetTicket::class:
               // Drop default actors
               $target_ticket_actor->deleteByCriteria([
                  'plugin_formcreator_targettickets_id' => $new_target_item_id
               ]);

               // Form target tickets actors
               $rows = $target_ticket_actor->find("`plugin_formcreator_targettickets_id` = '{$target_values['items_id']}'");
               foreach ($rows as $row) {
                  unset($row['id'],
                        $row['uuid']);
                  $row['plugin_formcreator_targettickets_id'] = $new_target_item_id;
                  if (!$target_ticket_actor->add($row)) {
                     return false;
                  }
               }
               break;

            case PluginFormcreatorTargetChange::class:
               // Drop default actors
               $target_ticket_actor->deleteByCriteria([
                  'plugin_formcreator_targetchanges_id' => $new_target_item_id
               ]);

               // Form target change actors
               $rows = $target_change_actor->find("`plugin_formcreator_targetchanges_id` = '{$target_values['items_id']}'");
               foreach ($rows as $row) {
                  unset($row['id'],
                        $row['uuid']);
                  $row['plugin_formcreator_targetchanges_id'] = $new_target_item_id;
                  if (!$target_change_actor->add($row)) {
                     return false;
                  }
               }
               break;
         }
      }

      return true;
   }

   /**
    * Transfer a form to another entity. Execute transfer action for massive action.
    *
    * @return Boolean true if success, false otherwise.
    */
   public function transfer($entity) {
      global $DB;

      $query = "UPDATE `glpi_plugin_formcreator_forms` SET
                   `entities_id` = " . $entity . "
                WHERE `id` = " . $this->fields['id'];
      $DB->query($query);
      return true;
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   public static function showMassiveActionsSubForm(MassiveAction $ma) {
      switch ($ma->getAction()) {
         case 'Transfert':
            Entity::dropdown([
               'name' => 'entities_id',
            ]);
            echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * Execute massive action for PluginFormcreatorFrom
    *
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,  array $ids) {
      switch ($ma->getAction()) {
         case 'Duplicate' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id) && $item->duplicate()) {
                  Session::addMessageAfterRedirect(sprintf(__('Form duplicated: %s', 'formcreator'), $item->getName()));
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            return;
         case 'Transfert' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id) && $item->transfer($ma->POST['entities_id'])) {
                  Session::addMessageAfterRedirect(sprintf(__('Form Transfered: %s', 'formcreator'), $item->getName()));
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            return;
         case 'Export' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id)) {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            echo "<br>";
            echo "<div class='center'>";
            echo "<a href='#' onclick='window.history.back()'>".__("Back")."</a>";
            echo "</div>";

            $listOfId = ['plugin_formcreator_forms_id' => array_values($ids)];
            Html::redirect(FORMCREATOR_ROOTDOC."/front/export.php?".Toolbox::append_params($listOfId));
            header("Content-disposition:attachment filename=\"test\"");
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

   public static function countAvailableForm() {
      global $DB;

      $form_table = getTableForItemType('PluginFormcreatorForm');
      $table_fp   = getTableForItemType('PluginFormcreatorForm_Profile');
      $nb         = 0;

      if ($DB->tableExists($form_table)
          && $DB->tableExists($table_fp)
          && isset($_SESSION['glpiactiveprofile']['id'])) {
         $where      = getEntitiesRestrictRequest( "", $form_table, "", "", true, false);
         $query      = "SELECT COUNT($form_table.id)
                        FROM $form_table
                        WHERE $form_table.`is_active` = 1
                        AND $form_table.`is_deleted` = 0
                        AND ($form_table.`language` = '{$_SESSION['glpilanguage']}'
                             OR $form_table.`language` IN ('0', '', NULL))
                        AND $where
                        AND ($form_table.`access_rights` != " . PluginFormcreatorForm::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
                           SELECT plugin_formcreator_forms_id
                           FROM $table_fp
                           WHERE profiles_id = " . $_SESSION['glpiactiveprofile']['id']."))";
         if ($result = $DB->query($query)) {
            list($nb) = $DB->fetch_array($result);
         }
      }

      return $nb;
   }

   /**
    * Export in an array all the data of the current instanciated form
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   function export($remove_uuid = false) {
      if (!$this->getID()) {
         return false;
      }

      $form           = $this->fields;
      $form_section   = new PluginFormcreatorSection;
      $form_target    = new PluginFormcreatorTarget;
      $form_validator = new PluginFormcreatorForm_Validator;
      $form_profile   = new PluginFormcreatorForm_Profile;

      // replace dropdown ids
      if ($form['plugin_formcreator_categories_id'] > 0) {
         $form['_plugin_formcreator_category']
            = Dropdown::getDropdownName('glpi_plugin_formcreator_categories',
                                        $form['plugin_formcreator_categories_id']);
      }
      if ($form['entities_id'] > 0) {
         $form['_entity']
            = Dropdown::getDropdownName('glpi_entities',
                                        $form['entities_id']);
      }

      // remove non needed keys
      unset($form['id'],
            $form['plugin_formcreator_categories_id'],
            $form['entities_id'],
            $form['usage_count']);

      // get sections
      $form['_sections'] = [];
      $all_sections = $form_section->find("plugin_formcreator_forms_id = ".$this->getID());
      foreach ($all_sections as $section) {
         $form_section->getFromDB($section['id']);
         $form['_sections'][] = $form_section->export($remove_uuid);
      }

      // get validators
      $form['_validators'] = [];
      $all_validators = $form_validator->find("plugin_formcreator_forms_id = ".$this->getID());
      foreach ($all_validators as $validator) {
         $form_validator->getFromDB($validator['id']);
         $form['_validators'][] = $form_validator->export($remove_uuid);
      }

      // get targets
      $form['_targets'] = [];
      $all_target = $form_target->find("plugin_formcreator_forms_id = ".$this->getID());
      foreach ($all_target as $target) {
         $form_target->getFromDB($target['id']);
         $form['_targets'][] = $form_target->export($remove_uuid);
      }

      // get profiles
      $form['_profiles'] = [];
      $all_profiles = $form_profile->find("plugin_formcreator_forms_id = ".$this->getID());
      foreach ($all_profiles as $profile) {
         $form_profile->getFromDB($profile['id']);
         $form['_profiles'][] = $form_profile->export($remove_uuid);
      }

      if ($remove_uuid) {
         $form['uuid'] = '';
      }

      return $form;
   }

   /**
    * Display an html form to upload a json with forms data
    */
   public function showImportForm() {
      $documentType = new DocumentType();
      $jsonTypeExists = $documentType->getFromDBByCrit(['ext' => 'json']);
      $jsonTypeEnabled = $jsonTypeExists && $documentType->getField('is_uploadable');
      $canAddType = $documentType->canCreate();
      $canUpdateType = $documentType->canUpdate();

      if (! ($jsonTypeExists && $jsonTypeEnabled)) {
         if (!$jsonTypeExists) {
            $message = __('Upload of JSON files not allowed.', 'formcreator');
            if ($canAddType) {
               $destination = PluginFormcreatorForm::getFormURL();
               $message .= __('You may allow JSON files right now.', 'formcreator');
               $button = Html::submit(_x('button', 'Create', 'formcreator'), array('name' => 'filetype_create'));
            } else {
               $destination = PluginFormcreatorForm::getSearchURL();
               $message .= __('Please contact your GLPI administrator.', 'formcreator');
               $button = Html::submit(_x('button', 'Back', 'formcreator'), array('name' => 'filetype_back'));
            }
         } else {
            $message = __('Upload of JSON files not enabled.', 'formcreator');
            if ($canUpdateType) {
               $destination = PluginFormcreatorForm::getFormURL();
               $message .= __('You may enable JSON files right now.', 'formcreator');
               $button = Html::submit(_x('button', 'Enable', 'formcreator'), array('name' => 'filetype_enable'));
            } else {
               $message .= __('You may enable JSON files right now.', 'formcreator');
               $message .= __('Please contact your GLPI administrator.', 'formcreator');
               $button = Html::submit(_x('button', 'Back', 'formcreator'), array('name' => 'filetype_back'));
            }
         }
         echo '<div class="spaced" id="tabsbody">';
         echo "<form name='form' method='post' action='". $destination."'>";
         echo '<table class="tab_cadre_fixe" id="mainformtable">';
         echo '<tr class="headerRow">';
         echo '<th>';
         echo __('Import forms');
         echo '</th>';
         echo '</tr>';
         echo '<tr>';
         echo '<td class="center">';
         echo $message;
         echo '</td>';
         echo '</tr>';
         echo '<td class="center">';
         echo $button;
         echo '</td>';
         echo '</tr>';
         echo '<tr>';
         echo '</table>';
         echo '</div>';
         Html::closeForm();
         echo '</div>';
      } else {
         echo "<form name='form' method='post' action='".
               PluginFormcreatorForm::getFormURL().
               "?import_send=1' enctype=\"multipart/form-data\">";

         echo "<div class='spaced' id='tabsbody'>";
         echo "<table class='tab_cadre_fixe' id='mainformtable'>";
         echo "<tr class='headerRow'>";
         echo "<th>";
         echo __("Import forms");
         echo "</th>";
         echo "</tr>";
         echo "<tr>";
         echo "<td>";
         echo Html::file(['name' => 'json_file']);
         echo "</td>";
         echo "</tr>";
         echo "<td class='center'>";
         echo Html::submit(_x('button', 'Send'), ['name' => 'import_send']);
         echo "</td>";
         echo "</tr>";
         echo "<tr>";
         echo "</table>";
         echo "</div>";

         Html::closeForm();
      }
   }

   /**
    * Process import of json file(s) sended by the submit of self::showImportForm
    * @param  array  $params GET/POST data that need to contain the filename(s) in _json_file key
    */
   public function importJson($params = []) {
      // parse json file(s)
      foreach ($params['_json_file'] as $filename) {
         if (!$json = file_get_contents(GLPI_TMP_DIR."/".$filename)) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file is empty"));
            continue;
         }
         if (!$forms_toimport = json_decode($json, true)) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file seems corrupt"));
            continue;
         }
         if (!isset($forms_toimport['forms'])) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file seems corrupt"));
            continue;
         }

         foreach ($forms_toimport['forms'] as $form) {
            self::import($form);
         }

         Session::addMessageAfterRedirect(sprintf(__("Forms successfully imported from %s", "formcreator"),
                                                  $filename));
      }
   }

   /**
    * Import a form into the db
    * @see PluginFormcreatorForm::importJson
    *
    * @param  array   $form the form data (match the form table)
    * @return integer the form's id
    */
   public static function import($form = []) {
      $form_obj = new self;
      $entity   = new Entity;
      $form_cat = new PluginFormcreatorCategory;

      // retrieve foreign keys
      if (!isset($form['_entity'])
          || !$form['entities_id']
                  = plugin_formcreator_getFromDBByField($entity,
                                                        'completename',
                                                        $form['_entity'])) {
         $form['entities_id'] = $_SESSION['glpiactive_entity'];
      }
      if (!isset($form['_plugin_formcreator_categories_id'])
          || !$form['_plugin_formcreator_categories_id']
            = plugin_formcreator_getFromDBByField($form_cat,
                                                  'completename',
                                                  $form['_plugin_formcreator_category'])) {
         $form['plugin_formcreator_categories_id'] = 0;
      }

      // retrieve form by its uuid
      if ($forms_id = plugin_formcreator_getFromDBByField($form_obj,
                                                          'uuid',
                                                          $form['uuid'])) {
         // add id key
         $form['id'] = $forms_id;

         // update existing form
         $form_obj->update($form);
      } else {
         // create new form
         $forms_id = $form_obj->add($form);
      }

      // import restrictions
      if ($forms_id) {
         // Delete all previous restrictions
         $FormProfile = new PluginFormcreatorForm_Profile();
         $FormProfile->deleteByCriteria([
            'plugin_formcreator_forms_id' => $forms_id,
         ]);

         // Import updates
         if (isset($form['_profiles'])) {
            foreach ($form['_profiles'] as $formProfile) {
               PluginFormcreatorForm_Profile::import($forms_id, $formProfile);
            }
         }
      }

      // import form's sections
      if ($forms_id
          && isset($form['_sections'])) {
         foreach ($form['_sections'] as $section) {
            PluginFormcreatorSection::import($forms_id, $section);
         }
      }
      // Save all question conditions stored in memory
      PluginFormcreatorQuestion_Condition::import(0, [], false);

      // import form's validators
      if ($forms_id
          && isset($form['_validators'])) {
         foreach ($form['_validators'] as $validator) {
            PluginFormcreatorForm_Validator::import($forms_id, $validator);
         }
      }

      // import form's targets
      if ($forms_id
          && isset($form['_targets'])) {
         foreach ($form['_targets'] as $target) {
            PluginFormcreatorTarget::import($forms_id, $target);
         }
      }
      // import form's ticket relations
      PluginFormcreatorItem_TargetTicket::import(0, [], false);

      return $forms_id;
   }

   public function createDocumentType() {
      $documentType = new DocumentType();
      $success = $documentType->add([
         'name'            => 'JSON file',
         'ext'             => 'json',
         'icon'            => '',
         'is_uploadable'   => '1'
      ]);
      if (!$success) {
         Session::addMessageAfterRedirect(__('Failed to create JSON document type', 'formcreator'));
      }
   }

   public function enableDocumentType() {
      $documentType = new DocumentType();
      if (!$documentType->getFromDBByCrit(['ext' => 'json'])) {
         Session::addMessageAfterRedirect(__('JSON document type not found', 'formcreator'));
      } else {
         $success = $documentType->update([
            'id'              => $documentType->getID(),
            'is_uploadable'   => '1'
         ]);
         if (!$success) {
            Session::addMessageAfterRedirect(__('Failed to update JSON document type', 'formcreator'));
         }
      }
   }

   /**
    * show list of available forms
    */
   public function showForCentral() {
      global $DB, $CFG_GLPI;

      // Define tables
      $cat_table  = getTableForItemType('PluginFormcreatorCategory');
      $form_table = getTableForItemType('PluginFormcreatorForm');
      $table_fp   = getTableForItemType('PluginFormcreatorForm_Profile');
      $where      = getEntitiesRestrictRequest("", $form_table, "", "", true, false);
      $language   = $_SESSION['glpilanguage'];

      // Show form whithout table
      $query_forms = "SELECT $form_table.id, $form_table.name, $form_table.description
                      FROM $form_table
                      WHERE $form_table.`plugin_formcreator_categories_id` = 0
                      AND $form_table.`is_active` = 1
                      AND $form_table.`is_deleted` = 0
                      AND $form_table.`helpdesk_home` = 1
                      AND ($form_table.`language` = '$language' OR $form_table.`language` IN (0, '', NULL))
                      AND $where
                      AND (`access_rights` != " . PluginFormcreatorForm::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
                         SELECT plugin_formcreator_forms_id
                         FROM $table_fp
                         WHERE profiles_id = " . $_SESSION['glpiactiveprofile']['id'] . "))
                      ORDER BY $form_table.name ASC";
      $result_forms = $DB->query($query_forms);

      // Show categories which have at least one form user can access
      $query  = "SELECT $cat_table.`name`, $cat_table.`id`
                 FROM $cat_table
                 WHERE 0 < (
                 SELECT COUNT($form_table.id)
                 FROM $form_table
                 WHERE $form_table.`plugin_formcreator_categories_id` = $cat_table.`id`
                 AND $form_table.`is_active` = 1
                 AND $form_table.`is_deleted` = 0
                 AND $form_table.`helpdesk_home` = 1
                 AND ($form_table.`language` = '$language' OR $form_table.`language` IN (0, '', NULL))
                 AND $where
                 AND ($form_table.`access_rights` != " . PluginFormcreatorForm::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
                    SELECT plugin_formcreator_forms_id
                    FROM $table_fp
                    WHERE profiles_id = " . $_SESSION['glpiactiveprofile']['id'] . "))
                 )
                 ORDER BY $cat_table.`name` ASC";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0 || $DB->numrows($result_forms) > 0) {
         echo '<table class="tab_cadrehov homepage_forms_container" id="homepage_forms_container">';
         echo '<tr class="noHover">';
         echo '<th><a href="../plugins/formcreator/front/formlist.php">' . _n('Form', 'Forms', 2, 'formcreator') . '</a></th>';
         echo '</tr>';

         if ($DB->numrows($result_forms) > 0) {
            echo '<tr class="noHover"><th>' . __('Forms without category', 'formcreator') . '</th></tr>';
            $i = 0;
            while ($form = $DB->fetch_array($result_forms)) {
               $i++;
               echo '<tr class="line' . ($i % 2) . ' tab_bg_' . ($i % 2 +1) . '">';
               echo '<td>';
               echo '<img src="' . $CFG_GLPI['root_doc'] . '/pics/plus.png" alt="+" title=""
                   onclick="showDescription(' . $form['id'] . ', this)" align="absmiddle" style="cursor: pointer">';
               echo '&nbsp;';
               echo '<a href="' . $CFG_GLPI['root_doc']
               . '/plugins/formcreator/front/formdisplay.php?id=' . $form['id'] . '"
                  title="' . plugin_formcreator_encode($form['description']) . '">'
                              . $form['name']
                              . '</a></td>';
                              echo '</tr>';
                              echo '<tr id="desc' . $form['id'] . '" class="line' . ($i % 2) . ' form_description">';
                              echo '<td><div>' . $form['description'] . '&nbsp;</div></td>';
                              echo '</tr>';
            }
         }

         if ($DB->numrows($result) > 0) {
            // For each categories, show the list of forms the user can fill
            $i = 0;
            while ($category = $DB->fetch_array($result)) {
               $categoryId = $category['id'];
               echo '<tr class="noHover"><th>' . $category['name'] . '</th></tr>';
               $query_forms = "SELECT $form_table.id, $form_table.name, $form_table.description
               FROM $form_table
               WHERE $form_table.`plugin_formcreator_categories_id` = '$categoryId'
               AND $form_table.`is_active` = 1
               AND $form_table.`is_deleted` = 0
               AND $form_table.`helpdesk_home` = 1
               AND ($form_table.`language` = '$language' OR $form_table.`language` IN (0, '', NULL))
               AND $where
               AND (`access_rights` != " . PluginFormcreatorForm::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
               SELECT plugin_formcreator_forms_id
               FROM $table_fp
               WHERE profiles_id = " . (int) $_SESSION['glpiactiveprofile']['id'] . "))
               ORDER BY $form_table.name ASC";
               $result_forms = $DB->query($query_forms);
               $i = 0;
               while ($form = $DB->fetch_array($result_forms)) {
                  $i++;
                  echo '<tr class="line' . ($i % 2) . ' tab_bg_' . ($i % 2 +1) . '">';
                  echo '<td>';
                  echo '<img src="' . $CFG_GLPI['root_doc'] . '/pics/plus.png" alt="+" title=""
                      onclick="showDescription(' . $form['id'] . ', this)" align="absmiddle" style="cursor: pointer">';
                  echo '&nbsp;';
                  echo '<a href="' . $CFG_GLPI['root_doc']
                  . '/plugins/formcreator/front/formdisplay.php?id=' . $form['id'] . '"
                     title="' . plugin_formcreator_encode($form['description']) . '">'
                                 . $form['name']
                                 . '</a></td>';
                                 echo '</tr>';
                                 echo '<tr id="desc' . $form['id'] . '" class="line' . ($i % 2) . ' form_description">';
                                 echo '<td><div>' . $form['description'] . '&nbsp;</div></td>';
                                 echo '</tr>';
               }
            }
         }
         echo '</table>';
         echo '<br />';
         echo '<script type="text/javascript">
            function showDescription(id, img){
               if(img.alt == "+") {
                 img.alt = "-";
                 img.src = "' . $CFG_GLPI['root_doc'] . '/pics/moins.png";
                 document.getElementById("desc" + id).style.display = "table-row";
               } else {
                 img.alt = "+";
                 img.src = "' . $CFG_GLPI['root_doc'] . '/pics/plus.png";
                 document.getElementById("desc" + id).style.display = "none";
               }
            }
         </script>';
      }
   }

   static function getInterface() {
      if (isset($_SESSION['glpiactiveprofile']['interface'])
            && ($_SESSION['glpiactiveprofile']['interface'] == 'helpdesk')) {
         if (plugin_formcreator_replaceHelpdesk()) {
            return 'servicecatalog';
         } else {
            return 'self-service';
         }

      } else if (!empty($_SESSION['glpiactiveprofile'])) {
         return 'central';
      }

      return 'public';
   }

   static function header() {
      switch (self::getInterface()) {
         case "servicecatalog";
            return PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));
         case "self-service";
            return Html::helpHeader(__('Form list', 'formcreator'), $_SERVER['PHP_SELF']);
         case "central";
            return Html::header(
               __('Form Creator', 'formcreator'),
               $_SERVER['PHP_SELF'],
               'helpdesk',
               'PluginFormcreatorFormlist'
            );
         case "public";
         default:
            return Html::nullHeader(__('Form Creator', 'formcreator'), $_SERVER['PHP_SELF']);
      }
   }

   static function footer() {
      switch (self::getInterface()) {
         case "servicecatalog";
            return PluginFormcreatorWizard::footer();
         case "self-service";
            return Html::helpFooter();
         case "central";
            return Html::footer();
         case "public";
         default:
            return Html::nullFooter();
      }
   }
}
