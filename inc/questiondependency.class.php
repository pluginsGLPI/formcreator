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
 * A question parameter to handle a depdency to an other question. For example
 * the content og the question A is computed from the content of the question B. In
 * this case the question A has this parameter to maitnain the dependency to the
 * question B
 */
class PluginFormcreatorQuestionDependency
extends PluginFormcreatorAbstractQuestionParameter
{
   use PluginFormcreatorTranslatable;

   /** @var string $fieldtype type of field useable for the dependency */
   protected $fieldType;

   /**
    * @param PluginFormcreatorFieldInterface $field Field
    * @param array $options
    *                - fieldName: name of the HTML input tag
    *                - label    : label for the parameter
    *                - fieldType: array of field types the dependency should filter
    */
   public function setField(PluginFormcreatorFieldInterface $field, array $options) {
      parent::setField($field, $options);
      $this->fieldtype = isset($options['fieldType']) ? $options['fieldType'] : [];
   }

   public static function getTypeName($nb = 0) {
      return _n('Question dependency', 'Question dependencies', $nb, 'formcreator');
   }

   public function getParameterFormSize() {
      return 0;
   }

   public function getParameterForm(PluginFormcreatorQuestion $question) {
      $form = PluginFormcreatorForm::getByItem($question);

      // get questions of type text in the form
      $eligibleQuestions = [];
      $criteria = ['fieldtype' => $this->fieldtype];
      foreach ($question->getQuestionsFromForm($form->getID(), $criteria) as $item) {
         $eligibleQuestions[$item->getID()] = $item->getField('name');
      }

      // get the name of the HTML input field
      $name = '_parameters[' . $this->field->getFieldTypeName() . '][' . $this->fieldName . ']';

      //  get the selected value in the dropdown
      $selected = 0;
      if (!$question->isNewItem()) {
         $this->getFromDBByCrit([
            'plugin_formcreator_questions_id' => $question->getID(),
            'fieldname' => $this->fieldName,
         ]);
         if (!$this->isNewItem()) {
            $selected = $this->fields['plugin_formcreator_questions_id_2'];
         }
      }

      // get the HTML for the dropdown
      $questionsDropdown = Dropdown::showFromArray(
         $name . '[plugin_formcreator_questions_id_2]',
         $eligibleQuestions,
         [
            'display'               => false,
            'display_emptychoice'   => true,
            'value'                 => $selected,
            'used'                  => [$question->getID() => ''],
         ]
      );

      // build HTML code
      $selector = $this->domId;
      $out = '';
      $out.= '<td id="' . $selector . '">' . $this->label . '</td>';
      $out.= '<td>' . $questionsDropdown . '</td>';

      return $out;
   }

   public function prepareInputForAdd($input) {
      $input = parent::prepareInputForAdd($input);
      $input['fieldname'] = $this->fieldName;

      return $input;
   }

   public function getFieldName() {
      return $this->fieldName;
   }

   public function post_getEmpty() {
      $this->fields['plugin_formcreator_questions_id_2'] = '0';
   }

   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0) {
      global $DB;

      if (!isset($input['uuid']) && !isset($input['id'])) {
         throw new ImportFailureException(sprintf('UUID or ID is mandatory for %1$s', static::getTypeName(1)));
      }

      $questionFk = PluginFormcreatorQuestion::getForeignKeyField();
      $input[$questionFk] = $containerId;

      $question = new PluginFormcreatorQuestion();
      $question->getFromDB($containerId);
      $field = $question->getSubField();

      $item = $field->getEmptyParameters();
      $item = $item[$input['fieldname']];

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
            $input['plugin_formcreator_questions_id_2']
         );
      }

      // escape text fields
      foreach (['fieldname'] as $key) {
         $input[$key] = $DB->escape($input[$key]);
      }

      // set ID for linked objects
      /** @var CommonDBTM $linked */
      $linked = $linker->getObject($input['plugin_formcreator_questions_id_2'], PluginFormcreatorQuestion::class);
      if ($linked === false) {
         $linked = new PluginFormcreatorQuestion();
         $linked->getFromDBByCrit([
            $idKey => $input['plugin_formcreator_questions_id']
         ]);
         if ($linked->isNewItem()) {
            $linker->postpone($input[$idKey], $item->getType(), $input, $containerId);
            return false;
         }
      }
      $input['plugin_formcreator_questions_id_2'] = $linked->getID();

      // Add or update condition
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

      // add the parameter to the linker
      $linker->addObject($originalId, $item);

      return $itemId;
   }

   public static function countItemsToImport($input) : int {
      return 1;
   }

   public function export(bool $remove_uuid = false) : array {
      if ($this->isNewItem()) {
         throw new ExportFailureException(sprintf(__('Cannot export an empty object: %s', 'formcreator'), $this->getTypeName()));
      }

      $parameter = $this->fields;

      $questionFk = PluginFormcreatorQuestion::getForeignKeyField();
      unset($parameter[$questionFk]);

      // remove ID or UUID
      $idToRemove = 'id';
      if ($remove_uuid) {
         $idToRemove = 'uuid';
      } else {
         // Convert IDs into UUIDs
         $question = new PluginFormcreatorQuestion();
         $question->getFromDB($parameter['plugin_formcreator_questions_id_2']);
         $parameter['plugin_formcreator_questions_id_2'] = $question->fields['uuid'];
      }
      unset($parameter[$idToRemove]);

      return $parameter;
   }
}
