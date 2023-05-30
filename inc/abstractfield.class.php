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
   /** @var PluginFormcreatorQuestion $question */
   protected $question = null;

   /** @var mixed $answer Value of the field */
   protected $value = null;

   /**
    * the form answer to source the values from
    *
    * @var PluginFormcreatorFormAnswer|null
    */
   protected ?PluginFormcreatorFormAnswer $form_answer = null;

   /**
    *
    * @param array $question PluginFormcreatorQuestion instance
    */
   public function __construct(PluginFormcreatorQuestion $question) {
      $this->question = $question;
   }

   public function setFormAnswer(PluginFormcreatorFormAnswer $form_answer): void {
      $this->form_answer = $form_answer;
      if ($this->hasInput($this->form_answer->getAnswers())) {
         // Parse an HTML input
         $this->parseAnswerValues($this->form_answer->getAnswers());
      } else {
         // Deserialize the default value from DB
         $this->deserializeValue($this->question->fields['default_values']);
      }
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
   public function show(string $domain, bool $canEdit = true): string {
      $html = '';

      if ($this->isEditableField() && !empty($this->question->fields['description'])) {
         $description = $this->question->fields['description'];
         foreach (PluginFormcreatorCommon::getDocumentsFromTag($description) as $document) {
            $prefix = uniqid('', true);
            $filename = $prefix . 'image_paste.' . pathinfo($document['filename'], PATHINFO_EXTENSION);
            if (!copy(GLPI_DOC_DIR . '/' . $document['filepath'], GLPI_TMP_DIR . '/' . $filename)) {
               continue;
            }
         }
         $description = Plugin::doHookFunction('formcreator_question_description', [
            'description' => $description,
            'question'    => $this->getQuestion()
         ])['description'];
         $html .= '<div class="help-block">' . html_entity_decode(__($description, $domain)) . '</div>';
      }
      $html .= '<div class="form_field">';
      $this->value = Plugin::doHookFunction('formcreator_question_default_value', [
         'value'    => $this->value,
         'question' => $this->getQuestion()
      ])['value'];
      $html .= $this->getRenderedHtml($domain, $canEdit);
      $html .= '</div>';

      // Determine if field is mandatory after generating it's HTML
      // useful for fields plugin
      // because fields plugin manage it's own mandatory system and can overload $this->question->fields['required']
      // when HTML is generated (see $this->getRenderedHtml)
      $label = '';
      if ($this->isVisibleField()) {
         $label .= '<label for="formcreator_field_' . $this->question->getID() . '">';
         $label .= __($this->getLabel(), $domain);
         if ($canEdit && $this->question->fields['required']) {
            $label .= ' <span class="red">*</span>';
         }
         $label .= '</label>';
      }

      return $label.$html;
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
         $trimmedValue = trim($value);
         if (($trimmedValue != '')) {
            $tab_values[$trimmedValue] = $trimmedValue;
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

      $additions = '';
      foreach ($parameters as $parameter) {
         $additions .= $parameter->getParameterForm($this->question);
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

   /**
    * Translates the label of the field into the current language
    *
    * @return string
    */
   protected function getTtranslatedLabel(): string {
      $form = PluginFormcreatorForm::getByItem($this->question);
      $domain = PluginFormcreatorForm::getTranslationDomain($form->getID());
      return __($this->getLabel(), $domain);
   }
}
