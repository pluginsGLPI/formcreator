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

/**
 * A question parameter to handle a depdency to an other question. For example
 * the content og the question A is computed from the content of the question B. In
 * this case the question A has this parameter to maitnain the dependency to the
 * question B
 */
class PluginFormcreatorQuestionRegex
extends PluginFormcreatorQuestionParameter
{

   protected $domId = 'plugin_formcreator_questionRegex';

   public function getParameterFormSize() {
      return 1;
   }

   public function getParameterForm(PluginFormcreatorForm $form, PluginFormcreatorQuestion $question) {
      // get the name of the HTML input field
      $name = '_parameters[' . $this->field->getFieldTypeName() . '][' . $this->fieldName . ']';

      // get the selected value in the dropdown
      $selected = '';
      $this->getFromDBByCrit([
         'plugin_formcreator_questions_id' => $question->getID(),
         'fieldname' => $this->fieldName,
      ]);
      if (!$this->isNewItem()) {
         $selected = $this->fields['regex'];
      }

      // build HTML code
      $selector = $this->domId;
      $out = '';
      $out.= '<td id="' . $selector . '">' . $this->label . '</td>';
      $out.= '<td colspan="3"><input type="text" name="'. $name . '[regex]" id="regex" style="width:98%;" value="'.$selected.'" />';
      $out.= '<em>';
      $out.= __('Specify the additional validation conditions in the description of the question to help users.', 'formcreator');
      $out.= '</em></td>';

      return $out;
   }

   public function post_getEmpty() {
      $this->fields['regex'] = null;
   }

   public function getJsShowHideSelector() {
      return "#" . $this->domId;
   }

   public function prepareInputForAdd($input) {
      $input = parent::prepareInputForAdd($input);
      $input['fieldname'] = $this->fieldName;

      return $input;
   }

   public function getFieldName() {
      return $this->fieldName;
   }

   public function export($remove_uuid = false) {
      if (!$this->getID()) {
         return false;
      }

      $parameter = $this->fields;
      $this->convertIds($parameter);
      unset($parameter['id'],
            $parameter[PluginFormcreatorQuestion::getForeignKeyField()]);

      if ($remove_uuid) {
         $parameter['uuid'] = '';
      }

      return $parameter;
   }

   public static function import(PluginFormcreatorImportLinker $importLinker, $question_id = 0, $fieldName = '', $parameter = []) {
      global $DB;

      // escape text fields
      foreach (['regex'] as $key) {
         $parameter[$key] = $DB->escape($parameter[$key]);
      }

      parent::import($importLinker, $question_id, $fieldName, $parameter);
   }
}
