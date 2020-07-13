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

class PluginFormcreatorMultiSelectField extends PluginFormcreatorField
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

      $common = $common = parent::getDesignSpecializationField();
      $additions .= $common['additions'];

      return [
         'label' => $label,
         'field' => $field,
         'additions' => $additions,
         'may_be_empty' => true,
         'may_be_required' => true,
      ];
   }


   public function displayField($canEdit = true) {
      if ($canEdit) {
         $id           = $this->question->getID();
         $rand         = mt_rand();
         $fieldName    = 'formcreator_field_' . $id;
         $values       = $this->getAvailableValues();
         $tab_values   = [];

         if (!empty($this->question->fields['values'])) {
            foreach ($values as $value) {
               if ((trim($value) != '')) {
                  $tab_values[$value] = $value;
               }
            }

            Dropdown::showFromArray($fieldName, $tab_values, [
               'display_emptychoice' => $this->question->fields['show_empty'] == 1,
               'value'     => '',
               'values'    => $this->value,
               'rand'      => $rand,
               'multiple'  => true,
            ]);
         }
         echo PHP_EOL;
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeMultiselect('$fieldName', '$rand');
         });");
      } else {
         echo empty($this->value) ? '' : implode('<br />', $this->value);
      }
   }

   public function serializeValue() {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      return implode("\r\n", Toolbox::addslashes_deep($this->value));
   }

   public function deserializeValue($value) {
      $this->value = ($value !== null && $value !== '')
                  ? explode("\r\n", $value)
                  : [];
   }

   public function getValueForDesign() {
      if ($this->value === null) {
         return '';
      }

      return implode("\r\n", $this->value);
   }

   public function isValid() {
      if ($this->value == '') {
         $this->value = [];
      }

      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '') {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(), false, ERROR);
         return false;

      }
      if (!$this->isValidValue($this->value)) {
         return false;
      }

      return true;
   }

   private function isValidValue($value) {
      $parameters = $this->getParameters();

      // Check the field matches the format regex
      $rangeMin = $parameters['range']->fields['range_min'];
      $rangeMax = $parameters['range']->fields['range_max'];
      if ($rangeMin > 0 && count($value) < $rangeMin) {
         $message = sprintf(__('The following question needs of at least %d answers', 'formcreator'), $rangeMin);
         Session::addMessageAfterRedirect($message . ' ' . $this->getLabel(), false, ERROR);
         return false;
      }

      if ($rangeMax > 0 && count($value) > $rangeMax) {
         $message = sprintf(__('The following question does not accept more than %d answers', 'formcreator'), $rangeMax);
         Session::addMessageAfterRedirect($message . ' ' . $this->getLabel(), false, ERROR);
         return false;
      }

      return true;
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['values'])) {
         if (empty($input['values'])) {
            Session::addMessageAfterRedirect(
               __('The field value is required:', 'formcreator') . ' ' . $input['name'],
               false,
               ERROR);
            return [];
         } else {
            // trim values
            $input['values'] = $this->trimValue($input['values']);
         }
      }
      if (isset($input['default_values'])) {
         // trim values
         $this->value = explode('\r\n', $input['default_values']);
         $this->value = array_map('trim', $this->value);
         $this->value = array_filter($this->value, function($value) {
            return ($value !== '');
         });
         $input['default_values'] = implode('\r\n', $this->value);
      }
      return $input;
   }

   public function hasInput($input) {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function getValueForTargetText($richText) {
      $input = $this->value;
      $value = [];
      $values = $this->getAvailableValues();

      if (empty($input)) {
         return '';
      }

      if (is_array($input)) {
         $tab_values = $input;
      } else if (is_array(json_decode($input))) {
         $tab_values = json_decode($input);
      } else {
         $tab_values = [$input];
      }

      foreach ($tab_values as $input) {
         if (in_array($input, $values)) {
            $value[] = $input;
         }
      }
      if ($richText) {
         $value = '<br />' . implode('<br />', $value);
      } else {
         $value = implode(', ', $value);
      }
      return $value;
   }

   public function moveUploads() {}

   public function getDocumentsForTarget() {
      return [];
   }

   public static function getName() {
      return __('Multiselect', 'formcreator');
   }

   public static function canRequire() {
      return true;
   }

   public function parseAnswerValues($input, $nonDestructive = false) {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!isset($input[$key])) {
         $input[$key] = [];
      } else {
         if (!is_array($input[$key])) {
            return false;
         }
      }

      $this->value = Toolbox::stripslashes_deep($input[$key]);
      return true;
   }

   public function getEmptyParameters() {
      return [
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
      if (!is_array( $this->value)) {
         // No selection
         return ($value === '');
      }
      return in_array($value, $this->value);
   }

   public function notEquals($value) {
      return !$this->equals($value);
   }

   public function greaterThan($value) {
      if (count($this->value) < 1) {
         return false;
      }
      foreach ($this->value as $answer) {
         if ($answer <= $value) {
            return false;
         }
      }
      return true;
   }

   public function lessThan($value) {
      if (count($this->value) < 1) {
         return false;
      }
      foreach ($this->value as $answer) {
         if ($answer >= $value) {
            return false;
         }
      }
      return true;
   }

   public function isAnonymousFormCompatible() {
      return true;
   }

   public function getHtmlIcon() {
      return '<img src="' . FORMCREATOR_ROOTDOC . '/pics/ui-multiselect-field.png" title="" />';
   }
}
