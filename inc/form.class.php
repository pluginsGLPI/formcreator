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

use GlpiPlugin\Formcreator\Exception\ImportFailureException;
use GlpiPlugin\Formcreator\Exception\ExportFailureException;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorForm extends CommonDBTM implements
PluginFormcreatorExportableInterface,
PluginFormcreatorDuplicatableInterface,
PluginFormcreatorConditionnableInterface,
PluginFormcreatorTranslatableInterface
{
   use PluginFormcreatorConditionnableTrait;
   use PluginFormcreatorExportableTrait;
   use PluginFormcreatorTranslatable;

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
         'table'              => $this::getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'searchtype'         => 'contains',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this::getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this::getTable(),
         'field'              => 'description',
         'name'               => __('Description'),
         'datatype'           => 'string',
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
         'table'              => $this::getTable(),
         'field'              => 'is_recursive',
         'name'               => __('Recursive'),
         'datatype'           => 'bool',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this::getTable(),
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
         'table'              => $this::getTable(),
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
         'table'              => $this::getTable(),
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
         'id'                 => '11',
         'table'              => $this::getTable(),
         'field'              => 'content',
         'name'               => _n('Header', 'Headers', 1, 'formcreator'),
         'datatype'           => 'text',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '30',
         'table'              => $this::getTable(),
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
         'table'              => $this::getTable(),
         'field'              => 'icon',
         'name'               => __('Icon', 'formcreator'),
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '32',
         'table'              => $this::getTable(),
         'field'              => 'icon_color',
         'name'               => __('Icon color', 'formcreator'),
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '33',
         'table'              => $this::getTable(),
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
            return Dropdown::showFromArray(
               $name,
               self::getEnumAccessType(), [
                  'value'               => $values[$field],
                  'display_emptychoice' => false,
                  'display'             => false
               ]
            );
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
    * @param  string $field   Name of the field as define in $this->getSearchOptions()
    * @param  mixed  $values  The value as it is stored in DB
    * @param  array  $options Options (optional)
    * @return mixed           Value to be displayed
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
            $output = '<div style="text-align: center" onclick="plugin_formcreator.toggleForm(' . $options['raw_data']['id']. ')">' . $output . '</div>';
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

         case 'icon':
            if ($values[$field] == '') {
               return '';
            }
            return '<i class="' . $values[$field] . '"></i>';
            break;

         case 'icon_color':
         case 'background_color':
            return '<span style="background: ' . $values[$field] . '">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
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
      global $DB, $CFG_GLPI;

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
         'value'   => $this->fields['content'],
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
         'value'     =>  $this->fields['validation_required'],
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
         $selectedValidatorUsers[$user->getID()] = $user->computeFriendlyName();
      }
      $users = $DB->request([
         'SELECT' => ['id', 'name', User::getFriendlyNameFields('friendly_name')],
         'FROM' => User::getTable(),
         'WHERE' => $usersCondition,
      ]);
      $validatorUsers = [];
      foreach ($users as $user) {
         $validatorUsers[$user['id']] = $user['friendly_name'];
      }
      echo '<div id="validators_users">';
      echo User::getTypeName() . '&nbsp';
      $params = [
         'specific_tags' => [
            'multiple' => 'multiple',
         ],
         'entity_restrict' => -1,
         'itemtype'        => User::getType(),
         'values'          => array_keys($selectedValidatorUsers),
         'valuesnames'     => array_values($selectedValidatorUsers),
         'condition'       => Dropdown::addNewCondition($usersCondition),
         '_idor_token'     => Session::getNewIDORToken(User::getType()),
      ];
      echo Html::jsAjaxDropdown(
         '_validator_users[]',
         '_validator_users' . mt_rand(),
         $CFG_GLPI['root_doc']."/ajax/getDropdownValue.php",
         $params
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
      foreach ($formValidator->getValidatorsForForm($this, Group::class) as $group) {
         $selectecValidatorGroups[$group->getID()] = $group->fields['name'];
      }
      $validatorGroups = [];
      foreach ($groups as $group) {
         $validatorGroups[$group['id']] = $group['name'];
      }
      echo '<div id="validators_groups" style="width: 100%">';
      echo Group::getTypeName() . '&nbsp';
      $params = [
         'specific_tags' => [
            'multiple' => 'multiple',
         ],
         'entity_restrict' => -1,
         'itemtype'        => Group::getType(),
         'values'          => array_keys($selectecValidatorGroups),
         'valuesnames'     => array_values($selectecValidatorGroups),
         'condition'       => Dropdown::addNewCondition($groupsCondition),
         'display_emptychoice' => false,
         '_idor_token'    => Session::getNewIDORToken(Group::getType()),
      ];
      echo Html::jsAjaxDropdown(
         '_validator_groups[]',
         '_validator_groups' . mt_rand(),
         $CFG_GLPI['root_doc']."/ajax/getDropdownValue.php",
         $params
      );
      echo '</div>';

      $script = '$(document).ready(function() {plugin_formcreator_changeValidators(' . $this->fields["validation_required"] . ');});';
      echo Html::scriptBlock($script);

      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>'.__('Default form in service catalog', 'formcreator').'</td>';
      echo '<td>';
      Dropdown::showYesNo('is_default', $this->fields['is_default']);
      echo '</td>';
      echo '<td></td>';
      echo '<td></td>';
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
      echo '<table class="tab_cadrehov">';

      echo '<tr>';
      echo '<th colspan="3">'._n('Target', 'Targets', 2, 'formcreator').'</th>';
      echo '</tr>';

      $allTargets = $this->getTargetsFromForm();
      $token = Session::getNewCSRFToken();
      $i = 0;
      foreach ($allTargets as $targetType => $targets) {
         foreach ($targets as $targetId => $target) {
            $i++;
            echo '<tr class="tab_bg_'.($i % 2).'">';
            $targetItemUrl = Toolbox::getItemTypeFormURL($targetType) . '?id=' . $targetId;
            // echo '<td onclick="document.location=\'' . $targetItemUrl . '\'" style="cursor: pointer">';
            $onclick = "plugin_formcreator_editTarget('$targetType', $targetId)";
            echo '<td onclick="' . $onclick . '" style="cursor: pointer">';

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
      echo '<tr class="tab_bg_'.(($i + 1) % 2).'" id="add_target_row">';
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

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch ($item->getType()) {
         case PluginFormcreatorForm::class:
            return [
               1 => self::createTabEntry(
                  _n('Target', 'Targets', Session::getPluralNumber(), 'formcreator'),
                  $item->countTargets()
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
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item
    * @param  integer    $tabnum       Number of the current tab
    * @param  integer    $withtemplate
    *
    * @see CommonDBTM::displayTabContentForItem
    *
    * @return null                     Nothing, just display the list
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case PluginFormcreatorForm::getType():
            /** @var PluginFormcreatorForm $item */
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
         case Central::getType():
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
      $this->addStandardTab(PluginFormcreatorForm_Language::class, $ong, $options);
      //$this->addStandardTab(PluginFormcreatorTranslation::class, $ong, $options);
      $this->addStandardTab(Log::class, $ong, $options);
      return $ong;
   }

   /**
    * Show the list of forms to be displayed to the end-user
    */
   public function showList() : void {
      echo '<div class="center" id="plugin_formcreator_wizard">';

      echo '<div class="plugin_formcreator_marginRight plugin_formcreator_card">';
      $this->showWizard();
      echo '</div>';

      echo '<div id="plugin_formcreator_lastForms">';
      $this->showMyLastForms();
      echo '</div>';

      echo '</div>';
   }

   public function showServiceCatalog() : void {
      echo "<div id='formcreator_servicecatalogue'>";

      // show wizard
      echo '<div id="plugin_formcreator_wizard">';
      $this->showWizard(true);
      echo '</div>';

      echo '</div>'; // formcreator_servicecatalogue
   }

   public function showWizard($service_catalog = false) : void {
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

      if (PluginFormcreatorEntityconfig::getUsedConfig('is_header_visible', Session::getActiveEntity()) == PluginFormcreatorEntityconfig::CONFIG_HEADER_VISIBLE) {
         echo '<div id="plugin_formcreator_header">';
         $this->showHeader();
         echo '</div>';
      }
      if (PluginFormcreatorEntityconfig::getUsedConfig('is_search_visible', Session::getActiveEntity()) == PluginFormcreatorEntityconfig::CONFIG_SEARCH_VISIBLE) {
         echo '<div id="plugin_formcreator_searchBar">';
         $this->showSearchBar();
         echo '</div>';
      }
      $sortSettings = PluginFormcreatorEntityConfig::getEnumSort();
      echo '<div class="plugin_formcreator_sort">';
      echo '<span class="radios">';
      $sortOrder = PluginFormcreatorEntityconfig::getUsedConfig('sort_order', Session::getActiveEntity());
      $selected = $sortOrder == PluginFormcreatorEntityconfig::CONFIG_SORT_POPULARITY ? 'checked="checked"' : '';
      echo '<input type="radio" class="form-control" id="plugin_formcreator_mostPopular" name="sort" value="mostPopularSort" '.$selected.'/>';
      echo '<label for="plugin_formcreator_mostPopular">'.$sortSettings[PluginFormcreatorEntityConfig::CONFIG_SORT_POPULARITY] .'</label>';
      echo '</span>';
      echo '<span class="radios">';
      $selected = $sortOrder == PluginFormcreatorEntityconfig::CONFIG_SORT_ALPHABETICAL ? 'checked="checked"' : '';
      echo '<input type="radio" class="form-control" id="plugin_formcreator_alphabetic" name="sort" value="alphabeticSort" '.$selected.'/>';
      echo '<label for="plugin_formcreator_alphabetic">'.$sortSettings[PluginFormcreatorEntityConfig::CONFIG_SORT_ALPHABETICAL].'</label>';
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
    * @param bool $helpdeskHome show items for helpdesk only
    * @return array
    */
   public function showFormList(int $rootCategory = 0, string $keywords = '', bool $helpdeskHome = false) : array {
      global $DB, $TRANSLATE;

      $table_cat          = getTableForItemType('PluginFormcreatorCategory');
      $table_form         = getTableForItemType('PluginFormcreatorForm');
      $table_fp           = getTableForItemType('PluginFormcreatorForm_Profile');
      $table_section      = getTableForItemType('PluginFormcreatorSections');
      $table_question     = getTableForItemType('PluginFormcreatorQuestions');
      $table_formLanguage = getTableForItemType(PluginFormcreatorForm_Language::class);

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
            'OR' => [
               "$table_form.language" => [$_SESSION['glpilanguage'], '0', '', null],
               "$table_formLanguage.name" => $_SESSION['glpilanguage']
            ],
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
            ],
            $table_formLanguage => [
               'FKEY' => [
                  $table_form => 'id',
                  $table_formLanguage => PluginFormcreatorForm::getForeignKeyField(),
               ]
            ],
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
            // load thanguage for the form, if any
            $domain = self::getTranslationDomain($form['id']);
            $phpfile = self::getTranslationFile($form['id'], $_SESSION['glpilanguage']);
            if (file_exists($phpfile)) {
               $TRANSLATE->addTranslationFile('phparray', $phpfile, $domain, $_SESSION['glpilanguage']);
            }
            $formList[] = [
               'id'               => $form['id'],
               'name'             => __($form['name'], $domain),
               'icon'             => $form['icon'],
               'icon_color'       => $form['icon_color'],
               'background_color' => $form['background_color'],
               'description'      => __($form['description'], $domain),
               'type'             => 'form',
               'usage_count'      => $form['usage_count'],
               'is_default'       => $form['is_default'] ? "true" : "false"
            ];
         }
      }

      if (PluginFormcreatorEntityConfig::getUsedConfig('is_kb_separated', Session::getActiveEntity()) != PluginFormcreatorEntityconfig::CONFIG_KB_DISTINCT
         && Session::haveRight('knowbase', KnowbaseItem::READFAQ)
      ) {
         // Find FAQ entries
         $query_faqs = KnowbaseItem::getListRequest([
            'faq'      => '1',
            'contains' => $keywords
         ]);
         $subQuery = new DBMysqlIterator($DB);
         $subQuery->buildQuery($query_faqs);
         $query_faqs = '(' . $subQuery->getSQL() . ')';

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
                  'id'               => $faq['id'],
                  'name'             => $faq['name'],
                  'icon'             => '',
                  'icon_color'       => '',
                  'background_color' => '',
                  'description'      => '',
                  'type'             => 'faq',
                  'usage_count'      => $faq['view'],
                  'is_default'       => false
               ];
            }
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
               'OR' => [
                  "$table_form.language" => [$_SESSION['glpilanguage'], '0', '', null],
                  "$table_formLanguage.name" => $_SESSION['glpilanguage'],
               ],
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
               $table_formLanguage => [
                  'FKEY' => [
                     $table_form => 'id',
                     $table_formLanguage => PluginFormcreatorForm::getForeignKeyField(),
                  ]
               ]
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

   protected function showHeader(): void {
      $header = PluginFormcreatorEntityconfig::getUsedConfig('is_header_visible', Session::getActiveEntity(), 'header');
      echo Html::entity_decode_deep($header);
   }

   protected function showSearchBar() : void {
      echo '<form name="plugin_formcreator_search" onsubmit="javascript: return false;" >';
      echo '<input type="text" name="words" id="plugin_formcreator_search_input" required/>';
      echo '<span id="plugin_formcreator_search_input_bar"></span>';
      echo '<label for="plugin_formcreator_search_input">'.__('What are you looking for?', 'formcreator').'</label>';
      echo '</form>';
   }

   protected function showMyLastForms() : void {
      $limit = 5;
      $userId = Session::getLoginUserID();
      echo '<div class="plugin_formcreator_card">';
      echo '<div class="plugin_formcreator_heading">'.sprintf(__('My %1$d last forms (requester)', 'formcreator'), $limit).'</div>';
      $result = PluginFormcreatorFormAnswer::getMyLastAnswersAsRequester($limit);
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

      if (PluginFormcreatorCommon::canValidate()) {
         echo '<div class="plugin_formcreator_card">';
         echo '<div class="plugin_formcreator_heading">'.sprintf(__('My %1$d last forms (validator)', 'formcreator'), $limit).'</div>';
         $groupList = Group_User::getUserGroups($userId);
         $groupIdList = [];
         foreach ($groupList as $group) {
            $groupIdList[] = $group['id'];
         }
         $result = PluginFormcreatorFormAnswer::getMyLastAnswersAsValidator($limit);
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
    * @return void
    */
   public function displayUserForm() : void {
      global $TRANSLATE;

      // Print css media
      $css = '/' . Plugin::getWebDir('formcreator', false) . '/css/print_form.css';
      echo Html::css($css, ['media' => 'print']);

      $style = "<style>";
      // force colums width
      $width_percent = 100 / PluginFormcreatorSection::COLUMNS;
      for ($i = 0; $i < PluginFormcreatorSection::COLUMNS; $i++) {
         $width = ($i+1) * $width_percent;
         $style.= '
         #plugin_formcreator_form.plugin_formcreator_form [data-itemtype = "PluginFormcreatorQuestion"][data-gs-width="' . ($i+1) . '"],
         #plugin_formcreator_form.plugin_formcreator_form .plugin_formcreator_gap[data-gs-width="' . ($i+1) . '"]
         {
            min-width: ' . $width_percent . '%;
            width: ' . $width . '%;
         }
         ';
      }
      $style.= "</style>";
      echo $style;

      $formName = 'plugin_formcreator_form';
      $formId = $this->getID();
      self::getFormURL();
      echo '<form name="' . $formName . '" method="post" role="form" enctype="multipart/form-data"'
      . ' class="plugin_formcreator_form"'
      . ' action="' . self::getFormURL() . '"'
      . ' id="plugin_formcreator_form"'
      . '>';

      // load thanguage for the form, if any
      $domain = self::getTranslationDomain($formId);
      $phpfile = self::getTranslationFile($formId, $_SESSION['glpilanguage']);
      if (file_exists($phpfile)) {
         $TRANSLATE->addTranslationFile('phparray', $phpfile, $domain, $_SESSION['glpilanguage']);
      }
      // form title
      echo "<h1 class='form-title'>";
      echo __($this->fields['name'], $domain) . "&nbsp;";
      echo '<i class="fas fa-print" style="cursor: pointer;" onclick="window.print();"></i>';
      echo '</h1>';

      // Form Header
      if (!empty($this->fields['content'])) {
         echo '<div class="form_header">';
         echo html_entity_decode(__($this->fields['content'], $domain));
         echo '</div>';
      }

      echo '<ol>';

      if (!isset($_SESSION['formcreator']['data'])) {
         $_SESSION['formcreator']['data'] = [];
      }
      $sections = (new PluginFormcreatorSection)->getSectionsFromForm($formId);
      foreach ($sections as $section) {
         $sectionId = $section->getID();

         // Section header
         echo '<li'
         . ' class="plugin_formcreator_section"'
         . ' data-itemtype="' . PluginFormcreatorSection::class . '"'
         . ' data-id="' . $sectionId . '"'
         . '">';

         // section name
         echo '<h2>';
         echo empty($section->fields['name']) ? '(' . $sectionId . ')' : __($section->fields['name'], $domain);
         echo '</h2>';

         // Section content
         echo '<div>';
         // Display all fields of the section
         $questions = (new PluginFormcreatorQuestion())->getQuestionsFromSection($section->getID());
         $lastQuestion = null;
         foreach ($questions as $question) {
            if ($lastQuestion !== null) {
               if ($lastQuestion->fields['row'] < $question->fields['row']) {
                  // the question begins a new line
                  echo '<div class="plugin_formcreator_newRow"></div>';
               } else {
                  $x = $lastQuestion->fields['col'] + $lastQuestion->fields['width'];
                  $width = $question->fields['col'] - $x;
                  if ($x < $question->fields['col']) {
                     // there is an horizontal gap between previous question and current one
                     echo '<div class="plugin_formcreator_gap" data-gs-x="' . $x . '" data-gs-width="' . $width . '"></div>';
                  }
               }
            }
            echo $question->getRenderedHtml($domain, true, $_SESSION['formcreator']['data']);
            $lastQuestion = $question;
         }
         echo '</div>';

         echo '</li>';
      }

      // Captcha for anonymous forms
      if ($this->fields['access_rights'] == PluginFormcreatorForm::ACCESS_PUBLIC
         && $this->fields['is_captcha_enabled'] != '0') {
         $captchaTime = time();
         $captchaId = md5($captchaTime . $this->getID());
         $captcha = PluginFormcreatorCommon::getCaptcha($captchaId);
         echo '<li class="plugin_formcreator_section" id="plugin_formcreator_captcha_section">';
         echo '<h2>' . __('Are you a robot ?', 'formcreator') . '</h2>';
         echo '<div class="form-group line1"><label for="plugin_formcreator_captcha">' . __('Are you a robot ?', 'formcreator') . '</label>';
         echo '<div><i onclick="plugin_formcreator_refreshCaptcha()" class="fas fa-sync-alt"></i>&nbsp;<img src="' . $captcha['img'] . '">';
         echo '<div style="width: 50%; float: right" class="form_field"><span class="no-wrap">';
         echo Html::input('plugin_formcreator_captcha');
         echo Html::hidden('plugin_formcreator_captcha_id', ['value' => $captchaId]);
         echo '</div></div>';
         echo '</div>';
         echo '</li>';
      }

      // Delete saved answers if any
      unset($_SESSION['formcreator']['data']);

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

      echo Html::scriptBlock('$(function() {
         plugin_formcreator.showFields($("form[name=\'' . $formName . '\']"));
      })');

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
      if (isset($input['toggle'])) {
         // Enable / disable form
         return [
            'id' => $input['id'],
            'is_active' => $this->fields['is_active'] == '0' ? '1' : '0',
         ];
      }

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
         // TODO: this call is done in post_updateItem. Shoud probably be removed here
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
         PluginFormcreatorForm_Language::class,
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
   private function updateValidators() : void {
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

   public function increaseUsageCount() : void {
      // Increase usage count of the form
      $this->update([
            'id' => $this->getID(),
            'usage_count' => $this->getField('usage_count') + 1,
      ]);
   }

   /**
    * gets a form from database from a question
    *
    * @param int $questionId
    */
   public function getByQuestionId(int $questionId) : void {
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

   public function duplicate(array $options = []) {
      $linker = new PluginFormcreatorLinker($options);

      try {
         $export = $this->export(true);
         $new_form_id =  static::import($linker, $export);
      } catch (ImportFailureException $e) {
         $forms = $linker->getObjectsByType(PluginFormcreatorForm::class);
         $form = reset($forms);
         if ($form === null) {
            return false;
         }
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
      $formLanguage     = PluginFormcreatorForm_Language::getTable();

      if ($DB->tableExists($formTable)
          && $DB->tableExists($formProfileTable)
          && isset($_SESSION['glpiactiveprofile']['id'])) {
         $nb = $DB->request([
            'COUNT' => 'c',
            'FROM' => $formTable,
            'LEFT JOIN' => [
               $formLanguage => [
                  'FKEY' => [
                     $formLanguage => $formFk,
                     $formTable    => 'id',
                  ],
               ],
            ],
            'WHERE' => [
               "$formTable.is_active" => '1',
               "$formTable.is_deleted" => '0',
               'OR' => [
                  "$formTable.language" => [$_SESSION['glpilanguage'], '0', '', null],
                  "$formLanguage.name"  => $_SESSION['glpilanguage'],
               ],
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
            ] + (new DbUtils())->getEntitiesRestrictCriteria($formTable, '', '', (new self())->maybeRecursive()),

         ])->next()['c'];
      }

      return $nb;
   }

   public function export(bool $remove_uuid = false) : array {
      if ($this->isNewItem()) {
         throw new ExportFailureException(sprintf(__('Cannot export an empty object: %s', 'formcreator'), $this->getTypeName()));
      }

      $export = $this->fields;

      // replace entity id
      /** @var Entity */
      $entity = Entity::getById($export['entities_id']);
      $export['_entity'] = $entity->fields['completename'];

      // replace form category id
      $export['_plugin_formcreator_category'] = '';
      /** @var PluginFormcreatorCategory */
      $formCategory = PluginFormcreatorCategory::getById($export['plugin_formcreator_categories_id']);
      if ($formCategory instanceof CommonDBTM) {
         $export['_plugin_formcreator_category'] = $formCategory->fiels['completename'];
      }

      // remove non needed keys
      unset($export['plugin_formcreator_categories_id'],
            $export['entities_id'],
            $export['usage_count']);

      $subItems = [
         '_profiles'     => PluginFormcreatorForm_Profile::class,
         '_sections'     => PluginFormcreatorSection::class,
         '_conditions'   => PluginFormcreatorCondition::class,
         '_targets'      => (new self())->getTargetTypes(),
         '_validators'   => PluginFormcreatorForm_Validator::class,
         '_translations' => PluginFormcreatorForm_Language::class,
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
            if (!self::checkImportVersion($forms_toimport['schema_version'])) {
               Session::addMessageAfterRedirect(
                  __("Forms import impossible, the file was generated with another version", 'formcreator'),
                  false, ERROR
               );
               continue;
            }
         } else {
            Session::addMessageAfterRedirect(
               __("The file does not specifies the schema version. It was probably generated with a version older than 2.10. Giving up.", 'formcreator'),
               false, ERROR
            );
            continue;
         }

         // Get the total count of objects to import, for the progressbar
         $linker = new PluginFormcreatorLinker();
         foreach ($forms_toimport['forms'] as $form) {
            $linker->countItems($form, self::class);
         }
         $linker->initProgressBar();

         $success = true;
         foreach ($forms_toimport['forms'] as $form) {
            $linker->reset();
            set_time_limit(30);
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
                                                           $$form['name']));
            }
         }
         if ($success) {
            Session::addMessageAfterRedirect(sprintf(__("Forms successfully imported from %s", "formcreator"),
                                                      $filename));
         }
      }
   }

   /**
    * Check the version is compatible with the current one
    * for forms import
    *
    * @return boolean
    */
   public static function checkImportVersion($version) {
      // Convert version to X.Y
      $version = explode('.', $version);
      if (count($version) < 2) {
         return false;
      }
      $minorVersion = [array_shift($version)];
      $minorVersion[] = array_shift($version);
      $minorVersion = implode('.', $minorVersion);

      return version_compare(PLUGIN_FORMCREATOR_SCHEMA_VERSION, $minorVersion) == 0;
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
               throw new ImportFailureException('Failed to add or update the item');
            } else {
               // The form is in an entity which does not exists yet
               Session::addMessageAfterRedirect(
                  sprintf(__('The entity %1$s is required for the form %2$s.', 'formcreator'), $input['_entity'], $input['name']),
                  false,
                  WARNING
               );
               throw new ImportFailureException('Failed to add or update the item');
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
         throw new ImportFailureException(sprintf(__('Failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the form to the linker
      $linker->addObject($originalId, $item);

      $subItems = [
         '_profiles'     => PluginFormcreatorForm_Profile::class,
         '_sections'     => PluginFormcreatorSection::class,
         '_conditions'   => PluginFormcreatorCondition::class,
         '_targets'      => (new self())->getTargetTypes(),
         '_validators'   => PluginFormcreatorForm_Validator::class,
         '_translations' => PluginFormcreatorForm_Language::class,
      ];
      $item->importChildrenObjects($item, $linker, $subItems, $input);

      return $itemId;
   }

   public static function countItemsToImport(array $input) : int {
      // Code similar to ImportChildrenObjects
      $subItems = [
         '_profiles'   => PluginFormcreatorForm_Profile::class,
         '_sections'   => PluginFormcreatorSection::class,
         '_conditions' => PluginFormcreatorCondition::class,
         '_targets'    => (new self())->getTargetTypes(),
         '_validators' => PluginFormcreatorForm_Validator::class,
      ];
      return 1 + self::countChildren($input, $subItems);
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
      global $DB, $CFG_GLPI, $TRANSLATE;

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
            echo '<tr class="noHover" data-itemtype="PluginFormcreatorCategory" data-id="' . $currentCategoryId . '"><th>' . $categories[$currentCategoryId] . '</th></tr>';
         }

         // Show a row for the form
         $language = $_SESSION['glpilanguage'];
         $domain = PluginFormcreatorForm::getTranslationDomain($row['id'], $language);
         $phpfile = self::getTranslationFile($row['id'], $_SESSION['glpilanguage']);
         if (file_exists($phpfile)) {
            $TRANSLATE->addTranslationFile('phparray', $phpfile, $domain, $_SESSION['glpilanguage']);
         }

         echo '<tr class="tab_bg_' . ($i % 2 +1) . '" data-itemtype="PluginFormcreatorForm" data-id="' . $row['id'] . '">';
         echo '<td>';
         echo '<img src="' . $CFG_GLPI['root_doc'] . '/pics/plus.png" alt="+" title=""
               onclick="showDescription(' . $row['id'] . ', this)" align="absmiddle" style="cursor: pointer">';
         echo '&nbsp;';
         echo '<a href="' . FORMCREATOR_ROOTDOC
            . '/front/formdisplay.php?id=' . $row['id'] . '"
               title="' . __($row['description'], $domain) . '">'
            . __($row['name'], $domain)
            . '</a></td>';
         echo '</tr>';
         echo '<tr id="desc' . $row['id'] . '" class="tab_bg_' . ($i % 2) . ' form_description">';
         echo '<td><div>' . __($row['description'], $domain) . '&nbsp;</div></td>';
         echo '</tr>';
      }

      echo '</table>';
      echo '<br />';
      echo Html::scriptBlock('function showDescription(id, img){
         if(img.alt == "+") {
            img.alt = "-";
            img.src = "' . $CFG_GLPI['root_doc'] . '/pics/moins.png";
            document.getElementById("desc" + id).style.display = "table-row";
         } else {
            img.alt = "+";
            img.src = "' . $CFG_GLPI['root_doc'] . '/pics/plus.png";
            document.getElementById("desc" + id).style.display = "none";
         }
      }');
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
            Html::nullHeader(__('Form Creator', 'formcreator'), $_SERVER['PHP_SELF']);
            Html::displayMessageAfterRedirect();
            return true;
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
   public function isPublicAccess() : bool {
      if ($this->isNewItem()) {
         return false;
      }
      return ($this->fields['access_rights'] == self::ACCESS_PUBLIC);
   }

   /**
    * gets the form containing the given section
    *
    * @param PluginFormcreatorSection $section
    * @return boolean true if success else false
    */
   public function getFromDBBySection(PluginFormcreatorSection $item) {
      if ($item->isNewItem()) {
         return false;
      }
      return $this->getFromDB($item->fields[self::getForeignKeyField()]);
   }

   public function getFromDBByTarget(CommonDBTM $item) {
      if ($item->isNewItem()) {
         return false;
      }
      return $this->getFromDB($item->fields[self::getForeignKeyField()]);
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
    * @return PluginFormcreatorAbstractField[]
    */
   public function getFields() : array {
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
   public static function getTargetTypes() : array {
      return [
         PluginFormcreatorTargetTicket::class,
         PluginFormcreatorTargetChange::class
      ];
   }

   /**
    * get all targets associated to the form
    *
    * @param int $formId
    * @return array
    */
   public function getTargetsFromForm() : array {
      global $DB;

      $targets = [];
      if ($this->isNewItem()) {
         return [];
      }

      foreach (PluginFormcreatorForm::getTargetTypes() as $targetType) {
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

   public function showAddTargetForm() {
      echo '<form name="form_target" method="post" action="'.static::getFormURL().'">';
      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="4">'.__('Add a target', 'formcreator').'</th></tr>';

      echo '<tr>';
      echo '<td width="15%"><strong>'.__('Name').' <span style="color:red;">*</span></strong></td>';
      echo '<td width="40%"><input type="text" name="name" style="width:100%;" value="" required="required"/></td>';
      echo '<td width="15%"><strong>'._n('Type', 'Types', 1).' <span style="color:red;">*</span></strong></td>';
      echo '<td width="30%">';
      $targetTypes = [];
      foreach (PluginFormcreatorForm::getTargetTypes() as $targetType) {
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

      echo '<tr>';
      echo '<td colspan="4" class="center">';
      echo Html::hidden('plugin_formcreator_forms_id', ['value' => $this->getID()]);
      echo Html::submit(__('Add'), ['name' => 'add_target']);
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
      if (!in_array($itemtype, PluginFormcreatorForm::getTargetTypes())) {
         Session::addMessageAfterRedirect(
            __('Unsupported target type.', 'formcreator'),
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
      if (!in_array($itemtype, PluginFormcreatorForm::getTargetTypes())) {
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
      global $TRANSLATE;

      // Set additional data for the API
      if (isAPI()) {
         $this->fields += \PluginFormcreatorSection::getFullData($this->fields['id']);
      }

      // Load translation for the current language if different from origianl form's language
      $language = $_SESSION['glpilanguage'];
      $formId = $this->getID();
      if ($language != $this->fields['language']) {
         $eventManagerEnabled = $TRANSLATE->isEventManagerEnabled();
         $TRANSLATE->enableEventManager();
         $domain = PluginFormcreatorForm::getTranslationDomain($language, $formId);
         $TRANSLATE->getEventManager()->attach(
            Laminas\I18n\Translator\Translator::EVENT_MISSING_TRANSLATION,
            static function (Laminas\EventManager\EventInterface $event) use ($formId, $domain, $TRANSLATE) {
               if ($event->getParams()['text_domain'] == $domain) {
                  $file = PluginFormcreatorForm::getTranslationFile($formId);
                  if (!is_readable($file)) {
                     return;
                  }
                  $TRANSLATE->addTranslationFile('phparray', $file, $domain);
               }
            }
         );
         __('plugin_formcreator_load_check', $domain);

         if (!$eventManagerEnabled) {
            $TRANSLATE->disableEventManager();
         }
      }
      $_SESSION['formcreator']['languages'][$formId][$language] = true;
   }

   /**
    * Get the count of targets for this item
    */
   public function countTargets() {
      $nb = 0;
      foreach (PluginFormcreatorForm::getTargetTypes() as $targetType) {
         $nb += (new DbUtils())->countElementsInTable(
            $targetType::getTable(),
            [
               'WHERE' => [
                  self::getForeignKeyField() => $this->getID(),
               ]
            ]
         );
      }
      return $nb;
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

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude) : bool {
      return true;
   }

   /**
    * Get all translatable strings for a form, with optional search criterias
    *
    * @param array $options
    *              - language:      translation language when searching with is_translated criteria
    *              - id:
    *              - is_translated:
    *              - searchText:
    *
    * @return array
    */
   public function getTranslatableStrings(array $options = []) : array {
      $strings = [
         'itemlink' => [],
         'string'   => [],
         'text'     => [],
      ];

      $params = [
         'searchText'      => '',
         'id'              => '',
         'is_translated'   => null,
         'language'        => '', // Mandatory if is_translated is true or id is not empty
      ];
      $options = array_merge($params, $options);

      if ($this->isNewItem()) {
         return $strings;
      }

      $strings = $this->getMyTranslatableStrings($options);

      foreach ((new PluginFormcreatorSection())->getSectionsFromForm($this->getID()) as $section) {
         foreach ($section->getTranslatableStrings($options) as $type => $subStrings) {
            $strings[$type] = array_merge($strings[$type], $subStrings);
         }
      }

      foreach (self::getTargetTypes() as $targetType) {
         foreach ((new $targetType())->getTargetsForForm($this->getID()) as $target) {
            foreach ($target->getTranslatableStrings($options) as $type => $subStrings) {
               $strings[$type] = array_merge($strings[$type], $subStrings);
            }
         }
      }

      if ($options['is_translated'] !== null) {
         $translations = $this->getTranslations($options['language']);
         foreach ($strings as $type => $list) {
            if ($type == 'id') {
               continue;
            }
            foreach ($strings[$type] as $id => $original) {
               if ($options['is_translated'] === true && !isset($translations[$original])
                  || $options['is_translated'] === false && isset($translations[$original])) {
                  unset($strings[$type][$id]);
                  unset($strings['id'][$id]);
               }
            }
         }
      }

      $strings = $this->deduplicateTranslatable($strings);

      return $strings;
   }

   /**
    * Get the translation file for a form for a given language
    * @param int     $id         Form ID
    * @param string  $language   a language in the form fr_FR, rn_US
    * @return string             filename of the language resource
    */
   public static function getTranslationFile($id, $language = '') {
      $file = implode('/', [
         GLPI_LOCAL_I18N_DIR,
         'formcreator',
         self::getTranslationDomain($id, $language)
      ]) . '.php';

      if (!is_dir(dirname($file))) {
         mkdir(dirname($file), 0750, true);
      }

      return $file;
   }

   public static function getTranslationDomain($id, $language = '') {
      if ($language == '') {
         $language = $_SESSION['glpilanguage'];
      }
      return "form_${id}_${language}";
   }

   /**
    * get all translations for strings of the form
    * @param string $language the language to load (i.e. en_US)
    * @return array
    */
   public function getTranslations(string $language) : array {
      $file = $this->getTranslationFile($this->getID(), $language);
      if (!is_readable($file)) {
         return [];
      }

      if (function_exists('opcache_invalidate')) {
         opcache_invalidate($file, true);
      }
      $translations = include($file);
      if (!is_array($translations)) {
         return [];
      }
      return $translations;
   }

   /**
    * Overwrite translations with new data
    *
    * @param string $language
    * @param array  $translations array of translations
    *               - key original string
    *               - value: translated string
    * @return boolean true if sucess
    */
   public function setTranslations(string $language, array $translations) : bool {
      $file = $this->getTranslationFile($this->getID(), $language);
      if (is_file($file) && !is_writable($file)) {
         return false;
      }

      $output = "<?php" . PHP_EOL . "return " . var_export($translations, true) . ";";
      $written = file_put_contents(
         $file,
         $output
      );
      return ($written == strlen($output));
   }

   /**
    * Choose the best language for anonymous form
    *
    * @return string the best language for this form and session context
    */
   public function getBestLanguage() {
       global $DB;

      if ($this->isNewItem()) {
         return $_SESSION['glpilanguage'] ?? '';
      }

      // get original of the form, if any
      $availableLanguages = [];
      $defaultLanguage = '';
      if ($this->fields['language'] != '') {
         $availableLanguages = [$this->fields['language']];
         $defaultLanguage = $this->fields['language'];
      }
      if ($defaultLanguage == '') {
         $defaultLanguage = $_SESSION['glpilanguage'] ?? '';
      }

      //  get all available other languages for the form
      $formLanguageTable = PluginFormcreatorForm_Language::getTable();
      $formTable = PluginFormcreatorForm::getTable();
      $result = $DB->request([
         'SELECT'    => ["$formLanguageTable.name"],
         'FROM'      => $formLanguageTable,
         'LEFT JOIN' => [
            $formTable => [
               'FKEY' => [
                  $formTable => 'id',
                  $formLanguageTable => PluginFormcreatorForm::getForeignKeyField(),
               ],
            ],
         ],
         'WHERE'     => [
            "$formTable.id" => $this->getID()
         ],
      ]);
      foreach ($result as $row) {
         $availableLanguages[] = $row['name'];
      }

      if (count($availableLanguages) < 1) {
         // Empty array does let \Locale::lookup return the default language
         // @see https://www.php.net/manual/fr/locale.lookup.php#115459
         $availableLanguages = [false];
      }
      return \Locale::lookup($availableLanguages, $_SESSION['glpilanguage'], false, $defaultLanguage);
   }
}
