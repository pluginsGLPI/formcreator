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
   const VALIDATION_STATUS_NONE      = 1;
   const VALIDATION_STATUS_WAITING   = 2;
   const VALIDATION_STATUS_ACCEPTED  = 3;
   const VALIDATION_STATUS_REFUSED   = 4;

   public static function getEnumValidationStatus() {
      return [
         self::VALIDATION_STATUS_NONE     => __('None', 'formcreator'),
         self::VALIDATION_STATUS_WAITING  => __('Waiting', 'formcreator'),
         self::VALIDATION_STATUS_ACCEPTED => __('Accepted', 'formcreator'),
         self::VALIDATION_STATUS_REFUSED  => __('Refused', 'formcreator'),
      ];
   }

   public static function getTypeName($nb = 0) {
      return _n('Validator', 'Validators', $nb, 'formcreator');
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      /** @var CommonDBTM $item */

      if ($item instanceof PluginFormcreatorForm) {
         return $this->getTypeName();
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
      global $DB, $CFG_GLPI;

      echo "<form method='post' action='".self::getFormURL()."'>";
      echo "<div class='spaced'><table class='tab_cadre_fixe'>";

      echo '<tr class="tab_bg_2">';
      echo '<td>' . __('Need validaton?', 'formcreator') . '</td>';
      echo '<td class="validators_bloc">';

      Dropdown::showFromArray('validation_required', [
         self::VALIDATION_NONE  => __('No'),
         self::VALIDATION_USER  => User::getTypeName(1),
         self::VALIDATION_GROUP => Group::getTypeName(1),
      ], [
         'value'     =>  $item->fields['validation_required'],
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
         "$userTable.id" => new QuerySubQuery($subQuery)
      ];
      $formValidator = new PluginFormcreatorForm_Validator();
      $selectedValidatorUsers = [];
      foreach ($formValidator->getValidatorsForForm($item) as $user) {
         if ($user::getType() != User::getType()) {
            continue;
         }
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
         'multiple' => true,
         'entity_restrict' => -1,
         'itemtype'        => User::getType(),
         'values'          => array_keys($selectedValidatorUsers),
         'valuesnames'     => array_values($selectedValidatorUsers),
         'condition'       => Dropdown::addNewCondition($usersCondition),
      ];
      $params['_idor_token'] = Session::getNewIDORToken(User::getType());
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
         "$groupTable.id" => new QuerySubQuery($subQuery),
      ];
      $groups = $DB->request([
         'SELECT' => ['id' ,'name'],
         'FROM'   => Group::getTable(),
         'WHERE'  => $groupsCondition,
      ]);
      $formValidator = new PluginFormcreatorForm_Validator();
      $selectecValidatorGroups = [];
      foreach ($formValidator->getValidatorsForForm($item) as $group) {
         if ($group::getType() != Group::getType()) {
            continue;
         }
         $selectecValidatorGroups[$group->getID()] = $group->fields['name'];
      }
      $validatorGroups = [];
      foreach ($groups as $group) {
         $validatorGroups[$group['id']] = $group['name'];
      }
      echo '<div id="validators_groups" style="width: 100%">';
      echo Group::getTypeName() . '&nbsp';
      $params = [
         'multiple' => true,
         'entity_restrict' => -1,
         'itemtype'        => Group::getType(),
         'values'          => array_keys($selectecValidatorGroups),
         'valuesnames'     => array_values($selectecValidatorGroups),
         'condition'       => Dropdown::addNewCondition($groupsCondition),
         'display_emptychoice' => false,
      ];
      $params['_idor_token'] = Session::getNewIDORToken(Group::getType());
      echo Html::jsAjaxDropdown(
         '_validator_groups[]',
         '_validator_groups' . mt_rand(),
         $CFG_GLPI['root_doc']."/ajax/getDropdownValue.php",
         $params
      );
      echo '</div>';

      $script = '$(document).ready(function() {plugin_formcreator_changeValidators(' . $item->fields["validation_required"] . ');});';
      echo Html::scriptBlock($script);

      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo "<td colspan='4' class='center'>";
      echo Html::hidden(PluginFormcreatorForm::getForeignKeyField(), ['value' => $item->getID()]);
      echo Html::submit(_x('button', 'Save'), ['name' => 'save']);
      echo "</td>";
      echo '</tr>';

      echo "</table></div>";
      Html::closeForm();
   }

   public function post_deleteItem() {
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $rows = $this->find(
         [
            $formFk => $this->fields[$formFk],
         ], [
            'level ASC'
         ]
      );

      // count items with the same level as the deleted item
      $currentLevelCount = 0;
      foreach ($rows as $row) {
         if ($row['level'] == $this->fields['level']) {
            $currentLevelCount++;
         }
      }

      if ($currentLevelCount < 1) {
         // No more items for this level. Moving decreasing level of above levels
         foreach ($rows as $row) {
            if ($row['level'] < $this->fields['level']) {
               continue;
            }
            $toUpdate = new self();
            $toUpdate->update([
               'id' => $row['id'],
               'level' => $row['level'] - 1,
            ]);
         }
      }
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $forms_id = 0) {
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $input[$formFk] = $forms_id;

      $item = new self();
      // Find an existing form to update, only if an UUID is available
      $itemId = false;
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
    * Get validators to a form
    *
    * @param PluginFormcreatorForm $form
    * @return User[]|Group[] array of User or Group objects
    */
   public static function getValidatorsForForm(PluginFormcreatorForm $form): array {
      global $DB;

      switch ($form->fields['validation_required']) {
         case PluginFormcreatorForm::VALIDATION_USER:
            $itemtype = User::class;
            break;

         case PluginFormcreatorForm::VALIDATION_GROUP:
            $itemtype = Group::class;
            break;

         default:
            return [];
      }

      $formValidatorTable = PluginFormcreatorForm_Validator::getTable();
      $formTable = PluginFormcreatorForm::getTable();
      $formFk = PluginFormcreatorForm::getForeignKeyField();

      $rows = $DB->request([
         'SELECT' => [$formValidatorTable => [
               'itemtype',
               'items_id',
            ],
         ],
         'FROM' => $formTable,
         'INNER JOIN' => [
            $formValidatorTable => [
              'FKEY' => [
                  $formTable => 'id',
                  $formValidatorTable => $formFk,
                  [
                     'AND' => [
                        'OR' => [
                           [
                              'AND' => [
                                 $formTable.'.validation_required' => 1,
                                 $formValidatorTable.'.itemtype' => 'User'
                              ],
                           ],
                           [
                              'AND' => [
                                 $formTable.'.validation_required' => 2,
                                 $formValidatorTable.'.itemtype' => 'Group'
                              ],
                           ],
                        ],
                     ],
                  ],
               ]
            ],
         ],
         'WHERE' => [
           "$formValidatorTable.$formFk" => $form->getID(),
         ],
      ]);
      $result = [];
      foreach ($rows as $row) {
         $itemtype = $row['itemtype'];
         $item = new $itemtype();
         if ($item->getFromDB($row['items_id'])) {
            $result[$row['items_id']] = $item;
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
    * Get HTML for multislect dropdown of validator users
    *
    * @return string
    */
   public static function dropdownValidatorUser(): string {
      global $CFG_GLPI;

      $userTable = User::getTable();
      $userFk = User::getForeignKeyField();
      $profileUserTable = Profile_User::getTable();
      $profileTable = Profile::getTable();
      $profileFk = Profile::getForeignKeyField();
      $profileRightTable = ProfileRight::getTable();
      $usersCondition = [
         "$userTable.id" => new QuerySubQuery([
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
                     "$profileRightTable.rights" => ['&', TicketValidation::VALIDATEREQUEST | TicketValidation::VALIDATEINCIDENT],
                  ],
               ],
               "$userTable.is_active" => '1',
            ],
         ])
      ];

      $params = [
         'specific_tags' => [
            'multiple' => 'multiple',
         ],
         'entity_restrict' => -1,
         'itemtype'        => User::getType(),
         'condition'       => Dropdown::addNewCondition($usersCondition),
         '_idor_token'     => Session::getNewIDORToken(User::getType()),
      ];

      return Html::jsAjaxDropdown(
         '_validator_users[]',
         '_validator_users' . mt_rand(),
         $CFG_GLPI['root_doc']."/ajax/getDropdownValue.php",
         $params
      );
   }

   /**
    * Get HTML for multislect dropdown of validator groups
    *
    * @return string
    */
   public static function dropdownValidatorGroup(): string {
      global $CFG_GLPI;

      $userTable = User::getTable();
      $userFk = User::getForeignKeyField();
      $groupTable = Group::getTable();
      $groupFk = Group::getForeignKeyField();
      $profileUserTable = Profile_User::getTable();
      $profileTable = Profile::getTable();
      $profileFk = Profile::getForeignKeyField();
      $profileRightTable = ProfileRight::getTable();
      $groupUserTable = Group_User::getTable();
      $groupsCondition = [
         "$groupTable.id" => new QuerySubQuery([
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
                     "$profileRightTable.rights" => ['&', TicketValidation::VALIDATEREQUEST | TicketValidation::VALIDATEINCIDENT],
                  ],
               ],
               "$userTable.is_active" => '1',
            ],
         ]),
      ];

      $params = [
         'specific_tags' => [
            'multiple' => 'multiple',
         ],
         'entity_restrict'     => -1,
         'itemtype'            => Group::getType(),
         'condition'           => Dropdown::addNewCondition($groupsCondition),
         'display_emptychoice' => false,
         '_idor_token'         => Session::getNewIDORToken(Group::getType()),
      ];

      return Html::jsAjaxDropdown(
         '_validator_groups[]',
         '_validator_groups' . mt_rand(),
         $CFG_GLPI['root_doc']."/ajax/getDropdownValue.php",
         $params
      );
   }

   /**
    * Get HTML ffor a dropdown to select one validator among valdiator groups or users
    * @return string
    *
    */
   public static function dropdownValidator(PluginFormcreatorForm $form): string {
      if (Plugin::isPluginActive(PLUGIN_FORMCREATOR_ADVANCED_VALIDATION)) {
         return PluginAdvformForm_Validator::dropdownValidator($form);
      }

      if (!$form->validationRequired()) {
         return '';
      }

      $validators = [];
      $formValidator = new PluginFormcreatorForm_Validator();
      // Validators of either user type or group type
      switch ($form->fields['validation_required']) {
         case PluginFormcreatorForm_Validator::VALIDATION_GROUP:
            $itemtype = Group::class;
            break;
         case PluginFormcreatorForm_Validator::VALIDATION_USER:
            $itemtype = User::class;
            break;
      }
      $result = $formValidator->getValidatorsForForm($form);
      foreach ($result as $validator) {
         if ($validator::getType() != $itemtype) {
            continue;
         }
         $validatorId = $validator->getID();
         $validators["{$itemtype}_{$validatorId}"] = $validator->getFriendlyName();
         $lastValidatorId = $validatorId;
         $lastValidatorItemtype = $itemtype;
      }

      $totalCount = count($result);

      if ($totalCount < 1) {
         return '';
      }

      if ($totalCount == 1) {
         reset($validators);
         $validatorId = key($validators);
         return Html::hidden('formcreator_validator', ['value' => "{$lastValidatorItemtype}_{$lastValidatorId}"]);
      }

      $out = '';
      $out .= '<h2>' . __('Validation', 'formcreator') . '</h2>';
      $out .= '<div class="form-group required liste" id="form-validator">';
      $out .= '<label>' . __('Choose a validator', 'formcreator') . ' <span class="red">*</span></label>';
      $out .= Dropdown::showFromArray(
         'formcreator_validator',
         $validators, [
            'display' => false,
            'display_emptychoice' => true,
         ]
      );
      $out .= '</div>';

      return $out;
   }
}
