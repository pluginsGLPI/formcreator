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

class PluginFormcreatorDateField extends PluginFormcreatorField
{
   public function displayField($canEdit = true) {
      if ($canEdit) {
         $required = ($canEdit && $this->fields['required']) ? ' required' : '';
         $rand     = mt_rand();

         Html::showDateField('formcreator_field_' . $this->fields['id'], [
            'value' => $this->getValue(),
            'rand'  => $rand,
         ]);
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     $( "#showdate' . $rand . '" ).on("change", function() {
                        formcreatorChangeValueOf(' . $this->fields['id'] . ', this.value);
                     });
                     $( "#resetdate' . $rand . '" ).on("click", function() {
                        formcreatorChangeValueOf(' . $this->fields['id'] . ', "");
                     });
                  });
               </script>';

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
      return Html::convDate($this->getValue());
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
      return __('Date');
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
      return "tab_fields_fields['date'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
