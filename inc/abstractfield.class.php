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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

require_once(realpath(dirname(__FILE__) . '/../../../inc/includes.php'));

abstract class PluginFormcreatorAbstractField implements PluginFormcreatorFieldInterface
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

   public function getDesignSpecializationField(): array {
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
    * @param string  $domain  Translation domain of the form
    * @param boolean $canEdit is the field editable ?
    */
   public function show($domain, $canEdit = true) {
      $html = '';

      if ($this->isVisibleField()) {
         $html .= '<label for="formcreator_field_' . $this->question->getID() . '">';
         $html .= __($this->getLabel(), $domain);
         if ($canEdit && $this->question->fields['required']) {
            $html .= ' <span class="red">*</span>';
         }
         $html .= '</label>';
      }
      if ($this->isEditableField() && !empty($this->question->fields['description'])) {
         $html .= '<div class="help-block">' . html_entity_decode(__($this->question->fields['description'], $domain)) . '</div>';
      }
      $html .= '<div class="form_field">';
      $html .= $this->getRenderedHtml($domain, $canEdit);
      $html .= '</div>';

      return $html;
   }

   public function getRenderedHtml($domain, $canEdit = true): string {
      if (!$canEdit) {
         return $this->value;
      }

      $html         = '';
      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $domId        = $fieldName . '_' . $rand;
      $defaultValue = Html::cleanInputText($this->value);
      $html .= Html::input($fieldName, [
         'id'    => $domId,
         'value' => $defaultValue
      ]);
      $html .= Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeField('$fieldName', '$rand');
      });");

      return $html;
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
      $values = json_decode($this->question->fields['values']);
      $tab_values = [];
      foreach ($values as $value) {
         if ((trim($value) != '')) {
            $tab_values[$value] = $value;
         }
      }
      return $tab_values;
   }

   public function isRequired(): bool {
      return ($this->question->fields['required'] != '0');
   }

   /**
    * trim values separated by \r\n
    * @param string $value a value or default value
    * @return string
    */
   protected function trimValue($value) {
      global $DB;

      $value = explode('\r\n', $value);
      // input has escpaed single quotes
      $value = Toolbox::stripslashes_deep($value);
      $value = array_filter($value, function ($value) {
         return ($value !== '');
      });
      $value = array_map(
         function ($value) {
            return trim($value);
         },
         $value
      );

      return $DB->escape(json_encode($value, JSON_UNESCAPED_UNICODE));
   }

   public function getFieldTypeName(): string {
      $classname = explode('\\', get_called_class());
      $classname = array_pop($classname);
      $matches = null;

      preg_match("#^(.+)Field$#", $classname, $matches);
      return strtolower($matches[1]);
   }

   public function getEmptyParameters(): array {
      return [];
   }

   /**
    * Undocumented function
    *
    * @return PluginFormcreatorAbstractQuestionParameter[]
    */
   public final function getParameters(): array {
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
         $input['_parameters'][$fieldTypeName][$fieldName]['plugin_formcreator_questions_id'] = $this->question->getID();
         $parameter->add($input['_parameters'][$fieldTypeName][$fieldName]);
      }
   }

   public final function updateParameters(PluginFormcreatorQuestion $question, array $input) {
      $fieldTypeName = $this->getFieldTypeName();
      if (!isset($input['_parameters'][$fieldTypeName])) {
         return;
      }

      foreach ($this->getParameters() as $fieldName => $parameter) {
         if (!isset($input['_parameters'][$fieldTypeName][$fieldName])) {
            continue;
         }
         $parameterInput = $input['_parameters'][$fieldTypeName][$fieldName];
         $parameterInput['plugin_formcreator_questions_id'] = $this->question->getID();
         if ($parameter->isNewItem()) {
            // In case of the parameter vanished in DB, just recreate it
            unset($parameterInput['id']);
            $parameter->add($parameterInput);
         } else {
            $parameterInput['id'] = $parameter->getID();
            $parameter->update($parameterInput);
         }
      }
   }

   public final function deleteParameters(PluginFormcreatorQuestion $question): bool {
      foreach ($this->getEmptyParameters() as $parameter) {
         if (!$parameter->deleteByCriteria(['plugin_formcreator_questions_id' => $question->getID()])) {
            // Don't make  this error fatal, but log it anyway
            Toolbox::logInFile('php-errors', 'Failed to delete parameter for question ' . $question->getID() . PHP_EOL);
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
      foreach ($parameters as $parameter) {
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

   /**
    * get the question matching the  field
    *
    * @return PluginFormcreatorQuestion
    */
   public function getQuestion() {
      return $this->question;
   }

   public function getTranslatableStrings(array $options = []) : array {
      $strings = [
         'itemlink' => [],
         'string'   => [],
         'text'     => [],
         'id'       => [],
      ];

      $params = [
         'searchText'      => '',
         'id'              => '',
         'is_translated'   => null,
         'language'        => '', // Mandatory if one of is_translated and is_untranslated is false
      ];
      $options = array_merge($params, $options);

      foreach ($this->getParameters() as $parameter) {
         foreach ($parameter->getTranslatableStrings($options) as $type => $subStrings) {
            $strings[$type] = array_merge($strings[$type], $subStrings);
         }
      }

      return $strings;
   }
}
