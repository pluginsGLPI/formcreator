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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

require_once(realpath(dirname(__FILE__ ) . '/../../../inc/includes.php'));

abstract class PluginFormcreatorField implements PluginFormcreatorFieldInterface
{
   const IS_MULTIPLE = false;

   protected $fields = [];

   /**
    * @param unknown $fields
    * @param array $data
    */
   public function __construct($fields, $data = []) {
      $this->fields           = $fields;
      $this->fields['answer'] = $data;
   }

   /**
    * Transform input to properly save it in the database
    *
    * @param  array $input data to transform before save
    *
    * @return array input data to save as is
    */
   public function prepareQuestionInputForSave($input) {
      return $input;
   }

   /**
    * Prepares an answer value for output in a target object
    * @param  string|array $input the answer to format for a target (ticket or change)
    * @return string
    */
   public function prepareQuestionInputForTarget($input) {
      return Toolbox::addslashes_deep($input);
   }

   public function prepareQuestionValuesForEdit($input) {
      return $input;
   }

   /**
    * Output HTML to display the field
    * @param boolean $canEdit is the field editable ?
    */
   public function show($canEdit = true) {
      $required = ($canEdit && $this->fields['required']) ? ' required' : '';

      echo '<div class="form-group ' . $required . '" id="form-group-formcreator_field_' . $this->fields['id'] . '">';
      echo '<label for="formcreator_field_' . $this->fields['id'] . '">';
      echo $this->getLabel();
      if ($canEdit && $this->fields['required']) {
         echo ' <span class="red">*</span>';
      }
      echo '</label>';
      echo '<div class="help-block">' . html_entity_decode($this->fields['description']) . '</div>';

      echo '<div class="form_field">';
      $this->displayField($canEdit);
      echo '</div>';

      echo '</div>';
      $value = is_array($this->getAnswer()) ? json_encode($this->getAnswer()) : $this->getAnswer();
      // $value = json_encode($this->getAnswer());
      if ($this->fields['fieldtype'] == 'dropdown') {
         echo Html::scriptBlock('$(function() {
            formcreatorAddValueOf(' . $this->fields['id'] . ', "'
            . str_replace("\r\n", "\\r\\n", addslashes($this->fields['answer'])) . '");
         })');
      } else {
         echo Html::scriptBlock('$(function() {
            formcreatorAddValueOf(' . $this->fields['id'] . ', "'
               . str_replace("\r\n", "\\r\\n", addslashes(html_entity_decode($value))) . '");
         })');
      }
   }

   /**
    * Outputs the HTML representing the field
    * @param string $canEdit
    */
   public function displayField($canEdit = true) {
      if ($canEdit) {
         echo '<input type="text" class="form-control"
                  name="formcreator_field_' . $this->fields['id'] . '"
                  id="formcreator_field_' . $this->fields['id'] . '"
                  value="' . $this->getAnswer() . '"
                  onchange="formcreatorChangeValueOf(' . $this->fields['id'] . ', this.value);" />';
      } else {
         echo $this->getAnswer();
      }
   }

   public function getLabel() {
      return $this->fields['name'];
   }

   public function getField() {

   }

   public function getValue() {
      if (isset($this->fields['answer'])) {
         if (!is_array($this->fields['answer']) && is_array(json_decode($this->fields['answer']))) {
            return json_decode($this->fields['answer']);
         }
         return $this->fields['answer'];
      } else {
         if (static::IS_MULTIPLE) {
            return explode("\r\n", $this->fields['default_values']);
         }
         if (!$this->fields['show_empty'] && empty($this->fields['default_values'])) {
            $availableValues = $this->getAvailableValues();
            return array_shift($availableValues);
         }
         return $this->fields['default_values'];
      }
   }

   public function getAnswer() {
      return $this->getValue();
   }

   /**
    * Gets the available values for the field
    *
    * @return array
    */
   public function getAvailableValues() {
      return explode("\r\n", $this->fields['values']);
   }

   /**
    * Is the field valid for the given value?
    *
    * @param string $value
    *
    * @return boolean True if the field has a valid value, false otherwise
    */
   public function isValid($value) {
      // If the field is required it can't be empty
      if ($this->isRequired() && empty($value)) {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   /**
    * Is the field required?
    *
    * @return boolean
    */
   public function isRequired() {
      return $this->fields['required'];
   }

   /**
    * trim values separated by \r\n
    * @param string $value a value or default value
    * @return string
    */
   protected function trimValue($value) {
      $value = explode('\\r\\n', $value);
      $value = array_map('trim', $value);
      return implode('\\r\\n', $value);
   }

}
