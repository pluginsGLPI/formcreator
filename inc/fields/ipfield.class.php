<?php
class PluginFormcreatorIpField extends PluginFormcreatorField
{
   public function show($canEdit = true) {
      if (method_exists('Toolbox', 'getRemoteIpAddress')) {
         $ip = Toolbox::getRemoteIpAddress();
      } else {
         $ip = (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"]
                                                        : $_SERVER["REMOTE_ADDR"]);
      }
      echo '<input type="hidden" class="form-control"
               name="formcreator_field_' . $this->fields['id'] . '"
               id="formcreator_field_' . $this->fields['id'] . '"
               value="' . $ip . '" />' . PHP_EOL;
   }

   public function isValid($value) {
      return true;
   }

   public static function getName() {
      return _n('IP address', 'Adresses IP', 1);
   }

   public static function getPrefs() {
      return [
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
      ];
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['ip'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
