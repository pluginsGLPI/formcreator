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

class PluginFormcreatorDescriptionField extends PluginFormcreatorField
{
   public function show($canEdit = true) {
      echo '<div class="description_field form-group" id="form-group-formcreator_field_' . $this->fields['id'] . '">';
      echo nl2br(html_entity_decode($this->fields['description']));
      echo '</div>' . PHP_EOL;
      echo Html::scriptBlock('$(function() {
            formcreatorAddValueOf(' . $this->fields['id'] . ', "");
         })');
   }

   public function isValid($value) {
      return true;
   }

   public static function getName() {
      return __('Description');
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['description'])) {
         if (strlen($input['description']) < 1) {
            Session::addMessageAfterRedirect(
               __('A description field should have a description:', 'formcreator') . ' ' . $input['name'],
               false,
               ERROR);
            return [];
         }
         $input['description'] = addslashes($input['description']);
      }

      return $input;
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
      return "tab_fields_fields['description'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
