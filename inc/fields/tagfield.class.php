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

class PluginFormcreatorTagField extends PluginFormcreatorDropdownField
{
   public function isPrerequisites() {
      return class_exists(PluginTagTag::class);
   }

   public function displayField($canEdit = true) {
      $id           = $this->fields['id'];
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $domId        = $fieldName . $rand;
      if ($canEdit) {
         if (!class_exists(PluginTagTag::class)) {
            // Plugin Tag not available
            echo '';
            return;
         }
         $required = $this->fields['required'] ? ' required' : '';

         $values = [];

         $obj = new PluginTagTag();
         $obj->getEmpty();

         $where = "(`type_menu` LIKE '%\"Ticket\"%' OR`type_menu` LIKE '%\"Change\"%' OR `type_menu` LIKE '0')";
         $where .= getEntitiesRestrictRequest('AND', getTableForItemType(PluginTagTag::class), '', '', true);

         $result = $obj->find($where, 'name');
         foreach ($result AS $id => $data) {
            $values[$id] = $data['name'];
         }

         Dropdown::showFromArray($fieldName, $values, [
            'values'              => $this->value,
            'comments'            => false,
            'rand'                => $rand,
            'multiple'            => true,
         ]);
         echo PHP_EOL;
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeTag('$fieldName', '$rand');
         });");
      } else {
         echo '<div class="form_field">';
         echo empty($this->value) ? '' : implode('<br />', json_decode($this->value));
         echo '</div>';
      }
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

   public function isValid() {
      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '') {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public function prepareQuestionInputForSave($input) {
      return $input;
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

   public static function getName() {
      return _n('Tag', 'Tags', 2, 'tag');
   }

   public static function getPrefs() {
      return [
         'required'       => 0,
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
      return "tab_fields_fields['tag'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }

   public function equals($value) {
      if (!class_exists(PluginTagTag::class)) {
         // Plugin Tag not available
         return false;
      }

      // find the tag to check for existence
      $tag = new PluginTagTag();
      $tag->getFromDBByRequest([
         'name' => $value
      ]);
      if ($tag->isNewItem()) {
         return false;
      }

      // Check it is available for the target itemtypes
      $types = json_decode($tag->fields['type_menu'], true);
      if (!isset($types[Ticket::class])
         && !isset($types[Change::class])
         && !isset($types['0'])
      ) {
         // Tag must be available for tickets, changes or all types
         // Do 0 means all ?
         return false;
      }

      // check it is in the tags if this question
      return (in_array($tag->getID(), $this->value));
   }

   public function notEquals($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
   }

   public function greaterThan($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
   }

   public function lessThan($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
   }
}
