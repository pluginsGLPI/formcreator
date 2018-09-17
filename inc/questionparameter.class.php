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
 *
 * @copyright Copyright © 2011 - 2018 Teclib'
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
      unset($parameter['id'],
            $parameter[PluginFormcreatorQuestion::getForeignKeyField()]);

      if ($remove_uuid) {
         $parameter['uuid'] = '';
      }

      return $parameter;
   }

   public static function import(PluginFormcreatorImportLinker $importLinker, $questions_id = 0, $fieldName = '', $parameter = []) {
      $parameter['plugin_formcreator_questions_id'] = $questions_id;

      // get a built instance of the parameter
      $question = new PluginFormcreatorquestion();
      $question->getFromDB($questions_id);
      $field = PluginFormcreatorFields::getFieldInstance($question->getField('fieldtype'), $question);
      $parameters = $field->getEmptyParameters();
      $item = $parameters[$fieldName];
      $found = $item->getFromDBByCrit([
         'plugin_formcreator_questions_id' => $questions_id,
         'fieldname' => $fieldName,
      ]);
      if (!$item->convertUuids($parameter)) {
         $importLinker->postponeImport($parameter['uuid'], $item->getType(), $parameter, $questions_id);
         return false;
      }

      if ($found) {
         $parameter['id'] = $item->getID();
         $item->update($parameter);
      } else {
         $item->add($parameter);
      }
      $importLinker->addImportedObject($item->fields['uuid'], $item);
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

   /**
    * Duplicates a parameter
    * @param PluginFormcreatorQuestion $newQuestion question which will contain the new parameter
    * @param array $tab_questions map old question ID => new question ID
    * @return PluginFormcreatorQuestionParameter new isntance (not saved in DB)
    */
   public function duplicate(PluginFormcreatorQuestion $newQuestion, array $tab_questions) {
      $parameter = new static($this->field, ['fieldName' => $this->fieldName, 'label' => $this->label]);
      $row = $this->fields;
      unset($row['id']);

      // Update the question ID linked to the parameter with the old/new question ID map
      $questionKey = PluginFormcreatorQuestion::getForeignKeyField();
      $row[$questionKey] = $tab_questions[$this->fields[$questionKey]];

      // return  the new instance, not saved yet in DB
      $parameter->fields = $row;
      return $parameter;
   }
}