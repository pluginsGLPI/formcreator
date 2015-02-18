<?php
class emailField extends PluginFormcreatorField
{
   public function displayField($canEdit = true)
   {
      if ($canEdit) {
         $required = $this->fields['required'] ? ' required' : '';

         echo '<input type="email" class="form-control"
                  name="formcreator_field_' . $this->fields['id'] . '"
                  id="formcreator_field_' . $this->fields['id'] . '"
                  value="' . $this->getValue() . '"
                  onchange="formcreatorChangeValueOf(' . $this->fields['id'] . ', this.value);" />';
      } else {
         echo $this->getAnswer();
      }
   }

   public function isValid($value)
   {
      if (!parent::isValid($value)) return false;

      // Specific format not set or well match
      if(!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
         Session::addMessageAfterRedirect(__('This is not a valid e-mail:', 'formcreator') . ' ' . $this->getLabel(), false, ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public static function getName()
   {
      return _n('Email', 'Emails', 1);
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
      return "tab_fields_fields['email'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
