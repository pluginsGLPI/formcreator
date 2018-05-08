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

class PluginFormcreatorSelectField extends PluginFormcreatorField
{
   public function displayField($canEdit = true) {
      if ($canEdit) {
         $rand       = mt_rand();
         $tab_values = [];
         $required   = $this->fields['required'] ? ' required' : '';
         $values     = $this->getAvailableValues();

         if (!empty($this->fields['values'])) {
            foreach ($values as $value) {
               if ((trim($value) != '')) {
                  if (version_compare(GLPI_VERSION, '9.2.1') <= 0) {
                     $tab_values[Html::entities_deep($value)] = $value;
                  } else {
                     $tab_values[$value] = $value;
                  }
               }
            }

            if ($this->fields['show_empty']) {
               $tab_values = ['' => '-----'] + $tab_values;
            }
            Dropdown::showFromArray('formcreator_field_' . $this->fields['id'], $tab_values, [
               'value'     => static::IS_MULTIPLE ? '' : $this->getValue(),
               'values'    => static::IS_MULTIPLE ? $this->getValue() : [],
               'rand'      => $rand,
               'multiple'  => static::IS_MULTIPLE,
            ]);
         }
         echo PHP_EOL;
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").on("change", function(e) {
                        var selectedValues = jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").val();
                        formcreatorChangeValueOf (' . $this->fields['id']. ', selectedValues);
                     });
                  });
               </script>';
      } else {
         echo nl2br($this->getAnswer());
         echo PHP_EOL;
      }
   }

   public function getAnswer() {
      $values = $this->getAvailableValues();
      $value  = $this->getValue();
      return in_array($value, $values) ? $value : $this->fields['default_values'];
   }

   public static function getName() {
      return __('Select', 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['values'])) {
         if (empty($input['values'])) {
            Session::addMessageAfterRedirect(
               __('The field value is required:', 'formcreator') . ' ' . $input['name'],
               false,
               ERROR);
            return [];
         } else {
            // trim values
            $input['values'] = $this->trimValue($input['values']);
         }
      }
      if (isset($input['default_values'])) {
         // trim values
         $input['default_values'] = $this->trimValue($input['default_values']);
      }
      return $input;
   }

   public static function getPrefs() {
      return [
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
         'range'          => 0,
         'show_empty'     => 1,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      ];
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['select'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
