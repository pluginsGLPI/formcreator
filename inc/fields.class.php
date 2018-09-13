<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorFields
{

   private $types = null;
   /**
    * Retrive all field types and file path
    *
    * @return array field_type => File_path
    */
   public static function getTypes() {
      $tab_field_types     = [];

      foreach (glob(dirname(__FILE__).'/fields/*field.class.php') as $class_file) {
         $matches = null;
         preg_match("#fields/(.+)field\.class.php$#", $class_file, $matches);

         if (PluginFormcreatorFields::fieldTypeExists($matches[1])) {
            $tab_field_types[strtolower($matches[1])] = $class_file;
         }
      }

      return $tab_field_types;
   }

   /**
    * Gets classe names of all known field types
    *
    * @return array field_type => classname
    */
   public static function getClasses() {
      $classes = [];
      foreach (glob(dirname(__FILE__).'/fields/*field.class.php') as $class_file) {
         $matches = null;
         preg_match("#fields/(.+)field\.class.php$#", $class_file, $matches);
         $classname = self::getFieldClassname($matches[1]);
         if (self::fieldTypeExists($matches[1])) {
            $classes[strtolower($matches[1])] = $classname;
         }
      }

      return $classes;
   }

   /**
    * Get type and name of all field types
    * @return Array     field_type => Name
    */
   public static function getNames() {
      // Get field types and file path
      $plugin = new Plugin();

      // Initialize array
      $tab_field_types_name     = [];
      $tab_field_types_name[''] = '---';

      // Get localized names of field types
      foreach (PluginFormcreatorFields::getClasses() as $field_type => $classname) {
         $classname = self::getFieldClassname($field_type);
         if ($classname == PluginFormcreatorTagField::class && !$plugin->isActivated('tag')) {
            continue;
         }

         $tab_field_types_name[$field_type] = $classname::getName();
      }

      asort($tab_field_types_name);

      return $tab_field_types_name;
   }

   public static function printAllTabFieldsForJS() {
      $tabFieldsForJS = '';
      // Get field types and file path
      $tab_field_types = self::getTypes();

      // Get field types preference for JS
      foreach (array_keys($tab_field_types) as $field_type) {
         $classname = PluginFormcreatorFields::getFieldClassname($field_type);
         if (method_exists($classname, 'getJSFields')) {
            $tabFieldsForJS .= PHP_EOL.'            '.$classname::getJSFields();
         }
      }
      return $tabFieldsForJS;
   }

   /**
    *
    * @param array $field fields of a PluginFormcreatorQuestion instance
    * @param mixed|null $data
    * @param boolean $edit
    */
   public static function showField($field, $data = null, $edit = true) {
      // Get field types and file path
      $tab_field_types = self::getTypes();

      if (array_key_exists($field['fieldtype'], $tab_field_types)) {
         $fieldClass = 'PluginFormcreator'.ucfirst($field['fieldtype']).'Field';

         $plugin = new Plugin();
         if ($fieldClass == 'PluginFormcreatorTagField' && !$plugin->isActivated('tag')) {
            return;
         }

         $obj = new $fieldClass($field, $data);
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
   public static function isVisible($id, $values) {
      /**
       * Keep track of questions being evaluated to detect infinite loops
       */
      static $evalQuestion = [];
      if (isset($evalQuestion[$id])) {
         // TODO : how to deal a infinite loop while evaulating visibility of question ?
         return true;
      }
      $evalQuestion[$id]   = $id;

      $question   = new PluginFormcreatorQuestion();
      $question->getFromDB($id);
      $conditions = [];

      // If the field is always shown
      if ($question->getField('show_rule') == 'always') {
         unset($evalQuestion[$id]);
         return true;
      }

      // Get conditions to show or hide field
      $questionId = $question->getID();
      $question_condition = new PluginFormcreatorQuestion_Condition();
      $questionConditions = $question_condition->getConditionsFromQuestion($questionId);
      if (count($questionConditions) < 1) {
         // No condition defined, then always show the question
         unset($evalQuestion[$id]);
         return true;
      }

      foreach ($questionConditions as $question_condition) {
         $conditions[] = [
            'logic'    => $question_condition->getField('show_logic'),
            'field'    => 'formcreator_field_' . $question_condition->getField('show_field'),
            'operator' => $question_condition->getField('show_condition'),
            'value'    => $question_condition->getField('show_value')
         ];
      }

      // Force the first logic operator to OR
      $conditions[0]['logic']       = 'OR';

      $return                       = false;
      $lowPrecedenceReturnPart      = false;
      $lowPrecedenceLogic           = 'OR';
      foreach ($conditions as $order => $condition) {
         $currentLogic = $condition['logic'];
         if (isset($conditions[$order + 1])) {
            $nextLogic = $conditions[$order + 1]['logic'];
         } else {
            // To ensure the low precedence return part is used at the end of the whole evaluation
            $nextLogic = 'OR';
         }
         if (!isset($values[$condition['field']])) {
            $values[$condition['field']] = '';
         }

         switch ($condition['operator']) {
            case '!=' :
               if (empty($values[$condition['field']])) {
                  $value = true;
               } else {
                  if (is_array($values[$condition['field']])) {
                     $decodedConditionField = null;
                  } else {
                     $decodedConditionField = json_decode($values[$condition['field']]);
                  }
                  if (is_array($values[$condition['field']])) {
                     $value = !in_array($condition['value'], $values[$condition['field']]);
                  } else if ($decodedConditionField !== null && $decodedConditionField != $values[$condition['field']]) {
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
                  if (is_array($values[$condition['field']])) {
                     $decodedConditionField = null;
                  } else {
                     $decodedConditionField = json_decode($values[$condition['field']]);
                  }
                  if (is_array($values[$condition['field']])) {
                     $value = in_array($condition['value'], $values[$condition['field']]);
                  } else if ($decodedConditionField !== null && $decodedConditionField != $values[$condition['field']]) {
                     $value = in_array($condition['value'], $decodedConditionField);
                  } else {
                     $value = $condition['value'] == $values[$condition['field']];
                  }
               }
               break;

            default:
               if (is_array($values[$condition['field']])) {
                  $decodedConditionField = null;
               } else {
                  $decodedConditionField = json_decode($values[$condition['field']]);
               }
               if (is_array($values[$condition['field']])) {
                  eval('$value = "'.$condition['value'].'" '.$condition['operator']
                    .' Array('.implode(',', $values[$condition['field']]).');');
               } else if ($decodedConditionField !== null && $decodedConditionField != $values[$condition['field']]) {
                  eval('$value = "'.$condition['value'].'" '.$condition['operator']
                    .' Array(' .implode(',', $decodedConditionField).');');
               } else {
                  eval('$value = "'.$values[$condition['field']].'" '
                    .$condition['operator'].' "'.$condition['value'].'";');
               }
         }

         // Combine all condition with respect of operator precedence
         // AND has precedence over OR and XOR
         if ($currentLogic != 'AND' && $nextLogic == 'AND') {
            // next condition has a higher precedence operator
            // Save the current computed return and operator to use later
            $lowPrecedenceReturnPart = $return;
            $lowPrecedenceLogic = $currentLogic;
            $return = $value;
         } else {
            switch ($currentLogic) {
               case 'AND' :
                  $return &= $value;
                  break;

               case 'OR'  :
                  $return |= $value;
                  break;

               default :
                  $return = $value;
            }
         }

         if ($currentLogic == 'AND' && $nextLogic != 'AND') {
            if ($lowPrecedenceLogic == 'OR') {
               $return |= $lowPrecedenceReturnPart;
            } else {
               $return ^= $lowPrecedenceReturnPart;
            }
         }
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
         return ($return == true);

         // else show it if condition is false
      } else {
         return !$return;
      }
   }

   /**
    * compute visibility of all fields of a form
    *
    * @param array $values    values of all fields of the form
    *                         id => mixed value of a field
    *
    * @rturn array
    */
   public static function updateVisibility($currentValues) {
      $adaptedValues = [];
      foreach ($currentValues as $key => $value) {
         $id = (int) str_replace('formcreator_field_', '', $key);
         if ($id !== 0) {
            // Ignore keys not matching a field name
            $adaptedValues[$id] = $value;
         }
      }

      $questionToShow = [];
      foreach ($currentValues as $id => $value) {
         $questionToShow[$id] = PluginFormcreatorFields::isVisible($id, $adaptedValues);
      }

      return $questionToShow;
   }

   /**
    * gets the classname for a field given its type
    *
    * @param string $type type of field to test for existence
    * @return string
    */
   public static function getFieldClassname($type) {
      return 'PluginFormcreator' . ucfirst($type) . 'Field';
   }

   /**
    * checks if a field type exists
    *
    * @param string $type type of field to test for existence
    * @return boolean
    */
   public static function fieldTypeExists($type) {
      return is_a(self::getFieldClassname($type), PluginFormcreatorFieldInterface::class, true);
   }

   /**
    * gets an instance of a field given its type
    *
    * @param string $type type of field to get
    * @param PluginFormcreatorQuestion $question question representing the field
    * @param array $data additional data
    * @return null|PluginFormcreatorFieldInterface
    */
   public static function getFieldInstance($type, PluginFormcreatorQuestion $question, $data = []) {
      $className = self::getFieldClassname($type);
      if (!self::fieldTypeExists($type)) {
         return null;
      }
      return new $className($question->fields, $data);
   }
}
