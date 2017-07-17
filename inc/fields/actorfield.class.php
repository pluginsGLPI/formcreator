<?php
/**
 * LICENSE
 *
 * Copyright © 2011-2018 Teclib'
 *
 * This file is part of Formcreator Plugin for GLPI.
 *
 * Formcreator is a plugin that allow creation of custom, easy to access forms
 * for users when they want to create one or more GLPI tickets.
 *
 * Formcreator Plugin for GLPI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2018 Teclib
 * @license   GPLv2 https://www.gnu.org/licenses/gpl2.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ------------------------------------------------------------------------------
 */
class PluginFormcreatorActorField extends PluginFormcreatorField
{
   const IS_MULTIPLE    = true;

   public static function getName() {
      return _n('Actor', 'Actors', 1, 'formcreator');
   }

   public function displayField($canEdit = true) {
      global $CFG_GLPI;

      $rand       = mt_rand();
      $readonly = $canEdit ? 'false' : 'true';
      if (isset($this->fields['answer'])) {
         $value = $this->sanitizeValue($this->fields['answer']);
      } else {
         $value = $this->sanitizeValue($this->fields['default_values']);
      }
      $initialValue = [];
      foreach ($value as $id => $item) {
         $initialValue[] = [
            'id'     => $id,
            'text'   => $item,
         ];
      }
      $initialValue = json_encode($initialValue);
      // Value needs to be non empty to allow execition of select2's initSelection
      echo '<input
               type="hidden"
               name="formcreator_field_' . $this->fields['id'] . $rand . '"
               id="actor_formcreator_field_' . $this->fields['id'] . $rand . '"
               value="" />';
      echo '<script type="text/javascript">
               $(function() {
                  $("#actor_formcreator_field_' . $this->fields['id'] . $rand . '").select2({
                     multiple: true,
                     tokenSeparators: [",", ";"],
                     minimumInputLength: 0,
                     ajax: {
                        url: "' . $CFG_GLPI['root_doc'] . '/ajax/getDropdownUsers.php",
                        type: "POST",
                        dataType: "json",
                        data: function (term, page) {
                           return {
                              entity_restrict: -1,
                              searchText: term,
                              page_limit: 100,
                              page: page
                           }
                        },
                        results: function (data, page) {
                           var more = (data.count >= 100);
                           return {results: data.results, more: more};
                        }
                     },
                     createSearchChoice: function itemCreator(term, data) {
                        if ($(data).filter(function() {
                           return this.text.localeCompare(term) === 0;
                        }).length === 0) {
                           return { id: term, text: term };
                        }
                     },
                     initSelection: function (element, callback) {
                        callback(JSON.parse(\'' . $initialValue . '\'));
                     }
                  })
                  $("#actor_formcreator_field_' . $this->fields['id'] . '").select2("readonly", ' . $readonly . ');
                  $("#actor_formcreator_field_' . $this->fields['id'] . $rand . '").on("change", function(e) {
                     var selectedValues = $("#actor_formcreator_field_' . $this->fields['id'] . $rand . '").val();
                     formcreatorChangeValueOf (' . $this->fields['id']. ', selectedValues);
                  });
               });
            </script>';
   }

   public function serializeValue($value) {
      $serialized = [];
      $value = explode("\r\n", $value);
      foreach ($value as $item) {
         if (filter_var($item, FILTER_VALIDATE_EMAIL)) {
            // a single email address
            $serialized[$item] = $item;
         } else {
            $user = new User();
            $user->getFromDBbyName($item);
            if (!$user->isNewItem()) {
               // A user known in the DB
               $serialized[$user->getID()] = $item;
            }
         }
      }

      return implode(',', (array_keys($serialized)));
   }

   public function deserializeValue($value) {
      $deserialized  = [];
      $serialized = explode(',', $value);
      if ($serialized !== null) {
         foreach ($serialized as $item) {
            $item = trim($item);
            if (filter_var($item, FILTER_VALIDATE_EMAIL) !== false) {
               $deserialized[$item] = $item;
            } else if (!empty($item) && ctype_digit($item) && intval($item)) {
               $user = new User();
               $user->getFromDB($item);
               if (!$user->isNewItem()) {
                  // A user known in the DB
                  $deserialized[$user->getID()] = $user->getField('name');
               }
            }
         }
      }

      return implode("\r\n", $deserialized);
   }

   protected function sanitizeValue($value) {
      $value = trim($value);
      $answerValue = array_filter(explode(',', $value));

      $unknownUsers = [];
      $knownUsers = [];
      $idToCheck = [];
      foreach ($answerValue as $item) {
         $item = trim($item);
         if (filter_var($item, FILTER_VALIDATE_EMAIL) !== false) {
            $unknownUsers[$item] = $item;
         } else if (!empty($item) && ctype_digit($item) && intval($item)) {
            $user = new User();
            $user->getFromDB($item);
            if (!$user->isNewItem()) {
               // A user known in the DB
               $knownUsers[$user->getID()] = $user->getField('name');
            }
         }
      }
      return $knownUsers + $unknownUsers;
   }

   public function isValid($value) {
      $sanitized = $this->sanitizeValue($value);

      // Ignore empty values
      $value = trim($value);
      $value = array_filter(explode(',', $value));

      // If the field is required it can't be empty
      if ($this->isRequired() && count($value) == 0) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(), false, ERROR);
         return false;
      }

      // If an item has been removed by sanitization, then the data is not valid
      if (count($sanitized) != count($value)) {
         Session::addMessageAfterRedirect(__('Invalid value:', 'formcreator') . ' ' . $this->getLabel(), false, ERROR);
         return false;
      }
      return true;
   }

   public function getValue() {
      if (isset($this->fields['answer'])) {
         $value = $this->sanitizeValue($this->fields['answer']);
      } else {
         $value = $this->sanitizeValue($this->fields['default_values']);
      }

      return implode(',', $value);
   }

   public static function getPrefs() {
      return [
         'required'       => 1,
         'default_values' => 1,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 0,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      ];
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['actor'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }

   public function equals($value) {
      $user = new User();
      if (!$user->getFromDBByName($value)) {
         return false;
      }
      if (is_array($this->fields['answer'])) {
         $users = explode(',',  $this->fields['answer']);
         foreach ($users as &$user) {
            $user = (int) $user;
         }

         // Remove duplicates IDs
         $users = array_unique($users);
      } else {
         $users = [(int) $this->fields['answer']];
      }
      return in_array($user->getID(), $users);
   }

   public function greaterThan($value) {
      $user = new User();
      if (!$user->getFromDBByName($value)) {
         return false;
      }
      $users = explode($this->fields['answer']);
      foreach ($users as &$user) {
         $user = (int) $user;
      }

      // Remove duplicates IDs
      $users = array_unique($users);

      foreach ($users as $user) {
         if ($user <= $user->getID()) {
            // At least one user has an ID less than or equal to the value to compare
            return false;
         }
      }

      return true;
   }
}
