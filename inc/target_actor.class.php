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

abstract class PluginFormcreatorTarget_Actor extends CommonDBTM implements PluginFormcreatorExportableInterface
{
   abstract protected function getTargetItem();

   const ACTOR_TYPE_CREATOR = 1;
   const ACTOR_TYPE_VALIDATOR = 2;
   const ACTOR_TYPE_PERSON = 3;
   const ACTOR_TYPE_QUESTION_PERSON = 4;
   const ACTOR_TYPE_GROUP = 5;
   const ACTOR_TYPE_QUESTION_GROUP = 6;
   const ACTOR_TYPE_SUPPLIER = 7;
   const ACTOR_TYPE_QUESTION_SUPPLIER = 8;
   const ACTOR_TYPE_QUESTION_ACTORS = 9;

   const ACTOR_ROLE_REQUESTER = 1;
   const ACTOR_ROLE_OBSERVER = 2;
   const ACTOR_ROLE_ASSIGNED = 3;
   const ACTOR_ROLE_SUPPLIER = 4;

   static function getEnumActorType() {
      return [
         self::ACTOR_TYPE_CREATOR            => __('Form requester', 'formcreator'),
         self::ACTOR_TYPE_VALIDATOR          => __('Form validator', 'formcreator'),
         self::ACTOR_TYPE_PERSON             => __('Specific person', 'formcreator'),
         self::ACTOR_TYPE_QUESTION_PERSON    => __('Person from the question', 'formcreator'),
         self::ACTOR_TYPE_GROUP              => __('Specific group', 'formcreator'),
         self::ACTOR_TYPE_QUESTION_GROUP     => __('Group from the question', 'formcreator'),
         self::ACTOR_TYPE_SUPPLIER           => __('Specific supplier', 'formcreator'),
         self::ACTOR_TYPE_QUESTION_SUPPLIER  => __('Supplier from the question', 'formcreator'),
         self::ACTOR_TYPE_QUESTION_ACTORS    => __('Actors from the question', 'formcreator'),
      ];
   }

   static function getEnumRole() {
      return [
         self::ACTOR_ROLE_REQUESTER => __('Requester'),
         self::ACTOR_ROLE_OBSERVER  => __('Observer'),
         self::ACTOR_ROLE_ASSIGNED  => __('Assigned to'),
         // TODO : support ACTOR_ROLE_SUPPLIER
      ];
   }
   public function prepareInputForAdd($input) {

      // generate a unique id
      if (!isset($input['uuid']) || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      $item = new static;

      $foreignKeyField = $item->getTargetItem()->getForeignKeyField();
      $input[$foreignKeyField] = $containerId;

      // retrieve FK
      if (isset($input['_question'])) {
         $question = new PluginFormcreatorQuestion;

         if ($questions_id = plugin_formcreator_getFromDBByField($question, 'uuid', $actor['_question'])) {
            $input['actor_value'] = $questions_id;
         } else {
            return false;
         }

      } else if (isset($input['_user'])) {
         $user = new User;
         if ($users_id = plugin_formcreator_getFromDBByField($user, 'name', $input['_user'])) {
            $input['actor_value'] = $users_id;
         } else {
            return false;
         }
      } else if (isset($input['_group'])) {
         $group = new Group;
         if ($groups_id = plugin_formcreator_getFromDBByField($group, 'completename', $input['_group'])) {
            $input['actor_value'] = $groups_id;
         } else {
            return false;
         }
      } else if (isset($input['_supplier'])) {
         $supplier = new Supplier;
         if ($suppliers_id = plugin_formcreator_getFromDBByField($supplier, 'name', $input['_supplier'])) {
            $input['actor_value'] = $suppliers_id;
         } else {
            return false;
         }
      }

      if ($actors_id = plugin_formcreator_getFromDBByField($item, 'uuid', $input['uuid'])) {
         // add id key
         $input['id'] = $actors_id;

         // update actor
         $item->update($input);
      } else {
         //create actor
         $actors_id = $item->add($input);
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
         case self::ACTOR_TYPE_QUESTION_PERSON:
         case self::ACTOR_TYPE_QUESTION_GROUP:
         case self::ACTOR_TYPE_SUPPLIER:
         case self::ACTOR_TYPE_QUESTION_ACTORS:
            $question = new PluginFormcreatorQuestion;
            if ($question->getFromDB($target_actor['actor_value'])) {
               $target_actor['_question'] = $question->fields['uuid'];
               unset($target_actor['actor_value']);
            }
            break;
         case self::ACTOR_TYPE_PERSON:
            $user = new User;
            if ($user->getFromDB($target_actor['actor_value'])) {
               $target_actor['_user'] = $user->fields['name'];
               unset($target_actor['actor_value']);
            }
            break;
         case self::ACTOR_TYPE_GROUP:
            $group = new Group;
            if ($group->getFromDB($target_actor['actor_value'])) {
               $target_actor['_group'] = $group->fields['completename'];
               unset($target_actor['actor_value']);
            }
            break;
         case self::ACTOR_TYPE_SUPPLIER:
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
