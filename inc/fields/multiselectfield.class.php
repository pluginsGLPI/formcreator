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

class PluginFormcreatorMultiSelectField extends PluginFormcreatorSelectField
{
   const IS_MULTIPLE    = true;

   public function isValid($value) {
      $value = json_decode($value);
      if (is_null($value)) {
         $value = [];
      }

      // If the field is required it can't be empty
      if ($this->isRequired() && empty($value)) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(), false, ERROR);
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
      }

      // All is OK
      return true;
   }

   public function displayField($canEdit = true) {
      if ($canEdit) {
         parent::displayField($canEdit);
      } else {
         $answer = $this->getAnswer();
         echo empty($answer) ? '' : implode('<br />', $answer);
      }
   }

   public function getAnswer() {
      $return = [];
      $values = $this->getAvailableValues();
      $value  = $this->getValue();

      if (empty($value)) {
         return '';
      }

      if (is_array($value)) {
         $tab_values = $value;
      } else if (is_array(json_decode($value))) {
         $tab_values = json_decode($value);
      } else {
         $tab_values = [$value];
      }

      foreach ($tab_values as $value) {
         if (in_array($value, $values)) {
            $return[] = $value;
         }
      }
      return $return;
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

   public static function getName() {
      return __('Multiselect', 'formcreator');
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
      return "tab_fields_fields['multiselect'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
