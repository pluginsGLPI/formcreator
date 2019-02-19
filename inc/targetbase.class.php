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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

abstract class PluginFormcreatorTargetBase extends CommonDBTM implements PluginFormcreatorExportableInterface
{

   protected $requesters;

   protected $observers;

   protected $assigned;

   protected $assignedSuppliers;

   protected $requesterGroups;

   protected $observerGroups;

   protected $assignedGroups;

   protected $attachedDocuments = [];

   protected $form = null;

   abstract public function export($remove_uuid = false);

   abstract public function save(PluginFormcreatorFormAnswer $formanswer);

   /**
    * Gets an instance object for the relation between the target itemtype
    * and an user
    *
    * @return CommonDBTM
    */
   abstract protected function getItem_User();

   /**
    * Gets an instance object for the relation between the target itemtype
    * and a group
    *
    * @return CommonDBTM
    */
   abstract protected function getItem_Group();

   /**
    * Gets an instance object for the relation between the target itemtype
    * and supplier
    *
    * @return CommonDBTM
    */
   abstract protected function getItem_Supplier();

   /**
    * Gets an instance object for the relation between the target itemtype
    * and an object of any itemtype
    *
    * @return CommonDBTM
    */
   abstract protected function getItem_Item();

   /**
    * Gets the class name of the target itemtype
    *
    * @return string
    */
   abstract protected function getTargetItemtypeName();

   abstract public function getItem_Actor();

   abstract protected function getCategoryFilter();

   static function getEnumDestinationEntity() {
      return [
         'current'   => __("Current active entity", 'formcreator'),
         'requester' => __("Default requester user's entity", 'formcreator'),
         'requester_dynamic_first' => __("First dynamic requester user's entity (alphabetical)", 'formcreator'),
         'requester_dynamic_last' => __("Last dynamic requester user's entity (alphabetical)", 'formcreator'),
         'form'      => __('The form entity', 'formcreator'),
         'validator' => __('Default entity of the validator', 'formcreator'),
         'specific'  => __('Specific entity', 'formcreator'),
         'user'      => __('Default entity of a user type question answer', 'formcreator'),
         'entity'    => __('From a GLPI object > Entity type question answer', 'formcreator'),
      ];
   }

   static function getEnumTagType() {
      return [
         'none'                   => __("None"),
         'questions'              => __('Tags from questions', 'formcreator'),
         'specifics'              => __('Specific tags', 'formcreator'),
         'questions_and_specific' => __('Tags from questions and specific tags', 'formcreator'),
         'questions_or_specific'  => __('Tags from questions or specific tags', 'formcreator')
      ];
   }

   static function getEnumDueDateRule() {
      return [
         'answer' => __('equals to the answer to the question', 'formcreator'),
         'ticket' => __('calculated from the ticket creation date', 'formcreator'),
         'calcul' => __('calculated from the answer to the question', 'formcreator'),
      ];
   }

   static function getEnumUrgencyRule() {
      return [
         'none'      => __('Urgency from template or Medium', 'formcreator'),
         'specific'  => __('Specific urgency', 'formcreator'),
         'answer'    => __('Equals to the answer to the question', 'formcreator'),
      ];
   }

   static function getEnumCategoryRule() {
      return [
         'none'      => __('Category from template or none', 'formcreator'),
         'specific'  => __('Specific category', 'formcreator'),
         'answer'    => __('Equals to the answer to the question', 'formcreator'),
      ];
   }

   static function getEnumLocationRule() {
      return [
         'none'      => __('Location from template or none', 'formcreator'),
         'specific'  => __('Specific location', 'formcreator'),
         'answer'    => __('Equals to the answer to the question', 'formcreator'),
      ];
   }

   /**
    * Check if current user have the right to create and modify requests
    *
    * @return boolean True if he can create and modify requests
    */
   public static function canCreate() {
      return true;
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView() {
      return true;
   }

   /**
    * get the associated form
    */
   public function getForm() {
      if ($this->form !== null) {
         return $this->form;
      }

      $targetItemId = $this->getID();
      $targetItemtype = static::getType();
      $target = new PluginFormcreatorTarget();
      $request = [
         'AND' => [
            'itemtype' => $targetItemtype,
            'items_id' => $targetItemId
         ]
      ];
      if (!$target->getFromDBByCrit($request)) {
         return null;
      }
      $form = new PluginFormcreatorForm();
      if (!$form->getFromDB($target->getField('plugin_formcreator_forms_id'))) {
         return null;
      }

      $this->form = $form;
      return $this->form;
   }

   /**
    * Set the entity of the target
    *
    * @param array $data input data of the target
    * @param PluginFormcreatorFormAnswer $formanswer
    * @param integer $requesters_id ID of the requester of the answers
    * @return integer ID of the entity where the target must be generated
    */
   protected function setTargetEntity($data, PluginFormcreatorFormAnswer $formanswer, $requesters_id) {
      global $DB;

      $entityId = 0;
      $entityFk = Entity::getForeignKeyField();
      switch ($this->fields['destination_entity']) {
         // Requester's entity
         case 'current' :
            $entityId = $formanswer->fields[$entityFk];
            break;

         case 'requester' :
            $userObj = new User();
            $userObj->getFromDB($requesters_id);
            $entityId = $userObj->fields[$entityFk];
            break;

         // Requester's first dynamic entity
         case 'requester_dynamic_first' :
            $order_entities = "glpi_profiles.name ASC";
         case 'requester_dynamic_last' :
            if (!isset($order_entities)) {
               $order_entities = "glpi_profiles.name DESC";
            }
            $profileUserTable = Profile_User::getTable();
            $profileTable = Profile::getTable();
            $profileFk  = Profile::getForeignKeyField();
            $res_entities = $DB->request([
               'SELECT' => [
                  $profileUserTable => [Entity::getForeignKeyField()]
               ],
               'FROM' => $profileUserTable,
               'LEFT JOIN' => [
                  $profileTable => [
                     'FKEY' => [
                        $profileTable => 'id',
                        $profileUserTable => $profileFk
                     ]
                  ]
               ],
               'WHERE' => [
                  "$profileUserTable.users_id" => $requesters_id
               ],
               'ORDER' => [
                  "$profileUserTable.is_dynamic DSC",
                  $order_entities
               ]
            ]);

            $data_entities = [];
            foreach ($res_entities as $entity) {
               $data_entities[] = $entity;
            }
            $first_entity = array_shift($data_entities);
            $entityId = $first_entity[$entityFk];
            break;

         // Specific entity
         case 'specific' :
            $entityId = $this->fields['destination_entity_value'];
            break;

         // The form entity
         case 'form' :
            $entityId = $formanswer->getForm()->fields[$entityFk];
            break;

         // The validator entity
         case 'validator' :
            $userObj = new User();
            $userObj->getFromDB($formanswer->fields['users_id_validator']);
            $entityId = $userObj->fields[$entityFk];
            break;

         // Default entity of a user from the answer of a user's type question
         case 'user' :
            $user = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['destination_entity_value'],
               ]
            ])->next();
            $user_id = $user['answer'];

