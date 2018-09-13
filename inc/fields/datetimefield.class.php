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

class PluginFormcreatorDatetimeField extends PluginFormcreatorField
{
   public function displayField($canEdit = true) {
      if ($canEdit) {
         $id        = $this->fields['id'];
         $rand      = mt_rand();
         $fieldName = 'formcreator_field_' . $id;
         $domId     = $fieldName . '_' . $rand;
         $required  = ($canEdit && $this->fields['required']) ? ' required' : '';

         Html::showDateTimeField($fieldName, [
            'value' => $this->getValue(),
            'rand'  => $rand,
         ]);
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeDate('$fieldName', '$rand');
         });");

      } else {
         echo $this->getAnswer();
      }
   }

   public function getValue() {
      if (isset($this->fields['answer'])) {
         $date = $this->fields['answer'];
      } else {
         $date = $this->fields['default_values'];
      }
      return (strtotime($date) != '') ? $date : null;
   }

   public function getAnswer() {
      return Html::convDateTime($this->getValue());
   }

   public function isValid($value) {
      // If the field is required it can't be empty
      if ($this->isRequired() && (strtotime($value) == '')) {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public static function getName() {
      return __('Datetime', 'formcreator');
   }

   public static function getPrefs() {
      return [
         'required'       => 1,
         'default_values' => 0,
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

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['datetime'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
