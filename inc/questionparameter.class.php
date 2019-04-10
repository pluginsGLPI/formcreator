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
abstract class PluginFormcreatorQuestionParameter
extends CommonDBChild
implements PluginFormcreatorQuestionParameterInterface, PluginFormcreatorExportableInterface
{

   // From CommonDBRelation
   static public $itemtype       = PluginFormcreatorQuestion::class;
   static public $items_id       = 'plugin_formcreator_questions_id';

   static public $disableAutoEntityForwarding   = true;

   /** @var string $fieldName the name of the field representing the parameter when editing the question */
   protected $fieldName = null;

   protected $label = null;

   protected $field;

   /** @var $domId string the first part of the DOM Id representing the parameter */
   protected $domId = '';

   /**
    * @param PluginFormcreatorFieldInterface $field Field
    * @param array $options
    *                - fieldName: name of the HTML input tag
    *                - label    : label for the parameter
    */
   public function __construct(PluginFormcreatorFieldInterface $field, array $options) {
      $fieldType = $field->getFieldTypeName();
      $fieldName = $options['fieldName'];
      $this->domId = $this->domId . "_{$fieldType}_{$fieldName}";
      $this->field = $field;
      $this->label = $options['label'];
      $this->fieldName = $options['fieldName'];
   }

   public function prepareInputforAdd($input) {
      $input = parent::prepareInputForAdd($input);
      // generate a uniq id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   /**
    * Export in an array all the data of the current instanciated condition
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if (!$this->getID()) {
         return false;
      }

      $parameter = $this->fields;
      unset($parameter[PluginFormcreatorQuestion::getForeignKeyField()]);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      }
      unset($parameter[$idToRemove]);

      return $parameter;
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      $questionFk = PluginFormcreatorQuestion::getForeignKeyField();
      $input[$questionFk] = $containerId;
      $fieldName = $input['fieldname'];

      // get a built instance of the parameter
      $question = new PluginFormcreatorquestion();
      $question->getFromDB($containerId);
      $field = PluginFormcreatorFields::getFieldInstance(
         $question->getField('fieldtype'),
         $question
      );
      $parameters = $field->getEmptyParameters();
      $item = $parameters[$fieldName];
      // Find an existing section to update, only if an UUID is available
      if (isset($input['uuid'])) {
         $parameterId = plugin_formcreator_getFromDBByField(
            $item,
            'uuid',
            $input['uuid']
         );
      }
      if (!$item->convertUuids($input)) {
         $linker->postpone($input['uuid'], $item->getType(), $input, $containerId);
         return false;
      }

      // escape text fields
      foreach (['fieldname'] as $key) {
         $input[$key] = $DB->escape($input[$key]);
      }

      // Add or update section
      if (!$item->isNewItem()) {
         $input['id'] = $parameterId;
         $originalId = $input['id'];
         $item->update($input);
      } else {
         $originalId = $input['id'];
         unset($input['id']);
         $parameterId = $item->add($input);
      }
      if ($parameterId === false) {
         throw new ImportFailureException();
      }

      // add the section to the linker
      if (isset($input['uuid'])) {
         $originalId = $input['uuid'];
      }
      $linker->addObject($item->fields['uuid'], $item);
   }

   /**
    * Covnerts IDs of related objects into their UUID
    * @param array $parameter
    */
   protected function convertIds(&$parameter) {}

   /**
    * Converts uuids of linked objects into ID
    * @param array $parameter
    * @return boolean true if success, or false otherwise
    */
   protected function convertUuids(&$parameter) {
      return true;
   }
}
