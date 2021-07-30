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

use PluginFormcreatorAbstractField;
use PluginFormcreatorCommon;
use Html;
use Toolbox;
use Session;
use PluginFormcreatorQuestionRange;
use PluginFormcreatorQuestionRegex;
use GlpiPlugin\Formcreator\Exception\ComparisonException;

class FloatField extends PluginFormcreatorAbstractField
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
      $additions .= __('Default value');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td id="dropdown_default_value_field">';
      $value = Html::entities_deep($this->question->fields['default_values']);
      $additions .= Html::input('default_values', [
         'id' => 'default_values',
         'value' => $value,
      ]);
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

   public function serializeValue(): string {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      return strval((float) $this->value);
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
      if ($this->isRequired() && $this->value == '') {
         Session::addMessageAfterRedirect(
            sprintf(__('A required field is empty: %s', 'formcreator'), $this->getLabel()),
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

      if (!empty($value) && !is_numeric($value)) {
         Session::addMessageAfterRedirect(
            sprintf(__('This is not a number: %s', 'formcreator'), $this->getLabel()),
            false,
            ERROR
         );
         return false;
      }

      $parameters = $this->getParameters();

      // Check the field matches the format regex
      if (!$parameters['regex']->isNewItem()) {
         $regex = $parameters['regex']->fields['regex'];
         if ($regex !== null && strlen($regex) > 0) {
            if (!preg_match($regex, $value)) {
               Session::addMessageAfterRedirect(sprintf(__('Specific format does not match: %s', 'formcreator'), $this->question->fields['name']), false, ERROR);
               return false;
            }
         }
      }

      // Check the field is in the range
      if (!$parameters['range']->isNewItem()) {
         $rangeMin = $parameters['range']->fields['range_min'];
         $rangeMax = $parameters['range']->fields['range_max'];
         if ($rangeMin > 0 && $value < $rangeMin) {
            $message = sprintf(__('The following number must be greater than %d: %s', 'formcreator'), $rangeMin, $this->question->fields['name']);
            Session::addMessageAfterRedirect($message, false, ERROR);
            return false;
         }

         if ($rangeMax > 0 && $value > $rangeMax) {
            $message = sprintf(__('The following number must be lower than %d: %s', 'formcreator'), $rangeMax, $this->question->fields['name']);
            Session::addMessageAfterRedirect($message, false, ERROR);
            return false;
         }
      }

      return true;
   }

   public static function getName(): string {
      return __('Float', 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      $success = true;
      $fieldType = $this->getFieldTypeName();
      // Add leading and trailing regex marker automaticaly
      if (isset($input['_parameters'][$fieldType]['regex']['regex']) && !empty($input['_parameters'][$fieldType]['regex']['regex'])) {
         $regex = Toolbox::stripslashes_deep($input['_parameters'][$fieldType]['regex']['regex']);
         $success = PluginFormcreatorCommon::checkRegex($regex);
         if (!$success) {
            Session::addMessageAfterRedirect(__('The regular expression is invalid', 'formcreator'), false, ERROR);
         }
      }
      if (!$success) {
         return false;
      }

      if (isset($input['default_values'])) {
         if ($input['default_values'] != '') {
            $this->value = (float) str_replace(',', '.', $input['default_values']);
         } else {
            $this->value = '';
         }
      }
      $input['values'] = '';

      return $input;
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!is_string($input[$key])) {
         $this->value = '';
      }
      // $input[$key] = (float) str_replace(',', '.', $input[$key]);

      $this->value = $input[$key];
      return true;
   }

   public static function canRequire(): bool {
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
      return ((float) $this->value) === ((float) $value);
   }

   public function notEquals($value): bool {
      return !$this->equals($value);
   }

   public function greaterThan($value): bool {
      return ((float) $this->value) > ((float) $value);
   }

   public function lessThan($value): bool {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function regex($value): bool {
      return (preg_grep($value, $this->value)) ? true : false;
   }

   public function isAnonymousFormCompatible(): bool {
      return true;
   }

   public function getHtmlIcon() {
      return '<i class="fas fa-square-root-alt" aria-hidden="true"></i>';
   }

   public function isVisibleField(): bool {
      return true;
   }

   public function isEditableField(): bool {
      return true;
   }
}
