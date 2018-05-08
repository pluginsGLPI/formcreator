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

abstract class PluginFormcreatorTarget_Actor extends CommonDBTM
{
   abstract protected function getTargetItem();

   static function getEnumActorType() {
      return [
         'creator'            => __("Form requester", 'formcreator'),
         'validator'          => __("Form validator", 'formcreator'),
         'person'             => __("Specific person", 'formcreator'),
         'question_person'    => __("Person from the question", 'formcreator'),
         'group'              => __('Specific group', 'formcreator'),
         'question_group'     => __('Group from the question', 'formcreator'),
         'supplier'           => __('Specific supplier', 'formcreator'),
         'question_supplier'  => __('Supplier from the question', 'formcreator'),
         'question_actors'    => __('Actors from the question', 'formcreator'),
      ];
   }

   static function getEnumRole() {
      return [
         'requester'          => __("Requester"),
         'observer'           => __("Observer"),
         'assigned'           => __("Assigned to"),
      ];
   }


   public function prepareInputForAdd($input) {

      // generate a unique id
      if (!isset($input['uuid']) || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   /**
    * Import a form's target ticket's actor into the db
    * @see PluginFormcreatorTargetTicket::import
    * @see PluginFormcreatorTargetChange::import
    *
    * @param  integer $targets_id  id of the parent targetticket
    * @param  array   $actor the actor data (match the actor table)
    * @return integer the actor's id
    */
   public static function import($targets_id = 0, $actor = []) {
      $item = new static;

      $foreignKeyField = $item->getTargetItem()->getForeignKeyField();
      $actor[$foreignKeyField] = $targets_id;

      // retrieve FK
      if (isset($actor['_question'])) {
         $question = new PluginFormcreatorQuestion;

         if ($questions_id = plugin_formcreator_getFromDBByField($question, 'uuid', $actor['_question'])) {
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

      $foreignKeyField = $this->getTargetItem()->getForeignKeyField();
      unset($target_actor['id'],
            $target_actor[$foreignKeyField]);

      // export FK
      switch ($target_actor['actor_type']) {
         case 'question_person':
         case 'question_group':
         case 'question_supplier':
         case 'question_actors':
            $question = new PluginFormcreatorQuestion;
            if ($question->getFromDB($target_actor['actor_value'])) {
               $target_actor['_question'] = $question->fields['uuid'];
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
