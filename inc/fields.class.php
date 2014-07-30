<?php
class PluginFormcreatorFields
{
   /**
    * Retrive all field types and file path
    *
    * @return Array     field_type => File_path
    */
   public static function getTypes()
   {
      $tab_field_types     = array();

      foreach (glob(dirname(__FILE__) . '/fields/*-field.class.php') as $class_file) {
         preg_match("/fields.(.+)-field\.class.php/", $class_file, $matches);
         $classname = $matches[1] . 'Field';

         include_once($class_file);

         if(class_exists($classname)) {
            $tab_field_types[strtolower($matches[1])] = $class_file;
         }
      }

      return $tab_field_types;
   }

   /**
    * Get type and name of all field types
    *
    * @return Array     field_type => Name
    */
   public static function getNames()
   {
      // Get field types and file path
      $tab_field_types = self::getTypes();

      // Initialize array
      $tab_field_types_name     = array();
      $tab_field_types_name[''] = '---';

      // Get localized names of field types
      foreach ($tab_field_types as $field_type => $class_file) {
         $classname = $field_type . 'Field';

         if(method_exists($classname, 'getName')) {
            $tab_field_types_name[$field_type] = $classname::getName();
         }
      }

      return $tab_field_types_name;
   }

   /**
    * Get field value to display
    *
    * @param Field $field     Field object to display
    *
    * @return String          field_value
    */
   public static function getValue($field, $value)
   {
      $class_file = dirname(__FILE__) . '/fields/' . $field['fieldtype'] . '-field.class.php';
      if(is_file($class_file)) {
         include_once ($class_file);

         $classname = $field['fieldtype'] . 'Field';
         if(class_exists($classname)) {
            return $classname::displayValue($value, $field['values']);
         }
      }
      return $value;
   }


   public static function printAllTabFieldsForJS()
   {
      // Get field types and file path
      $tab_field_types = self::getTypes();

      // Get field types preference for JS
      foreach ($tab_field_types as $field_type => $class_file) {
         $classname = $field_type . 'Field';

         if(method_exists($classname, 'getJSFields')) {
            echo PHP_EOL . '            ' . $classname::getJSFields();
         }
      }
   }

   public static function showField($field, $datas = null)
   {
      // Get field types and file path
      $tab_field_types = self::getTypes();

      if(array_key_exists($field['fieldtype'], $tab_field_types)) {
         $fieldClass = $field['fieldtype'] . 'Field';
         $fieldClass::show($field, $datas);
      }
   }
}
