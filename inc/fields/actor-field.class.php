<?php
require_once ('dropdown-field.class.php');
class actorField extends PluginFormcreatorField
{
   const IS_MULTIPLE    = true;

   public static function getName()
   {
      return _n('Actor', 'Actors', 1, 'formcreator');
   }

   public function show($canEdit = true)
   {
      $required = ($canEdit && $this->fields['required']) ? ' required' : '';

      echo '<div class="form-group ' . $required . '" id="form-group-field' . $this->fields['id'] . '">';
      echo '<label for="formcreator_field_' . $this->fields['id'] . '">';
      echo $this->getLabel();
      if($canEdit && $this->fields['required']) {
         echo ' <span class="red">*</span>';
      }
      echo '</label>';

      echo '<div class="form_field">';
      $this->displayField($canEdit);
      echo '</div>';

      echo '<div class="help-block">' . html_entity_decode($this->fields['description']) . '</div>';
      echo '</div>';
      $value = implode(',', (array_keys($this->getAnswer())));
      // $value = json_encode($this->getAnswer());
      echo '<script type="text/javascript">formcreatorAddValueOf(' . $this->fields['id'] . ', "'
            . str_replace("\r\n", "\\r\\n", addslashes($value)) . '");</script>';
   }

   public function displayField($canEdit = true)
   {
      global $CFG_GLPI;

      $readonly = $canEdit ? 'false' : 'true';
      $value = $this->getAnswer();
      $initialValue = array();
      foreach ($value as $id => $item) {
         $initialValue[] = array(
               'id'     => $id,
               'text'   => $item,
         );
      }
      $initialValue = json_encode($initialValue);
      $initialValue = '[{"id": "4"}]';
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
                     minimumInputLength: 2,
                     query: function(query) {
                        var items;
                        if (query.term.length > 0) {
                            $.ajax({
                              url: "' . $CFG_GLPI['root_doc'] . '/ajax/getDropdownUsers.php",
                              data: {
                                 all: 0,
                                 right: "all",
                                 entity_restrict: -1,
                                 searchText: query.term,
                                 page_limit: 20,
                                 page: query.page
                              },
                              type: "POST",
                              dataType: "json"
                           }).done(function(response) { query.callback(response) });
                        } else {
                           query.callback({});
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

   public function serializeDefaultValue($value) {
      $serialized = array();
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

      return json_encode(array_keys($serialized));
   }

   public function deserializeDefaultValue($value) {
      $deserialized  = array();
      $serialized = json_decode($value);
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
      $answerValue = json_decode($value, JSON_UNESCAPED_SLASHES);

      $unknownUsers = array();
      $knownUsers = array();
      $idToCheck = array();
      if ($answerValue !== null) {
         foreach($answerValue as $item) {
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
      }
      return $knownUsers + $unknownUsers;
   }

   public function isValid($value)
   {
      $sanitized = $this->sanitizeValue($value);

      $value = explode(',', $value);

      // If the field is required it can't be empty
      if ($this->isRequired() && count($value) == 0) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(), false, ERROR);
         return false;
      }

      // If an item has been removed by sanitization, then the data is not valid
      return count($sanitized) == count($value);
   }

   public function getValue()
   {
      if (isset($this->fields['answer'])) {
         return $this->sanitizeValue($this->fields['answer']);
      } else {
         return $this->sanitizeValue($this->fields['default_values']);
      }
   }

   public static function getPrefs()
   {
      return array(
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
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['actor'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
