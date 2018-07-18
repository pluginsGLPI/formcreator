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

class PluginFormcreatorTextField extends PluginFormcreatorField
{
   public function isValid($value) {
      if (!parent::isValid($value)) {
         return false;
      }

      $value = utf8_decode(stripcslashes($value));

      // Min range not set or text length longer than min length
      if (!empty($this->fields['range_min']) && strlen($value) < $this->fields['range_min']) {
         Session::addMessageAfterRedirect(sprintf(__('The text is too short (minimum %d characters):', 'formcreator'), $this->fields['range_min']) . ' ' . $this->fields['name'], false, ERROR);
         return false;

         // Max range not set or text length shorter than max length
      } else if (!empty($this->fields['range_max']) && strlen($value) > $this->fields['range_max']) {
         Session::addMessageAfterRedirect(sprintf(__('The text is too long (maximum %d characters):', 'formcreator'), $this->fields['range_max']) . ' ' . $this->fields['name'], false, ERROR);
         return false;

         // Specific format not set or well match
      } else if (!empty($this->fields['regex']) && !preg_match($this->fields['regex'], $value)) {
          Session::addMessageAfterRedirect(__('Specific format does not match:', 'formcreator') . ' ' . $this->fields['name'], false, ERROR);
          return false;
      }

       // All is OK
      return true;
   }

   public static function getName() {
      return __('Text', 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['default_values'])) {
         $input['default_values'] = addslashes($input['default_values']);
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
      return "tab_fields_fields['text'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
