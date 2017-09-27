<?php
class PluginFormcreatorActorField extends PluginFormcreatorField
{
   const IS_MULTIPLE    = true;

   public static function getName() {
      return _n('Actor', 'Actors', 1, 'formcreator');
   }

   public function displayField($canEdit = true) {
      global $CFG_GLPI;

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
               name="formcreator_field_' . $this->fields['id'] . '"
               id="actor_formcreator_field_' . $this->fields['id'] . '"
               value=" " />';
      echo '<script type="text/javascript">
               jQuery(document).ready(function() {
                  $("#actor_formcreator_field_' . $this->fields['id'] . '").select2({
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
}
