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

class PluginFormcreatorTextField extends PluginFormcreatorField
{
   public function isPrerequisites() {
      return true;
   }

   public function getDesignSpecializationField() {
      $rand = mt_rand();

      $label = '';
      $field = '';

      $additions = '<tr class="plugin_formcreator_question_specific">';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_default_values'.$rand.'">';
      $additions .= __('Default values');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td>';
      $value = Html::entities_deep($this->question->fields['default_values']);
      $additions .= '<input type="text" name="default_values" id="default_values" rows="4" cols="40"'
         .'style="width: 90%" value="'.$value.'">';
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '</td>';
      $additions .= '</tr>';

      $common = $common = parent::getDesignSpecializationField();
      $additions .= $common['additions'];

      return [
         'label' => $label,
         'field' => $field,
         'additions' => $additions,
         'may_be_empty' => false,
         'may_be_required' => true,
      ];
   }

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

   public function serializeValue() {
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

   public function getValueForDesign() {
      if ($this->value === null) {
         return '';
      }

      return $this->value;
   }

   public function getValueForTargetText($richText) {
      return $this->value;
   }

   public function moveUploads() {}

   public function getDocumentsForTarget() {
      return [];
   }

   public function isValid() {
      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '') {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      if (!$this->isValidValue($this->value)) {
         return false;
      }

       // All is OK
      return true;
   }

   private function isValidValue($value) {
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

   public static function getName() {
      return __('Text', 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      $success = true;
      $fieldType = $this->getFieldTypeName();
      // Add leading and trailing regex marker automaticaly
      if (isset($input['_parameters'][$fieldType]['regex']['regex']) && !empty($input['_parameters'][$fieldType]['regex']['regex'])) {
         $regex = Toolbox::stripslashes_deep($input['_parameters'][$fieldType]['regex']['regex']);
         $success = $this->checkRegex($regex);
         if (!$success) {
            Session::addMessageAfterRedirect(__('The regular expression is invalid', 'formcreator'), false, ERROR);
         }
      }
      if (!$success) {
         return [];
      }
      $this->value = Toolbox::stripslashes_deep(str_replace('\r\n', "\r\n", $input['default_values']));

      return $input;
   }

   public function hasInput($input) {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public static function canRequire() {
      return true;
   }

   public function parseAnswerValues($input, $nonDestructive = false) {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!isset($input[$key])) {
         return false;
      }
      if (!is_string($input[$key])) {
         return false;
      }

      $this->value = str_replace('\r\n', "\r\n", $input[$key]);
      $this->value = Toolbox::stripslashes_deep($this->value);
      return true;
   }

   public function getEmptyParameters() {
      $regexDoc = '<small>';
      $regexDoc.= '<a href="http://php.net/manual/reference.pcre.pattern.syntax.php" target="_blank">';
      $regexDoc.= '('.__('Regular expression', 'formcreator').')';
      $regexDoc.= '</small>';
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

   public function equals($value) {
      return Toolbox::stripslashes_deep($this->value) == $value;
   }

   public function notEquals($value) {
      return !$this->equals($value);
   }

   public function greaterThan($value) {
      return Toolbox::stripslashes_deep($this->value) > $value;
   }

   public function lessThan($value) {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function isAnonymousFormCompatible() {
      return true;
   }

   public function getHtmlIcon() {
      return '<i class="far fa-comment-dots" aria-hidden="true"></i>';
   }
}
