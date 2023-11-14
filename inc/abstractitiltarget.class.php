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

use Glpi\Toolbox\Sanitizer;
use GlpiPlugin\Formcreator\Field\TextareaField;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

abstract class PluginFormcreatorAbstractItilTarget extends PluginFormcreatorAbstractTarget implements
PluginFormcreatorExportableInterface,
PluginFormcreatorItilTargetInterface,
PluginFormcreatorConditionnableInterface,
PluginFormcreatorTranslatableInterface
{
   /** @var array $requesters requester actors of the target */
   protected $requesters;

   /** @var array $observers watcher actors of the target */
   protected $observers;

   /** @var array $assigned assigned actors of the target */
   protected $assigned;

   /** @var array $assignedSuppliers assigned suppliers actors of the target */
   protected $assignedSuppliers;

   /** @var array $requesterGroups requester groups of the target */
   protected $requesterGroups;

   /** @var array $observerGroups watcher groups of the target */
   protected $observerGroups;

   /** @var array $assignedGroups assigned groups of the target */
   protected $assignedGroups;

   protected $attachedDocuments = [];

   /** @var boolean $skipCreateActors Flag to disable creation of actors after creation of the item */
   protected $skipCreateActors = false;

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
    * Get an instance object for the relation between the target itemtype
    * and an object of any itemtype
    *
    * @return CommonDBRelation
    */
   abstract public static function getItem_Item(): CommonDBRelation;

   /**
    * Get the class name of the target itemtype's template class
    *
    * @return string
    */
   abstract protected function getTemplateItemtypeName(): string;

   /**
    * Get the class name of the target itemtype's template predefined field class
    *
    * @return string
    */
   abstract protected function getTemplatePredefinedFieldItemtype(): string;

   /**
    * Get the query criterias to query the ITIL categories
    * for the target
    *
    * @return array
    */
   abstract protected function getCategoryFilter();

   /**
    * Determine the template ID to use as basis for target generation
    *
    * @param array $data Data of the target being crezated
    * @return int
    */
   abstract protected function getTargetTemplate(array $data): int;

   const DUE_DATE_RULE_NONE = 1;
   const DUE_DATE_RULE_ANSWER = 2;
   const DUE_DATE_RULE_TICKET = 3;
   const DUE_DATE_RULE_CALC = 4;

   const DUE_DATE_PERIOD_MINUTE = 1;
   const DUE_DATE_PERIOD_HOUR = 2;
   const DUE_DATE_PERIOD_DAY = 3;
   const DUE_DATE_PERIOD_MONTH = 4;

   const URGENCY_RULE_NONE = 1;
   const URGENCY_RULE_SPECIFIC = 2;
   const URGENCY_RULE_ANSWER = 3;

   const TAG_TYPE_NONE = 1;
   const TAG_TYPE_QUESTIONS = 2;
   const TAG_TYPE_SPECIFICS = 3;
   const TAG_TYPE_QUESTIONS_AND_SPECIFIC = 4;
   const TAG_TYPE_QUESTIONS_OR_SPECIFIC = 5;

   const CATEGORY_RULE_NONE = 1;
   const CATEGORY_RULE_SPECIFIC = 2;
   const CATEGORY_RULE_ANSWER = 3;
   const CATEGORY_RULE_LAST_ANSWER = 4;

   const LOCATION_RULE_NONE = 1;
   const LOCATION_RULE_SPECIFIC = 2;
   const LOCATION_RULE_ANSWER = 3;
   const LOCATION_RULE_LAST_ANSWER  = 4;

   const COMMONITIL_VALIDATION_RULE_NONE = 1;
   const COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP = 2;
   const COMMONITIL_VALIDATION_RULE_ANSWER_USER = 3;
   const COMMONITIL_VALIDATION_RULE_ANSWER_GROUP = 4;

   const OLA_RULE_NONE = 1;
   const OLA_RULE_SPECIFIC = 2;
   const OLA_RULE_FROM_ANWSER = 3;

   const SLA_RULE_NONE = 1;
   const SLA_RULE_SPECIFIC = 2;
   const SLA_RULE_FROM_ANWSER = 3;

   public static function getEnumTagType() {
      return [
         self::TAG_TYPE_NONE                   => __('None'),
         self::TAG_TYPE_QUESTIONS              => __('Tags from questions', 'formcreator'),
         self::TAG_TYPE_SPECIFICS              => __('Specific tags', 'formcreator'),
         self::TAG_TYPE_QUESTIONS_AND_SPECIFIC => __('Tags from questions and specific tags', 'formcreator'),
         self::TAG_TYPE_QUESTIONS_OR_SPECIFIC  => __('Tags from questions or specific tags', 'formcreator')
      ];
   }

   public static function getEnumDueDateRule() {
      return [
         self::DUE_DATE_RULE_ANSWER => __('equals to the answer to the question', 'formcreator'),
         self::DUE_DATE_RULE_TICKET => __('calculated from the ticket creation date', 'formcreator'),
         self::DUE_DATE_RULE_CALC => __('calculated from the answer to the question', 'formcreator'),
      ];
   }

   public static function getEnumSlaRule() {
      return [
         self::SLA_RULE_NONE => __('SLA from template or none', 'formcreator'),
         self::SLA_RULE_SPECIFIC => __('Specific SLA', 'formcreator'),
         self::SLA_RULE_FROM_ANWSER => __('Equals to the answer to the question', 'formcreator'),
      ];
   }

   public static function getEnumOlaRule() {
      return [
         self::OLA_RULE_NONE => __('OLA from template or none', 'formcreator'),
         self::OLA_RULE_SPECIFIC => __('Specific OLA', 'formcreator'),
         self::OLA_RULE_FROM_ANWSER => __('Equals to the answer to the question', 'formcreator'),
      ];
   }

   public static function getEnumUrgencyRule() {
      return [
         self::URGENCY_RULE_NONE      => __('Urgency from template or Medium', 'formcreator'),
         self::URGENCY_RULE_SPECIFIC  => __('Specific urgency', 'formcreator'),
         self::URGENCY_RULE_ANSWER    => __('Equals to the answer to the question', 'formcreator'),
      ];
   }

   public static function getEnumCategoryRule() {
      return [
         self::CATEGORY_RULE_NONE         => __('Category from template or none', 'formcreator'),
         self::CATEGORY_RULE_SPECIFIC     => __('Specific category', 'formcreator'),
         self::CATEGORY_RULE_ANSWER       => __('Equals to the answer to the question', 'formcreator'),
         self::CATEGORY_RULE_LAST_ANSWER  => __('Last valid answer', 'formcreator'),
      ];
   }

   public static function getEnumLocationRule() {
      return [
         self::LOCATION_RULE_NONE         => __('Location from template or none', 'formcreator'),
         self::LOCATION_RULE_SPECIFIC     => __('Specific location', 'formcreator'),
         self::LOCATION_RULE_ANSWER       => __('Equals to the answer to the question', 'formcreator'),
         self::LOCATION_RULE_LAST_ANSWER  => __('Last valid answer', 'formcreator'),
      ];
   }

   public static function getEnumValidationRule() {
      return [
         self::COMMONITIL_VALIDATION_RULE_NONE                      => __('No validation', 'formcreator'),
         self::COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP    => __('Specific user or group', 'formcreator'),
         self::COMMONITIL_VALIDATION_RULE_ANSWER_USER               => __('User from question answer', 'formcreator'),
         self::COMMONITIL_VALIDATION_RULE_ANSWER_GROUP              => __('Group from question answer', 'formcreator'),
      ];
   }

   /**
    * @param array $data data of the target
    * @param PluginFormcreatorFormAnswer $formanswer Answers to the form used to populate the target
    * @return array
    */
   protected function setTargetCategory(array $data, PluginFormcreatorFormAnswer $formanswer) : array {
      global $DB;

      $category = null;

      switch ($this->fields['category_rule']) {
         case self::CATEGORY_RULE_ANSWER:
            $category = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['category_question']
               ]
            ])->current();
            $category = $category['answer'];
            break;
         case self::CATEGORY_RULE_SPECIFIC:
            $category = $this->fields['category_question'];
            break;
         case self::CATEGORY_RULE_LAST_ANSWER:
            $form_answer_id = $formanswer->fields['id'];

            // Get all answers for dropdown questions of this form, ordered
            // from last to first displayed
            $answers = $DB->request([
               'SELECT' => ['answer.plugin_formcreator_questions_id', 'answer.answer', 'question.values'],
               'FROM' => PluginFormcreatorAnswer::getTable() . ' AS answer',
               'JOIN' => [
                  PluginFormcreatorQuestion::getTable() . ' AS question' => [
                     'ON' => [
                        'answer' => 'plugin_formcreator_questions_id',
                        'question' => 'id',
                     ]
                  ]
               ],
               'WHERE' => [
                  'answer.plugin_formcreator_formanswers_id' => $form_answer_id,
                  'question.fieldtype'                       => "dropdown",
               ],
               'ORDER' => [
                  'row DESC',
                  'col DESC',
               ]
            ]);

            foreach ($answers as $answer) {
               // Decode dropdown settings
               $question = PluginFormcreatorQuestion::getById($answer[PluginFormcreatorQuestion::getForeignKeyField()]);
               $itemtype = $question->fields['itemtype'];

               // Skip if not a dropdown on categories
               if ($itemtype !== ITILCategory::class) {
                  continue;
               }

               // Skip if question was not answered
               if (empty($answer['answer'])) {
                  continue;
               }

               // Skip if question is not visible
               if (!$formanswer->isFieldVisible($answer['plugin_formcreator_questions_id'])) {
                  continue;
               }

               // Found a valid answer, stop here
               $category = $answer['answer'];
               break;
            }
            break;
      }
      if ($category !== null) {
         $data['itilcategories_id'] = $category;
      }

      return $data;
   }

   protected function setSLA($data, $formanswer) {
      global $DB;

      switch ($this->fields['sla_rule']) {
         case self::SLA_RULE_SPECIFIC:
            if (isset($this->fields['sla_question_tto'])) {
               $data['slas_id_tto'] = $this->fields['sla_question_tto'];
            }

            if (isset($this->fields['sla_question_ttr'])) {
               $data['slas_id_ttr'] = $this->fields['sla_question_ttr'];
            }
            break;

         case self::SLA_RULE_FROM_ANWSER:
            $tto = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->getID(),
                  'plugin_formcreator_questions_id'   => $this->fields['sla_question_tto']
               ]
            ])->current();
            $data['slas_id_tto'] = $tto['answer'];

            $ttr = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->getID(),
                  'plugin_formcreator_questions_id'   => $this->fields['sla_question_ttr']
               ]
            ])->current();
            $data['slas_id_ttr'] = $ttr['answer'];
            break;
      }

      return $data;
   }

   protected function setOLA($data, $formanswer) {
      global $DB;

      switch ($this->fields['ola_rule']) {
         case self::OLA_RULE_SPECIFIC:
            if (isset($this->fields['ola_question_tto'])) {
               $data['olas_id_tto'] = $this->fields['ola_question_tto'];
            }

            if (isset($this->fields['ola_question_ttr'])) {
               $data['olas_id_ttr'] = $this->fields['ola_question_ttr'];
            }
            break;

         case self::OLA_RULE_FROM_ANWSER:
            $tto = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->getID(),
                  'plugin_formcreator_questions_id'   => $this->fields['ola_question_tto']
               ]
            ])->current();
            $data['olas_id_tto'] = $tto['answer'];

            $ttr = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->getID(),
                  'plugin_formcreator_questions_id'   => $this->fields['ola_question_ttr']
               ]
            ])->current();
            $data['olas_id_ttr'] = $ttr['answer'];
            break;
      }

      return $data;
   }

   protected function setTargetUrgency($data, $formanswer) {
      global $DB;

      $urgency = null;
      switch ($this->fields['urgency_rule']) {
         case self::URGENCY_RULE_ANSWER:
            $urgency = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->getID(),
                  'plugin_formcreator_questions_id'   => $this->fields['urgency_question']
               ]
            ])->current();
            $urgency = $urgency['answer'];
            break;
         case self::URGENCY_RULE_SPECIFIC:
            $urgency = $this->fields['urgency_question'];
            break;
      }
      if (!is_null($urgency) && $urgency != 0) {
         $data['urgency'] = $urgency;
      }

      return $data;
   }

   protected function setTargetPriority(array $data): array {
      // Remove default priority so it can be computed
      if (isset($data['urgency']) || isset($data['impact'])) {
         unset($data['priority']);
      }
      return $data;
   }


   /**
    * find all actors and prepare data for the ticket being created
    */
   protected function prepareActors(PluginFormcreatorForm $form, PluginFormcreatorFormAnswer $formanswer) {
      global $DB, $PLUGIN_HOOKS;

      $rows = $DB->request([
         'FROM'   => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE'  => [
            'itemtype' => $this->getType(),
            'items_id' => $this->getID(),
         ]
      ]);
      foreach ($rows as $actor) {
         // If actor type is validator and if the form doesn't have a validator, continue to other actors
         if ($actor['actor_type'] == PluginFormcreatorTarget_Actor::ACTOR_TYPE_VALIDATOR && !$form->fields['validation_required']) {
            continue;
         }

         switch ($actor['actor_type']) {
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHOR :
               $userIds = [$formanswer->fields['requester_id']];
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_VALIDATOR :
               $userIds = [$_SESSION['glpiID']];
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_PERSON :
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP :
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_SUPPLIER :
               $userIds = [$actor['actor_value']];
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_PERSON :
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_GROUP :
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_SUPPLIER :
               $answer  = new PluginFormcreatorAnswer();
               $actorValue = $actor['actor_value'];
               $formanswerId = $formanswer->getID();
               $answer->getFromDBByCrit([
                  'AND' => [
                     'plugin_formcreator_questions_id'   => $actorValue,
                     'plugin_formcreator_formanswers_id' => $formanswerId
                  ]
               ]);

               if ($answer->isNewItem()) {
                  continue 2;
               } else {
                  $userIds = [$answer->fields['answer']];
               }
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_ACTORS:
               $answer  = new PluginFormcreatorAnswer();
               $actorValue = $actor['actor_value'];
               $formanswerId = $formanswer->getID();
               $answer->getFromDBByCrit([
                  'AND' => [
                     'plugin_formcreator_questions_id'   => $actorValue,
                     'plugin_formcreator_formanswers_id' => $formanswerId
                  ]
               ]);

               if ($answer->isNewItem()) {
                  continue 2;
               } else {
                  $userIds = json_decode($answer->fields['answer'], JSON_OBJECT_AS_ARRAY);
               }
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP_FROM_OBJECT:
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT:
                  // Get the object from the question
               $answer  = new PluginFormcreatorAnswer();
               $actorValue = $actor['actor_value'];
               $formanswerId = $formanswer->getID();
               $answer->getFromDBByCrit([
                  'AND' => [
                     'plugin_formcreator_questions_id'   => $actorValue,
                     'plugin_formcreator_formanswers_id' => $formanswerId
                  ]
               ]);
               if ($answer->isNewItem()) {
                  continue 2;
               }
               // Get the itemtype of the object
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($answer->fields[PluginFormcreatorQuestion::getForeignKeyField()]);
               if ($question->isNewItem()) {
                  continue 2;
               }
               $itemtype = $question->fields['itemtype'];
               if (!is_subclass_of($itemtype, CommonDBTM::class)) {
                  continue 2;
               }

               // Check the object has a group FK
               $groupFk = Group::getForeignKeyField();
               if ($actor['actor_type'] == PluginFormcreatorTarget_Actor::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT) {
                  $groupFk = $groupFk . '_tech';
               }
               $object = new $itemtype();
               if (!$DB->fieldExists($object->getTable(), $groupFk)) {
                  continue 2;
               }

               // get the group
               if (!$object->getFromDB($answer->fields['answer'])) {
                  continue 2;
               }

               // ignore invalid ID
               if (Group::isNewId($object->fields[$groupFk])) {
                  continue 2;
               }

               $userIds = [$object->fields[$groupFk]];
               break;

            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHORS_SUPERVISOR:
               $requester_id = $formanswer->fields['requester_id'];

               $user = new User;
               $user = User::getById($requester_id);
               if (is_object($user)) {
                  $userIds = [$user->fields['users_id_supervisor']];
               }
               break;
         }
         $notify = $actor['use_notification'];

         switch ($actor['actor_type']) {
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHOR :
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_VALIDATOR :
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_PERSON :
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_PERSON :
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_ACTORS:
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHORS_SUPERVISOR:
               foreach ($userIds as $userIdOrEmail) {
                  $this->addActor($actor['actor_role'], $userIdOrEmail, $notify);
               }
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP :
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_GROUP :
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP_FROM_OBJECT:
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT:
               foreach ($userIds as $groupId) {
                  $this->addGroupActor($actor['actor_role'], $groupId);
               }
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_SUPPLIER :
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_SUPPLIER :
               foreach ($userIds as $userId) {
                  $this->addActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_SUPPLIER, $userId, $notify);
               }
               break;
            default:
               foreach (($PLUGIN_HOOKS['formcreator_actors_type'] ?? []) as $plugin => $classes) {
                  foreach ($classes as $plugin_target) {
                     if (!is_a($plugin_target, PluginFormcreatorPluginTargetInterface::class, true)) {
                        continue;
                     }
                     if ($actor['actor_type']== $plugin_target::getId()) {
                        $value = $plugin_target::getActorId($formanswer, $actor['actor_value']);
                        if ($value) {
                           if ($plugin_target::getActorType() == PluginFormcreatorPluginTargetInterface::ACTOR_TYPE_USER) {
                              $this->addActor($actor['actor_role'], $value, $notify);
                           } else if (PluginFormcreatorPluginTargetInterface::ACTOR_TYPE_GROUP) {
                              $this->addGroupActor($actor['actor_role'], $value);
                           }
                        }
                        break 2;
                     }
                  }
               }
               break;
         }
      }
   }

   /**
    * Adds an user to the given actor role (requester, observer assigned or supplier)
    *
    * @param string $role role of the user
    * @param string $user user ID or email address for accountless users
    * @param bool $notify true to enable notification for the actor
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
         case PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER:
            $actorType = &$this->requesters['_users_id_requester'];
            $actorTypeNotif = &$this->requesters['_users_id_requester_notif'];
            break;
         case PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER:
            $actorType = &$this->observers['_users_id_observer'];
            $actorTypeNotif = &$this->observers['_users_id_observer_notif'];
            break;
         case PluginFormcreatorTarget_Actor::ACTOR_ROLE_ASSIGNED :
            $actorType = &$this->assigned['_users_id_assign'];
            $actorTypeNotif = &$this->assigned['_users_id_assign_notif'];
            break;
         case PluginFormcreatorTarget_Actor::ACTOR_ROLE_SUPPLIER :
            $actorType = &$this->assignedSuppliers['_suppliers_id_assign'];
            $actorTypeNotif = &$this->assignedSuppliers['_suppliers_id_assign_notif'];
            break;
         default:
            return false;
      }

      if ($userId > 0) {
         // search duplicate account
         $actorKey = array_search($userId, $actorType);
      } else {
         // search duplicate email
         $actorKey = array_search($alternativeEmail, $actorTypeNotif['alternative_email']);
      }
      if ($actorKey === false) {
         // Add the actor
         $actorType[]                           = $userId;
         $actorTypeNotif['use_notification'][]  = $notify;
         $actorTypeNotif['alternative_email'][] = $alternativeEmail;
      } else {
         // New actor settings takes precedence
         $actorType[$actorKey]                           = $userId;
         $actorTypeNotif['use_notification'][$actorKey]  = $notify;
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
      // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
      $actorType = null;
      switch ($role) {
         case PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER:
            $actorType = &$this->requesterGroups['_groups_id_requester'];
            break;
         case PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER :
            $actorType = &$this->observerGroups['_groups_id_observer'];
            break;
         case PluginFormcreatorTarget_Actor::ACTOR_ROLE_ASSIGNED :
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
      global $CFG_GLPI;

      $docItem = new Document_Item();
      if (count($this->attachedDocuments) <= 0) {
         return;
      }

      foreach ($this->attachedDocuments as $documentID => $dummy) {
         $docItem->add([
            'documents_id' => $documentID,
            'itemtype'     => $itemtype,
            'items_id'     => $targetID,
         ]);
         if ($itemtype === Ticket::class) {
            $document = new Document();
            $documentCategoryFk = DocumentCategory::getForeignKeyField();
            $document->update([
               'id' => $documentID,
               $documentCategoryFk => $CFG_GLPI["documentcategories_id_forticket"],
            ]);
         }
      }
   }

   public function addAttachedDocument($documentId) {
      $this->attachedDocuments[$documentId] = true;
   }

   protected function showTemplateSettings() {
      $templateType = $this->getTemplateItemtypeName();
      $templateFk = $templateType::getForeignKeyField();

      echo '<td width="15%">' . $templateType::getTypeName(1) . '</td>';
      echo '<td width="25%">';
      Dropdown::show($templateType, [
         'name'  => $templateFk,
         'value' => $this->fields[$templateFk]
      ]);
      echo '</td>';
   }

   protected  function showDueDateSettings() {
      echo '<td width="15%">' . __('Time to resolve') . '</td>';
      echo '<td width="45%">';

      // Due date type selection
      Dropdown::showFromArray('due_date_rule', self::getEnumDueDateRule(),
         [
            'value'     => $this->fields['due_date_rule'],
            'on_change' => 'plugin_formcreator_formcreatorChangeDueDate(this.value)',
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
      if ($this->fields['due_date_rule'] != self::DUE_DATE_RULE_ANSWER
            && $this->fields['due_date_rule'] != self::DUE_DATE_RULE_CALC) {
         echo '<div id="due_date_questions" style="display:none">';
      } else {
         echo '<div id="due_date_questions">';
      }
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['date', 'datetime'],
         ],
         'due_date_question',
         $this->fields['due_date_question']
      );
      echo '</div>';

      // time shift in minutes
      if ($this->fields['due_date_rule'] != self::DUE_DATE_RULE_TICKET
            && $this->fields['due_date_rule'] != self::DUE_DATE_RULE_CALC) {
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
         self::DUE_DATE_PERIOD_MINUTE => _n('Minute', 'Minutes', 2),
         self::DUE_DATE_PERIOD_HOUR   => _n('Hour', 'Hours', 2),
         self::DUE_DATE_PERIOD_DAY    => _n('Day', 'Days', 2),
         self::DUE_DATE_PERIOD_MONTH  => _n('Month', 'Months', 2),
      ], [
         'value' => $this->fields['due_date_period']
      ]);
      echo '</div>';
      echo '</td>';
   }

   protected function showSLASettings() {
      $label = __("SLA");

      echo '<tr>';
      echo "<td width='15%'>$label</td>";
      echo '<td width="25%">';

      // Due date type selection
      Dropdown::showFromArray("sla_rule", self::getEnumSlaRule(),
         [
            'value'     => $this->fields["sla_rule"],
            'on_change' => "plugin_formcreator_formcreatorChangeSla(this.value)",
            'display_emptychoice' => true
         ]
      );
      echo '</td>';

      $display_specific = $this->fields["sla_rule"] == self::SLA_RULE_SPECIFIC;
      $display_questions = $this->fields["sla_rule"] == self::SLA_RULE_FROM_ANWSER;
      $style_specific = !$display_specific ? "style='display: none'" : "";
      $style_questions = !$display_questions ? "style='display: none'" : "";

      echo '<td width="15%">';

      echo "<span id='sla_specific_title' $style_specific>" . __('SLA (TTO/TTR)', 'formcreator') . '</span>';
      echo "<span id='sla_question_title' $style_questions>" . __('Question (TTO/TTR)', 'formcreator') . '</span>';

      echo '</td>';
      echo '<td width="25%">';

      echo "<div id='sla_specific_value' $style_specific>";
      SLA::dropdown([
         'name'      => '_sla_specific_tto',
         'value'     => $this->fields["sla_question_tto"],
         'condition' => ['type' => SLM::TTO],
      ]);
      echo "&nbsp;&nbsp;";
      SLA::dropdown([
         'name'      => '_sla_specific_ttr',
         'value'     => $this->fields["sla_question_ttr"],
         'condition' => ['type' => SLM::TTR],
      ]);
      echo '</div>';

      echo "<div id='sla_questions' $style_questions>";

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => 'dropdown',
            'itemtype'  => SLA::getType(),
            new QueryExpression("`values` LIKE '%\"show_service_level_types\":\"1\"%'"),
         ],
         "_sla_questions_tto",
         $this->fields["sla_question_tto"]
      );
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => 'dropdown',
            'itemtype'  => SLA::getType(),
            new QueryExpression("`values` LIKE '%\"show_service_level_types\":\"0\"%'"),
         ],
         "_sla_questions_ttr",
         $this->fields["sla_question_ttr"]
      );

      echo '</div>';

      echo '</td>';
      echo '</tr>';
   }

   protected function showOLASettings() {
      $label = __("OLA");

      echo '<tr>';
      echo "<td width='15%'>$label</td>";
      echo '<td width="25%">';

      // Due date type selection
      Dropdown::showFromArray("ola_rule", self::getEnumOlaRule(),
         [
            'value'     => $this->fields["ola_rule"],
            'on_change' => "plugin_formcreator_formcreatorChangeOla(this.value)",
            'display_emptychoice' => true
         ]
      );
      echo '</td>';

      $display_specific = $this->fields["ola_rule"] == self::OLA_RULE_SPECIFIC;
      $display_questions = $this->fields["ola_rule"] == self::OLA_RULE_FROM_ANWSER;
      $style_specific = !$display_specific ? "style='display: none'" : "";
      $style_questions = !$display_questions ? "style='display: none'" : "";

      echo '<td width="15%">';

      echo "<span id='ola_specific_title' $style_specific>" . __('OLA (TTO/TTR)', 'formcreator') . '</span>';
      echo "<span id='ola_question_title' $style_questions>" . __('Question (TTO/TTR)', 'formcreator') . '</span>';

      echo '</td>';
      echo '<td width="25%">';

      echo "<div id='ola_specific_value' $style_specific>";
      OLA::dropdown([
         'name'      => '_ola_specific_tto',
         'value'     => $this->fields["ola_question_tto"],
         'condition' => ['type' => SLM::TTO],
      ]);
      echo "&nbsp;&nbsp;";
      OLA::dropdown([
         'name'      => '_ola_specific_ttr',
         'value'     => $this->fields["ola_question_ttr"],
         'condition' => ['type' => SLM::TTR],
      ]);
      echo '</div>';

      echo "<div id='ola_questions' $style_questions>";

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => 'dropdown',
            'itemtype'  => OLA::getType(),
            new QueryExpression("`values` LIKE '%\"show_service_level_types\":\"1\"%'"),
         ],
         "_ola_questions_tto",
         $this->fields["ola_question_tto"]
      );
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => 'dropdown',
            'itemtype'  => OLA::getType(),
            new QueryExpression("`values` LIKE '%\"show_service_level_types\":\"0\"%'"),
         ],
         "_ola_questions_ttr",
         $this->fields["ola_question_ttr"]
      );

      echo '</div>';

      echo '</td>';
      echo '</tr>';
   }

   protected function showCategorySettings($rand) {
      echo '<tr>';
      echo '<td width="15%">' . ITILCategory::getTypeName(1) . '</td>';
      echo '<td width="25%">';
      Dropdown::showFromArray(
         'category_rule',
         static::getEnumCategoryRule(),
         [
            'value'     => $this->fields['category_rule'],
            'on_change' => "plugin_formcreator_changeCategory($rand)",
            'rand'      => $rand,
         ]
      );
      echo Html::scriptBlock("plugin_formcreator_changeCategory($rand);");
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="category_specific_title" style="display: none">' . PluginFormcreatorCategory::getTypeName(1) . '</span>';
      echo '<span id="category_question_title" style="display: none">' . PluginFormcreatorQuestion::getTypeName(1) . '</span>';
      echo '</td>';
      echo '<td width="25%">';
      echo '<div id="category_question_value" style="display: none">';
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['dropdown'],
            'itemtype'  => ITILCategory::class,
         ],
         '_category_question',
         $this->fields['category_question']
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

   protected function showUrgencySettings($rand) {
      echo '<tr>';
      echo '<td width="15%">' . __('Urgency') . '</td>';
      echo '<td width="45%">';
      Dropdown::showFromArray('urgency_rule', static::getEnumUrgencyRule(), [
         'value'                 => $this->fields['urgency_rule'],
         'on_change'             => "plugin_formcreator_changeUrgency($rand)",
         'rand'                  => $rand
      ]);
      echo Html::scriptBlock("plugin_formcreator_changeUrgency($rand);");
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="urgency_question_title" style="display: none">' . PluginFormcreatorQuestion::getTypeName(1) . '</span>';
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
         $this->getForm(),
         [
            'fieldtype' => ['urgency'],
         ],
         '_urgency_question',
         $this->fields['urgency_question']
      );
      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   protected function showPluginTagsSettings($rand) {
      global $DB;

      $plugin = new Plugin();
      if ($plugin->isInstalled('tag') && $plugin->isActivated('tag')) {
         echo '<tr>';
         echo '<td width="15%">' . __('Ticket tags', 'formcreator') . '</td>';
         echo '<td width="25%">';
         Dropdown::showFromArray(
            'tag_type',
            self::getEnumTagType(),
            [
               'value'     => $this->fields['tag_type'],
               'on_change' => 'change_tag_type()',
               'rand'      => $rand,
            ]
         );

         $tagTypeQuestions = self::TAG_TYPE_QUESTIONS;
         $tagTypeSpecifics = self::TAG_TYPE_SPECIFICS;
         $tagTypeQuestionAndSpecific = self::TAG_TYPE_QUESTIONS_AND_SPECIFIC;
         $tagTypeQuestinOrSpecific = self::TAG_TYPE_QUESTIONS_OR_SPECIFIC;
         $script = <<<SCRIPT
            function change_tag_type() {
               $('#tag_question_title').hide();
               $('#tag_specific_title').hide();
               $('#tag_question_value').hide();
               $('#tag_specific_value').hide();

               switch($('#dropdown_tag_type$rand').val()) {
                  case '$tagTypeQuestions' :
                     $('#tag_question_title').show();
                     $('#tag_question_value').show();
                     break;
                  case '$tagTypeSpecifics' :
                     $('#tag_specific_title').show();
                     $('#tag_specific_value').show();
                     break;
                  case '$tagTypeQuestionAndSpecific' :
                  case '$tagTypeQuestinOrSpecific' :
                     $('#tag_question_title').show();
                     $('#tag_specific_title').show();
                     $('#tag_question_value').show();
                     $('#tag_specific_value').show();
                     break;
               }
            }
            change_tag_type();
SCRIPT;

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
            $this->getForm(),
            [
               'fieldtype' => ['tag'],
            ],
            '_tag_questions',
            explode(',', $this->fields['tag_questions']),
            [
               'multiple' => true,
            ]
         );
         echo '</div>';

         // Specific tags
         echo '<div id="tag_specific_value" style="display: none">';

         $dbUtils = new DbUtils();
         $entityRestrict = $dbUtils->getEntitiesRestrictCriteria(PluginTagTag::getTable(), "", "", true, false);
         if (count($entityRestrict)) {
            $entityRestrict = [$entityRestrict];
         }
         $result = $DB->request([
            'SELECT' => ['id', 'name'],
            'FROM'   => PluginTagTag::getTable(),
            'WHERE'  => [
               'AND' => [
                  'OR' => [
                     ['type_menu' => ['LIKE', '%"' . $this->getTargetItemtypeName() . '"%']],
                     ['type_menu' => ['LIKE', '%"0"%']],
                     ['type_menu' => ''],
                     ['type_menu' => 'NULL'],
                  ],
               ] + $entityRestrict,
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
      global $DB;

      // Get available questions for actors lists
      $actors = [
         PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER => [],
         PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER => [],
         PluginFormcreatorTarget_Actor::ACTOR_ROLE_ASSIGNED => [],
      ];
      $result = $DB->request([
         'SELECT' => ['id', 'actor_role', 'actor_type', 'actor_value', 'use_notification'],
         'FROM' => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE' => [
            'itemtype' => $this->getType(),
            'items_id' => $this->getID(),
         ],
      ]);
      foreach ($result as $actor) {
         $actors[$actor['actor_role']][$actor['id']] = [
            'actor_type'       => $actor['actor_type'],
            'actor_value'      => $actor['actor_value'],
            'use_notification' => $actor['use_notification'],
         ];
      }

      echo '<table class="tab_cadre_fixe" '
      . ' data-itemtype="' . $this->getType() . '"'
      . ' data-id="' . $this->getID() . '"'
      . '>';

      echo '<tr><th class="center" colspan="3">' . __('Actors', 'formcreator') . '</th></tr>';

      echo '<tr>';
      // Requester header
      $this->showActorSettingsHeader(CommonITILActor::REQUESTER);

      // Watcher header
      $this->showActorSettingsHeader(CommonITILActor::OBSERVER);

      // Assigned header
      $this->showActorSettingsHeader(CommonITILActor::ASSIGN);
      echo '</tr>';

      echo '<tr>';
      // Requester
      $this->showActorSettingsForType(CommonITILActor::REQUESTER, $actors);

      // Observer
      $this->showActorSettingsForType(CommonITILActor::OBSERVER, $actors);

      // Assigned to
      $this->showActorSettingsForType(CommonITILActor::ASSIGN, $actors);
      echo '</tr>';

      echo '</table>';
   }

   protected function showLocationSettings($rand) {
      global $DB;

      echo '<tr>';
      echo '<td width="15%">' . __('Location') . '</td>';
      echo '<td width="45%">';
      Dropdown::showFromArray('location_rule', static::getEnumLocationRule(), [
         'value'                 => $this->fields['location_rule'],
         'on_change'             => "plugin_formcreator_change_location($rand)",
         'rand'                  => $rand
      ]);

      echo Html::scriptBlock("plugin_formcreator_change_location($rand)");
      echo '</td>';
      echo '<td width="15%">';
      echo '<span id="location_question_title" style="display: none">' . PluginFormcreatorQuestion::getTypeName(1) . '</span>';
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
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $result = $DB->request([
         'SELECT' => [
            $questionTable => ['id', 'name', 'values', 'itemtype'],
            $sectionTable => ['name as sname'],
         ],
         'FROM' => $questionTable,
         'INNER JOIN' => [
            $sectionTable => [
               'FKEY' => [
                  $sectionTable => 'id',
                  $questionTable => $sectionFk
               ]
            ],
         ],
         'WHERE' => [
            "$formFk" => $this->getForm()->getID(),
            "$questionTable.fieldtype" => 'dropdown'
         ]
      ]);
      $users_questions = [];
      foreach ($result as $question) {
         if ($question['itemtype'] != 'Location') {
            continue;
         }
         $users_questions[$question['sname']][$question['id']] = $question['name'];
      }
      Dropdown::showFromArray('_location_question', $users_questions, [
         'value' => $this->fields['location_question'],
      ]);

      echo '</div>';
      echo '</td>';
      echo '</tr>';
   }

   protected function showValidationSettings($rand) {
      echo '<tr>';

      // Setting label
      echo '<td width="15%">' . __('Validation') . '</td>';

      // Possible values
      echo '<td width="45%">';
      Dropdown::showFromArray('commonitil_validation_rule', static::getEnumValidationRule(), [
         'value'     => $this->fields['commonitil_validation_rule'],
         'on_change' => "plugin_formcreator_change_validation($rand)",
         'rand'      => $rand
      ]);
      echo Html::scriptBlock("plugin_formcreator_change_validation($rand)");
      echo '</td>';

      // Hidden secondary labels, displayed according to the user main choice
      echo '<td width="15%">';

      // Read values
      $validation_rule = $this->fields['commonitil_validation_rule'];

      $display = $validation_rule == self::COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP ? "" : "display: none";
      echo "<span id='commonitil_validation_specific_title' style='$display'>";
      echo __('Approver');
      echo "</span>";

      $display = $validation_rule == self::COMMONITIL_VALIDATION_RULE_ANSWER_USER || $validation_rule == self::COMMONITIL_VALIDATION_RULE_ANSWER_GROUP ? "" : "display: none";
      echo "<span id='commonitil_validation_from_question_title' style='$display'>";
      echo PluginFormcreatorQuestion::getTypeName(1);
      echo "</span>";

      echo '</td>';

      // Hidden secondary values, displayed according to the user main choice
      echo '<td width="25%">';

      // COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP
      $display = $validation_rule == self::COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP ? "" : "display: none";
      echo "<div id='commonitil_validation_specific' style='$display'>";
      $validation_dropdown_params = [
         'name' => 'validation_specific'
      ];
      $validation_data = json_decode($this->fields['commonitil_validation_question'] ?? '', true);
      if (isset($validation_data['type'])) {
         $validation_dropdown_params['users_id_validate'] = $validation_data['values'];
      }
      $validation_dropdown_params['display'] = false;
      echo CommonITILValidation::dropdownValidator($validation_dropdown_params);
      echo '</div>';

      // COMMONITIL_VALIDATION_RULE_ANSWER_USER
      $display = $validation_rule == self::COMMONITIL_VALIDATION_RULE_ANSWER_USER ? "" : "display: none";
      echo "<div id='commonitil_validation_answer_user' style='$display'>";
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'OR' => [
               'fieldtype' => 'actor',
               'AND' => [
                  'fieldtype' => 'glpiselect',
                  'itemtype'  => 'User',
               ]
            ]
         ],
         '_validation_from_user_question',
         $this->fields['commonitil_validation_question'],
      );
      echo '</div>';

      // COMMONITIL_VALIDATION_RULE_ANSWER_GROUP
      $display = $validation_rule == self::COMMONITIL_VALIDATION_RULE_ANSWER_GROUP ? "" : "display: none";
      echo "<div id='commonitil_validation_answer_group' style='$display'>";
      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => 'glpiselect',
            'itemtype'  => 'Group',
         ],
         '_validation_from_group_question',
         $this->fields['commonitil_validation_question'],
      );
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
      if ($this->fields['due_date_question'] != 0) {
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

      $period = '';
      switch ($this->fields['due_date_period']) {
         case self::DUE_DATE_PERIOD_MINUTE:
            $period = "minute";
            break;
         case self::DUE_DATE_PERIOD_HOUR:
            $period = "hour";
            break;
         case self::DUE_DATE_PERIOD_DAY:
            $period = "day";
            break;
         case self::DUE_DATE_PERIOD_MONTH:
            $period = "month";
            break;
      }
      $str    = "+" . $this->fields['due_date_value'] . " $period";

      switch ($this->fields['due_date_rule']) {
         case self::DUE_DATE_RULE_ANSWER:
            $due_date = $date['answer'];
            break;
         case self::DUE_DATE_RULE_TICKET:
            $due_date = date('Y-m-d H:i:s', strtotime($str));
            break;
         case self::DUE_DATE_RULE_CALC:
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

   /**
    * Sets the time to resolve of the target object
    *
    * @param array $data data of the target object
    * @param PluginFormcreatorFormAnswer $formanswer    Answers previously saved
    * @return array updated data of the target object
    */
   protected function setTargetValidation(
      $data,
      PluginFormcreatorFormAnswer $formanswer
   ) {
      global $DB;

      switch ($this->fields['commonitil_validation_rule']) {
         case self::COMMONITIL_VALIDATION_RULE_NONE:
         default:
            // No action
            break;

         case self::COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP:
            $validation_data = json_decode($this->fields['commonitil_validation_question'], true);

            if (!is_null($validation_data)) {
               $data['validatortype'] = $validation_data['type'];
               $data['users_id_validate'] = $validation_data['values'];
            }

            break;

         case self::COMMONITIL_VALIDATION_RULE_ANSWER_USER:
            $answers = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['commonitil_validation_question']
               ]
            ]);

            foreach ($answers as $answer) {
               // Answer may be "2" or [2], both valid json
               $answer = json_decode($answer['answer']);
               if (!is_array($answer)) {
                  $answer = [$answer];
               }
               $data['validatortype'] = 'user';
               $data['users_id_validate'] = $answer;
               break;
            }

            break;

         case self::COMMONITIL_VALIDATION_RULE_ANSWER_GROUP:
            $answers = $DB->request([
               'SELECT' => ['answer'],
               'FROM'   => PluginFormcreatorAnswer::getTable(),
               'WHERE'  => [
                  'plugin_formcreator_formanswers_id' => $formanswer->fields['id'],
                  'plugin_formcreator_questions_id'   => $this->fields['commonitil_validation_question']
               ]
            ]);

            foreach ($answers as $answer) {
               // Answer may be "2" or [2], both valid json
               $answer = json_decode($answer['answer']);
               if (!is_array($answer)) {
                  $answer = [$answer];
               }

               // Get all user in the given group
               $user_group = new Group_User();
               $user_groups = $user_group->find([
                  'groups_id' => $answer
               ]);

               // Parse values
               $values = [];
               foreach ($user_groups as $row) {
                  $values[] = $row['users_id'];
               }

               $data['validatortype'] = 'group';
               $data['users_id_validate'] = $values;
               break;
            }

            break;
      }

      return $data;
   }

   public function prepareInputForAdd($input) {
      $input = parent::prepareInputForAdd($input);
      if ($input === false || (is_array($input) && count($input) < 1)) {
         return false;
      }

      if (isset($input['_skip_create_actors']) && $input['_skip_create_actors']) {
         $this->skipCreateActors = true;
      }

      if (!isset($input['target_name']) || strlen($input['target_name']) < 1) {
         $input['target_name'] = $input['name'];
      }

      // Set default content
      if (!isset($input['content']) || isset($input['content']) && empty($input['content'])) {
         $input['content'] = '##FULLFORM##';
      }

      return $input;
   }

   public function prepareInputForUpdate($input) {
      $input = parent::prepareInputForUpdate($input);
      if ($input === false || (is_array($input) && count($input) < 1)) {
         return [];
      }

      if (!$this->skipChecks) {
         if (isset($input['name'])
            && empty($input['name'])) {
            Session::addMessageAfterRedirect(__('The name cannot be empty!', 'formcreator'), false, ERROR);
            return [];
         }

         if (isset($input[('content')])) {
            $input['content'] = Html::entity_decode_deep($input['content']);
         }

         // - content is required
         if (isset($input['content']) && strlen($input['content']) < 1) {
            Session::addMessageAfterRedirect(__('The description cannot be empty!', 'formcreator'), false, ERROR);
            return [];
         }

         if (Plugin::isPluginActive('tag')) {
            if (isset($input['_tag_questions'])) {
               $input['tag_questions'] = (!empty($input['_tag_questions']))
                                          ? implode(',', $input['_tag_questions'])
                                          : '';
            }
            if (isset($input['_tag_specifics'])) {
               $input['tag_specifics'] = (!empty($input['_tag_specifics']))
                                       ? implode(',', $input['_tag_specifics'])
                                       : '';
            }
         }
      }

      if (isset($input['_skip_create_actors']) && $input['_skip_create_actors']) {
         $this->skipCreateActors = true;
      }

      if (isset($input['commonitil_validation_rule'])) {
         switch ($input['commonitil_validation_rule']) {
            default:
            case self::COMMONITIL_VALIDATION_RULE_NONE:
               $input['commonitil_validation_question'] = '0';
               break;

            case self::COMMONITIL_VALIDATION_RULE_SPECIFIC_USER_OR_GROUP:
               $input['commonitil_validation_question'] = json_encode([
                  'type'   => $input['validatortype'],
                  'values' => $input['validation_specific']
               ]);
               break;

            case self::COMMONITIL_VALIDATION_RULE_ANSWER_USER:
               $input['commonitil_validation_question'] = $input['_validation_from_user_question'];
               break;

            case self::COMMONITIL_VALIDATION_RULE_ANSWER_GROUP:
               $input['commonitil_validation_question'] = $input['_validation_from_group_question'];
               break;
         }
      }

      return $input;
   }

   public function post_addItem() {
      parent::post_addItem();
      if ($this->skipCreateActors) {
         return;
      }

      $target_actor = new PluginFormcreatorTarget_Actor();
      $target_actor->add([
         'itemtype'            => $this->getType(),
         'items_id'            => $this->getID(),
         'actor_role'          => PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER,
         'actor_type'          => PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHOR,
         'use_notification'    => '1',
      ]);
      $target_actor = new PluginFormcreatorTarget_Actor();
      $target_actor->add([
         'itemtype'            => $this->getType(),
         'items_id'            => $this->getID(),
         'actor_role'          => PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER,
         'actor_type'          => PluginFormcreatorTarget_Actor::ACTOR_TYPE_VALIDATOR,
         'use_notification'    => '1',
      ]);
   }

   protected function getDeleteImage() {
      $link = '<a onclick="plugin_formcreator.deleteActor(this)">';
      $link .= '<i style="color: #000" class="fas fa-trash-alt" alt="' . __('Delete') . '" title="' . __('Delete') . '"></i>';
      $link .= '</a>';
      return $link;
   }

   public function pre_purgeItem() {
      // delete actors related to this instance
      $targetItemActor = new PluginFormcreatorTarget_Actor();
      if (!$targetItemActor->deleteByCriteria(['itemtype' => $this->getType(), 'items_id' => $this->getID()])) {
         $this->input = false;
         return false;
      }

      return true;
   }

   /**
    * Associate tags to the target item
    *
    * @param PluginFormcreatorFormanswer $formanswer the source formanswer
    * @param int $targetId ID of the generated target
    * @return void
    */
   protected function saveTags(PluginFormcreatorFormanswer $formanswer, $targetId) {
      global $DB;

      // Add tag if presents
      $plugin = new Plugin();
      if (!$plugin->isActivated('tag')) {
         return;
      }

      $tagObj = new PluginTagTagItem();
      $tags   = [];

      // Add question tags
      if (($this->fields['tag_type'] == self::TAG_TYPE_QUESTIONS
            || $this->fields['tag_type'] == self::TAG_TYPE_QUESTIONS_AND_SPECIFIC
            || $this->fields['tag_type'] == self::TAG_TYPE_QUESTIONS_OR_SPECIFIC)
            && (!empty($this->fields['tag_questions']))) {
         $formAnswerFk = PluginFormcreatorFormAnswer::getForeignKeyField();
         $questionFk = PluginFormcreatorQuestion::getForeignKeyField();
         $result = $DB->request([
            'SELECT' => ['plugin_formcreator_questions_id', 'answer'],
            'FROM' => PluginFormcreatorAnswer::getTable(),
            'WHERE' => [
               $formAnswerFk => [(int) $formanswer->fields['id']],
               $questionFk => explode(',', $this->fields['tag_questions'])
            ],
         ]);
         foreach ($result as $line) {
            $question = new PluginFormcreatorQuestion();
            $question->getFromDB($line['plugin_formcreator_questions_id']);
            $field = $question->getSubField();
            $field->deserializeValue($line['answer']);
            $tab = $field->getRawValue();
            if (is_integer($tab)) {
               $tab = [$tab];
            }
            if (is_array($tab)) {
               $tags = array_merge($tags, $tab);
            }
         }
      }

      // Add specific tags
      if ($this->fields['tag_type'] == self::TAG_TYPE_SPECIFICS
                  || $this->fields['tag_type'] == self::TAG_TYPE_QUESTIONS_AND_SPECIFIC
                  || ($this->fields['tag_type'] == self::TAG_TYPE_QUESTIONS_OR_SPECIFIC && empty($tags))
                  && (!empty($this->fields['tag_specifics']))) {

         $tags = array_merge($tags, explode(',', $this->fields['tag_specifics']));
      }

      $tags = array_unique($tags);

      // Save tags in DB
      foreach ($tags as $tag) {
         $tagObj->add([
            'plugin_tag_tags_id' => $tag,
            'items_id'           => $targetId,
            'itemtype'           => $this->getTargetItemtypeName(),
         ]);
      }
   }

   /**
    * Show header for actors edition
    *
    * @param int $type see CommonITILActor constants
    * @return void
    */
   protected function showActorSettingsHeader($type) {
      switch ($type) { // Values from CommonITILObject::getSearchOptionsActors()
         case CommonITILActor::REQUESTER:
            $label =  _n('Requester', 'Requesters', 1);
            $displayJSFunction = 'plugin_formcreator_displayRequesterForm()';
            $hideJSFunction = 'plugin_formcreator_hideRequesterForm()';
            $buttonAdd = 'btn_add_requester';
            $buttonCancel = 'btn_cancel_requester';
            break;
         case CommonITILActor::OBSERVER:
            $label =  _n('Watcher', 'Watchers', 1);
            $displayJSFunction = 'plugin_formcreator_displayWatcherForm()';
            $hideJSFunction = 'plugin_formcreator_hideWatcherForm()';
            $buttonAdd = 'btn_add_watcher';
            $buttonCancel = 'btn_cancel_watcher';
            break;
         case CommonITILActor::ASSIGN:
            $label =  __('Assigned to');
            $displayJSFunction = 'plugin_formcreator_displayAssignedForm()';
            $hideJSFunction = 'plugin_formcreator_hideAssignedForm()';
            $buttonAdd = 'btn_add_assigned';
            $buttonCancel = 'btn_cancel_assigned';
            break;
      }

      echo '<th width="33%">';
      echo $label . ' &nbsp;';
      echo '<i class="fas fa-plus-circle" title="' . __('Add', 'formcreator'). '" alt="' . __('Add', 'formcreator'). '" onclick="' . $displayJSFunction . '" class="pointer"
         id="' . $buttonAdd . '"></i>';
      echo '<i class="fas fa-minus-circle" title="' . __('Cancel', 'formcreator'). '" alt="' . __('Cancel', 'formcreator'). '" onclick="' . $hideJSFunction . '" class="pointer"
         id="' . $buttonCancel . '" style="display:none"></i>';
      echo '</th>';
   }

   /**
    * Show header for actors edition
    *
    * @param int $actorType see CommonITILActor constants
    * @param array $actors actors to show
    * @return void
    */
   protected function showActorSettingsForType($actorType, array $actors) {
      global $DB, $PLUGIN_HOOKS;

      $itemActor = new PluginFormcreatorTarget_Actor();
      $dropdownItems = ['' => Dropdown::EMPTY_VALUE] + $itemActor::getEnumActorType();

      switch ($actorType) { // Values from CommonITILObject::getSearchOptionsActors()
         case CommonITILActor::REQUESTER:
            $type = 'requester';
            unset($dropdownItems[PluginFormcreatorTarget_Actor::ACTOR_TYPE_SUPPLIER]);
            unset($dropdownItems[PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_SUPPLIER]);
            $changeActorJSFunction = 'plugin_formcreator.changeActor("requester", this.value)';
            $actorRole = PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER;
            break;
         case CommonITILActor::OBSERVER:
            $type = 'watcher';
            $changeActorJSFunction = 'plugin_formcreator.changeActor("watcher", this.value)';
            $actorRole = PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER;
            break;
         case CommonITILActor::ASSIGN:
            $type = 'assigned';
            unset($dropdownItems[PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHORS_SUPERVISOR]);
            $changeActorJSFunction = 'plugin_formcreator.changeActor("assigned", this.value)';
            $actorRole = PluginFormcreatorTarget_Actor::ACTOR_ROLE_ASSIGNED;
            break;
      }

      echo '<td valign="top">';
      echo '<form name="form_target"'
      . ' id="form_add_' . $type . '"'
      . ' style="display:none"'
      . 'action="javascript:;"'
      . '">';
      Dropdown::showFromArray(
         'actor_type',
         $dropdownItems, [
            'on_change' => $changeActorJSFunction,
         ]
      );

      echo '<div style="display:none" data-actor-type="' . $type . "_" .  PluginFormcreatorTarget_Actor::ACTOR_TYPE_PERSON . '">';
      User::dropdown([
         'name' => 'actor_value_' . PluginFormcreatorTarget_Actor::ACTOR_TYPE_PERSON,
         'right' => 'all',
         'all'   => 0,
      ]);
      echo '</div>';

      echo '<div style="display:none" data-actor-type="' . $type . "_" . PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP . '">';
      Group::dropdown([
         'name' => 'actor_value_' . PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP,
      ]);
      echo '</div>';

      echo '<div style="display:none" data-actor-type="' . $type . "_" .  PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_PERSON . '">';
      // find already used items
      $request = $DB->request([
         'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE' => [
            'itemtype'   => $this->getType(),
            'items_id'   => $this->getID(),
            'actor_role' => $actorRole,
            'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_PERSON,
         ]
      ]);
      $used = [];
      foreach ($request as $row) {
         $used[$row['actor_value']] = $row['actor_value'];
      }

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'OR' => [
               'AND' => [
                  'fieldtype' => ['glpiselect'],
                  'itemtype'  => User::class,
               ],
               [
                  'fieldtype' => ['email'],
               ]
            ],
         ],
         'actor_value_' . PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_PERSON,
         0,
         [
            'used' => $used,
         ]
      );
      echo '</div>';

      echo '<div style="display:none" data-actor-type="' . $type . "_" .  PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_GROUP . '">';
      // find already used items
      $request = $DB->request([
         'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE' => [
            'itemtype'   => $this->getType(),
            'items_id'   => $this->getID(),
            'actor_role' => $actorRole,
            'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_GROUP,
         ]
      ]);
      $used = [];
      foreach ($request as $row) {
         $used[$row['actor_value']] = $row['actor_value'];
      }

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['glpiselect'],
            'itemtype'  => Group::class,
         ],
         'actor_value_' .  PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_GROUP,
         0,
         [
            'used' => $used,
         ]
      );
      echo '</div>';

      echo '<div style="display:none" data-actor-type="' . $type . "_" .  PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP_FROM_OBJECT . '">';
      // find already used items
      $request = $DB->request([
         'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE' => [
            'itemtype'   => $this->getType(),
            'items_id'   => $this->getID(),
            'actor_role' => $actorRole,
            'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP_FROM_OBJECT,
         ]
      ]);
      $used = [];
      foreach ($request as $row) {
         $used[$row['actor_value']] = $row['actor_value'];
      }

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['glpiselect'],
         ],
         'actor_value_' .  PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP_FROM_OBJECT,
         0,
         [
            'used' => $used,
         ]
      );
      echo '</div>';

      echo '<div style="display:none" data-actor-type="' . $type . "_" . PluginFormcreatorTarget_Actor::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT . '">';
      // find already used items
      $request = $DB->request([
         'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE' => [
            'itemtype'   => $this->getType(),
            'items_id'   => $this->getID(),
            'actor_role' => $actorRole,
            'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT,
         ]
      ]);
      $used = [];
      foreach ($request as $row) {
         $used[$row['actor_value']] = $row['actor_value'];
      }

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['glpiselect'],
         ],
         'actor_value_' .  PluginFormcreatorTarget_Actor::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT,
         0,
         [
            'used' => $used,
         ]
      );
      echo '</div>';

      echo '<div style="display:none" data-actor-type="' . $type . "_" . PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_ACTORS . '">';
       // find already used items
      $request = $DB->request([
         'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
         'WHERE' => [
            'itemtype'   => $this->getType(),
            'items_id'   => $this->getID(),
            'actor_role' => $actorRole,
            'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_ACTORS,
         ]
      ]);
      $used = [];
      foreach ($request as $row) {
         $used[$row['actor_value']] = $row['actor_value'];
      }

      PluginFormcreatorQuestion::dropdownForForm(
         $this->getForm(),
         [
            'fieldtype' => ['actor'],
         ],
         'actor_value_' . PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_ACTORS,
         0,
         [
            'used' => $used,
         ]
      );
      echo '</div>';

      if ($actorType == CommonITILActor::ASSIGN) {
         echo '<div style="display:none" data-actor-type="' . $type . "_" . PluginFormcreatorTarget_Actor::ACTOR_TYPE_SUPPLIER . '">';
         // find already used items
         $request = $DB->request([
            'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
            'WHERE' => [
               'itemtype'   => $this->getType(),
               'items_id'   => $this->getID(),
               'actor_role' => $actorRole,
               'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_SUPPLIER,
            ]
         ]);
         $used = [];
         foreach ($request as $row) {
            $used[$row['actor_value']] = $row['actor_value'];
         }

         Supplier::dropdown([
            'name' => 'actor_value_' . PluginFormcreatorTarget_Actor::ACTOR_TYPE_SUPPLIER,
            'used' => $used,
         ]);
         echo '</div>';

         echo '<div style="display:none" data-actor-type="' . $type . "_" . PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_SUPPLIER . '">';
         // find already used items
         $request = $DB->request([
            'FROM'  => PluginFormcreatorTarget_Actor::getTable(),
            'WHERE' => [
               'itemtype'   => $this->getType(),
               'items_id'   => $this->getID(),
               'actor_role' => $actorRole,
               'actor_type' => PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_SUPPLIER,
            ]
         ]);
         $used = [];
         foreach ($request as $row) {
            $used[$row['actor_value']] = $row['actor_value'];
         }

         PluginFormcreatorQuestion::dropdownForForm(
            $this->getForm(),
            [
               'fieldtype' => ['glpiselect'],
               'itemtype'  => Supplier::class,
            ],
            'actor_value_' . PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_SUPPLIER,
            0,
            [
               'used' => $used,
            ]
         );
         echo '</div>';
      }

      foreach (($PLUGIN_HOOKS['formcreator_actors_type'] ?? []) as $plugin => $classes) {
         foreach ($classes as $plugin_target) {
            if (!is_a($plugin_target, PluginFormcreatorPluginTargetInterface::class, true)) {
               continue;
            }

            // Show custom form
            echo '<div style="display:none" data-actor-type="' . $type . "_" . $plugin_target::getId() . '">';
            echo $plugin_target::getForm($this->getForm());
            echo '</div>';
         }
      }

      echo '<div>';
      echo __('Email followup');
      Dropdown::showYesNo('use_notification', 1);
      echo '</div>';

      echo '<p align="center">';
      echo Html::hidden('actor_role', ['value' => $actorRole]);
      echo Html::submit(_x('button', 'Save'), ['name' => 'update_actors', 'value' => __('Add'), 'onclick' => 'plugin_formcreator.addActor(this)']);
      echo '</p>';

      echo "<hr>";

      Html::closeForm();

      $img_user     = static::getUserImage();
      $img_group    = static::getGroupImage();
      $img_supplier = static::getSupplierImage();
      $img_mail     = static::getMailImage();
      $img_nomail   = static::getNoMailImage();

      foreach ($actors[$actorRole] as $id => $values) {
         echo '<div data-itemtype="PluginFormcreatorTarget_Actor" data-id="' . $id . '">';
         switch ($values['actor_type']) {
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHOR :
               echo $img_user . ' <b>' . __('Form author', 'formcreator') . '</b>';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_VALIDATOR :
               echo $img_user . ' <b>' . __('Form validator', 'formcreator') . '</b>';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_PERSON :
               $user = new User();
               $user->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('User') . ' </b> "' . $user->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_PERSON :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Person from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP :
               $group = new Group();
               $group->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Group') . ' </b> "' . $group->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_GROUP :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Group from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP_FROM_OBJECT:
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Group from the object', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT:
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_group . ' <b>' . __('Tech group from the object', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_ACTORS:
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_user . ' <b>' . __('Actors from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_SUPPLIER :
               $supplier = new Supplier();
               $supplier->getFromDB($values['actor_value']);
               echo $img_supplier . ' <b>' . __('Supplier') . ' </b> "' . $supplier->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_SUPPLIER :
               $question = new PluginFormcreatorQuestion();
               $question->getFromDB($values['actor_value']);
               echo $img_supplier . ' <b>' . __('Supplier from the question', 'formcreator')
               . '</b> "' . $question->getName() . '"';
               break;
            case PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHORS_SUPERVISOR :
               echo $img_user . ' <b>' . __('Form author\'s supervisor', 'formcreator') . '</b>';
               break;
            default:
               foreach (($PLUGIN_HOOKS['formcreator_actors_type'] ?? []) as $plugin => $classes) {
                  foreach ($classes as $plugin_target) {
                     if (!is_a($plugin_target, PluginFormcreatorPluginTargetInterface::class, true)) {
                        continue;
                     }

                     if ($values['actor_type'] == $plugin_target::getId()) {
                        echo $plugin_target::getDisplayedValue($values['actor_value']);
                        break 2;
                     }
                  }
               }
               break;
         }
         echo $values['use_notification'] ? ' ' . $img_mail . ' ' : ' ' . $img_nomail . ' ';
         echo $this->getDeleteImage();
         echo '</div>';
      }

      echo '</td>';
   }

   protected function initializeActors() {
      // Prepare actors structures for creation of the ticket
      $this->requesters = [
         '_users_id_requester'         => [],
         '_users_id_requester_notif'   => [
            'use_notification'      => [],
            'alternative_email'     => [],
         ],
      ];
      $this->observers = [
         '_users_id_observer'          => [],
         '_users_id_observer_notif'    => [
            'use_notification'      => [],
            'alternative_email'     => [],
         ],
      ];
      $this->assigned = [
         '_users_id_assign'            => [],
         '_users_id_assign_notif'      => [
            'use_notification'      => [],
            'alternative_email'     => [],
         ],
      ];

      $this->assignedSuppliers = [
         '_suppliers_id_assign'        => [],
         '_suppliers_id_assign_notif'  => [
            'use_notification'      => [],
            'alternative_email'     => [],
         ]
      ];

      $this->requesterGroups = [
         '_groups_id_requester'        => [],
      ];

      $this->observerGroups = [
         '_groups_id_observer'         => [],
      ];

      $this->assignedGroups = [
         '_groups_id_assign'           => [],
      ];
   }

   /**
    * Cleanup invalid actors or emoty keys on actors of the target
    *
    * @param array $data
    * @return array
    */
   public function cleanActors(array $data): array {
      $actorTypes = [
         '_users_id_requester',
         '_users_id_observer',
         '_users_id_assign',
         '_suppliers_id_assign',
      ];

      foreach ($actorTypes as $actorType) {
         if (isset($data["$actorType"])) {
            if (is_array($data["$actorType"])) {
               if (count($data["$actorType"]) < 1) {
                  unset($data["$actorType"]);
                  unset($data["{$actorType}_notif"]);
               } else {
                  $cleaned = [];
                  $cleaned_notif = [];
                  foreach ($data["$actorType"] as $key => $actor) {
                     $cleaned[] = $actor;
                     $cleaned_notif['use_notification'][] = $data["{$actorType}_notif"]['use_notification'][$key];
                     $cleaned_notif['alternative_email'][] = $data["{$actorType}_notif"]['alternative_email'][$key];
                  }
                  $data["$actorType"] = $cleaned;
                  $data["{$actorType}_notif"] = $cleaned_notif;
               }
            } else {
               if ($data["$actorType"] == 0) {
                  if (isset($data["{$actorType}_notif"]) && count($data["{$actorType}_notif"]) < 1) {
                     unset($data["$actorType"]);
                     unset($data["{$actorType}_notif"]);
                  }
               }
            }
         }
      }

      if (isset($data['_groups_id_requester']) && $data['_groups_id_requester'] == 0) {
         unset($data['_groups_id_requester']);
      }

      if (isset($data['_groups_id_observer']) && $data['_groups_id_observer'] == 0) {
         unset($data['_groups_id_observer']);
      }

      if (isset($data['_groups_id_assign']) && $data['_groups_id_assign'] == 0) {
         unset($data['_groups_id_assign']);
      }

      return $data;
   }

   /**
    * Set default values for the item to create
    *
    * @param PluginFormcreatorFormAnswer $formanswer
    * @return array
    */
   public function getDefaultData(PluginFormcreatorFormAnswer $formanswer): array {
      $this->initializeActors();

      $targetItemtype = $this->getTargetItemtypeName();
      $targetTemplateFk = $targetItemtype::getForeignKeyField();

      $data = $targetItemtype::getDefaultValues();

      $this->fields[$targetTemplateFk] = $this->getTargetTemplate($data);

      // Get predefined Fields
      $predefinedFieldItemtype = $this->getTemplatePredefinedFieldItemtype();
      $templatePredeinedField  = new $predefinedFieldItemtype();
      $predefined_fields       = $templatePredeinedField->getPredefinedFields($this->fields[$targetTemplateFk], true);

      if (isset($predefined_fields['_users_id_requester'])) {
         $this->addActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER, $predefined_fields['_users_id_requester'], true);
         unset($predefined_fields['_users_id_requester']);
      }
      if (isset($predefined_fields['_users_id_observer'])) {
         $this->addActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER, $predefined_fields['_users_id_observer'], true);
         unset($predefined_fields['_users_id_observer']);
      }
      if (isset($predefined_fields['_users_id_assign'])) {
         $this->addActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_ASSIGNED, $predefined_fields['_users_id_assign'], true);
         unset($predefined_fields['_users_id_assign']);
      }

      if (isset($predefined_fields['_groups_id_requester'])) {
         $this->addGroupActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER, $predefined_fields['_groups_id_requester']);
         unset($predefined_fields['_groups_id_requester']);
      }
      if (isset($predefined_fields['_groups_id_observer'])) {
         $this->addGroupActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER, $predefined_fields['_groups_id_observer']);
         unset($predefined_fields['_groups_id_observer']);
      }
      if (isset($predefined_fields['_groups_id_assign'])) {
         $this->addGroupActor(PluginFormcreatorTarget_Actor::ACTOR_ROLE_ASSIGNED, $predefined_fields['_groups_id_assign']);
         unset($predefined_fields['_groups_id_assign']);
      }

      // Manage special values
      if (!isset($predefined_fields['date']) || isset($predefined_fields['date']) && $predefined_fields['date'] == 'NOW') {
         $predefined_fields['date'] = $_SESSION['glpi_currenttime'];
      }

      $data = array_merge($data, $predefined_fields);

      $data = $this->setTargetCategory($data, $formanswer);

      if (($data['requesttypes_id'] ?? 0) == 0) {
         unset($data['requesttypes_id']);
      }

      return $data;
   }

   protected function prepareUploadsFromTextarea(array $data, PluginFormcreatorFormAnswer $formanswer): array {
      $saved_documents = $formanswer->getFileProperties();

      if ($saved_documents) {
         foreach ($formanswer->getForm()->getFields() as $questionId => $field) {
            if (!($field instanceOf TextareaField)) {
               continue;
            }
            if (!isset($saved_documents["_content"][$questionId])) {
               continue;
            }
            $data["_content"] = array_merge($data["_content"], $saved_documents["_content"][$questionId] ?? []);
            $data["_tag_content"] = array_merge($data["_tag_content"], $saved_documents["_tag_content"][$questionId] ?? []);

            foreach ($saved_documents["_content"][$questionId] as $key => $filename) {
               $uploaded_filename = $formanswer->getFileName($questionId, $key);
               if ($uploaded_filename != '') {
                  copy(GLPI_DOC_DIR . '/' . $uploaded_filename, GLPI_TMP_DIR . '/' . $filename);
               }
            }
         }
      } else {
         foreach ($formanswer->getForm()->getFields() as $questionId => $field) {
            if (!($field instanceOf TextareaField)) {
               continue;
            }
            $data["_content"] = array_merge($data["_content"], $formanswer->input["_formcreator_field_" . $questionId]);
            $data["_prefix_content"] = array_merge($data["_prefix_content"], $formanswer->input["_prefix_formcreator_field_" . $questionId]);
            $data["_tag_content"] = array_merge($data["_tag_content"], $formanswer->input["_tag_formcreator_field_" . $questionId]);
            foreach ($formanswer->input["_formcreator_field_" . $questionId] as $key => $filename) {
               $uploaded_filename = $formanswer->getFileName($questionId, $key);
               if ($uploaded_filename != '') {
                  copy(GLPI_DOC_DIR . '/' . $uploaded_filename, GLPI_TMP_DIR . '/' . $filename);
               }
            }
         }
      }

      return $data;
   }

   /**
    * Undocumented function
    *
    * @param array $data
    * @param PluginFormcreatorFormAnswer $formanswer
    * @return array
    */
   protected function setDocuments($data, PluginFormcreatorFormAnswer $formanswer): array {
      foreach ($formanswer->getQuestionFields($formanswer->getForm()->getID()) ?? [] as $field) {
         $question = $field->getQuestion();
         if ($question->fields['fieldtype'] !== 'glpiselect') {
            continue;
         }
         if ($question->fields['itemtype'] !== Document::class) {
            continue;
         }

         $data['_documents_id'][] = $field->getRawValue();
      }

      return $data;
   }

   /**
    * Emulate file uploads for documents provided to file questions
    *
    * @param array $data
    * @return array input $data updated with (fake) file uploads
    */
   protected function prepareUploadedFiles(array $data): array {
      $data['_filename'] = [];
      $data['_prefix_filename'] = [];
      $data['_tag_filename'] = [];

      // emulate file uploads of inline images
      // TODO: replace PluginFormcreatorCommon::getDocumentsFromTag by Toolbox::getDocumentsFromTag
      // when is merged https://github.com/glpi-project/glpi/pull/9335
      foreach (PluginFormcreatorCommon::getDocumentsFromTag($data['content']) as $document) {
         $prefix = uniqid('', true);
         $filename = $prefix . 'image_paste.' . pathinfo($document['filename'], PATHINFO_EXTENSION);
         if (!@copy(GLPI_DOC_DIR . '/' . $document['filepath'], GLPI_TMP_DIR . '/' . $filename)) {
            continue;
         }

         // Formanswers answers contains document tags to allow
         // Replace them with a IMG tag similar to those found after pasting an
         // image in a textarea
         // <img id="..." src="blob:http://..." data-upload_id=".." />
         // the attribute id is requires to let GLPI process the upload properly
         $img = "<img id='" . $document['tag'] . "' src='' />";
         $data['content'] = preg_replace(
            '/' . Document::getImageTag($document['tag']) . '/',
            Sanitizer::sanitize($img),
            $data['content']
         );

         $data['_filename'][] = $filename;
         $data['_prefix_filename'][] = $prefix;
         $data['_tag_filename'][] = $document['tag'];
      }

      // emulate file upload
      foreach (array_keys($this->attachedDocuments) as $documentId) {
         $document = new Document();
         if (!$document->getFromDB($documentId)) {
            continue;
         }

         $prefix = uniqid('', true);
         $filename = $prefix . $document->fields['filename'];
         if (!copy(GLPI_DOC_DIR . '/' . $document->fields['filepath'], GLPI_TMP_DIR . '/' . $filename)) {
            continue;
         }

         $data['_filename'][] = $filename;
         $data['_prefix_filename'][] = $prefix;
         $data['_tag_filename'][] = $document->fields['tag'];
      }

      return $data;
   }

   public static function getTargetType(): int {
      return self::TARGET_TYPE_OBJECT;
   }

   /**
    * Find generated targets for this target and the given form answer
    *
    * @param PluginFormcreatorFormAnswer $formAnswer
    * @return array
    */
   public static function findForFormAnswer(PluginFormcreatorFormAnswer $formAnswer): array {
      global $DB;

      $targets = [];
      $relationType = static::getItem_Item();
      $relationTable = $relationType::getTable();
      $generatedType = static::getTargetItemtypeName();
      $generatedTypeTable = $generatedType::getTable();
      $fk = $generatedType::getForeignKeyField();
      $iterator = $DB->request([
         'SELECT' => ["$generatedTypeTable.*"],
         'FROM' => $generatedTypeTable,
         'INNER JOIN' => [
            $relationTable => [
               'FKEY' => [
                  $generatedTypeTable => 'id',
                  $relationTable => $fk,
               ],
            ],
         ],
         'WHERE' => [
            "$relationTable.itemtype" => PluginFormcreatorFormAnswer::getType(),
            "$relationTable.items_id" => $formAnswer->getID(),
         ],
      ]);
      foreach ($iterator as $row) {
         /** @var $item CommonDBTM */
         $item = new $generatedType();
         $item->getFromResultSet($row);
         $targets[] = $item;
      }

      return $targets;
   }

   public static function getUserImage() {
      return '<i class="fas fa-user" alt="' . __('User') . '" title="' . __('User') . '" width="20"></i>';
   }

   public static function getGroupImage() {
      return  '<i class="fas fa-users" alt="' . __('Group') . '" title="' . __('Group') . '" width="20"></i>';
   }

   public static function getSupplierImage() {
      return '<i class="fas fa-suitcase" alt="' . __('Supplier') . '" title="' . __('Supplier') . '" width="20"></i>';
   }

   public static function getMailImage() {
      return '<i class="fas fa-envelope pointer"  title="' . __('Email followup') . ' ' . __('Yes') . '" width="20"></i>';
   }

   public static function getNoMailImage() {
      return '<i class="fas fa-envelope pointer" title="' . __('Email followup') . ' ' . __('No') . '" width="20"></i>';
   }

   public function getCloneRelations(): array {
      return [
         PluginFormcreatorTarget_Actor::class,
         PluginFormcreatorCondition::class,
      ];
   }

   public function prepareInputForClone($input) {
      $input = parent::prepareInputForClone($input);
      $input['_skip_create_actors'] = true;
      return $input;
   }
}
