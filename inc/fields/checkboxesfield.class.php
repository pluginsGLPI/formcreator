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
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
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
         echo '<input type="hidden" class="form-control"
                  name="formcreator_field_' . $this->fields['id'] . '" value="" />' . PHP_EOL;

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
                  echo Html::getCheckbox(['title'         => $value,
                                          'id'            => 'formcreator_field_'.$this->fields['id'].'_'.$i,
                                          'name'          => 'formcreator_field_'.$this->fields['id'] . '[]',
                                          'value'         => $value,
                                          'zero_on_empty' => false,
                                          'checked' => (!empty($current_value) && in_array($value, $current_value))]);
                  echo '<label for="formcreator_field_'.$this->fields['id'].'_'.$i.'">';
                  echo '&nbsp;'.$value;
                  echo '</label>';
                  echo "</div>";
               }
            }
            echo '</div>';
         }
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("input[name=\'formcreator_field_' . $this->fields['id']. '[]\']").on("change", function() {
                        var tab_values = new Array();
                        jQuery("input[name=\'formcreator_field_' . $this->fields['id']. '[]\']").each(function() {
                           if (this.checked == true) {
                              tab_values.push(this.value);
                           }
                        });
                        formcreatorChangeValueOf (' . $this->fields['id']. ', tab_values);
                     });
                  });
               </script>';

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

         // Min range not set or number of selected item lower than min
      } else if (!empty($this->fields['range_min']) && (count($value) < $this->fields['range_min'])) {
         $message = sprintf(__('The following question needs of at least %d answers', 'formcreator'), $this->fields['range_min']);
         Session::addMessageAfterRedirect($message . ' ' . $this->getLabel(), false, ERROR);
         return false;

         // Max range not set or number of selected item greater than max
      } else if (!empty($this->fields['range_max']) && (count($value) > $this->fields['range_max'])) {
          $message = sprintf(__('The following question does not accept more than %d answers', 'formcreator'), $this->fields['range_max']);
          Session::addMessageAfterRedirect($message . ' ' . $this->getLabel(), false, ERROR);
          return false;

         // All is OK
      } else {
          return true;
      }
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

      if ($CFG_GLPI['use_rich_text']) {
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
}
