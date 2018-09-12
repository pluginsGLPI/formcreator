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
   const IS_MULTIPLE    = true;
   public function displayField($canEdit = true) {
      if ($canEdit) {
         $id    = $this->fields['id'];
         $rand  = mt_rand();
         $fieldName    = 'formcreator_field_' . $id;
         $domId        = $fieldName . '_' . $rand;
         // echo '<input type="hidden" class="form-control"
         //       name="' . $fieldName . '" value="" />' . PHP_EOL;

         $values = [];
         $values = $this->getAvailableValues();
         if (!empty($values)) {
            echo '<div class="checkboxes">';
            $i = 0;
            foreach ($values as $value) {
               if ((trim($value) != '')) {
                  $i++;
                  $current_value = null;
                  $current_value = $this->getValue();
                  echo "<div class='checkbox'>";
                  echo Html::getCheckbox([
                     'title'         => $value,
                     'id'            => $domId.'_'.$i,
                     'name'          => $fieldName . '[]',
                     'value'         => $value,
                     'zero_on_empty' => false,
                     'checked' => (!empty($current_value) && in_array($value, $current_value))
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
         $answer = null;
         $answer = $this->getAnswer();
         if (!empty($answer)) {
            if (is_array($answer)) {
               echo implode("<br />", $answer);
            } else if (is_array(json_decode($answer))) {
               echo implode("<br />", json_decode($answer));
            } else {
               echo $this->getAnswer();
            }
         } else {
            echo '';
         }
      }
   }

   public function isValid($value) {
      $value = json_decode($value);
      if (is_null($value)) {
         $value = [];
      }

      // If the field is required it can't be empty
      if ($this->isRequired() && empty($value)) {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      if (!$this->isValidValue($value)) {
         return false;
      }

      return true;
   }

   private function isValidValue($value) {
      $parameters = $this->getParameters();

      // Check the field matches the format regex
      if (!$parameters['range']->isNewItem()) {
         $rangeMin = $parameters['range']->fields['range_min'];
         $rangeMax = $parameters['range']->fields['range_max'];
         if (strlen($rangeMin) > 0 && count($value) < $rangeMin) {
            $message = sprintf(__('The following question needs of at least %d answers', 'formcreator'), $rangeMin);
            Session::addMessageAfterRedirect($message . ' ' . $this->getLabel(), false, ERROR);
            return false;
         }

         if (strlen($rangeMax) > 0 && count($value) > $rangeMax) {
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

   public function getValue() {
      if (isset($this->fields['answer'])) {
         if (!is_array($this->fields['answer']) && is_array(json_decode($this->fields['answer']))) {
            return json_decode($this->fields['answer']);
         }
         return $this->fields['answer'];
      } else {
         return explode("\r\n", $this->fields['default_values']);
      }
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
         $input['default_values'] = $this->trimValue($input['default_values']);
      }
      return $input;
   }

   public function prepareQuestionInputForTarget($input) {
      global $CFG_GLPI;

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
            $value[] = addslashes($input);
         }
      }

      if (version_compare(PluginFormcreatorCommon::getGlpiVersion(), 9.4) >= 0 || $CFG_GLPI['use_rich_text']) {
         $value = '<br />' . implode('<br />', $value);
      } else {
         $value = '\r\n' . implode('\r\n', $value);
      }
      return $value;
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
}
