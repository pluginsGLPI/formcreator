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
 * @copyright Copyright © 2011 - 2020 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace GlpiPlugin\Formcreator\Field;

use Html;
use PluginFormcreatorAbstractField;
use PluginFormcreatorQuestionRange;
use PluginFormcreatorQuestionRegex;
use PluginFormcreatorCommon;
use Session;
use Toolbox;

class TextField extends PluginFormcreatorAbstractField
{
   public function isPrerequisites(): bool {
      return true;
   }

   public function getDesignSpecializationField(): array {
      $rand = mt_rand();

      $label = '';
      $field = '';

      $additions = '<tr class="plugin_formcreator_question_specific">';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_default_values' . $rand . '">';
      $additions .= __('Default values');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td>';
      $value = Html::entities_deep($this->question->fields['default_values']);
      $additions .= Html::input(
         'default_values',
         [
            'type'  => 'text',
            'id'    => 'default_values',
            'value' => $value,
         ]
      );
      $additions .= '</td>';
      $additions .= '<td></td>';
      $additions .= '<td></td>';
      $additions .= '</tr>';

      $common = parent::getDesignSpecializationField();
      $additions .= $common['additions'];

      return [
         'label' => $label,
         'field' => $field,
         'additions' => $additions,
         'may_be_empty' => false,
         'may_be_required' => true,
      ];
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
      $defaultValue = Html::cleanInputText(__($this->value, $domain));

      $html .= Html::input($fieldName, [
         'type'  => 'text',
         'id'    => $domId,
         'value' => $defaultValue,
      ]);
      $html .= Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeField('$fieldName', '$rand');
      });");

      return $html;
   }

   public function serializeValue(): string {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      return Toolbox::addslashes_deep($this->value);
   }

   public function deserializeValue($value) {
      $this->value = ($value !== null && $value !== '')
         ? $value
         : '';
   }

   public function getValueForDesign(): string {
      if ($this->value === null) {
         return '';
      }

      return $this->value;
   }

   public function getValueForTargetText($domain, $richText): ?string {
      return $this->value;
   }

   public function moveUploads() {
   }

   public function getDocumentsForTarget(): array {
      return [];
   }

   public function isValid(): bool {
      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '') {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR
         );
         return false;
      }

      return $this->isValidValue($this->value);
   }

   public function isValidValue($value): bool {
      if (strlen($value) == 0) {
         return true;
      }

      $parameters = $this->getParameters();

      // Check the field matches the format regex
      $regex = $parameters['regex']->fields['regex'];
      if ($regex !== null && strlen($regex) > 0) {
         if (!preg_match($regex, $value)) {
            Session::addMessageAfterRedirect(sprintf(__('Specific format does not match: %s', 'formcreator'), $this->question->fields['name']), false, ERROR);
            return false;
         }
      }

      // Check the field is in the range
      $rangeMin = $parameters['range']->fields['range_min'];
      $rangeMax = $parameters['range']->fields['range_max'];
      if ($rangeMin > 0 && strlen($value) < $rangeMin) {
         Session::addMessageAfterRedirect(sprintf(__('The text is too short (minimum %d characters): %s', 'formcreator'), $rangeMin, $this->question->fields['name']), false, ERROR);
         return false;
      }

      if ($rangeMax > 0 && strlen($value) > $rangeMax) {
         Session::addMessageAfterRedirect(sprintf(__('The text is too long (maximum %d characters): %s', 'formcreator'), $rangeMax, $this->question->fields['name']), false, ERROR);
         return false;
      }

      return true;
   }

   public static function getName(): string {
      return __('Text', 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      $success = true;
      $fieldType = $this->getFieldTypeName();
      if (isset($input['_parameters'][$fieldType]['regex']['regex']) && !empty($input['_parameters'][$fieldType]['regex']['regex'])) {
         $regex = Toolbox::stripslashes_deep($input['_parameters'][$fieldType]['regex']['regex']);
         $success = PluginFormcreatorCommon::checkRegex($regex);
         if (!$success) {
            Session::addMessageAfterRedirect(__('The regular expression is invalid', 'formcreator'), false, ERROR);
         }
      }
      if (!$success) {
         return [];
      }

      return $input;
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public static function canRequire(): bool {
      return true;
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!isset($input[$key])) {
         return false;
      }
      if (!is_string($input[$key])) {
         return false;
      }

      if (version_compare(GLPI_VERSION, '9.5.10', '>=')) {
         $input[$key] = str_replace('\r\n', "\r\n", $input[$key]);
      }

      $this->value = Toolbox::stripslashes_deep($input[$key]);
      return true;
   }

   public function getEmptyParameters(): array {
      $regexDoc = '<small>';
      $regexDoc .= '<a href="http://php.net/manual/reference.pcre.pattern.syntax.php" target="_blank">';
      $regexDoc .= '(' . __('Regular expression', 'formcreator') . ')';
      $regexDoc .= '</small>';
      return [
         'regex' => new PluginFormcreatorQuestionRegex(
            $this,
            [
               'fieldName' => 'regex',
               'label'     => __('Additional validation', 'formcreator') . $regexDoc,
               'fieldType' => ['text'],
            ]
         ),
         'range' => new PluginFormcreatorQuestionRange(
            $this,
            [
               'fieldName' => 'range',
               'label'     => __('Range', 'formcreator'),
               'fieldType' => ['text'],
            ]
         ),
      ];
   }

   public function equals($value): bool {
      return Toolbox::stripslashes_deep($this->value) == $value;
   }

   public function notEquals($value): bool {
      return !$this->equals($value);
   }

   public function greaterThan($value): bool {
      return Toolbox::stripslashes_deep($this->value) > $value;
   }

   public function lessThan($value): bool {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function regex($value): bool {
      return preg_match($value, Toolbox::stripslashes_deep($this->value)) ? true : false;
   }

   public function isAnonymousFormCompatible(): bool {
      return true;
   }

   public function getHtmlIcon(): string {
      return '<i class="far fa-comment-dots" aria-hidden="true"></i>';
   }

   public function isVisibleField(): bool {
      return true;
   }

   public function isEditableField(): bool {
      return true;
   }

   public function getTranslatableStrings(array $options = []) : array {
      $strings = parent::getTranslatableStrings($options);

      $params = [
         'searchText'      => '',
         'id'              => '',
         'is_translated'   => null,
         'language'        => '', // Mandatory if one of is_translated and is_untranslated is false
      ];
      $options = array_merge($params, $options);

      $searchString = Toolbox::stripslashes_deep(trim($options['searchText']));

      if ($searchString != '' && stripos($this->question->fields['default_values'], $searchString) === false) {
         return $strings;
      }
      $id = \PluginFormcreatorTranslation::getTranslatableStringId($this->question->fields['default_values']);
      if ($options['id'] != '' && $id != $options['id']) {
         return $strings;
      }
      if ($this->question->fields['default_values'] != '') {
         $strings['string'][$id] = $this->question->fields['default_values'];
         $strings['id'][$id] = 'string';
      }

      return $strings;
   }
}
