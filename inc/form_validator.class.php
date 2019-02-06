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

/**
 * @since 0.90-1.5
 */
class PluginFormcreatorForm_Validator extends CommonDBRelation implements
PluginFormcreatorExportableInterface
{

      // From CommonDBRelation
   static public $itemtype_1          = 'PluginFormcreatorForm';
   static public $items_id_1          = 'plugin_formcreator_forms_id';

   static public $itemtype_2          = 'itemtype';
   static public $items_id_2          = 'items_id';
   static public $checkItem_2_Rights  = self::HAVE_VIEW_RIGHT_ON_ITEM;

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
      $formFk = PluginFormcreatotrForm::getForeigkKeyField();
      $input[$formFk] = $forms_id;

      $item = new self();
      if (isset($input['uuid'])) {
         $validators_id = plugin_formcreator_getFromDBByField(
            $item,
            'uuid',
            $input['uuid']
         );
      }
      // Find the validator
      if (!in_array($validator['itemtype'], [User::class, Group::class])) {
         return false;
      }
      $linkedItemtype = $validator['itemtype'];
      $linkedItem = new $linkedItemtype();
      $crit = [
         'name' => $validator['_item'],
      ];
      if (!$linkedItem->getFromDBByCrit($crit)) {
         // validator not found. Let's ignore it
         return false;
      }
      $validator['items_id'] = $linkedItem->getID();

      // Add or update the form validator
      if (!$item->isNewItem()) {
         $input['id'] = $validators_id;
         $item->update($input);
      } else {
         $validators_id = $item->add($input);
      }
      if ($validators_id === false) {
         throw new ImportFailureException();
      }

      // add the item to the linker
      $originalId = $input['id'];
      if (isset($input['uuid'])) {
         $originalId = $input['uuid'];
      }
      $linker->addObject($originalId, $item);

      return $validators_id;
   }

   /**
    * Export in an array all the data of the current instanciated validator
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if (!$this->getID()) {
         return false;
      }

      $validator = $this->fields;

      // remove key and fk
      unset($validator['plugin_formcreator_forms_id']);

      if (is_subclass_of($validator['itemtype'], 'CommonDBTM')) {
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
}