            if ($user_id > 0) {
               $userObj = new User();
               $userObj->getFromDB($user_id);
               $entityId = $userObj->fields[$entityFk];
            }
            break;

         // Entity from the answer of an entity's type question
         case 'entity' :
            $entity = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['destination_entity_value'],
               ]
            ])->next();
            $entityId = $entity['answer'];
            break;
      }

      $data[$entityFk] = $entityId;
      return $data;
   }

   protected function setTargetCategory($data, $formanswer) {
      global $DB;

      switch ($this->fields['category_rule']) {
         case 'answer':
            $category = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['category_question']
               ]
            ])->next();
            $category = $category['answer'];
            break;
         case 'specific':
            $category = $this->fields['category_question'];
            break;
         default:
            $category = null;
      }
      if ($category !== null) {
         $data['itilcategories_id'] = $category;
      }

      return $data;
   }

   /**
    * find all actors and prepare data for the ticket being created
    */
   protected function prepareActors(PluginFormcreatorForm $form, PluginFormcreatorFormAnswer $formanswer) {
      global $DB;

      $target_actor = $this->getItem_Actor();
      $foreignKey   = $this->getForeignKeyField();

      $rows = $DB->request([
         'FROM'   => $target_actor::getTable(),
         'WHERE'  => [
            $foreignKey => $this->getID(),
         ]
      ]);
      foreach ($rows as $actor) {
         // If actor type is validator and if the form doesn't have a validator, continue to other actors
         if ($actor['actor_type'] == 'validator' && !$form->fields['validation_required']) {
            continue;
         }

         switch ($actor['actor_type']) {
            case 'creator' :
               $userIds = [$formanswer->fields['requester_id']];
               $notify  = $actor['use_notification'];
               break;
            case 'validator' :
               $userIds = [$_SESSION['glpiID']];
               $notify  = $actor['use_notification'];
               break;
            case 'person' :
            case 'group' :
            case 'supplier' :
               $userIds = [$actor['actor_value']];
               $notify  = $actor['use_notification'];
               break;
            case 'question_person' :
            case 'question_group' :
            case 'question_supplier' :
               $answer  = new PluginFormcreatorAnswer();
               $actorValue = $actor['actor_value'];
               $formanswerId = $formanswer->getID();
               $answer->getFromDBByCrit([
                  'AND' => [
                     'plugin_formcreator_questions_id'     => $actorValue,
                     'plugin_formcreator_formanswers_id' => $formanswerId
                  ]
               ]);

               if ($answer->isNewItem()) {
                  continue 2;
               } else {
                  $userIds = [$answer->getField('answer')];
               }
               $notify  = $actor['use_notification'];
               break;
            case 'question_actors':
               $answer  = new PluginFormcreatorAnswer();
               $actorValue = $actor['actor_value'];
               $formanswerId = $formanswer->getID();
               $answer->getFromDBByCrit([
                  'AND' => [
                     'plugin_formcreator_questions_id'     => $actorValue,
                     'plugin_formcreator_formanswers_id' => $formanswerId
                  ]
               ]);

               if ($answer->isNewItem()) {
                  continue 2;
               } else {
                  $userIds = json_decode($answer->fields['answer'], JSON_OBJECT_AS_ARRAY);
               }
               $notify = $actor['use_notification'];
               break;
         }

         switch ($actor['actor_type']) {
            case 'creator' :
            case 'validator' :
            case 'person' :
            case 'question_person' :
            case 'question_actors':
               foreach ($userIds as $userIdOrEmail) {
                  $this->addActor($actor['actor_role'], $userIdOrEmail, $notify);
               }
               break;
            case 'group' :
            case 'question_group' :
               foreach ($userIds as $groupId) {
                  $this->addGroupActor($actor['actor_role'], $groupId);
               }
               break;
            case 'supplier' :
            case 'question_supplier' :
               foreach ($userIds as $userId) {
                  $this->addActor('supplier', $userId, $notify);
               }
               break;
         }
      }
   }

   /**
    * Adds an user to the given actor role (requester, observer assigned or supplier)
    *
    * @param string $role role of the user
    * @param string $user user ID or email address for anonymous users
    * @param boolean $notify true to enable notification for the actor
    * @return boolean true on success, false on error
    */
   protected function addActor($role, $user, $notify) {
      if (filter_var($user, FILTER_VALIDATE_EMAIL) !== false) {
         $userId = 0;
         $alternativeEmail = $user;
      } else {
         $userId = (int) $user;
         $alternativeEmail = '';
         if ($userId == '0') {
            // there is no actor
            return false;
         }
      }

      $actorType = null;
      $actorTypeNotif = null;
      switch ($role) {
         case 'requester':
            $actorType = &$this->requesters['_users_id_requester'];
            $actorTypeNotif = &$this->requesters['_users_id_requester_notif'];
            break;
         case 'observer':
            $actorType = &$this->observers['_users_id_observer'];
            $actorTypeNotif = &$this->observers['_users_id_observer_notif'];
            break;
         case 'assigned' :
            $actorType = &$this->assigned['_users_id_assign'];
            $actorTypeNotif = &$this->assigned['_users_id_assign_notif'];
            break;
         case 'supplier' :
            $actorType = &$this->assignedSuppliers['_suppliers_id_assign'];
            $actorTypeNotif = &$this->assignedSuppliers['_suppliers_id_assign_notif'];
            break;
         default:
            return false;
      }

      $actorKey = array_search($userId, $actorType);
      if ($actorKey === false) {
         // Add the actor
         $actorType[]                      = $userId;
         $actorTypeNotif['use_notification'][]  = ($notify == true);
         $actorTypeNotif['alternative_email'][] = $alternativeEmail;
      } else {
         // New actor settings takes precedence
         $actorType[$actorKey] = $userId;
         $actorTypeNotif['use_notification'][$actorKey]  = ($notify == true);
         $actorTypeNotif['alternative_email'][$actorKey] = $alternativeEmail;
      }

      return true;
   }

   /**
    * Adds a group to the given actor role
    *
    * @param string $role Role of the group
    * @param string $group Group ID
    * @return boolean true on sucess, false on error
    */
   protected function addGroupActor($role, $group) {
      $actorType = null;
      switch ($role) {
         case 'requester':
            $actorType = &$this->requesterGroups['_groups_id_requester'];
            break;
         case 'observer' :
            $actorType = &$this->observerGroups['_groups_id_observer'];
            break;
         case 'assigned' :
            $actorType = &$this->assignedGroups['_groups_id_assign'];
            break;
         default:
            return false;
      }

      $actorKey = array_search($group, $actorType);
      if ($actorKey !== false) {
         return false;
      }

      // Add the group actor
      $actorType[] = $group;

      return true;
   }

   /**
    * Attach documents of the answer to the target
    */
   protected function attachDocument($formAnswerId, $itemtype, $targetID) {
      $docItem = new Document_Item();
      if (count($this->attachedDocuments) > 0) {
         foreach ($this->attachedDocuments as $documentID => $dummy) {
            $docItem->add([
               'documents_id' => $documentID,
               'itemtype'     => $itemtype,
               'items_id'     => $targetID
            ]);
         }
      }
   }

   public function addAttachedDocument($documentId) {
      $this->attachedDocuments[$documentId] = true;
   }

   protected function showDestinationEntitySetings($rand) {
      global $DB;

      echo '<tr class="line1">';
      echo '<td width="15%">' . __('Destination entity') . '</td>';
      echo '<td width="25%">';
      Dropdown::showFromArray(
         'destination_entity',
         self::getEnumDestinationEntity(),
         [
            'value'     => $this->fields['destination_entity'],
            'on_change' => 'change_entity()',
            'rand'      => $rand,
         ]
      );

      $script = <<<EOS
         function change_entity() {
            $('#entity_specific_title').hide();
            $('#entity_user_title').hide();
            $('#entity_entity_title').hide();
            $('#entity_specific_value').hide();
            $('#entity_user_value').hide();
            $('#entity_entity_value').hide();

            switch($('#dropdown_destination_entity$rand').val()) {
               case 'specific' :
                  $('#entity_specific_title').show();
                  $('#entity_specific_value').show();
                  break;
               case 'user' :
                  $('#entity_user_title').show();
                  $('#entity_user_value').show();
                  break;
               case 'entity' :
                  $('#entity_entity_title').show();
                  $('#entity_entity_value').show();
                  break;
            }
         }
         change_entity();
EOS;

      echo Html::scriptBlock($script);
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="entity_specific_title" style="display: none">' . _n('Entity', 'Entities', 1) . '</span>';
      echo '<span id="entity_user_title" style="display: none">' . __('User type question', 'formcreator') . '</span>';
      echo '<span id="entity_entity_title" style="display: none">' . __('Entity type question', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';

      echo '<div id="entity_specific_value" style="display: none">';
      Entity::dropdown([
         'name' => '_destination_entity_value_specific',
         'value' => $this->fields['destination_entity_value'],
      ]);
      echo '</div>';

      echo '<div id="entity_user_value" style="display: none">';
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['glpiselect'],
            'values' => User::class,
         ],
         '_destination_entity_value_user',
         [
            'value' => $this->fields['destination_entity_value']
         ]
      );
      echo '</div>';

      echo '<div id="entity_entity_value" style="display: none">';
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['glpiselect'],
            'values' => Entity::class,
         ],
         '_destination_entity_value_entity',
         [
            'value' => $this->fields['destination_entity_value']
         ]
      );
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   protected function showTemplateSettings($rand) {
      echo '<td width="15%">' . _n('Ticket template', 'Ticket templates', 1) . '</td>';
      echo '<td width="25%">';
      Dropdown::show('TicketTemplate', [
         'name'  => 'tickettemplates_id',
         'value' => $this->fields['tickettemplates_id']
      ]);
      echo '</td>';
   }

   protected  function showDueDateSettings(PluginFormcreatorForm $form, $rand) {
      global $DB;

      echo '<td width="15%">' . __('Time to resolve') . '</td>';
      echo '<td width="45%">';

      // Due date type selection
      Dropdown::showFromArray('due_date_rule', self::getEnumDueDateRule(),
         [
            'value'     => $this->fields['due_date_rule'],
            'on_change' => 'formcreatorChangeDueDate(this.value)',
            'display_emptychoice' => true
         ]
      );

      $questionTable = PluginFormcreatorQuestion::getTable();
      $questions = (new PluginFormcreatorQuestion)->getQuestionsFromForm(
         $this->getForm()->getID(),
         [
            "$questionTable.fieldtype" => ['date', 'datetime'],
         ]
      );
      $questions_list = [];
      foreach ($questions as $question) {
         $questions_list[$question->getID()] = $question->fields['name'];
      }
      // List questions
      if ($this->fields['due_date_rule'] != 'answer'
            && $this->fields['due_date_rule'] != 'calcul') {
         echo '<div id="due_date_questions" style="display:none">';
      } else {
         echo '<div id="due_date_questions">';
      }
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['date', 'datetime'],
         ],
         'due_date_question',
         [
            'value' => $this->fields['due_date_question']
         ]
      );
      echo '</div>';

      if ($this->fields['due_date_rule'] != 'ticket'
            && $this->fields['due_date_rule'] != 'calcul') {
         echo '<div id="due_date_time" style="display:none">';
      } else {
         echo '<div id="due_date_time">';
      }
      Dropdown::showNumber("due_date_value", [
         'value' => $this->fields['due_date_value'],
         'min'   => -30,
         'max'   => 30
      ]);
      Dropdown::showFromArray('due_date_period', [
         'minute' => _n('Minute', 'Minutes', 2),
         'hour'   => _n('Hour', 'Hours', 2),
         'day'    => _n('Day', 'Days', 2),
         'month'  => __('Month'),
      ], [
         'value' => $this->fields['due_date_period']
      ]);
      echo '</div>';
      echo '</td>';
   }

   protected function showCategorySettings(PluginFormcreatorForm $form, $rand) {
      global $DB;

      echo '<tr class="line0">';
      echo '<td width="15%">' . __('Ticket category', 'formcreator') . '</td>';
      echo '<td width="25%">';
      Dropdown::showFromArray(
            'category_rule',
            static::getEnumCategoryRule(),
            [
               'value'     => $this->fields['category_rule'],
               'on_change' => 'change_category()',
               'rand'      => $rand,
            ]);
      $script = <<<EOS
         function change_category() {
            $('#category_specific_title').hide();
            $('#category_specific_value').hide();
            $('#category_question_title').hide();
            $('#category_question_value').hide();

            switch($('#dropdown_category_rule$rand').val()) {
               case 'answer' :
                  $('#category_question_title').show();
                  $('#category_question_value').show();
                  break;
               case 'specific' :
                  $('#category_specific_title').show();
                  $('#category_specific_value').show();
                  break;
            }
         }
         change_category();
EOS;
      echo Html::scriptBlock($script);
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="category_specific_title" style="display: none">' . __('Category', 'formcreator') . '</span>';
      echo '<span id="category_question_title" style="display: none">' . __('Question', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';
      echo '<div id="category_question_value" style="display: none">';
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['dropdown'],
         ],
         '_category_question',
         [
            $this->fields['category_question']
         ]
      );
      echo '</div>';
      echo '<div id="category_specific_value" style="display: none">';
      ITILCategory::dropdown([
         'name'      => '_category_specific',
         'value'     => $this->fields["category_question"],
         'condition' => $this->getCategoryFilter(),
      ]);
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   protected function showUrgencySettings(PluginFormcreatorForm $form, $rand) {
      global $DB;

      echo '<tr class="line0">';
      echo '<td width="15%">' . __('Urgency') . '</td>';
      echo '<td width="45%">';
      Dropdown::showFromArray('urgency_rule', static::getEnumUrgencyRule(), [
         'value'                 => $this->fields['urgency_rule'],
         'on_change'             => 'change_urgency()',
         'rand'                  => $rand
      ]);
      $script = <<<EOS
         function change_urgency() {
            $('#urgency_specific_title').hide();
            $('#urgency_specific_value').hide();
            $('#urgency_question_title').hide();
            $('#urgency_question_value').hide();

            switch($('#dropdown_urgency_rule$rand').val()) {
               case 'answer' :
                  $('#urgency_question_title').show();
                  $('#urgency_question_value').show();
                  break;
               case 'specific':
                  $('#urgency_specific_title').show();
                  $('#urgency_specific_value').show();
                  break;
            }
         }
         change_urgency();
EOS;
      echo Html::scriptBlock($script);
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="urgency_question_title" style="display: none">' . __('Question', 'formcreator') . '</span>';
      echo '<span id="urgency_specific_title" style="display: none">' . __('Urgency ', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';

      echo '<div id="urgency_specific_value" style="display: none">';
      Ticket::dropdownUrgency([
         'name' => '_urgency_specific',
         'value' => $this->fields["urgency_question"],
      ]);
      echo '</div>';
      echo '<div id="urgency_question_value" style="display: none">';
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['urgency'],
         ],
         '_urgency_question',
         [
            'value' => $this->fields['urgency_question']
         ]
      );
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   protected function showPluginTagsSettings(PluginFormcreatorForm $form, $rand) {
      global $DB;

      $plugin = new Plugin();
      if ($plugin->isInstalled('tag') && $plugin->isActivated('tag')) {
         echo '<tr class="line1">';
         echo '<td width="15%">' . __('Ticket tags', 'formcreator') . '</td>';
         echo '<td width="25%">';
         Dropdown::showFromArray('tag_type', self::getEnumTagType(),
            [
               'value'     => $this->fields['tag_type'],
               'on_change' => 'change_tag_type()',
               'rand'      => $rand,
            ]
         );

         $script = <<<EOS
            function change_tag_type() {
               $('#tag_question_title').hide();
               $('#tag_specific_title').hide();
               $('#tag_question_value').hide();
               $('#tag_specific_value').hide();

               switch($('#dropdown_tag_type$rand').val()) {
                  case 'questions' :
                     $('#tag_question_title').show();
                     $('#tag_question_value').show();
                     break;
                  case 'specifics' :
                     $('#tag_specific_title').show();
                     $('#tag_specific_value').show();
                     break;
                  case 'questions_and_specific' :
                  case 'questions_or_specific' :
                     $('#tag_question_title').show();
                     $('#tag_specific_title').show();
                     $('#tag_question_value').show();
                     $('#tag_specific_value').show();
                     break;
               }
            }
            change_tag_type();
EOS;

         echo Html::scriptBlock($script);
         echo '</td>';
         echo '<td width="15%">';
         echo '<div id="tag_question_title" style="display: none">' . _n('Question', 'Questions', 2, 'formcreator') . '</div>';
         echo '<div id="tag_specific_title" style="display: none">' . __('Tags', 'tag') . '</div>';
         echo '</td>';
         echo '<td width="25%">';

         // Tag questions
         echo '<div id="tag_question_value" style="display: none">';
         PluginFormcreatorQuestion::dropdownForForm(
            $this->getForm()->getID(),
            [
               'fieldtype' => ['tag'],
            ],
            '_tag_questions',
            [
               'values' => explode(',', $this->fields['tag_questions']),
               'multiple' => true,
            ]
         );
         echo '</div>';

         // Specific tags
         echo '<div id="tag_specific_value" style="display: none">';

         $result = $DB->request([
            'SELECT' => ['name'],
            'FROM'   => PluginTagTag::getTable(),
            'WHERE'  => [
               'OR' => [
                  ['type_menu' => ['LIKE', $this->getTargetItemtypeName()]],
                  ['type_menu' => ['LIKE', '0']],
               ] + getEntitiesRestrictCriteria(PluginTagTag::getTable())
            ]
         ]);
         $values = [];
         foreach ($result AS $id => $data) {
            $values[$id] = $data['name'];
         }

         Dropdown::showFromArray('_tag_specifics', $values, [
            'values'   => explode(',', $this->fields['tag_specifics']),
            'comments' => false,
            'rand'     => $rand,
            'multiple' => true,
         ]);
         echo '</div>';
         echo '</td>';
         echo '</tr>';
      }
   }

   protected function showActorsSettings() {
      global $DB, $CFG_GLPI;

      // Get available questions for actors lists
      $questions_user_list     = [Dropdown::EMPTY_VALUE];
      $questions_group_list    = [Dropdown::EMPTY_VALUE];
      $questions_supplier_list = [Dropdown::EMPTY_VALUE];
      $questions_actors_list   = [Dropdown::EMPTY_VALUE];

      $query = "SELECT s.id, s.name
                FROM glpi_plugin_formcreator_targets t
                INNER JOIN glpi_plugin_formcreator_sections s
                  ON s.plugin_formcreator_forms_id = t.plugin_formcreator_forms_id
                WHERE t.items_id = ".$this->getID()."
                ORDER BY s.order";
      $result = $DB->query($query);
      // $result = $DB->request([
      //    'SELECT' => [
      //       $sectionTable => ['id']
      //    ]
      // ]);
      while ($section = $DB->fetch_array($result)) {
         // select all user, group or supplier questions (GLPI Object)
         $query2 = "SELECT q.id, q.name, q.fieldtype, q.values
                    FROM glpi_plugin_formcreator_questions q
                    INNER JOIN glpi_plugin_formcreator_sections s
                      ON s.id = q.plugin_formcreator_sections_id
                    WHERE s.id = {$section['id']}
                    AND ((q.fieldtype = 'glpiselect'
                      AND q.values IN ('User', 'Group', 'Supplier'))
                      OR (q.fieldtype = 'actor'))";
         $result2 = $DB->query($query2);
         $section_questions_user     = [];
         $section_questions_group    = [];
         $section_questions_supplier = [];
         $section_questions_actors   = [];
         while ($question = $DB->fetch_array($result2)) {
            if ($question['fieldtype'] == 'glpiselect') {
               switch ($question['values']) {
                  case 'User' :
                     $section_questions_user[$question['id']] = $question['name'];
                     break;
                  case 'Group' :
                     $section_questions_group[$question['id']] = $question['name'];
                     break;
                  case 'Supplier' :
                     $section_questions_supplier[$question['id']] = $question['name'];
                     break;
               }
            } else if ($question['fieldtype'] == 'actor') {
               $section_questions_actors[$question['id']] = $question['name'];
            }
         }
         $questions_user_list[$section['name']]     = $section_questions_user;
         $questions_group_list[$section['name']]    = $section_questions_group;
         $questions_supplier_list[$section['name']] = $section_questions_supplier;
         $questions_actors_list[$section['name']]   = $section_questions_actors;
      }

      // Get available questions for actors lists
      $itemActor = $this->getItem_Actor();
      $itemActorTable = $itemActor::getTable();
      $fk = self::getForeignKeyField();
      $actors = ['requester' => [], 'observer' => [], 'assigned' => []];
      $query = "SELECT id, actor_role, actor_type, actor_value, use_notification
                FROM $itemActorTable
                WHERE $fk = " . $this->getID();
      $result = $DB->query($query);
      while ($actor = $DB->fetch_array($result)) {
         $actors[$actor['actor_role']][$actor['id']] = [
            'actor_type'       => $actor['actor_type'],
            'actor_value'      => $actor['actor_value'],
            'use_notification' => $actor['use_notification'],
         ];
      }

      $img_user     = '<img src="../../../pics/users.png" alt="' . __('User') . '" title="' . __('User') . '" width="20" />';
      $img_group    = '<img src="../../../pics/groupes.png" alt="' . __('Group') . '" title="' . __('Group') . '" width="20" />';
      $img_supplier = '<img src="../../../pics/supplier.png" alt="' . __('Supplier') . '" title="' . __('Supplier') . '" width="20" />';
      $img_mail     = '<img src="../pics/email.png" alt="' . __('Yes') . '" title="' . __('Email followup') . ' ' . __('Yes') . '" />';
      $img_nomail   = '<img src="../pics/email-no.png" alt="' . __('No') . '" title="' . __('Email followup') . ' ' . __('No') . '" />';

      echo '<table class="tab_cadre_fixe">';

      echo '<tr><th colspan="3">' . __('Actors', 'formcreator') . '</th></tr>';

      echo '<tr>';

      echo '<th width="33%">';
      echo _n('Requester', 'Requesters', 1) . ' &nbsp;';
      echo '<img title="Ajouter" alt="Ajouter" onclick="displayRequesterForm()" class="pointer"
               id="btn_add_requester" src="../../../pics/add_dropdown.png">';
      echo '<img title="Annuler" alt="Annuler" onclick="hideRequesterForm()" class="pointer"
               id="btn_cancel_requester" src="../../../pics/delete.png" style="display:none">';
      echo '</th>';

      echo '<th width="34%">';
      echo _n('Watcher', 'Watchers', 1) . ' &nbsp;';
      echo '<img title="Ajouter" alt="Ajouter" onclick="displayWatcherForm()" class="pointer"
               id="btn_add_watcher" src="../../../pics/add_dropdown.png">';
      echo '<img title="Annuler" alt="Annuler" onclick="hideWatcherForm()" class="pointer"
               id="btn_cancel_watcher" src="../../../pics/delete.png" style="display:none">';
      echo '</th>';

      echo '<th width="33%">';
      echo __('Assigned to') . ' &nbsp;';
      echo '<img title="Ajouter" alt="Ajouter" onclick="displayAssignedForm()" class="pointer"
               id="btn_add_assigned" src="../../../pics/add_dropdown.png">';
      echo '<img title="Annuler" alt="Annuler" onclick="hideAssignedForm()" class="pointer"
               id="btn_cancel_assigned" src="../../../pics/delete.png" style="display:none">';
      echo '</th>';

      echo '</tr>';

      echo '<tr>';

      // Requester
      echo '<td valign="top">';

      // => Add requester form
      echo '<form name="form_target" id="form_add_requester" method="post" style="display:none" action="'
           . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetchange.form.php">';

      $dropdownItems = ['' => Dropdown::EMPTY_VALUE] + $itemActor::getEnumActorType();
      unset($dropdownItems['supplier']);
      unset($dropdownItems['question_supplier']);
      Dropdown::showFromArray(
         'actor_type',
         $dropdownItems, [
            'on_change'         => 'formcreatorChangeActorRequester(this.value)'
         ]
      );

      echo '<div id="block_requester_user" style="display:none">';
      User::dropdown([
         'name' => 'actor_value_person',
         'right' => 'all',
         'all'   => 0,
      ]);
      echo '</div>';

      echo '<div id="block_requester_group" style="display:none">';
      Group::dropdown([
         'name' => 'actor_value_group',
      ]);
      echo '</div>';

      echo '<div id="block_requester_question_user" style="display:none">';
      // Dropdown::showFromArray('actor_value_question_person', $questions_user_list, [
      //    'value' => $this->fields['due_date_question'],
      // ]);
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['glpiselect'],
            'values' => User::class,
         ],
         'actor_value_question_person',
         [
            'value' => 0
         ]
      );
      echo '</div>';

      echo '<div id="block_requester_question_group" style="display:none">';
      // Dropdown::showFromArray('actor_value_question_group', $questions_group_list, [
      //    'value' => $this->fields['due_date_question'],
      // ]);
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['glpiselect'],
            'values' => Group::class,
         ],
         'actor_value_question_person',
         [
            'value' => 0
         ]
      );
      echo '</div>';

      echo '<div id="block_requester_question_actors" style="display:none">';
      // Dropdown::showFromArray('actor_value_question_actors', $questions_actors_list, [
      //    'value' => $this->fields['due_date_question'],
      // ]);
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['actor'],
         ],
         'actor_value_question_person',
         [
            'value' => 0
         ]
      );
      echo '</div>';

      echo '<div>';
      echo __('Email followup');
      Dropdown::showYesNo('use_notification', 1);
      echo '</div>';

      echo '<p align="center">';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
      echo '<input type="hidden" name="actor_role" value="requester" />';
      echo '<input type="submit" value="' . __('Add') . '" class="submit_button" />';
      echo '</p>';

      echo "<hr>";

      Html::closeForm();

      // => List of saved requesters
      foreach ($actors['requester'] as $id => $values) {
         echo '<div>';
         switch ($values['actor_type']) {
            case 'creator' :
               echo $img_user . ' <b>' . __('Form requester', 'formcreator') . '</b>';
               break;
            case 'validator' :
               echo $img_user . ' <b>' . __('Form validator', 'formcreator') . '</b>';
               break;
            case 'person' :
               $user = new User();
               $user->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('User') . ' </b> "' . $user->getName() . '"';
               break;
            case 'question_person' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Person from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case 'group' :
               $group = new Group();
               $group->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Group') . ' </b> "' . $group->getName() . '"';
               break;
            case 'question_group' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Group from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case 'question_actors':
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Actors from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
         }
         echo $values['use_notification'] ? ' ' . $img_mail . ' ' : ' ' . $img_nomail . ' ';
         echo self::getDeleteImage($id);
         echo '</div>';
      }

      echo '</td>';

      // Observer
      echo '<td valign="top">';

      // => Add observer form
      echo '<form name="form_target" id="form_add_watcher" method="post" style="display:none" action="'
           . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetchange.form.php">';

      $dropdownItems = [''  => Dropdown::EMPTY_VALUE] + $itemActor::getEnumActorType();
      unset($dropdownItems['supplier']);
      unset($dropdownItems['question_supplier']);
      Dropdown::showFromArray('actor_type',
         $dropdownItems, ['on_change' => 'formcreatorChangeActorWatcher(this.value)']
      );

      echo '<div id="block_watcher_user" style="display:none">';
      User::dropdown([
         'name' => 'actor_value_person',
         'right' => 'all',
         'all'   => 0,
      ]);
      echo '</div>';

      echo '<div id="block_watcher_group" style="display:none">';
      Group::dropdown([
         'name' => 'actor_value_group',
      ]);
      echo '</div>';

      echo '<div id="block_watcher_question_user" style="display:none">';
      // Dropdown::showFromArray('actor_value_question_person', $questions_user_list, [
      //    'value' => $this->fields['due_date_question'],
      // ]);
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['glpiselect'],
            'values' => User::class,
         ],
         'actor_value_question_person',
         [
            'value' => 0
         ]
      );
      echo '</div>';

      echo '<div id="block_watcher_question_group" style="display:none">';
      // Dropdown::showFromArray('actor_value_question_group', $questions_group_list, [
      //    'value' => $this->fields['due_date_question'],
      // ]);
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['glpiselect'],
            'values' => Group::class,
         ],
         'actor_value_question_person',
         [
            'value' => 0
         ]
      );
      echo '</div>';

      echo '<div id="block_watcher_question_actors" style="display:none">';
      // Dropdown::showFromArray('actor_value_question_actors', $questions_actors_list, [
      //    'value' => $this->fields['due_date_question'],
      // ]);
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['actor'],
         ],
         'actor_value_question_person',
         [
            'value' => 0
         ]
      );
      echo '</div>';

      echo '<div>';
      echo __('Email followup');
      Dropdown::showYesNo('use_notification', 1);
      echo '</div>';

      echo '<p align="center">';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
      echo '<input type="hidden" name="actor_role" value="observer" />';
      echo '<input type="submit" value="' . __('Add') . '" class="submit_button" />';
      echo '</p>';

      echo "<hr>";

      Html::closeForm();

      // => List of saved observers
      foreach ($actors['observer'] as $id => $values) {
         echo '<div>';
         switch ($values['actor_type']) {
            case 'creator' :
               echo $img_user . ' <b>' . __('Form requester', 'formcreator') . '</b>';
               break;
            case 'validator' :
               echo $img_user . ' <b>' . __('Form validator', 'formcreator') . '</b>';
               break;
            case 'person' :
               $user = new User();
               $user->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('User') . ' </b> "' . $user->getName() . '"';
               break;
            case 'question_person' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Person from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case 'group' :
               $group = new Group();
               $group->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Group') . ' </b> "' . $group->getName() . '"';
               break;
            case 'question_group' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Group from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case 'question_actors' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Actors from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
         }
         echo $values['use_notification'] ? ' ' . $img_mail . ' ' : ' ' . $img_nomail . ' ';
         echo self::getDeleteImage($id);
         echo '</div>';
      }

      echo '</td>';

      // Assigned to
      echo '<td valign="top">';

      // => Add assigned to form
      echo '<form name="form_target" id="form_add_assigned" method="post" style="display:none" action="'
            . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetchange.form.php">';

      $dropdownItems = [''  => Dropdown::EMPTY_VALUE] + $itemActor::getEnumActorType();
      Dropdown::showFromArray(
         'actor_type',
         $dropdownItems, [
           'on_change'         => 'formcreatorChangeActorAssigned(this.value)'
         ]
      );

      echo '<div id="block_assigned_user" style="display:none">';
      User::dropdown([
            'name' => 'actor_value_person',
            'right' => 'all',
            'all'   => 0,
      ]);
      echo '</div>';

      echo '<div id="block_assigned_group" style="display:none">';
      Group::dropdown([
         'name' => 'actor_value_group',
      ]);
      echo '</div>';

      echo '<div id="block_assigned_supplier" style="display:none">';
      Supplier::dropdown([
         'name' => 'actor_value_supplier',
      ]);
      echo '</div>';

      echo '<div id="block_assigned_question_user" style="display:none">';
      // Dropdown::showFromArray('actor_value_question_person', $questions_user_list, [
      //    'value' => $this->fields['due_date_question'],
      // ]);
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['glpiselect'],
            'values' => User::class,
         ],
         'actor_value_question_person',
         [
            'value' => 0
         ]
      );
      echo '</div>';

      echo '<div id="block_assigned_question_group" style="display:none">';
      // Dropdown::showFromArray('actor_value_question_group', $questions_group_list, [
      //    'value' => $this->fields['due_date_question'],
      // ]);
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['glpiselect'],
            'values' => Group::class,
         ],
         'actor_value_question_person',
         [
            'value' => 0
         ]
      );
      echo '</div>';

      echo '<div id="block_assigned_question_actors" style="display:none">';
      // Dropdown::showFromArray('actor_value_question_actors', $questions_actors_list, [
      //    'value' => $this->fields['due_date_question'],
      // ]);
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['actor'],
         ],
         'actor_value_question_person',
         [
            'value' => 0
         ]
      );
      echo '</div>';

      echo '<div id="block_assigned_question_supplier" style="display:none">';
      // Dropdown::showFromArray('actor_value_question_supplier', $questions_supplier_list, [
      //    'value' => $this->fields['due_date_question'],
      // ]);
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm()->getID(),
         [
            'fieldtype' => ['glpiselect'],
            'values' => Supplier::class,
         ],
         'actor_value_question_person',
         [
            'value' => 0
         ]
      );
      echo '</div>';

      echo '<div>';
      echo __('Email followup');
      Dropdown::showYesNo('use_notification', 1);
      echo '</div>';

      echo '<p align="center">';
      echo '<input type="hidden" name="id" value="' . $this->getID() . '" />';
      echo '<input type="hidden" name="actor_role" value="assigned" />';
      echo '<input type="submit" value="' . __('Add') . '" class="submit_button" />';
      echo '</p>';

      echo "<hr>";

      Html::closeForm();

      // => List of saved assigned to
      foreach ($actors['assigned'] as $id => $values) {
         echo '<div>';
         switch ($values['actor_type']) {
            case 'creator' :
               echo $img_user . ' <b>' . __('Form requester', 'formcreator') . '</b>';
               break;
            case 'validator' :
               echo $img_user . ' <b>' . __('Form validator', 'formcreator') . '</b>';
               break;
            case 'person' :
               $user = new User();
               $user->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('User') . ' </b> "' . $user->getName() . '"';
               break;
            case 'question_person' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Person from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case 'group' :
               $group = new Group();
               $group->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Group') . ' </b> "' . $group->getName() . '"';
               break;
            case 'question_group' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Group from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case 'question_actors' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Actors from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case 'supplier' :
               $supplier = new Supplier();
               $supplier->getFromDB($values['actor_value']);
               echo $img_supplier . ' <b>' . __('Supplier') . ' </b> "' . $supplier->getName() . '"';
               break;
            case 'question_supplier' :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_supplier . ' <b>' . __('Supplier from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
         }
         echo $values['use_notification'] ? ' ' . $img_mail . ' ' : ' ' . $img_nomail . ' ';
         echo self::getDeleteImage($id);
         echo '</div>';
      }

      echo '</td>';

      echo '</tr>';

      echo '</table>';
   }

   /**
    * Parse target content to replace TAGS like ##FULLFORM## by the values
    *
    * @param  string $content                            String to be parsed
    * @param  PluginFormcreatorFormAnswer $formanswer   Formanswer object where answers are stored
    * @param  boolean $richText                          Disable rich text mode for field rendering
    * @return string                                     Parsed string with tags replaced by form values
    */
   protected function parseTags($content, PluginFormcreatorFormAnswer $formanswer, $richText = false) {
      global $DB;

      // retrieve answers
      $answers_values = $formanswer->getAnswers($formanswer->getID());

      // Retrieve questions
      $form = new PluginFormcreatorForm();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $form->getFromDB($formanswer->fields[$formFk]);
      $questions = (new PluginFormcreatorQuestion())
         ->getQuestionsFromForm($formanswer->getField($formFk));

      $fields = $form->getFields();

      // Prepare all fields of the form
      foreach ($questions as $questionId => $question) {
         $answer = $answers_values['formcreator_field_' . $questionId];
         $fields[$questionId]->deserializeValue($answer);
      }

      foreach ($questions as $questionId => $question) {
         if (!PluginFormcreatorFields::isVisible($questionId, $fields)) {
            $name = '';
            $value = '';
         } else {
            $name  = $question->getField('name');
            $value = $fields[$questionId]->getValueForTargetText($richText);
         }

         $content = str_replace('##question_' . $questionId . '##', Toolbox::addslashes_deep($name), $content);
         $content = str_replace('##answer_' . $questionId . '##', Toolbox::addslashes_deep($value), $content);
         foreach ($fields[$questionId]->getDocumentsForTarget() as $documentId) {
            $this->addAttachedDocument($documentId);
         }
         if ($question->getField('fieldtype') === 'file') {
            if (strpos($content, '##answer_' . $questionId . '##') !== false) {
               if (!is_array($value)) {
                  $value = [$value];
               }
            }
         }
      }

      return $content;
   }

   protected function showLocationSettings(PluginFormcreatorForm $form, $rand) {
      global $DB;

      echo '<tr class="line0">';
      echo '<td width="15%">' . __('Location') . '</td>';
      echo '<td width="45%">';
      Dropdown::showFromArray('location_rule', static::getEnumLocationRule(), [
         'value'                 => $this->fields['location_rule'],
         'on_change'             => 'change_location()',
         'rand'                  => $rand
      ]);
      $script = <<<JAVASCRIPT
         function change_location() {
            $('#location_specific_title').hide();
            $('#location_specific_value').hide();
            $('#location_question_title').hide();
            $('#location_question_value').hide();

            switch($('#dropdown_location_rule$rand').val()) {
               case 'answer' :
                  $('#location_question_title').show();
                  $('#location_question_value').show();
                  break;
               case 'specific':
                  $('#location_specific_title').show();
                  $('#location_specific_value').show();
                  break;
            }
         }
         change_location();
JAVASCRIPT;
      echo Html::scriptBlock($script);
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="location_question_title" style="display: none">' . __('Question', 'formcreator') . '</span>';
      echo '<span id="location_specific_title" style="display: none">' . __('Location ', 'formcreator') . '</span>';
      echo '</td>';
      echo '<td width="25%">';

      echo '<div id="location_specific_value" style="display: none">';
      Location::dropdown([
         'name' => '_location_specific',
         'value' => $this->fields["location_question"],
      ]);
      echo '</div>';
      echo '<div id="location_question_value" style="display: none">';
      // select all user questions (GLPI Object)
      $questionTable = PluginFormcreatorQuestion::getTable();
      $sectionTable = PluginFormcreatorSection::getTable();
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $targetTable = PluginFormcreatorTarget::getTable();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $result2 = $DB->request([
         'SELECT' => [
            $questionTable => ['id', 'name', 'values']
         ],
         'FROM' => $questionTable,
         'INNER JOIN' => [
            $sectionTable => [
               'FKEY' => [
                  $sectionTable => 'id',
                  $questionTable => $sectionFk
               ]
            ],
            $targetTable => [
               'FKEY' => [
                  $sectionTable => $formFk,
                  $targetTable => $formFk
               ]
            ]
         ],
         'WHERE' => [
            "$targetTable.items_id" => $this->getID(),
            "$questionTable.fieldtype" => 'dropdown'
         ]
      ]);
      $users_questions = [];
      foreach ($result2 as $question) {
         $decodedValues = json_decode($question['values'], JSON_OBJECT_AS_ARRAY);
         if (isset($decodedValues['itemtype']) && $decodedValues['itemtype'] === 'Location') {
            $users_questions[$question['id']] = $question['name'];
         }
      }
      Dropdown::showFromArray('_location_question', $users_questions, [
         'value' => $this->fields['location_question'],
      ]);
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   /**
    * Sets the time to resolve of the target object
    *
    * @param array $data data of the target object
    * @param PluginFormcreatorFormAnswer $formanswer    Answers previously saved
    * @return array updated data of the target object
    */
   protected function setTargetDueDate($data, PluginFormcreatorFormAnswer $formanswer) {
      global $DB;

      $answer  = new PluginFormcreatorAnswer();
      if ($this->fields['due_date_question'] !== null) {
         $request = [
            'FROM' => $answer::getTable(),
            'WHERE' => [
               'AND' => [
                  $formanswer::getForeignKeyField() => $formanswer->fields['id'],
                  PluginFormcreatorQuestion::getForeignKeyField() => $this->fields['due_date_question'],
               ],
            ],
         ];
         $iterator = $DB->request($request);
         if ($iterator->count() > 0) {
            $iterator->rewind();
            $date   = $iterator->current();
         }
      } else {
         $date = null;
      }
      $str    = "+" . $this->fields['due_date_value'] . " " . $this->fields['due_date_period'];

      switch ($this->fields['due_date_rule']) {
         case 'answer':
            $due_date = $date['answer'];
            break;
         case 'ticket':
            $due_date = date('Y-m-d H:i:s', strtotime($str));
            break;
         case 'calcul':
            $due_date = date('Y-m-d H:i:s', strtotime($date['answer'] . " " . $str));
            break;
         default:
            $due_date = null;
            break;
      }
      if (!is_null($due_date)) {
         $data['time_to_resolve'] = $due_date;
      }

      return $data;
   }

   public function prepareInputForUpdate($input) {
      global $DB;

      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      if (isset($input['name'])) {
         $target = new PluginFormcreatorTarget();
         $target->getFromDBByCrit([
            'itemtype' => static ::class,
            'items_id' => $this->getID()
         ]);
         if (!$target->isNewItem()) {
            $target->update([
               'id' => $target->getID(),
               'name' => $DB->escape($input['name']),
            ]);
         }
      }

      if (isset($input['title'])) {
         $input['name'] = $input['title'];
         unset($input['title']);
      }

      return $input;
   }

   protected static function getDeleteImage($id) {
      global $CFG_GLPI;

      $link  = ' &nbsp;<a href="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/targetticket.form.php?delete_actor=' . $id . '">';
      $link .= '<img src="../../../pics/delete.png" alt="' . __('Delete') . '" title="' . __('Delete') . '" />';
      $link .= '</a>';
      return $link;
   }

   /**
    * Prepare the template of the target
    *
    * @param string $template
    * @param PluginFormcreatorFormAnswer $formAnswer form answer to render
    * @param boolean $richText Disable rich text output
    * @return string
    */
   protected function prepareTemplate($template, PluginFormcreatorFormAnswer $formAnswer, $richText = false) {
      if (strpos($template, '##FULLFORM##') !== false) {
         $template = str_replace('##FULLFORM##', $formAnswer->getFullForm($richText), $template);
      }

      if ($richText) {
         $template = str_replace(['<p>', '</p>'], ['<div>', '</div>'], $template);
         $template = Html::entities_deep($template);
      }

      return $template;
   }
}
