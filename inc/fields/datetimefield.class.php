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
            'value' => strtotime($this->value) != '' ? $this->value : '',
            'rand'  => $rand,
         ]);
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeDate('$fieldName', '$rand');
         });");

      } else {
         echo $this->value();
      }
   }

   public function serializeValue() {
      return '';
   }

   public function deserializeValue($value) {
      $this->value = '';
   }

   public function getValueForDesign() {
      return '';
   }

   public function isValid() {
      // If the field is required it can't be empty
      if ($this->isRequired() && (strtotime($this->value) == '')) {
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

   public function equals($value) {
      if ($this->value === '') {
         $answer = '0000-00-00 00:00';
      } else {
         $answer = $this->value;
      }
      $answerDatetime = DateTime::createFromFormat("Y-m-d H:i", $answer);
      $compareDatetime = DateTime::createFromFormat("Y-m-d H:i", $value);
      return $answerDatetime == $compareDatetime;
   }

   public function notEquals($value) {
      return !$this->equals($value);
   }

   public function greaterThan($value) {
      if (empty($this->value)) {
         $answer = '0000-00-00 00:00';
      } else {
         $answer = $this->value;
      }
      $answerDatetime = DateTime::createFromFormat("Y-m-d H:i", $answer);
      $compareDatetime = DateTime::createFromFormat("Y-m-d H:i", $value);
      return $answerDatetime > $compareDatetime;
   }

   public function lessThan($value) {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function parseAnswerValues($input) {
      $key = 'formcreator_field_' . $this->fields['id'];
      if (!is_string($input[$key])) {
         return false;
      }

      $this->value = $input[$key];
      return true;
   }
}
