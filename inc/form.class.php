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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

use GlpiPlugin\Formcreator\Exception\ImportFailureException;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorForm extends CommonDBTM implements
PluginFormcreatorExportableInterface,
PluginFormcreatorDuplicatableInterface,
PluginFormcreatorConditionnableInterface
{
   use PluginFormcreatorConditionnable;
   use PluginFormcreatorExportable;

   static $rightname = 'entity';

   public $dohistory         = true;

   const ACCESS_PUBLIC       = 0;
   const ACCESS_PRIVATE      = 1;
   const ACCESS_RESTRICTED   = 2;

   const VALIDATION_NONE     = 0;
   const VALIDATION_USER     = 1;
   const VALIDATION_GROUP    = 2;

   public static function getEnumAccessType() {
      return [
         self::ACCESS_PUBLIC     => __('Public access', 'formcreator'),
         self::ACCESS_PRIVATE    => __('Private access', 'formcreator'),
         self::ACCESS_RESTRICTED => __('Restricted access', 'formcreator'),
      ];
   }

   public static function canCreate() {
      return Session::haveRight('entity', UPDATE);
   }

   public static function canView() {
      return true;
   }

   public static function canDelete() {
      return Session::haveRight('entity', UPDATE);
   }

   public static function canPurge() {
      return Session::haveRight('entity', UPDATE);
   }

   public function canPurgeItem() {
      $DbUtil = new DbUtils();

      $criteria = [
         PluginFormcreatorForm::getForeignKeyField() => $this->getID(),
      ];
      if ($DbUtil->countElementsInTable(PluginFormcreatorFormAnswer::getTable(), $criteria) > 0) {
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
      $menu  = parent::getMenuContent();
      $menu['icon'] = 'fas fa-edit';
      $validation_image = '<img src="' . FORMCREATOR_ROOTDOC . '/pics/check.png"
                                title="' . __('Forms waiting for validation', 'formcreator') . '">';
      $import_image     = '<img src="' . FORMCREATOR_ROOTDOC . '/pics/import.png"
                                title="' . __('Import forms', 'formcreator') . '">';
      $menu['links']['search']          = PluginFormcreatorFormList::getSearchURL(false);
      $menu['links']['config']          = PluginFormcreatorForm::getSearchURL(false);
      $menu['links'][$validation_image] = PluginFormcreatorFormAnswer::getSearchURL(false);
      $menu['links'][$import_image]     = PluginFormcreatorForm::getFormURL(false)."?import_form=1";

      return $menu;
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

      $tab[] = [
         'id'                 => '31',
         'table'              => $this->getTable(),
         'field'              => 'icon',
         'name'               => __('Icon', 'formcreator'),
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '32',
         'table'              => $this->getTable(),
         'field'              => 'icon_color',
         'name'               => __('Icon color', 'formcreator'),
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '33',
         'table'              => $this->getTable(),
         'field'              => 'background_color',
         'name'               => __('Background color', 'formcreator'),
         'massiveaction'      => false
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
    */
   public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
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
      if (!is_array($values)) {
         $values = [$field => $values];
      }

      if (isAPI()) {
         return parent::getSpecificValueToDisplay($field, $values, $options);
      }

      switch ($field) {
         case 'is_active':
            if ($values[$field] == 0) {
               $class = "plugin-forcreator-inactive";
               $title =  __('Inactive');
            } else {
               $class = "plugin-forcreator-active";
               $title =  __('Active');
            }
            $output = '<i class="fa fa-circle '
            . $class
            . '" aria-hidden="true" title="' . $title . '"></i>';
            $output = '<div style="text-align: center">' . $output . '</div>';
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
   public function showForm($ID, $options = []) {
      global $DB;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo '<tr class="tab_bg_1">';
      echo '<td width="20%"><strong>' . __('Name') . ' <span class="red">*</span></strong></td>';
      echo '<td width="30%"><input type="text" name="name" required="required" value="' . $this->fields["name"] . '" size="35"/></td>';
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
      echo '<td>' . __('Form icon', 'formcreator') . '</td>';
      echo '<td>';
      $icon = $this->fields['icon'] == '' ? 'fa fa-question-circle' : $this->fields['icon'];
      PluginFormcreatorCommon::showFontAwesomeDropdown('icon', ['value' => $icon]);
      $iconColor = $this->fields['icon_color'] == '' ? '#999999' : $this->fields['icon_color'];
      Html::showColorField('icon_color', ['value' => $iconColor]);
      echo '</td>';
      echo '<td>' . __('Background color', 'formcreator') . '</td>';
      echo '<td>';
      $tileColor = $this->fields['background_color'] == '' ? '#E7E7E7' : $this->fields['background_color'];
      Html::showColorField('background_color', ['value' => $tileColor]);
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
      echo '<td colspan="3">';
      echo Html::textarea([
         'name'    => 'content',
         'value'   => $this->fields["content"],
         'enable_richtext' => true,
         'display' => false,
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_2">';
      echo '<td>' . __('Need to be validate?', 'formcreator') . '</td>';
      echo '<td class="validators_bloc">';

      Dropdown::showFromArray('validation_required', [
         self::VALIDATION_NONE  => Dropdown::EMPTY_VALUE,
         self::VALIDATION_USER  => User::getTypeName(1),
         self::VALIDATION_GROUP => Group::getTypeName(1),
      ], [
         'value'     =>  $this->fields["validation_required"],
         'on_change' => 'plugin_formcreator_changeValidators(this.value)'
      ]);
      echo '</td>';
      echo '<td colspan="2">';

      // Select all users with ticket validation right and the groups
      $userTable = User::getTable();
      $userFk = User::getForeignKeyField();
      $groupTable = Group::getTable();
      $groupFk = Group::getForeignKeyField();
      $profileUserTable = Profile_User::getTable();
      $profileTable = Profile::getTable();
      $profileFk = Profile::getForeignKeyField();
      $profileRightTable = ProfileRight::getTable();
      $groupUserTable = Group_User::getTable();
      $subQuery = [
         'SELECT' => "$profileUserTable.$userFk",
         'FROM' => $profileUserTable,
         'INNER JOIN' => [
            $profileTable => [
               'FKEY' => [
                  $profileTable =>  'id',
                  $profileUserTable => $profileFk,
               ]
            ],
            $profileRightTable =>[
               'FKEY' => [
                  $profileTable => 'id',
                  $profileRightTable => $profileFk,
               ]
            ],
         ],
         'WHERE' => [
            "$profileRightTable.name" => "ticketvalidation",
            [
               'OR' => [
                  "$profileRightTable.rights" => ['&', TicketValidation::VALIDATEREQUEST],
                  "$profileRightTable.rights" => ['&', TicketValidation::VALIDATEINCIDENT],
               ],
            ],
            "$userTable.is_active" => '1',
         ],
      ];
      $usersCondition = [
         "$userTable.id" => new QuerySubquery($subQuery)
      ];
      $formValidator = new PluginFormcreatorForm_Validator();
      $selectedValidatorUsers = [];
      foreach ($formValidator->getValidatorsForForm($this, User::class) as $user) {
         $selectedValidatorUsers[$user->getID()] = $user->getID();
      }
      $users = $DB->request([
         'SELECT' => ['id', 'name'],
         'FROM' => User::getTable(),
         'WHERE' => $usersCondition,
      ]);
      $validatorUsers = [];
      foreach($users as $user) {
         $validatorUsers[$user['id']] = $user['name'];
      }
      echo '<div id="validators_users">';
      Dropdown::showFromArray(
         '_validator_users',
         $validatorUsers, [
            'multiple' => true,
            'values' => $selectedValidatorUsers
         ]
      );
      echo '</div>';

      // Validators groups
      $subQuery = [
         'SELECT' => "$groupUserTable.$groupFk",
         'FROM' => $groupUserTable,
         'INNER JOIN' => [
            $userTable => [
               'FKEY' => [
                  $groupUserTable => $userFk,
                  $userTable => 'id',
               ]
            ],
            $profileUserTable => [
               'FKEY' => [
                  $profileUserTable => $userFk,
                  $userTable => 'id',
               ],
            ],
            $profileTable => [
               'FKEY' => [
                  $profileTable =>  'id',
                  $profileUserTable => $profileFk,
               ]
            ],
            $profileRightTable =>[
               'FKEY' => [
                  $profileTable => 'id',
                  $profileRightTable => $profileFk,
               ]
            ],
         ],
         'WHERE' => [
            "$groupUserTable.$userFk" => new QueryExpression("`$userTable`.`id`"),
            "$profileRightTable.name" => "ticketvalidation",
            [
               'OR' => [
                  "$profileRightTable.rights" => ['&', TicketValidation::VALIDATEREQUEST],
                  "$profileRightTable.rights" => ['&', TicketValidation::VALIDATEINCIDENT],
               ],
            ],
            "$userTable.is_active" => '1',
         ],
      ];
      $groupsCondition = [
         "$groupTable.id" => new QuerySubquery($subQuery),
      ];
      $groups = $DB->request([
         'SELECT' => ['id' ,'name'],
         'FROM'   => Group::getTable(),
         'WHERE'  => $groupsCondition,
      ]);
      $formValidator = new PluginFormcreatorForm_Validator();
      $selectecValidatorGroups = [];
      foreach($formValidator->getValidatorsForForm($this, Group::class) as $group) {
         $selectecValidatorGroups[$group->getID()] = $group->getID();
      }
      $validatorGroups = [];
      foreach($groups as $group) {
         $validatorGroups[$group['id']] = $group['name'];
      }
      echo '<div id="validators_groups" style="width: 100%">';
      Dropdown::showFromArray(
         '_validator_groups',
         $validatorGroups,
         [
            'multiple' => true,
            'values'   => $selectecValidatorGroups
         ]
      );
      echo '</div>';

      $script = '$(document).ready(function() {plugin_formcreator_changeValidators(' . $this->fields["validation_required"] . ');});';
      echo Html::scriptBlock($script);

      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>'.__('Default form in service catalog', 'formcreator').'</td>';
      echo '<td>';
      Dropdown::showYesNo("is_default", $this->fields["is_default"]);
      echo '</td>';
      echo '</tr>';

      if (!$this->canPurgeItem()) {
         echo '<tr>';
         echo '<td colspan="4">'
         . '<i class="fas fa-exclamation-triangle"></i>&nbsp;'
         . __('To delete this form you must delete all its answers first.', 'formcreator')
         . '</td>';
         echo '</tr>';
      }

      $this->showFormButtons($options);
   }

   public function showTargets($ID, $options = []) {
      echo '<table class="tab_cadre_fixe">';

      echo '<tr>';
      echo '<th colspan="3">'._n('Destinations', 'Destinations', 2, 'formcreator').'</th>';
      echo '</tr>';

      $allTargets = $this->getTargetsFromForm();
      $token = Session::getNewCSRFToken();
      $i = 0;
      foreach ($allTargets as $targetType => $targets) {
         foreach ($targets as $targetId => $target) {
            $i++;
            echo '<tr class="line'.($i % 2).'">';
            $targetItemUrl = Toolbox::getItemTypeFormURL($targetType) . '?id=' . $targetId;
            echo '<td onclick="document.location=\'' . $targetItemUrl . '\'" style="cursor: pointer">';

            echo $target->fields['name'];
            echo '</td>';

            echo '<td align="center" width="32">';
            echo '<img src="'.FORMCREATOR_ROOTDOC.'/pics/edit.png"
                     alt="*" title="'.__('Edit').'" ';
            echo 'onclick="document.location=\'' . $targetItemUrl . '\'" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';

            echo '<td align="center" width="32">';
            echo '<img src="'.FORMCREATOR_ROOTDOC.'/pics/delete.png"
                     alt="*" title="'.__('Delete', 'formcreator').'"
                     onclick="plugin_formcreator_deleteTarget(\''. $target->getType() . '\', '.$targetId.', \''.$token.'\')" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';

            echo '</tr>';
         }
      }

      // Display add target link...
      echo '<tr class="line'.(($i + 1) % 2).'" id="add_target_row">';
      echo '<td colspan="3">';
      echo '<a href="javascript:plugin_formcreator_addTarget('.$ID.', \''.$token.'\');">
                <i class="fa fa-plus"></i>
                '.__('Add a target', 'formcreator').'
            </a>';
      echo '</td>';
      echo '</tr>';

      // OR display add target form
      echo '<tr class="line'.(($i + 1) % 2).'" id="add_target_form" style="display: none;">';
      echo '<td colspan="3" id="add_target_form_td"></td>';
      echo '</tr>';

      echo "</table>";
   }

   /**
    * Return the name of the tab for item including forms like the config page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Config Item)
    * @param  integer    $withtemplate
    *
    * @return String                   Name to be displayed
    */
   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch ($item->getType()) {
         case PluginFormcreatorForm::class:
            $nb = 0;
            foreach ($this->getTargetTypes() as $targetType) {
               $nb += (new DbUtils())->countElementsInTable(
                  $targetType::getTable(),
                  [
                     'WHERE' => [
                        'plugin_formcreator_forms_id' => $item->getID(),
                     ]
                  ]
               );
            }
            return [
               1 => self::createTabEntry(
                  _n('Target', 'Targets', Session::getPluralNumber(), 'formcreator'),
                  $nb
               ),
               2 => __('Preview'),
            ];
            break;
         case Central::class:
            return _n('Form', 'Forms', Session::getPluralNumber(), 'formcreator');
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
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $uri = strrchr($_SERVER['HTTP_REFERER'], '/');
      if (strpos($uri, '?')) {
         $uri = substr($uri, 0, strpos($uri, '?'));
      }
      $uri = trim($uri, '/');

      switch ($uri) {
         case "form.form.php":
            switch ($tabnum) {
               case 1:
                  $item->showTargets($item->getID());
                  break;

               case 2:
                  echo '<div style="text-align: left">';
                  $item->displayUserForm($item);
                  echo '</div>';
                  break;
            }
            break;
         case 'central.php':
            $form = new static();
            $form->showForCentral();
            break;
      }
   }


   public function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(PluginFormcreatorQuestion::class, $ong, $options);
      $this->addStandardTab(PluginFormcreatorForm_Profile::class, $ong, $options);
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab(PluginFormcreatorFormAnswer::class, $ong, $options);
      $this->addStandardTab(Log::class, $ong, $options);
      return $ong;
   }

   /**
    * Show the list of forms to be displayed to the end-user
    */
   public function showList() {
      echo '<div class="center" id="plugin_formcreator_wizard">';

      echo '<div class="plugin_formcreator_marginRight plugin_formcreator_card">';
      $this->showWizard();
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
      $table_section = getTableForItemType('PluginFormcreatorSections');
      $table_question= getTableForItemType('PluginFormcreatorQuestions');

      $order         = "$table_form.name ASC";

      $dbUtils = new DbUtils();
      $entityRestrict = $dbUtils->getEntitiesRestrictCriteria($table_form, "", "", true, false);
      if (count($entityRestrict)) {
         $entityRestrict = [$entityRestrict];
      }
      $where_form = [
         'AND' => [
            "$table_form.is_active" => '1',
            "$table_form.is_deleted" => '0',
            "$table_form.language" => [$_SESSION['glpilanguage'], '0', '', null],
         ] + $entityRestrict
      ];
      if ($helpdeskHome) {
         $where_form['AND']["$table_form.helpdesk_home"] = '1';
      }

      $selectedCategories = [];
      if ($rootCategory != 0) {
         $selectedCategories = getSonsOf($table_cat, $rootCategory);
         $where_form['AND']["$table_form.plugin_formcreator_categories_id"] = $selectedCategories;
      }

      // Find forms accessible by the current user
      $keywords = trim($keywords);
      if (!empty($keywords)) {
         $keywordsWithWilcards = $DB->escape(PluginFormcreatorCommon::prepareBooleanKeywords($keywords));
         $where_form['AND'][] = [
            'OR' => [
               new QueryExpression("MATCH($table_form.`name`, $table_form.`description`)
                  AGAINST('$keywordsWithWilcards' IN BOOLEAN MODE)"),
               new QueryExpression("MATCH($table_question.`name`, $table_question.`description`)
                  AGAINST('$keywordsWithWilcards' IN BOOLEAN MODE)"),
            ]
         ];
      }
      $where_form['AND'][] = [
         'OR' => [
            'access_rights' => ['!=', PluginFormcreatorForm::ACCESS_RESTRICTED],
            [
               "$table_fp.profiles_id" => $_SESSION['glpiactiveprofile']['id']
            ]
         ]
      ];

      $result_forms = $DB->request([
         'SELECT' => [
            $table_form => ['id', 'name', 'icon', 'icon_color', 'background_color', 'description', 'usage_count', 'is_default'],
         ],
         'FROM' => $table_form,
         'LEFT JOIN' => [
            $table_cat => [
               'FKEY' => [
                  $table_cat => 'id',
                  $table_form => PluginFormcreatorCategory::getForeignKeyField(),
               ]
            ],
            $table_section => [
               'FKEY' => [
                  $table_section => PluginFormcreatorForm::getForeignKeyField(),
                  $table_form => 'id',
               ]
            ],
            $table_question => [
               'FKEY' => [
                  $table_question => PluginFormcreatorSection::getForeignKeyField(),
                  $table_section => 'id'
               ]
            ],
            $table_fp => [
               'FKEY' => [
                  $table_fp => PluginFormcreatorForm::getForeignKeyField(),
                  $table_form => 'id',
               ]
            ]
         ],
         'WHERE' => $where_form,
         'GROUPBY' => [
            "$table_form.id",
            "$table_form.name",
            "$table_form.description",
            "$table_form.usage_count",
            "$table_form.is_default"
         ],
         'ORDER' => [
            $order
         ],
      ]);

      $formList = [];
      if ($result_forms->count() > 0) {
         foreach ($result_forms as $form) {
            $formList[] = [
               'id'           => $form['id'],
               'name'         => $form['name'],
               'icon'         => $form['icon'],
               'icon_color'   => $form['icon_color'],
               'background_color'   => $form['background_color'],
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
      if (version_compare(GLPI_VERSION, "9.4") > 0) {
         $subQuery = new DBMysqlIterator($DB);
         $subQuery->buildQuery($query_faqs);
         $query_faqs = '(' . $subQuery->getSQL() . ')';
      }

      $query_faqs = [
         'SELECT' => ['faqs' => '*'],
         'FROM' => new QueryExpression('(' . $query_faqs . ') AS `faqs`'),
      ];
      if (count($selectedCategories) > 0) {
         $query_faqs['WHERE'] = [
            'knowbaseitemcategories_id' => new QuerySubQuery([
               'SELECT' => 'knowbaseitemcategories_id',
               'FROM' => $table_cat,
               'WHERE' => [
                  'id' => $selectedCategories,
                  'knowbaseitemcategories_id' => ['!=', 0],
               ],
            ]),
         ];
      } else {
         $query_faqs['INNER JOIN'] = [
            $table_cat => [
               'FKEY' => [
                  'faqs' => 'knowbaseitemcategories_id',
                  $table_cat => 'knowbaseitemcategories_id'
               ]
            ]
         ];
         $query_faqs['WHERE'] = [
            'faqs.knowbaseitemcategories_id' => ['!=', 0],
         ];
      }
      $result_faqs = $DB->request($query_faqs);
      if ($result_faqs->count() > 0) {
         foreach ($result_faqs as $faq) {
            $formList[] = [
               'id'           => $faq['id'],
               'name'         => $faq['name'],
               'icon'         => '',
               'icon_color'   => '',
               'background_color'   => '',
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
         $where_form = [
            'AND' => [
               "$table_form.is_active" => '1',
               "$table_form.is_deleted" => '0',
               "$table_form.language" => [$_SESSION['glpilanguage'], '0', '', null],
               "$table_form.is_default" => ['<>', '0']
            ] + $dbUtils->getEntitiesRestrictCriteria($table_form, '', '', true, false),
         ];
         $where_form['AND'][] = [
            'OR' => [
               'access_rights' => ['!=', PluginFormcreatorForm::ACCESS_RESTRICTED],
               "$table_form.id" => new QuerySubQuery([
                  'SELECT' => 'plugin_formcreator_forms_id',
                  'FROM' => $table_fp,
                  'WHERE' => [
                     'profiles_id' => $_SESSION['glpiactiveprofile']['id']
                  ]
               ])
            ]
         ];

         $query_forms = [
            'SELECT' => [
               $table_form => ['id', 'name', 'icon', 'icon_color', 'background_color', 'description', 'usage_count'],
            ],
            'FROM' => $table_form,
            'LEFT JOIN' => [
               $table_cat => [
                  'FKEY' => [
                     $table_cat => 'id',
                     $table_form => PluginFormcreatorCategory::getForeignKeyField(),
                  ]
               ],
            ],
            'WHERE' => $where_form,
            'ORDER' => [
               $order
            ],
         ];
         $result_forms = $DB->request($query_forms);

         if ($result_forms->count() > 0) {
            foreach ($result_forms as $form) {
               $formList[] = [
                  'id'           => $form['id'],
                  'name'         => $form['name'],
                  'icon'         => $form['icon'],
                  'icon_color'   => $form['icon_color'],
                  'background_color'   => $form['background_color'],
                  'description'  => $form['description'],
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
      $userId = $_SESSION['glpiID'];
      echo '<div class="plugin_formcreator_card">';
      echo '<div class="plugin_formcreator_heading">'.__('My last forms (requester)', 'formcreator').'</div>';
      $result = PluginFormcreatorFormAnswer::getMyLastAnswersAsRequester();
      if ($result->count() == 0) {
         echo '<div class="line1" align="center">'.__('No form posted yet', 'formcreator').'</div>';
         echo "<ul>";
      } else {
         foreach ($result as $form) {
               switch ($form['status']) {
                  case PluginFormcreatorFormAnswer::STATUS_WAITING:
                     $status = 'waiting';
                     break;
                  case PluginFormcreatorFormAnswer::STATUS_REFUSED:
                     $status = 'refused';
                     break;
                  case PluginFormcreatorFormAnswer::STATUS_ACCEPTED:
                     $status = 'accepted';
                     break;
               }
               echo '<li class="plugin_formcreator_answer">';
               echo ' <a class="plugin_formcreator_'.$status.'" href="formanswer.form.php?id='.$form['id'].'">'.$form['name'].'</a>';
               echo '<span class="plugin_formcreator_date">'.Html::convDateTime($form['request_date']).'</span>';
               echo '</li>';
         }
         echo "</ul>";
         echo '<div align="center">';
         echo '<a href="formanswer.php?criteria[0][field]=4&criteria[0][searchtype]=equals&criteria[0][value]='.$userId.'">';
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
         $result = PluginFormcreatorFormAnswer::getMyLastAnswersAsValidator();
         if ($result->count() == 0) {
            echo '<div class="line1" align="center">'.__('No form waiting for validation', 'formcreator').'</div>';
         } else {
            echo "<ul>";
            foreach ($result as $form) {
               switch ($form['status']) {
                  case PluginFormcreatorFormAnswer::STATUS_WAITING:
                     $status = 'waiting';
                     break;
                  case PluginFormcreatorFormAnswer::STATUS_REFUSED:
                     $status = 'refused';
                     break;
                  case PluginFormcreatorFormAnswer::STATUS_ACCEPTED:
                     $status = 'accepted';
                     break;
               }
               echo '<li class="plugin_formcreator_answer">';
               echo ' <a class="plugin_formcreator_'.$status.'" href="formanswer.form.php?id='.$form['id'].'">'.$form['name'].'</a>';
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

            echo '<a href="formanswer.php?' . $criteria . '">';
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
    * @return Null                     Nothing, just display the form
    */
   public function displayUserForm() {
      if (isset($_SESSION['formcreator']['data'])) {
         $data = $_SESSION['formcreator']['data'];
         unset($_SESSION['formcreator']['data']);
      } else {
         $data = null;
      }

      // Print css media
      if (method_exists(Plugin::class, 'getWebDir')) {
         $css = '/' . Plugin::getWebDir('formcreator', false) . '/css/print_form.css';
      } else {
         $css =  '/plugins/formcreator/css/print_form.css';
      }
      echo Html::css($css, ['media' => 'print']);

      // Display form
      $formName = 'plugin_formcreator_form';
      echo "<form name='$formName' method='post' role='form' enctype='multipart/form-data'
               action='". FORMCREATOR_ROOTDOC . "/front/form.form.php'
               class='formcreator_form form_horizontal'>";
      echo "<h1 class='form-title'>";
      echo $this->fields['name'] . "&nbsp;";
      echo '<i class="fas fa-print" style="cursor: pointer;" onclick="window.print();"></i>';
      echo '</h1>';

      // Form Header
      if (!empty($this->fields['content'])) {
         echo '<div class="form_header">';
         echo html_entity_decode($this->fields['content']);
         echo '</div>';
      }

      // Get and display sections of the form
      $sections = (new PluginFormcreatorSection)->getSectionsFromForm($this->getID());
      foreach ($sections as $section) {
         echo '<div class="form_section" data-section-id="'. $section->getID().'">';
         echo '<h2>' . $section->fields['name'] . '</h2>';
         // Display all fields of the section
         $questions = (new PluginFormcreatorQuestion())->getQuestionsFromSection($section->getID());
         foreach ($questions as $question) {
            $field = PluginFormcreatorFields::getFieldInstance(
               $question->fields['fieldtype'],
               $question
            );
            if (!$field->isPrerequisites()) {
               continue;
            }
            if ($field->hasInput($data)) {
               $field->parseAnswerValues($data);
            } else {
               $field->deserializeValue($question->fields['default_values']);
            }
            $field->show();
         }
         echo '</div>';
      }
      echo Html::scriptBlock('$(function() {
         formcreatorShowFields($("form[name=\'' . $formName . '\']"));
      })');

      // Show validator selector
      if ($this->fields['validation_required'] != PluginFormcreatorForm_Validator::VALIDATION_NONE) {
         $validators = [];
         $formValidator = new PluginFormcreatorForm_Validator();
         switch ($this->fields['validation_required']) {
            case PluginFormcreatorForm_Validator::VALIDATION_GROUP:
               $validatorType = Group::class;
               $result = $formValidator->getValidatorsForForm($this, $validatorType);
               foreach ($result as $validator) {
                  $validators[$validator->getID()] = $validator->fields['completename'];
               }
               break;
            case PluginFormcreatorForm_Validator::VALIDATION_USER:
               $validatorType = User::class;
               $result = $formValidator->getValidatorsForForm($this, $validatorType);
               foreach ($result as $validator) {
                  $validators[$validator->getID()] = formatUserName($validator->getID(), $validator->fields['name'], $validator->fields['realname'], $validator->fields['firstname']);
               }
               break;
         }

         $resultCount = count($result);
         if ($resultCount == 1) {
            reset($validators);
            $validatorId = key($validators);
            echo Html::hidden('formcreator_validator', ['value' => $validatorId]);
         } else if ($resultCount > 1) {
            $validators = [0 => Dropdown::EMPTY_VALUE] + $validators;
            echo '<h2>' . __('Validation', 'formcreator') . '</h2>';
            echo '<div class="form-group required liste" id="form-validator">';
            echo '<label>' . __('Choose a validator', 'formcreator') . ' <span class="red">*</span></label>';
            Dropdown::showFromArray('formcreator_validator', $validators);
            echo '</div>';
         }
      }

      // Display submit button
      echo '<div class="center">';
      echo Html::submit(__('Send'), ['name' => 'submit_formcreator']);
      echo '</div>';

      echo Html::hidden('plugin_formcreator_forms_id', ['value' => $this->getID()]);
      echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
      echo Html::hidden('uuid', ['value' => $this->fields['uuid']]);
      Html::closeForm();
   }

   /**
    * Prepare input data for adding the form
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
    */
   public function prepareInputForAdd($input) {
      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      // Control fields values :
      // - name is required
      if (isset($input['name'])) {
         if (empty($input['name'])) {
            Session::addMessageAfterRedirect(
               __('The name cannot be empty!', 'formcreator'),
               false,
               ERROR
            );
            return [];
         }
      }

      if (!isset($input['requesttype'])) {
         $requestType = new RequestType();
         $requestType->getFromDBByCrit(['name' => ['LIKE', 'Formcreator']]);
         $input['requesttype'] = $requestType->getID();
      }

      return $input;
   }

   /**
    * Actions done after the ADD of the item in the database
    *
    * @return void
    */
   public function post_addItem() {
      $this->updateValidators();
      if (!isset($this->input['_skip_checks']) || !$this->input['_skip_checks']) {
         $this->updateConditions($this->input);
      }
      return true;
   }

   /**
    * Actions done after the UPDATE of the item in the database
    *
    * @return void
    */
    public function post_updateItem($history = 1) {
      $this->updateValidators();
      if (!isset($this->input['_skip_checks']) || !$this->input['_skip_checks']) {
         $this->updateConditions($this->input);
      }
   }

   /**
    * Prepare input data for updating the form
    *
    * @param array $input data used to add the item
    *
    * @return array the modified $input array
    */
   public function prepareInputForUpdate($input) {
      if (isset($input['access_rights'])
            || isset($_POST['massiveaction'])
            || isset($input['usage_count'])) {

         if (isset($input['access_rights'])
            && $input['access_rights'] == self::ACCESS_PUBLIC
         ) {
            // check that accessibility to the form is compatible with its questions
            $fields = $this->getFields();
            $incompatibleQuestion = false;
            foreach ($fields as $field) {
               if (!$field->isAnonymousFormCompatible()) {
                  $incompatibleQuestion = true;
                  $message = __('The question %s is not compatible with public forms', 'formcreator');
                  Session::addMessageAfterRedirect(sprintf($message, $field->getLabel()), false, ERROR);
               }
            }
            if ($incompatibleQuestion) {
               return [];
            }
         }

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
    */
   public function post_purgeItem() {
      $associated = [
         PluginFormcreatorTargetTicket::class,
         PluginFormcreatorTargetChange::class,
         PluginFormcreatorSection::class,
         PluginFormcreatorForm_Validator::class,
         PluginFormcreatorForm_Profile::class,
      ];
      foreach ($associated as $itemtype) {
         $item = new $itemtype();
         $item->deleteByCriteria(['plugin_formcreator_forms_id' => $this->getID()]);
      }
   }

   /**
    * Save form validators
    *
    * @return void
    */
   private function updateValidators() {
      if (!isset($this->input['validation_required'])) {
         return;
      }
      if ($this->input['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_NONE) {
         return;
      }
      if ($this->input['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_USER
         && empty($this->input['_validator_users'])) {
         return;
      }
      if ($this->input['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_GROUP
         && empty($this->input['_validator_groups'])) {
         return;
      }

      $form_validator = new PluginFormcreatorForm_Validator();
      $form_validator->deleteByCriteria(['plugin_formcreator_forms_id' => $this->getID()]);

      switch ($this->input['validation_required']) {
         case PluginFormcreatorForm_Validator::VALIDATION_USER:
            $validators = $this->input['_validator_users'];
            $validatorItemtype = User::class;
            break;
         case PluginFormcreatorForm_Validator::VALIDATION_GROUP:
            $validators = $this->input['_validator_groups'];
            $validatorItemtype = Group::class;
            break;
      }
      if (!is_array($validators)) {
         $validators = [$validators];
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

   public function increaseUsageCount() {
      // Increase usage count of the form
      $this->update([
            'id' => $this->getID(),
            'usage_count' => $this->getField('usage_count') + 1,
      ]);
   }

   /**
    * gets a form from database from a question
    *
    * @param integer $questionId
    */
   public function getByQuestionId($questionId) {
      $formTable = PluginFormcreatorForm::getTable();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $sectionTable = PluginFormcreatorSection::getTable();
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $questionTable = PluginFormcreatorQuestion::getTable();
      $this->getFromDBByRequest([
         'INNER JOIN' => [
            $sectionTable => [
               'FKEY' => [
                  $formTable    => 'id',
                  $sectionTable => $formFk,
               ]
            ],
            $questionTable => [
               'FKEY' => [
                  $questionTable => $sectionFk,
                  $sectionTable  => 'id'
               ]
            ]
         ],
         'WHERE' => [
            $questionTable . '.id' => $questionId,
         ]
      ]);
   }

   /**
    * Duplicate a form. Execute duplicate action for massive action.
    *
    * @return Boolean true if success, false otherwise.
    */
   public function duplicate() {
      $linker = new PluginFormcreatorLinker();

      $export = $this->export(true);
      try {
         $new_form_id =  static::import($linker, $export);
      } catch (ImportFailureException $e) {
         $forms = $linker->getObjectsByType(PluginFormcreatorForm::class);
         $form = reset($forms);
         $form->update([
            'id' => $form->getID(),
            'name' => $form->fields['name'] . ' [' . __('Errored duplicate', 'formcreator') . ']',
         ]);
         Session::addMessageAfterRedirect($e->getMessage(), false, WARNING);
         return false;
      }

      if ($new_form_id === false) {
         return false;
      }
      $newForm = new self();
      $newForm->getFromDB($new_form_id);
      $newName = $newForm->fields['name'] . ' [' . __('Duplicate', 'formcreator') . ']';
      $newForm->update([
         'id' => $new_form_id,
         'name' => Toolbox::addslashes_deep($newName),
      ]);
      $linker->linkPostponed();

      return $new_form_id;
   }

   /**
    * Transfer a form to another entity. Execute transfert action for massive action.
    *
    * @return Boolean true if success, false otherwize.
    */
   public function transfer($entity) {
      global $DB;

      $result = $DB->update(
         self::getTable(),
         [
            Entity::getForeignKeyField() => $entity
         ],
         [
            'id' => $this->getID()
         ]
      );
      return $result;
   }

   public function getForbiddenStandardMassiveAction() {
      return [
         'clone',
      ];
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    */
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
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      switch ($ma->getAction()) {
         case 'Duplicate' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id) && $item->duplicate() !== false) {
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

      $formTable        = PluginFormcreatorForm::getTable();
      $formFk           = PluginFormcreatorForm::getForeignKeyField();
      $formProfileTable = PluginFormcreatorForm_Profile::getTable();

      if ($DB->tableExists($formTable)
          && $DB->tableExists($formProfileTable)
          && isset($_SESSION['glpiactiveprofile']['id'])) {
         $nb = (new DBUtils())->countElementsInTableForMyEntities(
            $formTable,
            [
               'WHERE' => [
                  "$formTable.is_active" => '1',
                  "$formTable.is_deleted" => '0',
                  "$formTable.language" => [$_SESSION['glpilanguage'], '0', '', null],
                  [
                     'OR' => [
                        "$formTable.access_rights" => ['<>', PluginFormcreatorForm::ACCESS_RESTRICTED],
                        "$formTable.id" => new QuerySubQuery([
                           'SELECT' => $formFk,
                           'FROM' => $formProfileTable,
                           'WHERE' => [
                              'profiles_id' => $_SESSION['glpiactiveprofile']['id']
                           ]
                        ]),
                     ],
                  ],
               ],
            ]
         );
      }

      return $nb;
   }

   function export($remove_uuid = false) {
      if ($this->isNewItem()) {
         return false;
      }

      $export = $this->fields;

      // replace entity id
      $export['_entity']
         = Dropdown::getDropdownName(Entity::getTable(),
                                       $export['entities_id']);

      // replace form category id
      $export['_plugin_formcreator_category'] = '';
      if ($export['plugin_formcreator_categories_id'] > 0) {
         $export['_plugin_formcreator_category']
            = Dropdown::getDropdownName(PluginFormcreatorCategory::getTable(),
                                        $export['plugin_formcreator_categories_id']);
      }

      // remove non needed keys
      unset($export['plugin_formcreator_categories_id'],
            $export['entities_id'],
            $export['usage_count']);

      $subItems = [
         '_profiles'   => PluginFormcreatorForm_Profile::class,
         '_sections'   => PluginFormcreatorSection::class,
         '_conditions' => PluginFormcreatorCondition::class,
         '_targets'    => (new self())->getTargetTypes(),
         '_validators' => PluginFormcreatorForm_Validator::class,
      ];
      $export = $this->exportChildrenObjects($subItems, $export, $remove_uuid);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }
      unset($export[$idToRemove]);

      return $export;
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
               $button = Html::submit(_x('button', 'Create', 'formcreator'), ['name' => 'filetype_create']);
            } else {
               $destination = PluginFormcreatorForm::getSearchURL();
               $message .= __('Please contact your GLPI administrator.', 'formcreator');
               $button = Html::submit(_x('button', 'Back', 'formcreator'), ['name' => 'filetype_back']);
            }
         } else {
            $message = __('Upload of JSON files not enabled.', 'formcreator');
            if ($canUpdateType) {
               $destination = PluginFormcreatorForm::getFormURL();
               $message .= __('You may enable JSON files right now.', 'formcreator');
               $button = Html::submit(_x('button', 'Enable', 'formcreator'), ['name' => 'filetype_enable']);
            } else {
               $message .= __('You may enable JSON files right now.', 'formcreator');
               $message .= __('Please contact your GLPI administrator.', 'formcreator');
               $button = Html::submit(_x('button', 'Back', 'formcreator'), ['name' => 'filetype_back']);
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
      if (!isset($params['_json_file'])) {
         Session::addMessageAfterRedirect(
            __("No file uploaded", 'formcreator'),
            false, ERROR
         );
         return;
      }

      // parse json file(s)
      foreach ($params['_json_file'] as $filename) {
         if (!$json = file_get_contents(GLPI_TMP_DIR."/".$filename)) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file is empty", 'formcreator'));
            continue;
         }
         if (!$forms_toimport = json_decode($json, true)) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file seems corrupt", 'formcreator'));
            continue;
         }
         if (!isset($forms_toimport['forms'])) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file seems corrupt", 'formcreator'));
            continue;
         }
         if (isset($forms_toimport['schema_version'])) {
            if (($forms_toimport['schema_version']) != PLUGIN_FORMCREATOR_SCHEMA_VERSION . '.0') {
               Session::addMessageAfterRedirect(
                  __("Forms import impossible, the file was generated with another version", 'formcreator'),
                  false, ERROR
               );
               continue;
            }
         } else {
            Session::addMessageAfterRedirect(
               __("The file does not specifies the schema version. It was probably generated with a version older than 2.10 and import is expected to create incomplete or buggy forms.", 'formcreator'),
               false, WARNING
            );
         }

         $success = true;
         foreach ($forms_toimport['forms'] as $form) {
            set_time_limit(30);
            $linker = new PluginFormcreatorLinker();
            try {
               self::import($linker, $form);
            } catch (ImportFailureException $e) {
               // Import failed, give up
               $success = false;
               Session::addMessageAfterRedirect($e->getMessage(), false, ERROR);
               continue;
            }
            if (!$linker->linkPostponed()) {
               Session::addMessageAfterRedirect(sprintf(__("Failed to import %s", "formcreator"),
                                                           $form['name']));
            }
         }
         if ($success) {
            Session::addMessageAfterRedirect(sprintf(__("Forms successfully imported from %s", "formcreator"),
                                                      $filename));
         }
      }
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      $input['_skip_checks'] = true;

      $item = new self();
      // Find an existing form to update, only if an UUID is available
      $itemId = false;
      /** @var string $idKey key to use as ID (id or uuid) */
      $idKey = 'id';
      if (isset($input['uuid'])) {
         // Try to find an existing item to update
         $idKey = 'uuid';
         $itemId = plugin_formcreator_getFromDBByField(
            $item,
            'uuid',
            $input['uuid']
         );
      }

      // Set entity of the form
      $entity = new Entity();
      $entityFk = Entity::getForeignKeyField();
      $entityId = $_SESSION['glpiactive_entity'];
      if (isset($input['_entity'])) {
         plugin_formcreator_getFromDBByField(
            $entity,
            'completename',
            $input['_entity']
         );
         // Check rights on the destination entity of the form
         if (!$entity->isNewItem() && $entity->canUpdateItem()) {
            $entityId = $entity->getID();
         } else {
            if ($itemId !== false) {
               // The form is in an entity where we don't have UPDATE right
               Session::addMessageAfterRedirect(
                  sprintf(__('The form %1$s already exists and is in an unmodifiable entity.', 'formcreator'), $input['name']),
                  false,
                  WARNING
               );
               throw new ImportFailureException('failed to add or update the item');
            } else {
               // The form is in an entity which does not exists yet
               Session::addMessageAfterRedirect(
                  sprintf(__('The entity %1$s is required for the form %2$s.', 'formcreator'), $input['_entity'], $input['name']),
                  false,
                  WARNING
               );
               throw new ImportFailureException('failed to add or update the item');
            }
         }
      }
      $input[$entityFk] = $entityId;

      // Import form category
      $formCategory = new PluginFormcreatorCategory();
      $formCategoryFk = PluginFormcreatorCategory::getForeignKeyField();
      $formCategoryId = 0;
      if ($input['_plugin_formcreator_category'] != '') {
         $formCategoryId = $formCategory->import([
            'completename' => Toolbox::addslashes_deep($input['_plugin_formcreator_category']),
         ]);
      }
      $input[$formCategoryFk] = $formCategoryId;

      // Escape text fields
      foreach (['name', 'description', 'content'] as $key) {
         $input[$key] = $DB->escape($input[$key]);
      }

      // Add or update the form
      $originalId = $input[$idKey];
      if ($itemId !== false) {
         $input['id'] = $itemId;
         $item->update($input);
      } else {
         unset($input['id']);
         $itemId = $item->add($input);
      }
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the form to the linker
      $linker->addObject($originalId, $item);

      // sort sections
      if (isset($input['_sections']) && is_array($input['_sections'])) {
         usort($input['_sections'], function($a, $b) {
            if ($a['order'] == $b['order']) {
               return 0;
            }
            return ($a['order'] < $b['order']) ? -1 : 1;
         });
      }

      $subItems = [
         '_profiles'   => PluginFormcreatorForm_Profile::class,
         '_sections'   => PluginFormcreatorSection::class,
         '_conditions' => PluginFormcreatorCondition::class,
         '_targets'    => (new self())->getTargetTypes(),
         '_validators' => PluginFormcreatorForm_Validator::class,
      ];
      $item->importChildrenObjects($item, $linker, $subItems, $input);

      return $itemId;
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
      $form_table = PluginFormcreatorForm::getTable();

      // Show form whithout category
      $formCategoryFk = PluginFormcreatorCategory::getForeignKeyField();

      // Show categories which have at least one form user can access
      $result = PluginFormcreatorCategory::getAvailableCategories();
      // For each categories, show the list of forms the user can fill
      $categories = [0 => __('Forms without category', 'formcreator')];
      foreach ($result as $category) {
         $categories[$category['id']] = $category['name'];
      }
      $formRestriction = PluginFormcreatorForm::getFormRestrictionCriterias($form_table);
      $formRestriction["$form_table.$formCategoryFk"] = 0;
      $formRestriction["$form_table.helpdesk_home"] = 1;
      $formRestriction["$form_table.$formCategoryFk"] = array_keys($categories);
      $result_forms = $DB->request([
         'SELECT' => [
            $form_table => ['id', 'name', 'description', $formCategoryFk],
         ],
         'FROM'  => $form_table,
         'WHERE' => $formRestriction,
         'ORDER' => [
            "$form_table.$formCategoryFk ASC",
            "$form_table.name ASC",
         ]
      ]);

      if ($result_forms->count() < 1) {
         echo '<table class="tab_cadrehov" id="plugin_formcreatorHomepageForms">';
         echo '<tr class="noHover">';
         echo '<th>' . __('No form available', 'formcreator') . '</th>';
         echo '</tr>';
         echo '</table>';
         return;
      }

      echo '<table class="tab_cadrehov" id="plugin_formcreatorHomepageForms">';
      echo '<tr class="noHover">';
      echo '<th><a href="' . FORMCREATOR_ROOTDOC . '/front/formlist.php">' . _n('Form', 'Forms', 2, 'formcreator') . '</a></th>';
      echo '</tr>';

      $currentCategoryId = -1;
      $i = 0;
      foreach ($result_forms as $row) {
         if ($currentCategoryId != $row[$formCategoryFk]) {
            // show header for the category
            $currentCategoryId = $row[$formCategoryFk];
            echo '<tr class="noHover"><th>' . $categories[$currentCategoryId] . '</th></tr>';
         }

         // Show a rox for the form
         echo '<tr class="line' . ($i % 2) . ' tab_bg_' . ($i % 2 +1) . '">';
         echo '<td>';
         echo '<img src="' . $CFG_GLPI['root_doc'] . '/pics/plus.png" alt="+" title=""
               onclick="showDescription(' . $row['id'] . ', this)" align="absmiddle" style="cursor: pointer">';
         echo '&nbsp;';
         echo '<a href="' . FORMCREATOR_ROOTDOC
            . '/front/formdisplay.php?id=' . $row['id'] . '"
               title="' . $row['description'] . '">'
            . $row['name']
            . '</a></td>';
         echo '</tr>';
         echo '<tr id="desc' . $row['id'] . '" class="line' . ($i % 2) . ' form_description">';
         echo '<td><div>' . $row['description'] . '&nbsp;</div></td>';
         echo '</tr>';
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

   public static function getInterface() {
      if (Session::getCurrentInterface() == 'helpdesk') {
         if (plugin_formcreator_replaceHelpdesk()) {
            return 'servicecatalog';
         }
         return 'self-service';
      }
      if (!empty($_SESSION['glpiactiveprofile'])) {
         return 'central';
      }

      return 'public';
   }

   public static function header() {
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

   /**
    * Gets the footer HTML
    *
    * @return string HTML to show a footer
    */
   public static function footer() {
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

   /**
    * Is the form accessible anonymously (without being logged in) ?
    * @return boolean true if the form is accessible anonymously
    */
   public function isPublicAccess() {
      if ($this->isNewItem()) {
         return false;
      }
      return ($this->fields['access_rights'] == \PluginFormcreatorForm::ACCESS_PUBLIC);
   }

   /**
    * gets the form containing the given section
    *
    * @param PluginFormcreatorSection $section
    * @return boolean true if success else false
    */
   public function getFromDBBySection(PluginFormcreatorSection $section) {
      if ($section->isNewItem()) {
         return false;
      }
      return $this->getFromDB($section->getField(self::getForeignKeyField()));
   }

   public function getFromDBByQuestion(PluginFormcreatorQuestion $question) {
      global $DB;

      if ($question->isNewItem()) {
         return false;
      }
      $questionTable = PluginFormcreatorQuestion::getTable();
      $sectionTable = PluginFormcreatorSection::getTable();
      $iterator = $DB->request([
         'SELECT' => self::getForeignKeyField(),
         'FROM' => PluginFormcreatorSection::getTable(),
         'INNER JOIN' => [
            $questionTable => [
               'FKEY' => [
                  $sectionTable => PluginFormcreatorSection::getIndexName(),
                  $questionTable => PluginFormcreatorSection::getForeignKeyField()
               ]
            ]
         ],
         'WHERE' => [
            $questionTable . '.' . PluginFormcreatorQuestion::getIndexName() => $question->getID()
         ]
      ]);
      if ($iterator->count() !== 1) {
         return false;
      }
      return $this->getFromDB($iterator->next()[self::getForeignKeyField()]);
   }

   /**
    * Get an array of instances of all fields for the form
    *
    * @return PluginFormcreatorField[]
    */
   public function getFields() {
      $fields = [];
      if ($this->isNewItem()) {
         return $fields;
      }

      $question = new PluginFormcreatorQuestion();
      $found_questions = $question->getQuestionsFromForm($this->getID());
      foreach ($found_questions as $id => $question) {
         $fields[$id] = PluginFormcreatorFields::getFieldInstance(
            $question->fields['fieldtype'],
            $question
         );
      }

      return $fields;
   }

   /**
    * Get supported target itemtypes
    *
    * @return array
    */
   public function getTargetTypes() {
      return [
         PluginFormcreatorTargetTicket::class,
         PluginFormcreatorTargetChange::class
      ];
   }

   /**
    * get all targets associated to the form
    *
    * @param integer $formId
    * @return array
    */
   public function getTargetsFromForm() {
      global $DB;

      $targets = [];
      if ($this->isNewItem()) {
         return [];
      }

      foreach ($this->getTargetTypes() as $targetType) {
         $request = [
            'SELECT' => 'id',
            'FROM' => $targetType::getTable(),
            'WHERE' => [
               self::getForeignKeyField() => $this->getID(),
            ]
         ];
         foreach ($DB->request($request) as $row) {
            $target = new $targetType();
            $target->getFromDB($row['id']);
            $targets[$targetType][$row['id']] = $target;
         }
      }

      return $targets;
   }

   public  function showAddTargetForm() {
      echo '<form name="form_target" method="post" action="'.static::getFormURL().'">';
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="4">'.__('Add a target', 'formcreator').'</th></tr>';

      echo '<tr class="line1">';
      echo '<td width="15%"><strong>'.__('Name').' <span style="color:red;">*</span></strong></td>';
      echo '<td width="40%"><input type="text" name="name" style="width:100%;" value="" required="required"/></td>';
      echo '<td width="15%"><strong>'._n('Type', 'Types', 1).' <span style="color:red;">*</span></strong></td>';
      echo '<td width="30%">';
      $targetTypes = [];
      foreach ($this->getTargetTypes() as $targetType) {
         $targetTypes[$targetType] = $targetType::getTypeName(1);
      }
      Dropdown::showFromArray(
         'itemtype',
         $targetTypes,
         [
            'display_emptychoice' => true
         ]
      );
      echo '</td>';
      echo '</tr>';

      echo '<tr class="line0">';
      echo '<td colspan="4" class="center">';
      echo '<input type="hidden" name="plugin_formcreator_forms_id" value="'.(int) $_REQUEST['form_id'].'" />';
      echo '<input type="submit" name="add_target" class="submit_button" value="'.__('Add').'" />';
      echo '</td>';
      echo '</tr>';

      echo '</table>';
      Html::closeForm();
   }

   /**
    * Add a target item to the form
    *
    * @param string $input
    * @return integer|false ID of the new item or false on error
    */
   public function addTarget($input) {
      $itemtype = $input['itemtype'];
      if (!in_array($itemtype, $this->getTargetTypes())) {
         Session::addMessageAfterRedirect(
            __('Unsupported target type.', 'formcreator'),
            false,
            ERROR
         );
         return false;
      }

      // Check the form exists
      $form = new self();
      if (!$form->getFromDB($input[self::getForeignKeyField()])) {
         // The linked form does not exists
         Session::addMessageAfterRedirect(
            __('The form does not exists.', 'formcreator'),
            false,
            ERROR
         );
         return false;
      }

      // Create the target
      $item = new $itemtype();
      unset($input['itemtype']);
      return $item->add($input);
   }

   /**
    * Delete a target fromfor the form
    *
    * @param aray $input
    * @return boolean
    */
   public function deleteTarget($input) {
      $itemtype = $input['itemtype'];
      if (!in_array($itemtype, $this->getTargetTypes())) {
         Session::addMessageAfterRedirect(
            __('Unsuported target type.', 'formcreator'),
            false,
            ERROR
         );
         return false;
      }

      $item = new $itemtype();
      $item->delete(['id' => $input['items_id']]);
      return true;
   }

   public function post_getFromDB() {
      // Set additional data for the API
      if (isAPI()) {
         $this->fields += \PluginFormcreatorSection::getFullData($this->fields['id']);
      }
   }

   public static function getFormRestrictionCriterias($formTable = '') {
      if ($formTable == '') {
         $formTable       = PluginFormcreatorForm::getTable();
      }
      $formFk           = self::getForeignKeyField();
      $table_fp         = PluginFormcreatorForm_Profile::getTable();
      $entitiesRestrict = (new DBUtils())->getEntitiesRestrictCriteria($formTable, '', '', true, false);
      $language         = $_SESSION['glpilanguage'];

      $restriction = [
         "$formTable.is_active" => 1,
         "$formTable.is_deleted" => 0,
         "$formTable.language" => [$language, 0, null, ''],
         [
            'OR' => [
               "$formTable.access_rights" => ['<>', PluginFormcreatorForm::ACCESS_RESTRICTED],
               "$formTable.id" => new QuerySubQuery([
                  'SELECT' => $formFk,
                  'FROM' => $table_fp,
                  'WHERE' => [
                     'profiles_id' => $_SESSION['glpiactiveprofile']['id']
                  ]
               ]),
            ],
         ],
      ] + $entitiesRestrict;

      return $restriction;
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude) {}
}
