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

use Glpi\Features\Clonable;
use GlpiPlugin\Formcreator\Exception\ImportFailureException;
use GlpiPlugin\Formcreator\Exception\ExportFailureException;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorTarget_Actor extends CommonDBChild implements PluginFormcreatorExportableInterface
{
   use Clonable;
   use PluginFormcreatorExportableTrait;

   static public $itemtype = 'itemtype';
   static public $items_id = 'items_id';

   const ACTOR_TYPE_AUTHOR = 1;
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
   CONST ACTOR_TYPE_AUTHORS_SUPERVISOR = 12;

   const ACTOR_ROLE_REQUESTER = 1;
   const ACTOR_ROLE_OBSERVER = 2;
   const ACTOR_ROLE_ASSIGNED = 3;
   const ACTOR_ROLE_SUPPLIER = 4;

   static function getEnumActorType() {
      global $PLUGIN_HOOKS;

      $types = [
         self::ACTOR_TYPE_AUTHOR                 => __('Form author', 'formcreator'),
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
         self::ACTOR_TYPE_AUTHORS_SUPERVISOR     => __('Form author\'s supervisor', 'formcreator'),
      ];

      // Add extra plugin types
      foreach (($PLUGIN_HOOKS['formcreator_actors_type'] ?? []) as $plugin => $classes) {
         foreach ($classes as $plugin_target) {
            if (!is_a($plugin_target, PluginFormcreatorPluginTargetInterface::class, true)) {
               continue;
            }

            $types[$plugin_target::getId()] = $plugin_target::getLabel();
         }
      }

      asort($types);
      return $types;
   }

   static function getEnumRole() {
      return [
         self::ACTOR_ROLE_REQUESTER => _n('Requester', 'Requesters', 1),
         self::ACTOR_ROLE_OBSERVER  => __('Observer'),
         self::ACTOR_ROLE_ASSIGNED  => __('Assigned to'),
         // TODO : support ACTOR_ROLE_SUPPLIER
      ];
   }

   public static function getTypeName($nb = 0) {
      return _n('Target actor', 'Target actors', $nb, 'formcreator');
   }

   public function prepareInputForAdd($input) {
      $requiredKeys = ['itemtype', 'items_id', 'actor_role', 'actor_type'];
      if (count(array_intersect(array_keys($input), $requiredKeys)) < count($requiredKeys)) {
         Session::addMessageAfterRedirect(__('Bad request while adding an actor.', 'formcreator'), false, ERROR);
         return false;
      }

      $input['actor_value'] = $input['actor_value_' . $input['actor_type']] ?? 0;

      if (isset($input['use_notification'])) {
         $input['use_notification'] = ($input['use_notification'] == 0) ? 0 : 1;
      } else {
         $input['use_notification'] = 0;
      }

      switch ($input['actor_type']) {
         case self::ACTOR_TYPE_PERSON:
         case self::ACTOR_TYPE_GROUP:
            if (!isset($input['actor_value']) || $input['actor_value'] == 0) {
               Session::addMessageAfterRedirect(__('Bad request while adding an actor.', 'formcreator'), false, ERROR);
               return false;
            }
            break;

         case self::ACTOR_TYPE_QUESTION_PERSON:
         case self::ACTOR_TYPE_QUESTION_GROUP:
         case self::ACTOR_TYPE_QUESTION_ACTORS:
            if (!isset($input['actor_value']) || $input['actor_value'] == 0) {
               Session::addMessageAfterRedirect(__('Bad request while adding an actor.', 'formcreator'), false, ERROR);
               return false;
            }
            break;

      }

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

      // Convert UUIDs or names into IDs
      switch ($input['actor_type']) {
         case self::ACTOR_TYPE_QUESTION_PERSON :
         case self::ACTOR_TYPE_QUESTION_GROUP :
         case self::ACTOR_TYPE_QUESTION_SUPPLIER :
         case self::ACTOR_TYPE_QUESTION_ACTORS :
         case self::ACTOR_TYPE_GROUP_FROM_OBJECT :
         case self::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT :
            /** @var PluginFormcreatorQuestion $question */
            $question = $linker->getObject($input['actor_value'], PluginFormcreatorQuestion::class);
            if ($question === false) {
               $linker->postpone($input[$idKey], $item->getType(), $input, $containerId);
               return false;
            }
            $input['actor_value'] = $question->getID();
            break;

         case self::ACTOR_TYPE_AUTHORS_SUPERVISOR:
            $input['actor_value'] = 0;
            break;

         case self::ACTOR_TYPE_PERSON:
            $user = new User;
            $field = $idKey == 'id' ? 'id' : 'name';
            $users_id = plugin_formcreator_getFromDBByField($user, $field, $input['actor_value']);
            if ($users_id === false) {
               throw new ImportFailureException(sprintf(__('Failed to find a user: %1$s'), $input['actor_value']));
            }
            $input['actor_value'] = $users_id;
            break;

         case self::ACTOR_TYPE_GROUP:
            $group = new Group;
            $field = $idKey == 'id' ? 'id' : 'completename';
            $groups_id = plugin_formcreator_getFromDBByField($group, $field, $input['actor_value']);
            if ($groups_id === false) {
               throw new ImportFailureException(sprintf(__('Failed to find a group: %1$s'), $input['actor_value']));
            }
            $input['actor_value'] = $groups_id;
            break;

         case self::ACTOR_TYPE_SUPPLIER:
            $supplier = new Supplier;
            $field = $idKey == 'id' ? 'id' : 'name';
            $suppliers_id = plugin_formcreator_getFromDBByField($supplier, $field, $input['actor_value']);
            if ($suppliers_id === false) {
               throw new ImportFailureException(sprintf(__('Failed to find a supplier: %1$s'), $input['actor_value']));
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
         $input['actor_value_' . $input['actor_type']] = $input['actor_value'];
         $itemId = $item->add($input);
      }
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('Failed to add or update the %1$s %2$s', 'formceator'), $typeName, $input['name']));
      }

      // add the question to the linker
      $linker->addObject($originalId, $item);

      return $itemId;
   }

   public static function countItemsToImport($input) : int {
      return 1;
   }

   /**
    * Export in an array all the data of the current instanciated actor
    * @param bool $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export(bool $remove_uuid = false) : array {
      if ($this->isNewItem()) {
         throw new ExportFailureException(sprintf(__('Cannot export an empty object: %s', 'formcreator'), $this->getTypeName()));
      }

      $target_actor = $this->fields;

      // remove key and fk
      unset($target_actor[static::$items_id]);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }

      // Convert IDs into UUIDs or names
      switch ($target_actor['actor_type']) {
         case self::ACTOR_TYPE_QUESTION_PERSON:
         case self::ACTOR_TYPE_QUESTION_GROUP:
         case self::ACTOR_TYPE_QUESTION_SUPPLIER:
         case self::ACTOR_TYPE_QUESTION_ACTORS:
         case self::ACTOR_TYPE_GROUP_FROM_OBJECT:
         case self::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT :
            $question = new PluginFormcreatorQuestion;
            $field = $idToRemove == 'uuid' ? 'id' : 'uuid';
            $question->getFromDBByCrit([
               $field => $target_actor['actor_value']
            ]);
            if (!$question->isNewItem()) {
               $target_actor['actor_value'] = $idToRemove == 'uuid' ? $question->getID() : $question->fields['uuid'];
            }
            break;
         case self::ACTOR_TYPE_AUTHORS_SUPERVISOR:
            $target_actor['actor_value'] = 0;
            break;
         case self::ACTOR_TYPE_PERSON:
            $user = new User;
            $field = $idToRemove == 'uuid' ? 'id' : 'name';
            if ($user->getFromDB($target_actor['actor_value'])) {
               $target_actor['actor_value'] = $user->fields[$field];
            }
            break;
         case self::ACTOR_TYPE_GROUP:
            $group = new Group;
            $field = $idToRemove == 'uuid' ? 'id' : 'completename';
            if ($group->getFromDB($target_actor['actor_value'])) {
               $target_actor['actor_value'] = $group->fields[$field];
            }
            break;
         case self::ACTOR_TYPE_SUPPLIER:
            $supplier = new Supplier;
            $field = $idToRemove == 'uuid' ? 'id' : 'name';
            if ($supplier->getFromDB($target_actor['actor_value'])) {
               $target_actor['actor_value'] = $supplier->fields[$field];
            }
            break;
      }

      unset($target_actor[$idToRemove]);

      return $target_actor;
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude) : bool {
      $keepCriteria = [
         static::$itemtype => $container->getType(),
         static::$items_id => $container->getID(),
      ];
      if (count($exclude) > 0) {
         $keepCriteria[] = ['NOT' => ['id' => $exclude]];
      }
      return $this->deleteByCriteria($keepCriteria);
   }

   public function prepareInputForClone($input) {
      $input['actor_value_' . $input['actor_type']] = $input['actor_value'];
      unset($input['uuid']);
      return $input;
   }
}
