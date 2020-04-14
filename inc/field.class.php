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

require_once(realpath(dirname(__FILE__ ) . '/../../../inc/includes.php'));

abstract class PluginFormcreatorField implements PluginFormcreatorFieldInterface
{
   /** @var array $fields Fields of an instance of PluginFormcreatorQuestion */
   protected $question = null;

   /** @var mixed $answer Value of the field */
   protected $value = null;

   /**
    *
    * @param array $question PluginFormcreatorQuestion instance
    */
   public function __construct(PluginFormcreatorQuestion $question) {
      $this->question  = $question;
   }

   public function getDesignSpecializationField() {
      return [
         'label' => '',
         'field' => '',
         'additions' => $this->getParametersHtmlForDesign(),
         'may_be_empty' => false,
         'may_be_required' => true,
      ];
   }

   public function prepareQuestionInputForSave($input) {
      $this->value = $input['default_values'];
      return $input;
   }

   public function getRawValue() {
      return $this->value;
   }

   /**
    * Output HTML to display the field
    * @param boolean $canEdit is the field editable ?
    */
   public function show($canEdit = true) {
      $required = ($canEdit && $this->question->fields['required']) ? ' required' : '';

      echo '<div class="form-group ' . $required . '" id="form-group-field-' . $this->question->getID() . '">';
      echo '<label for="formcreator_field_' . $this->question->getID() . '">';
      echo $this->getLabel();
      if ($canEdit && $this->question->fields['required']) {
         echo ' <span class="red">*</span>';
      }
      echo '</label>';
      echo '<div class="help-block">' . html_entity_decode($this->question->fields['description']) . '</div>';

      echo '<div class="form_field">';
      $this->displayField($canEdit);
      echo '</div>';
      echo '</div>';
   }

   /**
    * Outputs the HTML representing the field
    * @param string $canEdit
    */
   public function displayField($canEdit = true) {
      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $domId        = $fieldName . '_' . $rand;
      $defaultValue = Html::cleanInputText($this->value);
      if ($canEdit) {
         echo '<input type="text" class="form-control"
                  name="' . $fieldName . '"
                  id="' . $domId . '"
                  value="' . $defaultValue . '" />';
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeField('$fieldName', '$rand');
         });");
      } else {
         echo $this->value;
      }
   }

   /**
    * Gets the label of the field
    *
    * @return string
    */
   public function getLabel() {
      return $this->question->fields['name'];
   }

   /**
    * Gets the available values for the field
    * @return array available values
    */
   public function getAvailableValues() {
      return explode("\r\n", $this->question->fields['values']);
   }

   public function isRequired() {
      return $this->question->fields['required'];
   }

   /**
    * trim values separated by \r\n
    * @param string $value a value or default value
    * @return string
    */
   protected function trimValue($value) {
      $value = explode('\r\n', $value);
      $value = array_map('trim', $value);
      return implode('\r\n', $value);
   }

   public function getFieldTypeName() {
      $classname = get_called_class();
      $matches = null;
      preg_match("#^PluginFormcreator(.+)Field$#", $classname, $matches);
      return strtolower($matches[1]);
   }

   public function getEmptyParameters() {
      return [];
   }

   public final function getParameters() {
      $parameters = $this->getEmptyParameters();
      foreach ($parameters as $fieldname => $parameter) {
         $parameter->getFromDBByCrit([
            'plugin_formcreator_questions_id'   => $this->question->getID(),
            'fieldname'                         => $fieldname,
         ]);
         if ($parameter->isNewItem()) {
            $parameter->getEmpty();
         }
      }

      return $parameters;
   }

   public final function addParameters(PluginFormcreatorQuestion $question, array $input) {
      $fieldTypeName = $this->getFieldTypeName();
      if (!isset($input['_parameters'][$fieldTypeName])) {
         return;
      }

      foreach ($this->getEmptyParameters() as $fieldName => $parameter) {
         $input['_parameters'][$fieldTypeName][$fieldName]['plugin_formcreator_questions_id'] = $question->getID();
         $parameter->add($input['_parameters'][$fieldTypeName][$fieldName]);
      }
   }

   public final function updateParameters(PluginFormcreatorQuestion $question, array $input) {
      $fieldTypeName = $this->getFieldTypeName();
      if (!isset($input['_parameters'][$fieldTypeName])) {
         return;
      }

      $parameters = $this->getParameters();
      foreach ($parameters as $fieldName => $parameter) {
         $input['_parameters'][$fieldTypeName][$fieldName]['plugin_formcreator_questions_id'] = $question->getID();
         if ($parameter->isNewItem()) {
            // In case of the parameter vanished in DB, just recreate it
            unset($$input['_parameters'][$fieldTypeName][$fieldName]['id']);
            $parameter->add($input['_parameters'][$fieldTypeName][$fieldName]);
         } else {
            $input['_parameters'][$fieldTypeName][$fieldName]['id'] = $parameter->getID();
            $parameter->update($input['_parameters'][$fieldTypeName][$fieldName]);
         }
      }
   }

   public final function deleteParameters(PluginFormcreatorQuestion $question) {
      foreach ($this->getEmptyParameters() as $parameter) {
         if (!$parameter->deleteByCriteria(['plugin_formcreator_questions_id' => $question->getID()])) {
            // Don't make  this error fatal, but log it anyway
            Toolbox::logInFile('php-errors', 'failed to delete parameter for question ' . $question->getID() . PHP_EOL);
         }
      }
      return true;
   }

   /**
    * get HTML of parameters for question design
    *
    * @return string
    */
   protected function getParametersHtmlForDesign() {
      $parameters = $this->getParameters();
      if (count($parameters) == 0) {
         return '';
      }

      $question = new PluginFormcreatorQuestion();
      $question->getFromDB($this->question->getID());
      $form = new PluginFormcreatorForm();
      $form->getByQuestionId($question->getID());

      /** @var integer $column 0 for 2 first columns, 1 for 2 right ones */
      $column = 0;
      $rowSize = 2;
      $additions = '';
      foreach ($parameters as $fieldname => $parameter) {
         if ($column == 0) {
            $additions .= '<tr class="plugin_formcreator_question_specific">';
         }
         $parameterSize = 1 + $parameter->getParameterFormSize();
         if ($column + $parameterSize > $rowSize) {
            // The parameter needs more room than available in the current row
            if ($column < $rowSize) {
               // fill the remaining of the row
               $additions .= str_repeat('<td></td><td></td>', $rowSize - $column);
               // Close current row and open an new one
               $additions .= '</tr><tr class="plugin_formcreator_question_specific">';
               $column = 0;
            }
         }
         $additions .= $parameter->getParameterForm($form, $question);
         $column += $parameterSize;
         if ($column == $rowSize) {
            // Finish the row
            $additions .= '</tr>';
            $column = 0;
         }
      }
      if ($column < $rowSize) {
         // fill the remaining of the row
         $additions .= str_repeat('<td></td><td></td>', $rowSize - $column);
         // Close current row and open an new one
         $additions .= "</tr>";
      }
      return $additions;
   }

   public function getQuestion() {
      return $this->question;
   }

   /**
    * Validate a regular expression
    *
    * @param string $regex
    * @return boolean true if the regex is valid, false otherwise
    */
   protected function checkRegex($regex) {
      // Avoid php notice when validating the regular expression
      set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {});
      $isValid = !(preg_match($regex, null) === false);
      restore_error_handler();

      return $isValid;
   }
}
