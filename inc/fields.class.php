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

      foreach (glob(dirname(__FILE__).'/fields/*field.class.php') as $class_file) {
         preg_match("#fields/(.+)field\.class.php$#", $class_file, $matches);
         $classname = 'PluginFormcreator' . ucfirst($matches[1]) . 'Field';

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
      $plugin = new Plugin();

      // Initialize array
      $tab_field_types_name     = array();
      $tab_field_types_name[''] = '---';

      // Get localized names of field types
      foreach ($tab_field_types as $field_type => $class_file) {
         $classname                         = 'PluginFormcreator' . ucfirst($field_type) . 'Field';

         if ($classname == 'tagField' &&(!$plugin->isInstalled('tag') || !$plugin->isActivated('tag'))) {
            continue;
         }

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
      $class_file = dirname(__FILE__).'/fields/'.$field['fieldtype'].'-field.class.php';
      if(is_file($class_file)) {
         include_once ($class_file);

         $classname = 'PluginFormcreator'.ucfirst($field['fieldtype']).'Field';
         if(class_exists($classname)) {
            $obj = new $classname($field, $value);
            return $obj->getAnswer();
         }
      }
      return $value;
   }


   public static function printAllTabFieldsForJS()
   {
      $tabFieldsForJS = '';
      // Get field types and file path
      $tab_field_types = self::getTypes();

      // Get field types preference for JS
      foreach ($tab_field_types as $field_type => $class_file) {
         $classname = 'PluginFormcreator' . ucfirst($field_type) . 'Field';

         if(method_exists($classname, 'getJSFields')) {
            $tabFieldsForJS .= PHP_EOL.'            '.$classname::getJSFields();
         }
      }
      return $tabFieldsForJS;
   }

   public static function showField($field, $datas = null, $edit = true)
   {
      // Get field types and file path
      $tab_field_types = self::getTypes();

      if(array_key_exists($field['fieldtype'], $tab_field_types)) {
         $fieldClass = 'PluginFormcreator'.ucfirst($field['fieldtype']).'Field';

         $plugin = new Plugin();
         if ($fieldClass == 'tagField' &&(!$plugin->isInstalled('tag') || !$plugin->isActivated('tag'))) {
            return;
         }

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
      global $DB;

      /**
       * Keep track of questions being evaluated to detect infinite loops
       */
      static $evalQuestion = array();
      if (isset($evalQuestion[$id])) {
         // TODO : how to deal a infinite loop while evaulating visibility of question ?
         return true;
      }
      $evalQuestion[$id]   = $id;

      $question   = new PluginFormcreatorQuestion();
      $question->getFromDB($id);
      $fields     = $question->fields;
      $conditions = array();

      // If the field is always shown
      if ($fields['show_rule'] == 'always') {
         return true;
      }

      // Get conditions to show or hide field
      $questionId = $fields['id'];
      $question_condition = new PluginFormcreatorQuestion_Condition();
      $rows = $question_condition->find("`plugin_formcreator_questions_id` = '$questionId'");
      $questionConditions = $question_condition->getConditionsFromQuestion($questionId);
      if (count($questionConditions) < 1) {
         // No condition defined, then always show the question
         return true;
      }
      foreach ($questionConditions as $question_condition) {
         $conditions[] = array(
               'multiple' => in_array($fields['fieldtype'], array('checkboxes', 'multiselect')),
               'logic'    => $question_condition->getField('show_logic'),
               'field'    => $question_condition->getField('show_field'),
               'operator' => $question_condition->getField('show_condition'),
               'value'    => $question_condition->getField('show_value')
            );
      }

      $return                       = false;
      $currentLogic                 = 'OR';
      $lowPrecedenceReturnPart      = false;
      $lowPrecedenceLogic           = 'OR';
      foreach ($conditions as $condition) {
         if (!isset($values[$condition['field']])) {
            return false;
         }
         if (!self::isVisible($condition['field'], $values)) {
            return false;
         }

         switch ($condition['operator']) {
            case '!=' :
               if (empty($values[$condition['field']])) {
                  $value = true;
               } else {
                  $decodedConditionField = json_decode($values[$condition['field']]);
                  if (is_array($values[$condition['field']])) {
                     $value = !in_array($condition['value'], $values[$condition['field']]);
                  } elseif ($decodedConditionField !== null && $decodedConditionField != $values[$condition['field']]) {
                     $value = !in_array($condition['value'], $decodedConditionField);
                  } else {
                     $value = $condition['value'] != $values[$condition['field']];
                  }
               }
               break;
            case '==' :
               if (empty($condition['value'])) {
                  $value = false;
               } else {
                  $decodedConditionField = json_decode($values[$condition['field']]);
                  if (is_array($values[$condition['field']])) {
                     $value = in_array($condition['value'], $values[$condition['field']]);
                  } elseif ($decodedConditionField !== null && $decodedConditionField != $values[$condition['field']]) {
                     $value = in_array($condition['value'], $decodedConditionField);
                  } else {
                     $value = $condition['value'] == $values[$condition['field']];
                  }
               }
               break;
            default:
               $decodedConditionField = json_decode($values[$condition['field']]);
               if (is_array($values[$condition['field']])) {
                  eval('$value = "'.$condition['value'].'" '.$condition['operator']
                    .' Array('.implode(',', $values[$condition['field']]).');');
               } elseif ($decodedConditionField !== null && $decodedConditionField != $values[$condition['field']]) {
                  eval('$value = "'.$condition['value'].'" '.$condition['operator']
                    .' Array(' .implode(',', $decodedConditionField).');');
               } else {
                  eval('$value = "'.$values[$condition['field']].'" '
                    .$condition['operator'].' "'.$condition['value'].'";');
               }
         }

         // Combine all condition with respect of operator precedence
         // AND has precedence over OR and XOR
         if ($currentLogic != 'AND' && $condition['logic'] == 'AND') {
            // next condition has a higher precedence operator
            // Save the current computed return and operator to use later
            $lowPrecedenceReturnPart = $return;
            $lowPrecedenceLogic = $currentLogic;
            $return = $value;
         } else {
            switch ($currentLogic) {
               case 'AND' :   $return &= $value; break;
               case 'OR'  :   $return |= $value; break;
               case 'XOR' :   $return ^= $value; break;
               default :      $return = $value;
            }
         }

         if ($currentLogic == 'AND' && $condition['logic'] != 'AND') {
            if ($lowPrecedenceLogic == 'OR') {
               $return |= $lowPrecedenceReturnPart;
            } else {
               $return ^= $lowPrecedenceReturnPart;
            }
         }

         // Use current show_logic operator for next condition, if any
         $currentLogic = $condition['logic'];
      }

      // Ensure the low precedence part is used if last condition has logic == AND
      if ($lowPrecedenceLogic == 'OR') {
         $return |= $lowPrecedenceReturnPart;
      } else {
         $return ^= $lowPrecedenceReturnPart;
      }

      unset($evalQuestion[$id]);

      // If the field is hidden by default, show it if condition is true
      if ($question->fields['show_rule'] == 'hidden') {
         return $return;

      // else show it if condition is false
      } else {
         return !$return;
      }
   }
}
