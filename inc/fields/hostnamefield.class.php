<?php
class PluginFormcreatorHostnameField extends PluginFormcreatorField
{
   public function show($canEdit = true) {
      $id           = $this->fields['id'];
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $domId        = $fieldName . '_' . $rand;
      if ($canEdit) {
         $hostname = gethostbyaddr(Toolbox::getRemoteIpAddress());
         echo '<input type="hidden" class="form-control"
            name="' . $fieldName . '"
            id="' . $domId . '"
            value="' . $hostname . '" />' . PHP_EOL;
      } else {
         parent::show($canEdit);
      }
   }

   public function isValid($value) {
      return true;
   }

   public static function getName() {
      return _n('Hostname', 'Hostname', 1);
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
      return "tab_fields_fields['hostname'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
