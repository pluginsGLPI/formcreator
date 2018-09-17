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

class PluginFormcreatorUrgencyField extends PluginFormcreatorField
{
   public function displayField($canEdit = true) {
      if ($canEdit) {
         $id           = $this->fields['id'];
         $rand         = mt_rand();
         $fieldName    = 'formcreator_field_' . $id;
         $domId        = $fieldName . '_' . $rand;
         $required = $this->fields['required'] ? ' required' : '';
         Ticket::dropdownUrgency(['name'     => $fieldName,
                                  'value'    => $this->value,
                                  'comments' => false,
                                  'rand'     => $rand
         ]);
         echo PHP_EOL;
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeUrgency('$fieldName', '$rand');
         });");
      } else {
         echo Ticket::getPriorityName($this->value);
      }
   }

   public function getAnswer() {
      $values = $this->getAvailableValues();
      $value  = $this->value;
      return in_array($value, $values) ? $value : $this->fields['default_values'];
   }

   public static function getName() {
      return __('Urgency');
   }

   public function prepareQuestionInputForSave($input) {
      return $input;
   }

   public function parseAnswerValues($input) {
      $key = 'formcreator_field_' . $this->fields['id'];
      if (!is_string($input[$key])) {
         return false;
      }

       $this->value = $input[$key];
       return true;
  }

   public static function getPrefs() {
      return [
         'required'       => 1,
         'default_values' => 1,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      ];
   }

   public function getAvailableValues() {
      return [
         5 =>_x('urgency', 'Very high'),
         4 =>_x('urgency', 'High'),
         3 =>_x('urgency', 'Medium'),
         2 =>_x('urgency', 'Low'),
         1 =>_x('urgency', 'Very low'),
      ];
   }

   public function serializeValue() {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      return $this->value;
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

   public function isValid() {
      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '0') {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['urgency'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }

   public function equals($value) {
      $available = $this->getAvailableValues();
      return strcasecmp($available[$this->value], $value) === 0;
   }

   public function notEquals($value) {
      return !$this->equals($value);
   }

   public function greaterThan($value) {
      $available = $this->getAvailableValues();
      return strcasecmp($available[$this->value], $value) > 0;
   }

   public function lessThan($value) {
      return !$this->greaterThan($value) && !$this->equals($value);
   }
}
