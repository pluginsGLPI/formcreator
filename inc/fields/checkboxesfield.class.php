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
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class PluginFormcreatorCheckboxesField extends PluginFormcreatorField
{
   public function isPrerequisites() {
      return true;
   }

   public function displayField($canEdit = true) {
      if ($canEdit) {
         $id    = $this->fields['id'];
         $rand  = mt_rand();
         $fieldName    = 'formcreator_field_' . $id;
         $domId        = $fieldName . '_' . $rand;

         $values = [];
         $values = $this->getAvailableValues();
         if (!empty($values)) {
            echo '<div class="checkboxes">';
            $i = 0;
            foreach ($values as $value) {
               if ((trim($value) != '')) {
                  $i++;
                  $current_value = $this->value;
                  echo "<div class='checkbox'>";
                  echo Html::getCheckbox([
                     'title'         => htmlentities($value, ENT_QUOTES),
                     'id'            => $domId.'_'.$i,
                     'name'          => htmlentities($fieldName, ENT_QUOTES) . '[]',
                     'value'         => htmlentities($value, ENT_QUOTES),
                     'zero_on_empty' => false,
                     'checked' => in_array($value, $this->value)
                  ]);
                  echo '<label for="' . $domId . '_' . $i . '">';
                  echo '&nbsp;' . $value;
                  echo '</label>';
                  echo "</div>";
               }
            }
            echo '</div>';
         }
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeCheckboxes('$fieldName', '$rand');
         });");

      } else {
         if (count($this->value)) {
            echo implode('<br />', $this->value);
         } else {
            echo '';
         }
      }
   }

   /**
    * Gets the available values for the field
    * @return array available values
    */
   public function getAvailableValues() {
      return explode("\r\n", $this->fields['values']);
   }

   public function serializeValue() {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      return implode("\r\n", $this->value);
   }

   public function deserializeValue($value) {
      $deserialized  = [];
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

   public function parseAnswerValues($input) {
      $key = 'formcreator_field_' . $this->fields['id'];
      if (!isset($input[$key])) {
         $input[$key] = [];
      } else {
         if (!is_array($input[$key])) {
            return false;
         }
      }

      $this->value = $input[$key];
      return true;
   }

   public function isValid() {
      $value = $this->value;
      if (is_null($value)) {
         $value = [];
      }

      // If the field is required it can't be empty
      if ($this->isRequired() && count($value) <= 0) {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      return $this->isValidValue($value);
   }

   private function isValidValue($value) {
      $parameters = $this->getParameters();

      // Check the field matches the format regex
      if (!$parameters['range']->isNewItem()) {
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
      }

      return true;
   }

   public static function getName() {
      return __('Checkboxes', 'formcreator');
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
            $input['values'] = $this->trimValue($input['values']);
         }
      }
      if (isset($input['default_values'])) {
         $this->value = explode('\r\n', $input['default_values']);
         $this->value = array_map('trim', $this->value);
         $this->value = array_filter($this->value, function($value) {
            return ($value !== '');
         });
         $input['default_values'] = implode('\r\n', $this->value);
      }
      return $input;
   }

   public function getValueForTargetText($richText) {
      global $CFG_GLPI;

      $value = [];
      $values = $this->getAvailableValues();

      if (count($this->value) === 0) {
         return '';
      }

      foreach ($this->value as $input) {
         if (in_array($input, $values)) {
            $value[] = Toolbox::addslashes_deep($input);
         }
      }

      if ($richText) {
         $value = '<br />' . implode('<br />', $value);
      } else {
         $value = implode(', ', $value);
      }
      return $value;
   }

   public function getDocumentsForTarget() {
      return [];
   }

   public static function getPrefs() {
      return [
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
         'range'          => 1,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      ];
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['checkboxes'] = 'showFields(" . implode(', ', $prefs) . ");';";
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
      if (!is_array($this->value)) {
         // No checkbox enabled
         return ($value === '');
      }
      return in_array($value, $this->value);
   }

   public function notEquals($value) {
      return !$this->equals($value);
   }

   public function greaterThan($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
   }

   public function lessThan($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
   }

   public function isAnonymousFormCompatible() {
      return true;
   }
}
