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

use GlpiPlugin\Formcreator\Exception\ImportFailureException;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

abstract class PluginFormcreatorTarget_Actor extends CommonDBChild implements PluginFormcreatorExportableInterface
{
   use PluginFormcreatorExportable;

   const ACTOR_TYPE_CREATOR = 1;
   const ACTOR_TYPE_VALIDATOR = 2;
   const ACTOR_TYPE_PERSON = 3;
   const ACTOR_TYPE_QUESTION_PERSON = 4;
   const ACTOR_TYPE_GROUP = 5;
   const ACTOR_TYPE_QUESTION_GROUP = 6;
   const ACTOR_TYPE_SUPPLIER = 7;
   const ACTOR_TYPE_QUESTION_SUPPLIER = 8;
   const ACTOR_TYPE_QUESTION_ACTORS = 9;
   const ACTOR_TYPE_GROUP_FROM_OBJECT = 10;
   const ACTOR_TYPE_TECH_GROUP_FROM_OBJECT = 11;

   const ACTOR_ROLE_REQUESTER = 1;
   const ACTOR_ROLE_OBSERVER = 2;
   const ACTOR_ROLE_ASSIGNED = 3;
   const ACTOR_ROLE_SUPPLIER = 4;

   static function getEnumActorType() {
      return [
         self::ACTOR_TYPE_CREATOR                => __('Form requester', 'formcreator'),
         self::ACTOR_TYPE_VALIDATOR              => __('Form validator', 'formcreator'),
         self::ACTOR_TYPE_PERSON                 => __('Specific person', 'formcreator'),
         self::ACTOR_TYPE_QUESTION_PERSON        => __('Person from the question', 'formcreator'),
         self::ACTOR_TYPE_GROUP                  => __('Specific group', 'formcreator'),
         self::ACTOR_TYPE_QUESTION_GROUP         => __('Group from the question', 'formcreator'),
         self::ACTOR_TYPE_GROUP_FROM_OBJECT      => __('Group from an object', 'formcreator'),
         self::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT => __('Tech group from an object', 'formcreator'),
         self::ACTOR_TYPE_SUPPLIER               => __('Specific supplier', 'formcreator'),
         self::ACTOR_TYPE_QUESTION_SUPPLIER      => __('Supplier from the question', 'formcreator'),
         self::ACTOR_TYPE_QUESTION_ACTORS        => __('Actors from the question', 'formcreator'),
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

   public static function getTypeName($nb = 0) {
      return _n('Target actor', 'Target actors', $nb, 'formcreator');
   }

   public function prepareInputForAdd($input) {

      // generate a unique id
      if (!isset($input['uuid']) || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      $input[static::$items_id] = $containerId;

      $item = new static();
      // Find an existing condition to update, only if an UUID is available
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

      // set ID for linked objects
      switch ($input['actor_type']) {
         case self::ACTOR_TYPE_QUESTION_PERSON :
         case self::ACTOR_TYPE_QUESTION_GROUP :
         case self::ACTOR_TYPE_QUESTION_SUPPLIER :
         case self::ACTOR_TYPE_GROUP_FROM_OBJECT :
         case self::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT :
            $question = $linker->getObject($input['actor_value'], PluginFormcreatorQuestion::class);
            if ($question === false) {
               $linker->postpone($input[$idKey], $item->getType(), $input, $containerId);
               return false;
            }
            $input['actor_value'] = $question->getID();
            break;

         case self::ACTOR_TYPE_PERSON:
            $user = new User;
            $users_id = plugin_formcreator_getFromDBByField($user, 'name', $input['actor_value']);
            if ($users_id === false) {
               throw new ImportFailureException(sprintf(__('failed to find a user: ID %1$d', 'formcreator'), $users_id));
            }
            $input['actor_value'] = $users_id;
            break;

         case self::ACTOR_TYPE_GROUP:
            $group = new Group;
            $groups_id = plugin_formcreator_getFromDBByField($group, 'completename', $input['actor_value']);
            if ($groups_id === false) {
               throw new ImportFailureException(sprintf(__('failed to find a group: ID %1$d', 'formcreator'), $groups_id));
            }
            $input['actor_value'] = $groups_id;
            break;

         case self::ACTOR_TYPE_SUPPLIER:
            $supplier = new Supplier;
            $suppliers_id = plugin_formcreator_getFromDBByField($supplier, 'name', $input['actor_value']);
            if ($suppliers_id === false) {
               throw new ImportFailureException(sprintf(__('failed to find a supplier: ID %1$d', 'formcreator'), $suppliers_id));
            }
            $input['actor_value'] = $suppliers_id;
            break;
      }

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
         throw new ImportFailureException(sprintf(__('failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the question to the linker
      $linker->addObject($originalId, $item);

      return $itemId;
   }

   /**
    * Export in an array all the data of the current instanciated actor
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if ($this->isNewItem()) {
         return false;
      }

      $target_actor = $this->fields;

      // remove key and fk
      unset($target_actor[static::$items_id]);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      } else {
         // Convert IDs into UUIDs
         switch ($target_actor['actor_type']) {
            case self::ACTOR_TYPE_QUESTION_PERSON:
            case self::ACTOR_TYPE_QUESTION_GROUP:
            case self::ACTOR_TYPE_SUPPLIER:
            case self::ACTOR_TYPE_QUESTION_ACTORS:
            case self::ACTOR_TYPE_GROUP_FROM_OBJECT:
            case self::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT :
               $question = new PluginFormcreatorQuestion;
               if ($question->getFromDB($target_actor['actor_value'])) {
                  $target_actor['actor_value'] = $question->fields['uuid'];
               }
               break;
            case self::ACTOR_TYPE_PERSON:
               $user = new User;
               if ($user->getFromDB($target_actor['actor_value'])) {
                  $target_actor['actor_value'] = $user->fields['name'];
               }
               break;
            case self::ACTOR_TYPE_GROUP:
               $group = new Group;
               if ($group->getFromDB($target_actor['actor_value'])) {
                  $target_actor['actor_value'] = $group->fields['completename'];
               }
               break;
            case self::ACTOR_TYPE_SUPPLIER:
               $supplier = new Supplier;
               if ($supplier->getFromDB($target_actor['actor_value'])) {
                  $target_actor['actor_value'] = $supplier->fields['name'];
               }
               break;
         }
      }
      unset($target_actor[$idToRemove]);

      return $target_actor;
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
   {
      $keepCriteria = [
         static::$items_id => $container->getID(),
      ];
      if (count($exclude) > 0) {
         $keepCriteria[] = ['NOT' => ['id' => $exclude]];
      }
      return $this->deleteByCriteria($keepCriteria);
   }
}
