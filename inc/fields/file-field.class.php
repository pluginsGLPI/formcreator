<?php
class fileField extends PluginFormcreatorField
{
   public function displayField($canEdit = true)
   {
      if ($canEdit) {
         $required = $this->isRequired() ? ' required' : '';

         echo '<input type="hidden" class="form-control"
                  name="formcreator_field_' . $this->fields['id'] . '" value="" />' . PHP_EOL;

         echo '<input type="file" class="form-control"
                  name="formcreator_field_' . $this->fields['id'] . '"
                  id="formcreator_field_' . $this->fields['id'] . '" />';
      } else {
         $doc = new Document();
         $answer = $this->getAnswer();
         if($doc->getFromDB($answer)) {
            echo $doc->getDownloadLink();
         }
      }
   }

   public function isValid($value)
   {
      // If the field is not visible, don't check it's value
      if (!PluginFormcreatorFields::isVisible($this->fields['id'], $this->fields['answer'])) return true;

      // If the field is required it can't be empty
      if ($this->isRequired() && (empty($_FILES['formcreator_field_' . $this->fields['id']]['tmp_name'])
                                 || !is_file($_FILES['formcreator_field_' . $this->fields['id']]['tmp_name']))) {
         Session::addMessageAfterRedirect(__('A required file is missing:', 'formcreator') . ' ' . $this->fields['name'], false, ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public static function getName()
   {
      return __('File');
   }

   public static function getPrefs()
   {
      return array(
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
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['file'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
