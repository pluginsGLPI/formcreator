<?php
class ipField extends PluginFormcreatorField
{
   public function show($canEdit = true)
   {
      echo '<input type="hidden" class="form-control"
               name="formcreator_field_' . $this->fields['id'] . '"
               id="formcreator_field_' . $this->fields['id'] . '"
               value="' . $_SERVER["REMOTE_ADDR"] . '" />' . PHP_EOL;
   }

   public function isValid($value)
   {
      return true;
   }

   public static function getName()
   {
      return _n('IP address', 'Adresses IP', 1);
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 0,
         'default_values' => 0,
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
      return "tab_fields_fields['ip'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
