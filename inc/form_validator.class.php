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

   const VALIDATION_NONE  = 0;
   const VALIDATION_USER  = 1;
   const VALIDATION_GROUP = 2;

   public function prepareInputForAdd($input) {
      // generate a unique id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $forms_id = 0) {
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $input[$formFk] = $forms_id;

      $item = new self();
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
    * Get validators of type $itemtype associated to a form
    *
    * @param PluginFormcreatorForm $form
    * @param string $itemtype
    * @return User[]|Group[] array of User or Group objects
    */
   public function getValidatorsForForm(PluginFormcreatorForm $form, $itemtype) {
      global $DB;

      if (!in_array($itemtype, [User::class, Group::class])) {
         return [];
      }

      $formValidatorTable = PluginFormcreatorForm_Validator::getTable();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $itemTable = $itemtype::getTable();

      $rows = $DB->request([
         'SELECT' => [
            $itemTable => ['*']
         ],
         'FROM' => $itemTable,
         'LEFT JOIN' => [
            $formValidatorTable => [
               'FKEY' => [
                  $formValidatorTable => 'items_id',
                  $itemTable => 'id'
               ]
            ],
         ],
         'WHERE' => [
            "$formValidatorTable.itemtype" => $itemtype,
            "$formValidatorTable.$formFk" => $form->getID(),
         ],
      ]);
      $result = [];
      foreach ($rows as $row) {
         $item = new $itemtype();
         if ($item->getFromDB($row['id'])) {
            $result[$row['id']] = $item;
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
}
