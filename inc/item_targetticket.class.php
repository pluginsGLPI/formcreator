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

class PluginFormcreatorItem_TargetTicket extends CommonDBRelation
implements PluginFormcreatorExportableInterface
{
   use PluginFormcreatorExportable;

   static public $itemtype_1           = 'itemtype';
   static public $items_id_1           = 'items_id';
   static public $itemtype_2           = PluginFormcreatorTargetTicket::class;
   static public $items_id_2           = 'plugin_formcreator_targettickets_id';

   static public $logs_for_item_1      = false;

   public static function getTypeName($nb = 0) {
      return _n('Composite ticket relation', 'Composite ticket relations', $nb, 'formcreator');
   }

   public function export($remove_uuid = false) {
      if ($this->isNewItem()) {
         return false;
      }

      $export = $this->fields;

      // remove non needed keys
      $targetTicketFk = PluginFormcreatorTargetTicket::getForeignKeyField();
      unset($export[$targetTicketFk]);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }
      unset($export[$idToRemove]);

      // This is a CommonDBRelation object
      // We must output the object of the 1st relation
      // @see PluginFormcreatorExportable::exportChildrenObjects()
      $linkedItemtype = $export['itemtype'];
      $linkedItem = new $linkedItemtype();
      $linkedItemId = $export['items_id'];
      $linkedItem->getFromDBByCrit([
         'id' => $linkedItemId
      ]);
      if ($linkedItem->isNewItem()) {
         // TODO: error linked item not found
      }
      $identifierColumn = 'id';
      if (!$remove_uuid && strpos($export['itemtype'], 'PluginFormcreator') === 0) {
         $identifierColumn = 'uuid';
      }
      $export['items_id'] = $linkedItem->fields[$identifierColumn];

      return $export;
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      $targetTicketFk = PluginFormcreatorTargetTicket::getForeignKeyField();
      $input[$targetTicketFk] = $containerId;
      $input['_skip_checks'] = true;

      $item = new self;
      // Find an existing target to update, only if an UUID is available
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

      // set ID for linked objects
      $linkedItemtype = $input['itemtype'];
      $linkedItemId = $input['items_id'];
      $linkedItem = $linker->findObject($linkedItemtype, $idKey, $linkedItemId);
      if ($linkedItem->isNewItem()) {
         if (strpos($linkedItemtype, 'PluginFormcreator') === 0) {
            // the linnked object belongs to the plugin, maybe the item will be imported later
            $linker->postpone($input[$idKey], $item->getType(), $input, $containerId);
            return false;
         }
         // linked item is not an object of Formcreator, it will not be imported
         throw new ImportFailureException('Failed to find a linked object to a target ticket');
      }

      // Add or update
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

      // add the target to the linker
      $linker->addObject($originalId, $item);

      return $itemId;
   }

   public function prepareInputForAdd($input) {
      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   // protected function convertIds(&$parameter) {
   //    if ($parameter['itemtype'] == PluginFormcreatorTargetTicket::getType()) {
   //       $targetTicket = new PluginFormcreatorTargetTicket();
   //       $targetTicket->getFromDB($parameter['items_id']);
   //       $parameter['items_id'] = $targetTicket->fields['uuid'];
   //    }
   // }

   // protected function convertUuids(&$parameter) {
   //    if ($questionId2
   //        = plugin_formcreator_getFromDBByField(new PluginFormcreatorQuestion(),
   //                                                'uuid',
   //                                                $parameter['plugin_formcreator_questions_id_2'])) {
   //       $parameter['plugin_formcreator_questions_id_2'] = $questionId2;
   //       return true;
   //    }
   //    return false;
   // }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
   {
      $keepCriteria = [
         self::$items_id_2 => $container->getID(),
      ];
      if (count($exclude) > 0) {
         $keepCriteria[] = ['NOT' => ['id' => $exclude]];
      }
      return $this->deleteByCriteria($keepCriteria);
   }
}
