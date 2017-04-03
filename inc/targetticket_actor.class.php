<?php
class PluginFormcreatorTargetTicket_Actor extends CommonDBTM
{

   static function getEnumActorType() {
      return array(
            'creator'            => __("Form requester", 'formcreator'),
            'validator'          => __("Form validator", 'formcreator'),
            'person'             => __("Specific person", 'formcreator'),
            'question_person'    => __("Person from the question", 'formcreator'),
            'group'              => __('Specific group', 'formcreator'),
            'question_group'     => __('Group from the question', 'formcreator'),
            'supplier'           => __('Specific supplier', 'formcreator'),
            'question_supplier'  => __('Supplier from the question', 'formcreator'),
            'question_actors'    => __('Actors from the question', 'formcreator'),
      );
   }

   static function getEnumRole() {
      return array(
            'requester'          => __("Requester"),
            'observer'           => __("Observer"),
            'assigned'           => __("Assigned to"),
      );
   }

   public function prepareInputForAdd($input) {

      // generate a uniq id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   /**
    * Import a form's targetticket's actor into the db
    * @see PluginFormcreatorTargetTicket::import
    *
    * @param  integer $targettickets_id  id of the parent targetticket
    * @param  array   $actor the actor data (match the actor table)
    * @return integer the actor's id
    */
   public static function import($targettickets_id = 0, $actor = array()) {
      $item = new self;

      $actor['plugin_formcreator_targettickets_id'] = $targettickets_id;

      // retrieve FK
      if (isset($actor['_question'])) {
         $section = new PluginFormcreatorSection;
         $question = new PluginFormcreatorQuestion;
         $exploded = explode('##$$##', $actor['_question']);

         if (plugin_formcreator_getFromDBByField($section, 'name', $exploded[0])
             && $questions_id = plugin_formcreator_getFromDBByField($question, 'name', $exploded[1])) {
            $actor['actor_value'] = $questions_id;
         } else {
            return false;
         }

      } else if (isset($actor['_user'])) {
         $user = new User;
         if ($users_id = plugin_formcreator_getFromDBByField($user, 'name', $actor['_user'])) {
            $actor['actor_value'] = $users_id;
         } else {
            return false;
         }
      } else if (isset($actor['_group'])) {
         $group = new Group;
         if ($groups_id = plugin_formcreator_getFromDBByField($group, 'completename', $actor['_group'])) {
            $actor['actor_value'] = $groups_id;
         } else {
            return false;
         }
      } else if (isset($actor['_supplier'])) {
         $supplier = new Supplier;
         if ($suppliers_id = plugin_formcreator_getFromDBByField($supplier, 'name', $actor['_supplier'])) {
            $actor['actor_value'] = $suppliers_id;
         } else {
            return false;
         }
      }

      if ($actors_id = plugin_formcreator_getFromDBByField($item, 'uuid', $actor['uuid'])) {
         // add id key
         $actor['id'] = $actors_id;

         // update actor
         $item->update($actor);
      } else {
         //create actor
         $actors_id = $item->add($actor);
      }

      return $actors_id;
   }

   /**
    * Export in an array all the data of the current instanciated actor
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if (!$this->getID()) {
         return false;
      }

      $target_actor = $this->fields;

      unset($target_actor['id'],
            $target_actor['plugin_formcreator_targettickets_id']);

      // export FK
      switch ($target_actor['actor_type']) {
         case 'question_person':
         case 'question_group':
         case 'question_supplier':
            $question = new PluginFormcreatorQuestion;
            $section = new PluginFormcreatorSection;
            if ($question->getFromDB($target_actor['actor_value'])
                && $section->getFromDB($question->fields['plugin_formcreator_sections_id'])) {
               $target_actor['_question'] = $section->fields['name'].
                                            "##$$##".
                                            $question->fields['name'];
               unset($target_actor['actor_value']);
            }
            break;
         case 'person':
            $user = new User;
            if ($user->getFromDB($target_actor['actor_value'])) {
               $target_actor['_user'] = $user->fields['name'];
               unset($target_actor['actor_value']);
            }
            break;
         case 'group':
            $group = new Group;
            if ($group->getFromDB($target_actor['actor_value'])) {
               $target_actor['_group'] = $group->fields['completename'];
               unset($target_actor['actor_value']);
            }
            break;
         case 'supplier':
            $supplier = new Supplier;
            if ($supplier->getFromDB($target_actor['actor_value'])) {
               $target_actor['_supplier'] = $supplier->fields['name'];
               unset($target_actor['actor_value']);
            }
            break;
      }

      if ($remove_uuid) {
         $target_actor['uuid'] = '';
      }

      return $target_actor;
   }

}
