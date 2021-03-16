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

/**
 * @since 0.90-1.5
 */
class PluginFormcreatorForm_Validator extends CommonDBRelation implements
PluginFormcreatorExportableInterface
{
   use PluginFormcreatorExportableTrait;

   // From CommonDBRelation
   static public $itemtype_1          = PluginFormcreatorForm::class;
   static public $items_id_1          = 'plugin_formcreator_forms_id';

   static public $itemtype_2          = 'itemtype';
   static public $items_id_2          = 'items_id';
   static public $checkItem_2_Rights  = self::HAVE_VIEW_RIGHT_ON_ITEM;

   const VALIDATION_NONE  = 0;
   const VALIDATION_USER  = 1;
   const VALIDATION_GROUP = 2;

   // status
   const VALIDATION_STATUS_NONE      = 1; // none
   const VALIDATION_STATUS_WAITING   = 2; // waiting
   const VALIDATION_STATUS_ACCEPTED  = 3; // accepted
   const VALIDATION_STATUS_REFUSED   = 4; // rejected

   public static function getTypeName($nb = 0) {
      return _n('Validator', 'Validators', $nb, 'formcreator');
   }

   public  function getValidatorsCount(PluginFormcreatorForm $item) {
      global $DB;
      $formTable = PluginFormcreatorForm::getTable();
      $formValidatorTable = self::getTable();
      $count = $DB->request([
         'COUNT' => 'c',
         'FROM' => $formValidatorTable,
         'INNER JOIN' => [
            $formTable => [
               'FKEY' => [
                  $formTable => 'id',
                  $formValidatorTable => PluginFormcreatorForm::getForeignKeyField(),
               ]
            ]
         ],
         'WHERE' => [
            "$formTable.id" => $item->getID(),
         ]
      ])->next();
      return $count !== null ? $count['c'] : 0;
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      /** @var CommonDBTM $item */

      switch ($item->getType()) {
         case PluginFormcreatorForm::class:
            return self::createTabEntry(
                  $this->getTypeName(),
                  $this->getValidatorsCount($item)
            );
            break;
      }
      return '';
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case PluginFormcreatorForm::class:
            $itemformValitator = new self();
            $itemformValitator->showForForm($item);
      }
   }

   public function prepareInputForAdd($input) {
      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public function showForForm(PluginFormcreatorForm $item, $options = []) {
      $formId = $item->getID();
      $rand = mt_rand();

      $canEdit = Session::haveRight('entity', UPDATE);

      if ($canEdit) {
         // Global validation settings
         echo "<form method='post' action='".self::getFormURL()."'>";
         echo "<div class='spaced'><table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><th colspan='3'>";
         echo __('General settings', 'formcreator');
         echo "</th>";
         echo "<tr><td width='20%'>";
         echo __('Minimum validation required', 'formcreator');
         echo "</td><td>";
         echo Dropdown::showNumber('validation_percent', [
            'min'     => 0,
            'max'     => 100,
            'step'    => 50,
            'value'   => $item->fields['validation_percent'],
            'display' => false,
         ]);
         echo "</td><td width='20%'>";
         echo "<input type='hidden' name='plugin_formcreator_forms_id' value='$formId'>";
         echo "<input type='submit' name='set_validation_percent' value=\""._sx('button', 'Save')."\"
                class='submit'>";
         echo "</td>";
         echo "</tr>";
         echo "</table></div>";
         Html::closeForm();

         echo "<form method='post' action='".self::getFormURL()."'>";
         echo "<div class='spaced'><table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><td class='center'>";
         echo __('Validation level', 'formcreator');
         echo "</td><td width='20%'>";
         echo User::getTypeName(Session::getPluralNumber());
         echo "</td><td width='20%'>";
         echo Group::getTypeName(Session::getPluralNumber());
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'><td class='center'>";
         echo "<input type='hidden' name='plugin_formcreator_forms_id' value='$formId'>";

         echo self::dropdownLevel($item);
         echo "</td><td width='20%'>";
         echo $this->dropdownValidatorUser($item);
         echo "</td><td width='20%'>";
         echo $this->dropdownValidatorGroup($item);
         echo "</td><td width='20%'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\"
                class='submit'>";
         echo "</td>";
         echo "</tr>";
         echo "</table></div>";
         Html::closeForm();
      }

      // Show current validators
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $rows = $this->find([$formFk => $formId], ['level ASC']);

      $rand = mt_rand();
      Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
      $massiveactionparams
         = ['num_displayed'
                   => min($_SESSION['glpilist_limit'], count($rows)),
                 'container'
                   => 'mass'.__CLASS__.$rand,
                 'specific_actions'
                   => ['purge' => _x('button', 'Delete permanently')]];

      Html::showMassiveActions($massiveactionparams);
      echo '<table class="tab_cadre_fixehov">';
      $header_begin  = '<tr>';
      $header_top    = '';
      $header_bottom = '';
      $header_end    = '';
      if ($canEdit) {
         $header_begin  .= '<th width="10">';
         $header_top    .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_bottom .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_end    .= '</th>';
      }
      $header_end .= '<th>' . __('Type', 'formcreator') . '</th><th>' . __('Name') . '</th>';
      $header_end .= '<th>' .__('Level', 'formcreator').'</th>';
      $header_end .= '</tr>';
      echo $header_begin.$header_top.$header_end;

      foreach ($rows as $row) {
         $itemtype = $row['itemtype'];
         $validator = new $itemtype();
         if (!$validator->getFromDB($row['items_id'])) {
            continue;
         }
         $validatorId = $row['items_id'];
         switch ($itemtype) {
            case User::class:
               $name = formatUserName($validatorId, $validator->fields['name'], $validator->fields['realname'], $validator->fields['firstname']);
               break;
            default:
               $name = $validator->fields['name'];
               break;
         }
         $typeName = $validator::getTypeName();
         echo '<tr>';
         echo '<td>';
         Html::showMassiveActionCheckBox(__CLASS__, $row['id']);
         echo '</td>';
         echo '<td>' . $typeName . '</td>';
         echo '<td>' . $name . '</td>';
         echo '<td>' . $row['level'] . '</td>';
         echo '<tr>';
      }

      echo $header_begin.$header_bottom.$header_end;
      echo "</table>";
      $massiveactionparams['ontop'] = false;
      Html::showMassiveActions($massiveactionparams);
      Html::closeForm();
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $forms_id = 0) {
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $input[$formFk] = $forms_id;

      $item = new self();
       /** @var string $idKey key to use as ID (id or uuid) */
       $idKey = 'id';
      if (isset($input['uuid'])) {
         $idKey = 'uuid';
         $itemId = plugin_formcreator_getFromDBByField(
            $item,
            'uuid',
            $input['uuid']
         );
      }
      // Find the validator
      if (!in_array($input['itemtype'], [User::class, Group::class])) {
         return false;
      }
      $linkedItemtype = $input['itemtype'];
      $linkedItem = new $linkedItemtype();
      $crit = [
         'name' => $input['_item'],
      ];
      if (!$linkedItem->getFromDBByCrit($crit)) {
         // validator not found. Let's ignore it
         return false;
      }
      $input['items_id'] = $linkedItem->getID();

      // Add or update the form validator
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

      // add the item to the linker
      if (isset($input['uuid'])) {
         $originalId = $input['uuid'];
      }
      $linker->addObject($originalId, $item);

      return $itemId;
   }

   public static function countItemsToImport(array $input) : int {
      return 1;
   }

   /**
    * Export in an array all the data of the current instanciated validator
    * @param bool $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export(bool $remove_uuid = false) : array {
      if ($this->isNewItem()) {
         throw new ExportFailureException(sprintf(__('Cannot export an empty object: %s', 'formcreator'), $this->getTypeName()));
      }

      $validator = $this->fields;

      // remove key and fk
      unset($validator['plugin_formcreator_forms_id']);

      if (is_subclass_of($validator['itemtype'], CommonDBTM::class)) {
         $validator_obj = new $validator['itemtype'];
         if ($validator_obj->getFromDB($validator['items_id'])) {

            // replace id data
            $identifier_field = isset($validator_obj->fields['completename'])
                                 ? 'completename'
                                 : 'name';
            $validator['_item'] = $validator_obj->fields[$identifier_field];
         }
      }
      unset($validator['items_id']);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }
      unset($validator[$idToRemove]);

      return $validator;
   }

   /**
    * Get validators of type $itemtype associated to a form
    *
    * @param PluginFormcreatorForm $form
    * @param string $itemtype
    * @return User[]|Group[] array of User or Group objects
    */
   public function getValidatorsForForm(PluginFormcreatorForm $form, $itemtype) {
      global $DB;

      if (!in_array($itemtype, [User::class, Group::class])) {
         return [];
      }

      $formValidatorTable = PluginFormcreatorForm_Validator::getTable();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $itemTable = $itemtype::getTable();

      $rows = $DB->request([
         'SELECT' => [
            $itemTable => ['id']
         ],
         'FROM' => $itemTable,
         'LEFT JOIN' => [
            $formValidatorTable => [
               'FKEY' => [
                  $formValidatorTable => 'items_id',
                  $itemTable => 'id'
               ]
            ],
         ],
         'WHERE' => [
            "$formValidatorTable.itemtype" => $itemtype,
            "$formValidatorTable.$formFk" => $form->getID(),
         ],
      ]);
      $result = [];
      foreach ($rows as $row) {
         $item = new $itemtype();
         if ($item->getFromDB($row['id'])) {
            $result[$row['id']] = $item;
         }
      }

      return $result;
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude) : bool {
      $keepCriteria = [
         self::$items_id_1 => $container->getID(),
      ];
      if (count($exclude) > 0) {
         $keepCriteria[] = ['NOT' => ['id' => $exclude]];
      }
      return $this->deleteByCriteria($keepCriteria);
   }

   /**
    * Get users eligible for validation
    *
    * @return DBmysqlIterator
    */
   public function getAvailableValidatorUsers() {
      global $DB;

      // Select all users with ticket validation right and the groups
      $userTable = User::getTable();
      $userFk = User::getForeignKeyField();
      $profileUserTable = Profile_User::getTable();
      $profileTable = Profile::getTable();
      $profileFk = Profile::getForeignKeyField();
      $profileRightTable = ProfileRight::getTable();
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
      return $DB->request([
         'SELECT' => ['id', 'name'],
         'FROM' => User::getTable(),
         'WHERE' => $usersCondition,
      ]);
   }

   /**
    * Get HTML for multislect dropdown of validator users
    *
    * @param PluginFormcreatorForm $form
    * @return string
    */
   public function dropdownValidatorUser(PluginFormcreatorForm $form) {
      // get all posible validator users
      $users = $this->getAvailableValidatorUsers();
      $validatorUsers = [];
      foreach ($users as $user) {
         $validatorUsers[$user['id']] = $user['name'];
      }

      return Dropdown::showFromArray(
         '_validator_users',
         $validatorUsers, [
            'multiple' => true,
            'values' => [],
            'display' => false,
         ]
      );
   }

   /**
    * Get groups eligible for validation
    *
    * @return DBmysqlIterator
    */
   public function getAvailableValidatorGroups() {
      global $DB;

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
      return $DB->request([
         'SELECT' => ['id' ,'name'],
         'FROM'   => Group::getTable(),
         'WHERE'  => $groupsCondition,
      ]);

   }

   /**
    * Get HTML for multislect dropdown of validator groups
    *
    * @param PluginFormcreatorForm $form
    * @return void
    */
   public function dropdownValidatorGroup(PluginFormcreatorForm $form) {
      // get all posible validator groups
      $groups = $this->getAvailableValidatorGroups();
      $validatorGroups = [];
      foreach ($groups as $group) {
         $validatorGroups[$group['id']] = $group['name'];
      }

      return Dropdown::showFromArray(
         '_validator_groups',
         $validatorGroups, [
            'multiple' => true,
            'values' => [],
            'display' => false,
         ]
      );
   }

   /**
    * Add several users and groups at once
    *
    * @param array $input
    * @return bool true on success, false otherwise
    */
   public function addMultipleItems($input) {
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $success = true;
      if (isset($input['_validator_users']) && is_array($input['_validator_users'])) {
         $newItems = [];
         foreach ($input['_validator_users'] as $userId) {
            $newId = $this->add([
               $formFk => $input[$formFk],
               'itemtype' => User::getType(),
               'items_id' => $userId,
               'level' => $input['level'],
            ]);
            if ($newId === false) {
               $success = false;
            } else {
               $newItems[] = $newId;
            }
         }
      }
      if (isset($input['_validator_groups']) && is_array($input['_validator_groups'])) {
         foreach ($input['_validator_groups'] as $userId) {
            $newId = $this->add([
              $formFk => $input[$formFk],
              'itemtype' => Group::getType(),
              'items_id' => $userId,
              'level' => $input['level'],
            ]);
            if ($newId === false) {
               $success = false;
            }
         }
      }
      return $success;
   }

   /**
    * Get HTML ffor a dropdown to select one validator among valdiator groups and users
    * @return string
    *
    */
   public static function dropdownValidator(PluginFormcreatorForm $form): string {
      $totalCount = 0;
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $formValidator = new self();
      $rows = $formValidator->find(
         [$formFk => $form->getID(), 'level' => '1'],
         ['itemtype ASC']
      );
      $items = [];
      $lastValidatorId = 0;
      $lastValidatorItemtype = '';
      foreach ($rows as $row) {
         $itemtype = $row['itemtype'];
         $validator = new $itemtype();
         if (!$validator->getFromDB($row['items_id'])) {
            continue;
         }
         $validatorId = $row['items_id'];
         switch ($itemtype) {
            case User::class:
               $name = formatUserName($validatorId, $validator->fields['name'], $validator->fields['realname'], $validator->fields['firstname']);
               break;
            default:
               $name = $validator->fields['name'];
               break;
         }
         $items[$itemtype]["${itemtype}_${validatorId}"] = $name;
         $lastValidatorId = $validatorId;
         $lastValidatorItemtype = $itemtype;
         $totalCount++;
      };

      if ($totalCount < 1) {
         return '';
      }

      $out = '';
      if ($totalCount == 1) {
         $out .= Html::hidden('formcreator_validator', ['value' => "${lastValidatorItemtype}_${lastValidatorId}"]);
         return $out;
      }

      $out .= '<h2>' . __('Validation', 'formcreator') . '</h2>';
      $out .= '<div class="form-group required liste" id="form-validator">';
      $out .= '<label>' . __('Choose a validator', 'formcreator') . ' <span class="red">*</span></label>';
      $out .= Dropdown::showFromArray('formcreator_validator', $items, ['display' => false]);
      $out .= '</div>';
      return $out;
   }

   /**
    * Get HTML of dropdown to select a validation level
    *
    * @param PluginFormcreatorForm $form
    * @return string
    */
   public static function dropdownLevel(PluginFormcreatorForm $form): string {
      global $DB;
      $out = '';

      // Find current maximum valdiation level
      $formTable = PluginFormcreatorForm::getTable();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $formValidatorTable = self::getTable();
      $maxLevel = $DB->request([
         'SELECT' => ['MAX' => 'level as m'],
         'FROM' => $formValidatorTable,
         'INNER JOIN' => [
            $formTable => [
               'FKEY' => [
                  $formTable => 'id',
                  $formValidatorTable => $formFk,
               ],
            ],
         ],
         'WHERE' => [
            $formFk => $form->getID(),
         ]
      ])->next();
      $maxLevel = $maxLevel === null ? 0 : $maxLevel['m'];
      $maxLevel = $maxLevel > 4 ? 4 : $maxLevel;

      $out .= Dropdown::showNumber('level', ['display' => false, 'min' => '1', 'max' => $maxLevel + 1]);
      return $out;
   }
}
