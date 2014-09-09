<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class hiddenField implements Field
{
   public static function show($field, $datas)
   {
      echo '<input type="hidden" class="form-control"
               name="formcreator_field_' . $field['id'] . '"
               id="formcreator_field_' . $field['id'] . '"
               value="' . $field['default_values'] . '" />' . PHP_EOL;
   }

   public static function displayValue($value, $values)
   {
      return $value;
   }

   public static function isValid($field, $value, $datas)
   {
      return true;
   }

   public static function getName()
   {
      return _n('Hidden field', 'Hidden fields', 1);
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 0,
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
      return "tab_fields_fields['hidden'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
