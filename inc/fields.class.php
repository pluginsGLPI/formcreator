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
 * @copyright Copyright © 2011 - 2019 Teclib'
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
    * Check if a question should be shown or not
    *
    * @param   integer     $id         ID of the question tested for visibility
    * @param   array       $fields     Array of fields instances (question id => instance)
    * @return  boolean                 If true the question should be visible
    */
   public static function isVisible($id, $fields) {
      /**
       * Keep track of questions being evaluated to detect infinite loops
       */
      static $evalQuestion = [];
      if (isset($evalQuestion[$id])) {
         // TODO : how to deal a infinite loop while evaluating visibility of question ?
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
            'logic'    => $question_condition->fields['show_logic'],
            'field'    => $question_condition->fields['show_field'],
            'operator' => $question_condition->fields['show_condition'],
            'value'    => $question_condition->fields['show_value']
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

         // TODO: find the best behavior if the question does not exists
         $conditionField = $fields[$condition['field']];

         switch ($condition['operator']) {
            case '!=' :
               if (!$conditionField->isPrerequisites()) {
                  return true;
               }
               try {
                  $value = self::isVisible($conditionField->getQuestionId(), $fields) && $conditionField->notEquals($condition['value']);
               } catch (PluginFormcreatorComparisonException $e) {
                  $value = false;
               }
               break;

            case '==' :
               if (!$conditionField->isPrerequisites()) {
                  return false;
               }
               try {
                  $value = self::isVisible($conditionField->getQuestionId(), $fields) && $conditionField->equals($condition['value']);
               } catch (PluginFormcreatorComparisonException $e) {
                  $value = false;
               }
               break;

            case '>':
               if (!$conditionField->isPrerequisites()) {
                  return false;
               }
               try {
                  $value = self::isVisible($conditionField->getQuestionId(), $fields) && $conditionField->greaterThan($condition['value']);
               } catch (PluginFormcreatorComparisonException $e) {
                  $value = false;
               }
               break;

            case '<':
               if (!$conditionField->isPrerequisites()) {
                  return false;
               }
               try {
                  $value = self::isVisible($conditionField->getQuestionId(), $fields) && $conditionField->lessThan($condition['value']);
               } catch (PluginFormcreatorComparisonException $e) {
                  $value = false;
               }
               break;

            case '>=':
               if (!$conditionField->isPrerequisites()) {
                  return false;
               }
               try {
                  $value = self::isVisible($conditionField->getQuestionId(), $fields) && ($conditionField->greaterThan($condition['value'])
                           || $conditionField->equals($condition['value']));
               } catch (PluginFormcreatorComparisonException $e) {
                  $value = false;
               }
               break;

            case '<=':
               if (!$conditionField->isPrerequisites()) {
                  return false;
               }
               try {
                  $value = self::isVisible($conditionField->getQuestionId(), $fields) && ($conditionField->lessThan($condition['value'])
                           || $conditionField->equals($condition['value']));
               } catch (PluginFormcreatorComparisonException $e) {
                  $value = false;
               }
               break;
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
                  $return = ($return and $value);
                  break;

               case 'OR'  :
                  $return = ($return or $value);
                  break;

               default :
                  $return = $value;
            }
         }

         if ($currentLogic == 'AND' && $nextLogic != 'AND') {
            if ($lowPrecedenceLogic == 'OR') {
               $return = ($return or $lowPrecedenceReturnPart);
            } else {
               $return = ($return xor $lowPrecedenceReturnPart);
            }
         }
      }

      // Ensure the low precedence part is used if last condition has logic == AND
      if ($lowPrecedenceLogic == 'OR') {
         $return = ($return or $lowPrecedenceReturnPart);
      } else {
         $return = ($return xor $lowPrecedenceReturnPart);
      }

      unset($evalQuestion[$id]);

      if ($question->fields['show_rule'] == 'hidden') {
         // If the field is hidden by default, show it if condition is true
         return $return;
      } else {
         // else show it if condition is false
         return !$return;
      }
   }

   /**
    * compute visibility of all fields of a form
    *
    * @param array $input     values of all fields of the form
    *
    * @return array
    */
   public static function updateVisibility($input) {
      $fields = [];
      // Prepare form fields for validation
      $question = new PluginFormcreatorQuestion();

      $formId = $input['formcreator_form'];
      $found_questions = $question->getQuestionsFromForm($formId);
      foreach ($found_questions as $id => $question) {
         $key = 'formcreator_field_' . $id;
         $fields[$id] = PluginFormcreatorFields::getFieldInstance(
            $question->fields['fieldtype'],
            $question
         );
         $fields[$id]->parseAnswerValues($input, true);
      }

      $questionToShow = [];
      foreach ($fields as $id => $value) {
         $questionToShow[$id] = PluginFormcreatorFields::isVisible($id, $fields);
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
   public static function getFieldInstance($type, PluginFormcreatorQuestion $question, $data = null) {
      $className = self::getFieldClassname($type);
      if (!self::fieldTypeExists($type)) {
         return null;
      }
      return new $className($question->fields, $data);
   }
}
