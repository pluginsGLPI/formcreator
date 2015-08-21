<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');
require_once('dropdown-field.class.php');

class glpiselectField extends dropdownField
{

   public static function show($field, $datas, $edit = true)
   {
      parent::show($field, $datas, $edit = true);
      
      $default_value = static::getDefaultValue($field);
      
      if (!empty($default_value )) {
         echo '<script type="text/javascript">
                  Ext.onReady(function() {
                     loadFields(' . $field['id'] . ', "' . Toolbox::addslashes_deep($default_value) . '");
                  });
               </script>';
      }
   }
   
   public static function getDefaultValue($field)
   {
      $default_values = explode("\r\n", $field['default_values']);
      $default_value  = array_shift($default_values);

      if (!empty($datas['formcreator_field_' . $field['id']])) {
         $default_value = $datas['formcreator_field_' . $field['id']];
      }
      return $default_value;
   }

   public static function getName()
   {
      return _n('GLPI object', 'GLPI objects', 1, 'formcreator');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 1,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 1,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['glpiselect'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
