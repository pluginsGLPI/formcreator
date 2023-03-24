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
 * @link     https://github.com/pluginsGLPI/formcreator/
 * @link     https://pluginsglpi.github.io/formcreator/
 * @link     http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
use GlpiPlugin\Formcreator\Exception\ImportFailureException;
use GlpiPlugin\Formcreator\Exception\ExportFailureException;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

abstract class PluginFormcreatorRestrictedFormCriteria
   extends CommonDBRelation
   implements PluginFormcreatorExportableInterface
{
   use PluginFormcreatorExportableTrait;

   public static $itemtype_1 = PluginFormcreatorForm::class;
   public static $items_id_1 = 'plugin_formcreator_forms_id';


   public static function getTypeName($nb = 0) {
      // Using 'Access type' name is enough, it won't be shown anywhere
      return PluginFormcreatorFormAccessType::getTypeName($nb);
   }

   /**
    * Get the criteria used to filter a list of form and ensure they match the
    * visibility restriction for this class
    *
    * @return QuerySubQuery
    */
   abstract public static function getListCriteriaSubQuery(): QuerySubQuery;

   /**
    * Get all forms that have a defined restriction
    *
    * @return QuerySubQuery
    */
   public static function getFormWithDefinedRestrictionSubQuery(): QuerySubQuery {
      return new QuerySubQuery([
         'SELECT' => static::$items_id_1,
         'FROM'   => static::getTable(),
      ]);
   }

   /**
    * Check if the current user is in the "whitelist" regarding this
    * specific visibility restriction criteria for the given form
    *
    * @param PluginFormcreatorForm $form The given form
    *
    * @return bool True if there is a match, the user is whitelisted
    */
   public static function userMatchRestrictionCriteria(
      PluginFormcreatorForm $form
   ): bool {
      global $DB;

      // Intersect the given form with the form for which the user is whitelisted
      $data = $DB->request([
         'COUNT' => 'count',
         'FROM'  => PluginFormcreatorForm::getTable(),
         'WHERE' => [
            ['id' => static::getListCriteriaSubQuery()],
            ['id' => $form->fields['id']],
         ]
      ]);

      $row = $data->current();
      return $row['count'] > 0;
   }

   public static function countItemsToImport($input): int {
      return 1;
   }

   /**
   * Prepare input data for adding the form
   *
   * @param array $input data used to add the item
   *
   * @return array the modified $input array
   */
   public function prepareInputForAdd($input) {
      // generate a unique id
      if (!isset($input['uuid']) || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }
      return $input;
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude): bool {
      $keepCriteria = [
         self::$items_id_1 => $container->getID(),
      ];
      if (count($exclude) > 0) {
         $keepCriteria[] = ['NOT' => ['id' => $exclude]];
      }
      return $this->deleteByCriteria($keepCriteria);
   }

   /**
    * Import a form's visibility criteria into the db
    * @see PluginFormcreatorForm::importJson
    *
    * @param  PluginFormcreatorLinker  $linker  id of the parent form
    * @param  array                    $input
    * @param  int                      $container_id
    * @return integer|false the item ID or false on error
    */
   public static function import(
      PluginFormcreatorLinker $linker,
      $input = [],
      $container_id = 0
   ) {
      $itemtype2 = static::$itemtype_2;
      $item2_input_key = "_" . strtolower($itemtype2);

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(
            sprintf(
               'UUID or ID is mandatory for %1$s',
               static::getTypeName(1)
            )
         );
      }

      $form_fk = PluginFormcreatorForm::getForeignKeyField();
      $input[$form_fk] = $container_id;
      $item = new static();
      // Find an existing form to update, only if an UUID is available
      $item_id = false;
      /** @var string $id_key key to use as ID (id or uuid) */
      $id_key = 'id';
      if (isset($input['uuid'])) {
         // Try to find an existing item to update
         $id_key = 'uuid';
         $item_id = plugin_formcreator_getFromDBByField(
            $item,
            'uuid',
            $input['uuid']
         );
      }

      // Set the linked item
      $item2 = new $itemtype2();
      $form_fk  = PluginFormcreatorForm::getForeignKeyField();
      if (!plugin_formcreator_getFromDBByField($item2, 'name', $input[$item2_input_key])) {
         // Item not found, stop import
         throw new ImportFailureException(
            sprintf(
               __('Failed to find %1$s %2$s', 'formceator'),
               $itemtype2::getTypeName(),
               $input[$item2_input_key]
            )
         );
      }
      $input[static::$items_id_2] = $item2->getID();

      // Add or update the linked item
      $original_id = $input[$id_key];
      if ($item_id !== false) {
         $input['id'] = $item_id;
         $item->update($input);
      } else {
         unset($input['id']);
         $item_id = $item->add($input);
      }
      if ($item_id === false) {
         $type_name = strtolower(self::getTypeName());
         throw new ImportFailureException(
            sprintf(
               __('Failed to add or update the %1$s %2$s', 'formceator'),
               $type_name,
               $input[$item2_input_key]
            )
         );
      }

      // Add the item to the linker
      $linker->addObject($original_id, $item);

      return $item_id;
   }

   /**
    * Export in an array all the data of the current instanciated item2
    * @param bool $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export(bool $remove_uuid = false) : array {
      $itemtype2 = static::$itemtype_2;
      $item2_input_key = "_" . strtolower($itemtype2);

      if ($this->isNewItem()) {
         throw new ExportFailureException(
            sprintf(
               __('Cannot export an empty object: %s', 'formcreator'),
               $this->getTypeName()
            )
         );
      }

      $export = $this->fields;

      // export fk
      $item = new $itemtype2();
      if ($item->getFromDB($export[static::$items_id_2])) {
         $export[$item2_input_key] = $item->fields['name'];
      }

      // remove fk
      unset(
         $export[static::$items_id_2],
         $export['plugin_formcreator_forms_id']
      );

      // remove ID or UUID
      $id_to_remove = 'id';
      if ($remove_uuid) {
         $id_to_remove = 'uuid';
      }
      unset($export[$id_to_remove]);

      return $export;
   }
}
