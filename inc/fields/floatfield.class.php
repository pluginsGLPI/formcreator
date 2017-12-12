<?php
/**
 * LICENSE
 *
 * Copyright © 2011-2018 Teclib'
 *
 * This file is part of Formcreator Plugin for GLPI.
 *
 * Formcreator is a plugin that allow creation of custom, easy to access forms
 * for users when they want to create one or more GLPI tickets.
 *
 * Formcreator Plugin for GLPI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2018 Teclib
 * @license   GPLv2 https://www.gnu.org/licenses/gpl2.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ------------------------------------------------------------------------------
 */
class PluginFormcreatorFloatField extends PluginFormcreatorField
{
   public function isValid($value) {
      if (!parent::isValid($value)) {
         return false;
      }

      // Not a number
      if (!empty($value) && !is_numeric($value)) {
         Session::addMessageAfterRedirect(__('This is not a number:', 'formcreator') . ' ' . $this->fields['name'], false, ERROR);
         return false;

         // Min range not set or text length longer than min length
      } else if (!empty($this->fields['range_min']) && ($value < $this->fields['range_min'])) {
         $message = sprintf(__('The following number must be greater than %d:', 'formcreator'), $this->fields['range_min']);
         Session::addMessageAfterRedirect($message . ' ' . $this->fields['name'], false, ERROR);
         return false;

         // Max range not set or text length shorter than max length
      } else if (!empty($this->fields['range_max']) && ($value > $this->fields['range_max'])) {
         $message = sprintf(__('The following number must be lower than %d:', 'formcreator'), $this->fields['range_max']);
         Session::addMessageAfterRedirect($message . ' ' . $this->fields['name'], false, ERROR);
         return false;

         // Specific format not set or well match
      } else if (!empty($this->fields['regex']) && !preg_match($this->fields['regex'], $value)) {
         Session::addMessageAfterRedirect(__('Specific format does not match:', 'formcreator') . ' ' . $this->fields['name'], false, ERROR);
         return false;

         // All is OK
      } else {
         return true;
      }
   }

   public static function getName() {
      return __('Float', 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['range_min'])
          && isset($input['range_max'])
          && isset($input['default_values'])) {
         $input['default_values'] = !empty($input['default_values'])
                                  ? (float) str_replace(',', '.', $input['default_values'])
                                  : null;
         $input['range_min']      = !empty($input['range_min'])
                                  ? (float) str_replace(',', '.', $input['range_min'])
                                  : null;
         $input['range_max']      = !empty($input['range_max'])
                                  ? (float) str_replace(',', '.', $input['range_max'])
                                  : null;
      }
      return $input;
   }

   public static function getPrefs() {
      return [
         'required'       => 1,
         'default_values' => 1,
         'values'         => 0,
         'range'          => 1,
         'show_empty'     => 0,
         'regex'          => 1,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      ];
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['float'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }

   public function equals($value) {
      return (float) $this->getValue() == (float) $value;
   }

   public function greaterThan($value) {
      return (float) $this->getValue() > (float) $value;
   }
}
