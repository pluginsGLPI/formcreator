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

class PluginFormcreatorDropdownField extends PluginFormcreatorField
{
   public function displayField($canEdit = true) {
      if ($canEdit) {
         if (!empty($this->fields['values'])) {
            $rand     = mt_rand();
            $required = $this->fields['required'] ? ' required' : '';
            $decodedValues = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
            if ($decodedValues === null) {
               $itemtype = $this->fields['values'];
            } else {
               $itemtype = $decodedValues['itemtype'];
            }

            $dparams = ['name'     => 'formcreator_field_' . $this->fields['id'],
                        'value'    => $this->getValue(),
                        'comments' => false,
                        'rand'     => $rand];

            if ($itemtype == "User") {
               $dparams['right'] = 'all';
            } else if ($itemtype == "ITILCategory") {
               $dparams['condition'] = '1';
               if (isset ($_SESSION['glpiactiveprofile']['interface'])
                   && $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk') {
                  $dparams['condition'] .= " AND `is_helpdeskvisible` = '1'";
               }
               switch ($decodedValues['show_ticket_categories']) {
                  case 'request':
                     $dparams['condition'] .= " AND `is_request` = '1'";
                     break;
                  case 'incident':
                     $dparams['condition'] .= " AND `is_incident` = '1'";
                     break;
               }
               if (isset($decodedValues['show_ticket_categories_depth'])
                   && $decodedValues['show_ticket_categories_depth'] > 0) {
                  $dparams['condition'] .= " AND `level` <= '" . $decodedValues['show_ticket_categories_depth'] . "'";
               }
            }

            $itemtype::dropdown($dparams);
         }
         echo PHP_EOL;
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("#dropdown_formcreator_field_' . $this->fields['id'] . $rand . '").on("select2-selecting", function(e) {
                        formcreatorChangeValueOf (' . $this->fields['id']. ', e.val);
                     });
                  });
               </script>';
      } else {
         echo $this->getAnswer();
      }
   }

   public function getAnswer() {
      $value = $this->getValue();
      $DbUtil = new DbUtils();
      if ($this->fields['values'] == 'User') {
         return $DbUtil->getUserName($value);
      } else {
         $decodedValues = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
         if (!isset($decodedValues['itemtype'])) {
            return Dropdown::getDropdownName($DbUtil->getTableForItemType($this->fields['values']), $value);
         } else {
            return Dropdown::getDropdownName($DbUtil->getTableForItemType($decodedValues['itemtype']), $value);
         }
      }
   }

   public function prepareQuestionInputForTarget($input) {
      $DbUtil = new DbUtils();
      if ($this->fields['values'] == User::class) {
         $value = $DbUtil->getUserName($input);
      } else {
         $decodedValues = json_decode($this->fields['values'], JSON_OBJECT_AS_ARRAY);
         if (!isset($decodedValues['itemtype'])) {
            $value = Dropdown::getDropdownName($DbUtil->getTableForItemType($this->fields['values']), $input);
         } else {
            $value = Dropdown::getDropdownName($DbUtil->getTableForItemType($decodedValues['itemtype']), $input);
         }
      }

      return addslashes($value);
   }

   public static function getName() {
      return _n('Dropdown', 'Dropdowns', 1);
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['dropdown_values'])) {
         if (empty($input['dropdown_values'])) {
            Session::addMessageAfterRedirect(
                  __('The field value is required:', 'formcreator') . ' ' . $input['name'],
                  false,
                  ERROR);
            return [];
         }
         $allowedDropdownValues = [];
         foreach (Dropdown::getStandardDropdownItemTypes() as $categoryOfTypes) {
            $allowedDropdownValues = array_merge($allowedDropdownValues, array_keys($categoryOfTypes));
         }
         if (!in_array($input['dropdown_values'], $allowedDropdownValues)) {
            Session::addMessageAfterRedirect(
                  __('Invalid dropdown type:', 'formcreator') . ' ' . $input['name'],
                  false,
                  ERROR);
            return [];
         }
         $input['values'] = [
            'itemtype' => $input['dropdown_values'],
         ];
         if ($input['dropdown_values'] == 'ITILCategory') {
            $input['values']['show_ticket_categories'] = $input['show_ticket_categories'];
            if ($input['show_ticket_categories_depth'] != (int) $input['show_ticket_categories_depth']) {
               $input['values']['show_ticket_categories_depth'] = 0;
            } else {
               $input['values']['show_ticket_categories_depth'] = $input['show_ticket_categories_depth'];
            }
         }
         $input['values'] = json_encode($input['values']);
         unset($input['show_ticket_categories']);
         unset($input['show_ticket_categories_depth']);
         $input['default_values'] = isset($input['dropdown_default_value']) ? $input['dropdown_default_value'] : '';
      }
      return $input;
   }

   public static function getPrefs() {
      return [
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 1,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 1,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      ];
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['dropdown'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
