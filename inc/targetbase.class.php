<?php
abstract class PluginFormcreatorTargetBase extends CommonDBTM
{

   protected $requesters;

   protected $observers;

   protected $assigned;

   protected $assignedSuppliers;

   protected $requesterGroups;

   protected $observerGroups;

   protected $assignedGroups;

   abstract public function export();

   abstract public function save(PluginFormcreatorForm_Answer $formanswer);

   abstract protected function getItem_User();

   abstract protected function getItem_Group();

   abstract protected function getItem_Supplier();

   abstract protected function getItem_Item();

   static function getEnumDestinationEntity() {
      return array(
            'current'   => __("Current active entity", 'formcreator'),
            'requester' => __("Default requester user's entity", 'formcreator'),
            'requester_dynamic_first' => __("First dynamic requester user's entity (alphabetical)", 'formcreator'),
            'requester_dynamic_last' => __("Last dynamic requester user's entity (alphabetical)", 'formcreator'),
            'form'      => __('The form entity', 'formcreator'),
            'validator' => __('Default entity of the validator', 'formcreator'),
            'specific'  => __('Specific entity', 'formcreator'),
            'user'      => __('Default entity of a user type question answer', 'formcreator'),
            'entity'    => __('From a GLPI object > Entity type question answer', 'formcreator'),
      );
   }

   static function getEnumTagType() {
      return array(
            'none'                   => __("None"),
            'questions'              => __('Tags from questions', 'formcreator'),
            'specifics'              => __('Specific tags', 'formcreator'),
            'questions_and_specific' => __('Tags from questions and specific tags', 'formcreator'),
            'questions_or_specific'  => __('Tags from questions or specific tags', 'formcreator')
      );
   }

   static function getEnumDueDateRule() {
      return array(
            'answer' => __('equals to the answer to the question', 'formcreator'),
            'ticket' => __('calculated from the ticket creation date', 'formcreator'),
            'calcul' => __('calculated from the answer to the question', 'formcreator'),
      );
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

   /*
    *
    */
   public function getForm() {
      $targetItemId = $this->getID();
      $targetItemtype = static::getType();

      $target = new PluginFormcreatorTarget();
      if (!$target->getFromDBByQuery("WHERE `itemtype` = '$targetItemtype' AND `items_id` = '$targetItemId'")) {
         return null;
      } else {
         $form = new PluginFormcreatorForm();
         if (!$form->getFromDB($target->getField('plugin_formcreator_forms_id'))) {
            return null;
         }
         return $form;
      }

      return null;
   }

   /**
    * find all actors and prepare data for the ticket being created
    */
   protected function prepareActors(PluginFormcreatorForm $form, PluginFormcreatorForm_Answer $formanswer) {
      $targetTicketId = $this->getID();
      $targetTicketActor = new PluginFormcreatorTargetTicket_Actor();
      $rows = $targetTicketActor->find("`plugin_formcreator_targettickets_id` = '$targetTicketId'");

      foreach ($rows as $actor) {
         // If actor type is validator and if the form doesn't have a validator, continue to other actors
         if ($actor['actor_type'] == 'validator' && !$form->fields['validation_required']) {
            continue;
         }

         switch ($actor['actor_type']) {
            case 'creator' :
               $userIds = array($formanswer->fields['requester_id']);
               $notify  = $actor['use_notification'];
               break;
            case 'validator' :
               $userIds = array($_SESSION['glpiID']);
               $notify  = $actor['use_notification'];
               break;
            case 'person' :
            case 'group' :
            case 'supplier' :
               $userIds = array($actor['actor_value']);
               $notify  = $actor['use_notification'];
               break;
            case 'question_person' :
            case 'question_group' :
            case 'question_supplier' :
               $answer  = new PluginFormcreatorAnswer();
               $actorValue = $actor['actor_value'];
               $formanswerId = $formanswer->getID();
               $answer->getFromDBByQuery("WHERE `plugin_formcreator_question_id` = '$actorValue'
                     AND `plugin_formcreator_forms_answers_id` = '$formanswerId'");

               if ($answer->isNewItem()) {
                  continue;
               } else {
                  $userIds = array($answer->getField('answer'));
               }
               $notify  = $actor['use_notification'];
               break;
            case 'question_actors':
               $answer  = new PluginFormcreatorAnswer();
               $actorValue = $actor['actor_value'];
               $formanswerId = $formanswer->getID();
               $answer->getFromDBByQuery("WHERE `plugin_formcreator_question_id` = '$actorValue'
                     AND `plugin_formcreator_forms_answers_id` = '$formanswerId'");

               if ($answer->isNewItem()) {
                  continue;
               } else {
                  $userIds = array_filter(explode(',', trim($answer->getField('answer'))));
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

   protected function addActor($role, $user, $notify) {
      if (filter_var($user, FILTER_VALIDATE_EMAIL) !== false) {
         $userId = 0;
         $alternativeEmail = $user;
      } else {
         $userId = intval($user);
         $alternativeEmail = '';
         if ($userId == '0') {
            // there is no actor
            return;
         }
      }

      switch ($role) {
         case 'requester':
            $this->requesters['_users_id_requester'][]                                    = $userId;
            $this->requesters['_users_id_requester_notif']['use_notification'][]          = ($notify == true);
            $this->requesters['_users_id_requester_notif']['alternative_email'][]         = $alternativeEmail;
            break;
         case 'observer' :
            $this->observers['_users_id_observer'][]                                      = $userId;
            $this->observers['_users_id_observer_notif']['use_notification'][]            = ($notify == true);
            $this->observers['_users_id_observer_notif']['alternative_email'][]           = $alternativeEmail;
            break;
         case 'assigned' :
            $this->assigned['_users_id_assign'][]                                         = $userId;
            $this->assigned['_users_id_assign_notif']['use_notification'][]               = ($notify == true);
            $this->assigned['_users_id_assign_notif']['alternative_email'][]              = $alternativeEmail;
            break;
         case 'supplier' :
            $this->assignedSuppliers['_suppliers_id_assign'][]                            = $userId;
            $this->assignedSuppliers['_suppliers_id_assign_notif']['use_notification'][]  = ($notify == true);
            $this->assignedSuppliers['_suppliers_id_assign_notif']['alternative_email'][] = $alternativeEmail;
            break;
      }
   }

   protected function addGroupActor($role, $group) {
      switch ($role) {
         case 'requester':
            $this->requesterGroupss['_groups_id_requester'][]  = $group;
            break;
         case 'observer' :
            $this->observerGroups['_groups_id_observer'][]     = $group;
            break;
         case 'assigned' :
            $this->assignedGroups['_groups_id_assign'][]       = $group;
            break;
      }
   }

   /**
    * Attach documents of the answer to the target
    */
   protected function attachDocument($formAnswerId, $itemtype, $targetID) {
      $docItem = new Document_Item();
      $found = $docItem->find("itemtype = 'PluginFormcreatorForm_Answer'
                               AND items_id = '$formAnswerId'");
      if (count($found) > 0) {
         foreach ($found as $document) {
            $docItem->add(array(
                  'documents_id' => $document['documents_id'],
                  'itemtype'     => $itemtype,
                  'items_id'     => $targetID
            ));
         }
      }
   }
}