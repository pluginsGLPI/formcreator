<?php
require_once ('dropdown-field.class.php');
class actorField extends PluginFormcreatorField
{
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
                  value="' . $this->getAnswer() . '" />';
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
                                 url: "' . $CFG_GLPI['url_base'] . '/ajax/getDropdownUsers.php",
                                 data: {
                                    all: 0,
                                    right: "all",
                                    entity_restrict: -1,
                                    searchText: query.term,
                                    page_limit: 20,
                                    page: query.page
                                 },
                                 type: "GET",
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
      $value = json_decode($value);
      if (is_null($value)) $value = array();

      // If the field is required it can't be empty
      if ($this->isRequired() && empty($value)) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(), false, ERROR);
         return false;
      }

      // Distinguish IDs and emails
      $unknownUsers = array();
      $idToCheck = array();
      foreach($value as $id => $label) {
         if (ctype_digit($id) || is_int($id)) {
            // this is the ID of an existing user. Check it belongs to a reachable user
            $idToCheck[] = $id;
         } else {
            $email = filter_var($id, FILTER_VALIDATE_EMAIL);
            if ($email === false) {
               // Email not validated
               return false;
            }
            $unknownUsers[] = $email;
         }
      }

      // Check all IDs exist
      $user = new User();
      $rows = $user->find("`id` IN (" . implode(', ', $idToCheck). ")");
      if (count($rows) != count($idToCheck)) {
         //at least one ID is not valid
         return false;
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
