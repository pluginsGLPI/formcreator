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
         $classname                         = $field_type . 'Field';
         $tab_field_types_name[$field_type] = $classname::getName();
      }

      asort($tab_field_types_name);

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
            $obj = new $classname($field, $value);
            return $obj->getAnswer();
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

   public static function showField($field, $datas = null, $edit = true)
   {
      // Get field types and file path
      $tab_field_types = self::getTypes();

      if(array_key_exists($field['fieldtype'], $tab_field_types)) {
         $fieldClass = $field['fieldtype'] . 'Field';
         $obj = new $fieldClass($field, $datas);
         $obj->show($edit);
      }
   }

   /**
    * Check if a field should be shown or not
    *
    * @param   Integer     $id         ID of the current question
    * @param   Array       $values     Array of current fields values (id => value)
    * @return  boolean                 Should be shown or not
    */
   public static function isVisible($id, $values)
   {
      $question   = new PluginFormcreatorQuestion();
      $question->getFromDB($id);
      $fields     = $question->fields;
      $conditions = array();

      // If the field is always shown
      if ($fields['show_rule'] == 'always') return true;

      // Get conditions to show or hide field
      $query = "SELECT `show_logic`, `show_field`, `show_condition`, `show_value`
                FROM glpi_plugin_formcreator_questions_conditions
                WHERE `plugin_formcreator_questions_id` = {$fields['id']}";
      $result = $GLOBALS['DB']->query($query);
      while ($line = $GLOBALS['DB']->fetch_array($result)) {
         $conditions[] = array(
               'multiple' => in_array($fields['fieldtype'], array('checkboxes', 'multiselect')),
               'logic'    => $line['show_logic'],
               'field'    => $line['show_field'],
               'operator' => $line['show_condition'],
               'value'    => $line['show_value']
            );
      }

      foreach ($conditions as $condition) {
         if (!isset($values[$condition['field']]))             return false;
         if (!self::isVisible($condition['field'], $values))   return false;

         switch ($condition['operator']) {
            case '!=' :
               if (empty($values[$condition['field']])) {
                  $value = true;
               } else {
                  if (is_array($values[$condition['field']])) {
                     $value = !in_array($condition['value'], $values[$condition['field']]);
                  } elseif (!is_null(json_decode($values[$condition['field']]))) {
                     $value = !in_array($condition['value'], json_decode($values[$condition['field']]));
                  } else {
                     $value = $condition['value'] != $values[$condition['field']];
                  }
               }
               break;
            case '==' :
               if (empty($condition['value'])) {
                  $value = false;
               } else {
                  if (is_array($values[$condition['field']])) {
                     $value = in_array($condition['value'], $values[$condition['field']]);
                  } elseif (!is_null(json_decode($values[$condition['field']]))) {
                     $value = in_array($condition['value'], json_decode($values[$condition['field']]));
                  } else {
                     $value = $condition['value'] == $values[$condition['field']];
                  }
               }
               break;
            default:
               if (is_array($values[$condition['field']])) {
                  eval('$value = "' . $condition['value'] . '" ' . $condition['operator']
                     . ' Array(' . implode(',', $values[$condition['field']]) . ');');
               } elseif (!is_null(json_decode($values[$condition['field']]))) {
                  eval('$value = "' . $condition['value'] . '" ' . $condition['operator']
                     . ' Array(' .implode(',', json_decode($values[$condition['field']])) . ');');
               } else {
                  eval('$value = "' . $values[$condition['field']] . '" '
                     . $condition['operator'] . ' "' . $condition['value'] . '";');
               }
         }
         switch ($condition['logic']) {
            case 'AND' :   $return &= $value; break;
            case 'OR'  :   $return |= $value; break;
            case 'XOR' :   $return ^= $value; break;
            default :      $return = $value;
         }
      }

      // If the field is hidden by default, show it if condition is true
      if ($question->fields['show_rule'] == 'hidden') {
         return $return;

      // else show it if condition is false
      } else {
         return !$return;
      }
   }
}
