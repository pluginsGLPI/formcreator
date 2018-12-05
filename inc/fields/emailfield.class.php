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

class PluginFormcreatorEmailField extends PluginFormcreatorField
{
   public function isPrerequisites() {
      return true;
   }

   public function displayField($canEdit = true) {
      if ($canEdit) {
         $id           = $this->fields['id'];
         $rand         = mt_rand();
         $fieldName    = 'formcreator_field_' . $id;
         $domId        = $fieldName . '_' . $rand;
         $required     = $this->fields['required'] ? ' required' : '';
         $defaultValue = Html::cleanInputText($this->value);

         echo '<input type="email" class="form-control"
                  name="' . $fieldName . '"
                  id="' . $domId . '"
                  value="' . $defaultValue . '" />';
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeEmail('$fieldName', '$rand');
         });");
      } else {
         echo $this->value;
      }
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

   public function getValueForTargetText($richText) {
      return Toolbox::addslashes_deep($this->value);
   }

   public function getDocumentsForTarget() {
      return [];
   }

   public function isValid() {
      if ($this->value == '') {
         if ($this->isRequired()) {
            Session::addMessageAfterRedirect(
               __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
               false,
               ERROR);
            return false;
         }
      } else {
         if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            Session::addMessageAfterRedirect(__('This is not a valid e-mail:', 'formcreator') . ' ' . $this->getLabel(), false, ERROR);
            return false;
         }
      }

      // All is OK
      return true;
   }

   public static function getName() {
      return _n('Email', 'Emails', 1);
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
      return "tab_fields_fields['email'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }

   public function prepareQuestionInputForSave($input) {
      $input['values'] = '';
      $this->value = $input['default_values'];
      return $input;
   }

   public function parseAnswerValues($input) {
      $key = 'formcreator_field_' . $this->fields['id'];
      if (!is_string($input[$key])) {
         return false;
      }
      if ($input[$key] === '') {
         return true;
      }
      if (!filter_var($input[$key], FILTER_VALIDATE_EMAIL)) {
         return false;
      }

       $this->value = $input[$key];
       return true;
   }

   public function equals($value) {
      return $this->value == $value;
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
