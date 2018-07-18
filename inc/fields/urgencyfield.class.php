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

class PluginFormcreatorUrgencyField extends PluginFormcreatorField
{
   public function displayField($canEdit = true) {
      if ($canEdit) {
         $rand     = mt_rand();
         $required = $this->fields['required'] ? ' required' : '';
         Ticket::dropdownUrgency(['name'     => 'formcreator_field_' . $this->fields['id'],
                                  'value'    => $this->getValue(),
                                  'comments' => false,
                                  'rand'     => $rand]
         );
         echo PHP_EOL;
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").on("select2-selecting", function(e) {
                        formcreatorChangeValueOf (' . $this->fields['id']. ', e.val);
                     });
                  });
               </script>';
      } else {
         echo Ticket::getPriorityName($this->getValue());
      }
   }

   public function getAnswer() {
      $values = $this->getAvailableValues();
      $value  = $this->getValue();
      return in_array($value, $values) ? $value : $this->fields['default_values'];
   }

   public static function getName() {
      return __('Urgency');
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['values'])) {
         $input['values'] = addslashes($input['values']);
      }
      return $input;
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
         _x('urgency', 'Very high'),
         _x('urgency', 'High'),
         _x('urgency', 'Medium'),
         _x('urgency', 'Low'),
         _x('urgency', 'Very low'),
      ];
   }

   public function getValue() {
      if (isset($this->fields['answer'])) {
         if (!is_array($this->fields['answer']) && is_array(json_decode($this->fields['answer']))) {
            return json_decode($this->fields['answer']);
         }
         return $this->fields['answer'];
      } else {
         return 3;
      }
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['urgency'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }

}
