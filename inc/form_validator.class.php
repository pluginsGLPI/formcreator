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
use Glpi\Application\View\TemplateRenderer;

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

   const VALIDATION_NONE       = 0;
   const VALIDATION_USER       = 1;
   const VALIDATION_GROUP      = 2;
   const VALIDATION_SUPERVISOR = 3;

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

   public static function getEnumValidation() {
      $output = [
         self::VALIDATION_NONE       => __('No', 'formcretor'),
         self::VALIDATION_USER       => User::getTypeName(1),
         self::VALIDATION_GROUP      => Group::getTypeName(1),
         self::VALIDATION_SUPERVISOR => __('Supervisor of the requester', 'formcretor'),
      ];
      if (version_compare(GLPI_VERSION, '10.1') < 0) {
         unset($output[self::VALIDATION_SUPERVISOR]);
      }

      return $output;
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
      // Check the form exists
      $form = new PluginFormcreatorForm();
      if (!$form->getFromDB($input[PluginFormcreatorForm::getForeignKeyField()])) {
         Session::addMessageAfterRedirect(__('Form not found.', 'formcreator'), false, ERROR);
         return [];
      }

      if (!isset($input['itemtype'])) {
         Session::addMessageAfterRedirect(__('Invalid validator.', 'formcreator'), false, ERROR);
         return [];
      }

      switch ($input['itemtype']) {
         case User::class:
            $user = User::getById($input['items_id']);
            if (!($user instanceof User)) {
               Session::addMessageAfterRedirect(__('Invalid validator.', 'formcreator'), false, ERROR);
               return [];
            }
            // TODO: check if the user has at least 1 profile with validation right
            break;

         case Group::class:
            $group = Group::getById($input['items_id']);
            if (!($group instanceof Group)) {
               Session::addMessageAfterRedirect(__('Invalid validator.', 'formcreator'), false, ERROR);
               return [];
            }
            // TODO: check if the group has at least one member with validation right
            break;

         case PluginFormcreatorSupervisorValidator::class:
            if ($input['level'] != 1) {
               Session::addMessageAfterRedirect(sprintf(
                  __('Only level 1 is allowed for %s.', 'formcreator'),
                  PluginFormcreatorSupervisorValidator::getTypeName(1)
               ), false, ERROR);
               return [];
            }
            $input['items_id'] = 0;
            break;

         default:
            Session::addMessageAfterRedirect(__('Invalid validator type.', 'formcreator'), false, ERROR);
            return [];
            break;
      }

      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public function showForForm(PluginFormcreatorForm $item, $options = []) {
      global $DB;

      $canEdit = Session::haveRight(PluginFormcreatorForm::$rightname, UPDATE);

      if ($canEdit) {
         TemplateRenderer::getInstance()->display('@formcreator/pages/form.validation.html.twig', [
            'item'           => $item,
            'options'        => $options,
            'params'         => [
               'candel'         => false,
            ],
            'all_validators' => $DB->request(self::getAllValidators($item)),
         ]);
      }
   }

   public function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '5',
         'table'              => self::getTable(),
         'field'              => 'level',
         'name'               => __('Level', 'formcreator'),
         'datatype'           => 'integer',
         'massiveaction'      => false,
      ];

      return $tab;
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

      // Modify the input to match the DB structure
      $input[$input['itemtype']::getForeignKeyField()] = $input['items_id'];

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

   public function post_addItem() {
      if (in_array(PluginFormcreatorSpecificValidator::class, class_implements($this->fields['itemtype']))) {
         return;
      }

      parent::post_addItem();
   }

   public function post_deleteFromDB() {
      if (in_array(PluginFormcreatorSpecificValidator::class, class_implements($this->fields['itemtype']))) {
         return;
      }

      parent::post_deleteFromDB();
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
    * @return array of User or Group objects. 1st dimension is the itemtype
    *               2nd dimension is the items_id
    * @param array  $condition
    */
   public static function getValidatorsForForm(PluginFormcreatorForm $form, array $condition = []): array {
      global $DB;

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
               ]
            ],
         ],
         'WHERE' => $condition + [
           "$formValidatorTable.$formFk" => $form->getID(),
         ],
      ]);
      $result = [];
      foreach ($rows as $row) {
         $itemtype = $row['itemtype'];
         if (!isset($result[$itemtype])) {
            $result[$itemtype] = [];
         }
         if (in_array(PluginFormcreatorSpecificValidator::class, class_implements($itemtype))) {
            if (($current_user_id = Session::getLoginUserID()) === false) {
               continue;
            }
            $current_user = User::getById($current_user_id);
            if (!($current_user instanceof User)) {
               continue;
            }
            if (User::isNewID($current_user->fields['users_id_supervisor'])) {
               continue;
            }
            $result[$itemtype][0] = new $itemtype();
         } else {
            $item = $itemtype::getById($row['items_id']);
            if (!($item instanceof $itemtype)) {
               continue;
            }
            $result[$itemtype][$row['items_id']] = $item;
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
      ];
      $params['_idor_token'] = Session::getNewIDORToken(
          User::getType(),
          [
              'condition' => $params['condition'],
          ]
      );

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
      ];
      $params['_idor_token'] = Session::getNewIDORToken(
          Group::getType(),
          [
              'condition' => $params['condition'],
          ]
      );

      return Html::jsAjaxDropdown(
         '_validator_groups[]',
         '_validator_groups' . mt_rand(),
         $CFG_GLPI['root_doc']."/ajax/getDropdownValue.php",
         $params
      );
   }

   /**
    * Get HTML for a dropdown to select one validator among valdiator groups or users
    *
    * If the supervisor of the user is also a declared validator, this
    * supervisor will appear only once in the dropdown.
    *
    * @return string
    *
    */
   public static function dropdownValidator(PluginFormcreatorForm $form): string {
      if (!$form->validationRequired()) {
         return '';
      }

      $validators = [];

      $result = PluginFormcreatorForm_Validator::getValidatorsForForm($form, ['level' => '1']);
      foreach ($result as $itemtype => $items) {
         if (in_array(PluginFormcreatorSpecificValidator::class, class_implements($itemtype))) {
            if (!(new $itemtype())->MayBeResolvedIntoOneValidator()) {
               continue;
            }
            $validator = (new PluginFormcreatorSupervisorValidator())->getOneValidator(
               Session::getLoginUserID()
            );
            if ($validator === null) {
               continue;
            }
            $items = [
               $validator->getID() => $validator,
            ];
         }
         foreach ($items as $key => $validator) {
            if (is_numeric($key)) {
               $itemtype = $validator::getType();
            } else {
               $itemtype = $validator::getType();
            }
            $validatorId = $validator->getID();
            $validators["{$itemtype}_{$validatorId}"] = $validator->getFriendlyName();
            $lastValidatorId = $validatorId;
            $lastValidatorItemtype = $itemtype;
         }
      }

      $totalCount = count($validators);

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

   /**
    * Get query condition to find groups containing users granted to validate a form answer
    *
    * @return array Query builder array
    */
   public static function getValidatorGroupsQueryCondition(): array {
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

      return $groupsCondition;
   }

   /**
    * Get validators of a form
    *
    * @param PluginFormcreatorForm $form
    * @param bool $count only count validators
    * @return array
    */
   public static function getAllValidators(PluginFormcreatorForm $form, bool $count = false): array {
      if ($form->isNewItem()) {
         return [];
      }

      $query = [
         'SELECT' => self::getTable() . '.*',
         'FROM'  => self::getTable(),
         'WHERE' => [
            PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
         ],
         'ORDER' => ['level ASC']
      ];
      if ($count) {
         unset($query['SELECT']);
         $query['COUNT'] = 'c';
      }
      return $query;
   }

   /**
    * Get query condition to find users granted to validate a form answer
    *
    * @return array Query builder array
    */
   private static function getValidatorUsersQueryCondition(): array {
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
         "$userTable.id" => new QuerySubQuery($subQuery)
      ];

      return $usersCondition;
   }

   /**
    * Get HTML of dropdown to select a validation level
    *
    * @param PluginFormcreatorForm $form
    * @param array $options
    * @return string
    */
   public static function dropdownLevel(PluginFormcreatorForm $form, $options = []): string {
      global $DB;

      $params = [
        'display'      => false,
      ];
      $options = array_merge($params, $options);

      $out = '';

      // Find current maximum valdiation level
      $formTable = PluginFormcreatorForm::getTable();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $formValidatorTable = self::getTable();
      $result = $DB->request([
         'SELECT' => ['MAX' => 'level as m'],
         'FROM' => $formValidatorTable,
         'INNER JOIN' => [
            $formTable => [
               'FKEY' => [
                  $formTable => 'id',
                  $formValidatorTable => "$formFk",
               ],
            ],
         ],
         'WHERE' => [
            "$formValidatorTable.$formFk" => $form->getID(),
         ]
      ]);
      $maxLevel = $result->current();
      $maxLevel = $maxLevel === null ? 0 : $maxLevel['m'];
      $maxLevel = $maxLevel > 4 ? 4 : $maxLevel;
      $options['min'] = 1;
      $options['max'] = $maxLevel + 1;

      $out .= Dropdown::showNumber($options['name'], $options);
      return $out;
   }

   public static function dropdownValidatorItemtype($options = []) {
      $params = [
         'display'      => false,
      ];
      $options = array_merge($params, $options);

      $availableTypes = [
         User::class => User::getTypeName(1),
         Group::class => Group::getTypeName(1),
         PluginFormcreatorSupervisorValidator::class => PluginFormcreatorSupervisorValidator::getTypeName(1),
      ];

      $selectedType = User::class;
      return Dropdown::showFromArray(
         $options['name'],
         $availableTypes,
         array_merge($options, [
            'value'     =>  $selectedType,
            // 'on_change' => 'plugin_formcreator.changeValidators(this.value)'
         ])
      );
   }

   /**
    * Add several users and groups at once
    *
    * @param array $input
    * @return bool true on success, false otherwise
    */
   public function addMultipleItems($input): bool {
      if (!isset($input['_validator_users']) && !isset($input['_validator_groups'])) {
         // Fallback to single item add
         return $this->add($input);
      }

      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $success = true;
      if (isset($input['_validator_users']) && is_array($input['_validator_users'])) {
         $newItems = [];
         foreach ($input['_validator_users'] as $userId) {
            if (User::isNewID($userId)) {
               continue;
            }
            $newId = $this->add([
               $formFk    => $input[$formFk],
               'itemtype' => User::getType(),
               'items_id' => $userId,
               'level'    => $input['level'],
            ]);
            if ($newId === false) {
               $success = false;
            } else {
               $newItems[] = $newId;
            }
         }
      }
      if (isset($input['_validator_groups']) && is_array($input['_validator_groups'])) {
         foreach ($input['_validator_groups'] as $groupId) {
            if (Group::isNewID($groupId)) {
               continue;
            }
            $newId = $this->add([
              $formFk    => $input[$formFk],
              'itemtype' => Group::getType(),
              'items_id' => $groupId,
              'level'    => $input['level'],
            ]);
            if ($newId === false) {
               $success = false;
            }
         }
      }
      return $success;
   }
}
