<?php
require_once ('dropdown-field.class.php');
class actorField extends PluginFormcreatorField
{
   const IS_MULTIPLE    = true;

   public static function getName()
   {
      return _n('Actor', 'Actors', 1, 'formcreator');
   }

   public function displayField($canEdit = true)
   {
      global $CFG_GLPI;

      if ($canEdit) {
         echo '<input
                  type="hidden"
                  name="formcreator_field_' . $this->fields['id'] . '"
                  id="actor_formcreator_field_' . $this->fields['id'] . '"
                  value="" />';
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
                        }
                     });
                  });
               </script>';
         } else {
         echo $this->getAnswer();
      }
   }

   public function isValid($value)
   {
      $valueToTrim = explode(',', $value);
      $value = array();
      foreach ($valueToTrim as $item) {
         $value[] = trim($item);
      }

      // If the field is required it can't be empty
      if ($this->isRequired() && count($value) == 0) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(), false, ERROR);
         return false;
      }

      // Distinguish IDs and emails
      $unknownUsers = array();
      $idToCheck = array();
      foreach($value as $label) {
         if (ctype_digit($label) || is_int($label)) {
            // this is the ID of an existing user. Check it belongs to a reachable user
            $idToCheck[] = $label;
         } else if (!empty($label)) {
            $email = filter_var($label, FILTER_VALIDATE_EMAIL);
            if ($email === false) {
               // Email not validated
               return false;
            }
            $unknownUsers[] = $email;
         }
      }

      // Check all IDs exist
      if (count($idToCheck) > 0) {
         $user = new User();
         $rows = $user->find("`id` IN (" . implode(', ', $idToCheck). ")");
         if (count($rows) != count($idToCheck)) {
            //at least one ID is not valid
            return false;
         }
      }

      // Unknown users are valid email addresses. These emails will be imported if necessary when creatong a ticket

      // All is OK
      return true;
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
