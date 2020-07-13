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

class PluginFormcreatorRadiosField extends PluginFormcreatorField
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
      $additions .= '<small>('.__('One per line', 'formcreator').')</small>';
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= Html::textarea([
         'name'             => 'default_values',
         'id'               => 'default_values',
         'value'            => $this->question->fields['default_values'],
         'cols'             => '50',
         'display'          => false,
      ]);
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_default_values'.$rand.'">';
      $additions .= __('Values');
      $additions .= '<small>('.__('One per line', 'formcreator').')</small>';
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= Html::textarea([
         'name'             => 'values',
         'id'               => 'values',
         'value'            => $this->question->fields['values'],
         'cols'             => '50',
         'display'          => false,
      ]);
      $additions .= '</td>';
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

   public function displayField($canEdit = true) {
      if ($canEdit) {
         $id           = $this->question->getID();
         $rand         = mt_rand();
         $fieldName    = 'formcreator_field_' . $id;
         $domId        = $fieldName . '_' . $rand;

         $values = $this->getAvailableValues();
         if (!empty($values)) {
            echo '<div class="radios">';
            $i = 0;
            foreach ($values as $value) {
               if ((trim($value) != '')) {
                  $i++;
                  $checked = ($this->value == $value) ? ' checked' : '';
                  echo '<div class="radio">';
                  echo '<span class="form-group-radio">';
                  echo '<input type="radio" class="form-control"
                        name="' . $fieldName . '"
                        id="' . $domId . '_' . $i . '"
                        value="' . $value . '"' . $checked . ' /> ';
                  echo '<label class="label-radio" title="' . $value . '" for="' . $domId . '_' . $i . '">';
                  echo '<span class="box"></span>';
                  echo '<span class="check"></span>';
                  echo '</label>';
                  echo '</span>';
                  echo '<label for="' . $domId . '_' . $i . '">';
                  echo $value;
                  echo '</label>';
                  echo '</div>';

               }
            }
            echo '</div>';
         }
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeRadios('$fieldName', '$rand');
         });");

      } else {
         echo $this->value;
      }
   }

   public static function getName() {
      return __('Radios', 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['values'])) {
         if (empty($input['values'])) {
            Session::addMessageAfterRedirect(
                  __('The field value is required:', 'formcreator') . ' ' . $input['name'],
                  false,
                  ERROR);
            return [];
         }
         // trim values
         $input['values'] = $this->trimValue($input['values']);
      }
      if (isset($input['default_values'])) {
         // trim values
         $this->value = explode('\r\n', $input['default_values']);
         $this->value = array_map('trim', $this->value);
         $this->value = array_filter($this->value, function($value) {
            return ($value !== '');
         });
         $this->value = array_shift($this->value);
         $input['default_values'] = $this->value;
      }
      return $input;
   }

   public function hasInput($input) {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function parseAnswerValues($input, $nonDestructive = false) {
      $key = 'formcreator_field_' . $this->question->getID();
      if (isset($input[$key])) {
         if (!is_string($input[$key])) {
            return false;
         }
      } else {
         $this->value = '';
         return true;
      }

       $this->value = Toolbox::stripslashes_deep($input[$key]);
       return true;
   }

   public static function canRequire() {
      return true;
   }

   public function parseDefaultValue($defaultValue) {
      $this->value = explode('\r\n', $defaultValue);
      $this->value = array_filter($this->value, function($value) {
         return ($value !== '');
      });
      $this->value = array_shift($this->value);
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

      // All is OK
      return true;
   }

   public function equals($value) {
      return $this->value == $value;
   }

   public function notEquals($value) {
      return !$this->equals($value);
   }

   public function greaterThan($value) {
      return $this->value > $value;
   }

   public function lessThan($value) {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function isAnonymousFormCompatible() {
      return true;
   }

   public function getHtmlIcon() {
      return '<i class="fa fa-check-circle" aria-hidden="true"></i>';
   }
}
