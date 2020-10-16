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
   /**
    * Keep track of questions results and computation status
    * null = is being avaluated
    * true or false = result of a previous evaluation
    * not set = not evaluated yet AND not being evaluated
    * @var CommonDBTM[] $visibility
    * @see self::isVisible
    */
   private static $visibility = [];

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

   /**
    * Reset cache of evaluated visibility
    * used for unit tests
    *
    * @return void
    */
   public static function resetVisibilityCache() {
      self::$visibility = [];
   }

   /**
    * Check if an item should be shown or not
    *
    * @param   integer     $item       Item tested for visibility
    * @param   array       $fields     Array of fields instances (question id => instance)
    * @return  boolean                 If true the question should be visible
    */
   public static function isVisible(PluginFormcreatorConditionnableInterface $item, $fields) {
      $itemtype = get_class($item);
      $itemId = $item->getID();
      if (!isset(self::$visibility[$itemtype][$itemId])) {
         self::$visibility[$itemtype][$itemId] = null;
      } else if (self::$visibility[$itemtype][$itemId] !== null) {
         return self::$visibility[$itemtype][$itemId];
      } else {
         throw new Exception("Infinite loop in show conditions evaluation");
      }

      /**
       * Get inherit visibility from parent item.
       * @return boolean
       */
      $getParentVisibility = function() use ($item, $itemtype, $itemId, $fields) {
         // Check if item has a condtionnable visibility parent
         if ($item instanceof CommonDBChild) {
            if (is_subclass_of($item::$itemtype, PluginFormcreatorConditionnableInterface::class)) {
               if ($parent = $item->getItem(true, false)) {
                  if ($parent->getType() == PluginFormcreatorForm::class) {
                     // the condition for form is only for its submit button. A form is always visible
                     return true;
                  }
                  // Use visibility of the parent item
                  self::$visibility[$itemtype][$itemId] = self::isVisible($parent, $fields);
                  return self::$visibility[$itemtype][$itemId];
               }
            }
         }
         self::$visibility[$itemtype][$itemId] = true;
         return self::$visibility[$itemtype][$itemId];
      };

      // If the field is always shown
      if ($item->fields['show_rule'] == PluginFormcreatorCondition::SHOW_RULE_ALWAYS) {
         return $getParentVisibility();
      }

      // Get conditions to show or hide the item
      $conditions = [];
      $condition = new PluginFormcreatorCondition();
      $conditions = $condition->getConditionsFromItem($item);
      if ($getParentVisibility() === false || count($conditions) < 1) {
         // No condition defined or parent hidden
         self::$visibility[$itemtype][$itemId] = false;
         return self::$visibility[$itemtype][$itemId];
      }
      self::$visibility[$itemtype][$itemId] = null;

      // Force the first logic operator to OR
      $conditions[0]->fields['show_logic']       = PluginFormcreatorCondition::SHOW_LOGIC_OR;

      $return                       = false;
      $lowPrecedenceReturnPart      = false;
      $lowPrecedenceLogic           = 'OR';
      foreach ($conditions as $order => $condition) {
         $currentLogic = $condition->fields['show_logic'];
         if (isset($conditions[$order + 1])) {
            $nextLogic = $conditions[$order + 1]->fields['show_logic'];
         } else {
            // To ensure the low precedence return part is used at the end of the whole evaluation
            $nextLogic = PluginFormcreatorCondition::SHOW_LOGIC_OR;
         }

         if (!isset($fields[$condition->fields['plugin_formcreator_questions_id']])) {
            // The field does not exists, give up and make the field visible
            return true;
         }
         $conditionField = $fields[$condition->fields['plugin_formcreator_questions_id']];

         $value = false;
         if (in_array($condition->fields['show_condition'], [PluginFormcreatorCondition::SHOW_CONDITION_QUESTION_VISIBLE, PluginFormcreatorCondition::SHOW_CONDITION_QUESTION_INVISIBLE])) {
            switch ($condition->fields['show_condition']) {
               case PluginFormcreatorCondition::SHOW_CONDITION_QUESTION_VISIBLE:
                  if (!$conditionField->isPrerequisites()) {
                     self::$visibility[$itemtype][$itemId] = false;
                     return self::$visibility[$itemtype][$itemId];
                  }
                  try {
                     $value = self::isVisible($conditionField->getQuestion(), $fields);
                  } catch (PluginFormcreatorComparisonException $e) {
                     $value = false;
                  }
                  break;
               case PluginFormcreatorCondition::SHOW_CONDITION_QUESTION_INVISIBLE:
                  if (!$conditionField->isPrerequisites()) {
                     self::$visibility[$itemtype][$itemId] = false;
                     return self::$visibility[$itemtype][$itemId];
                  }
                  try {
                     $value = !self::isVisible($conditionField->getQuestion(), $fields);
                  } catch (PluginFormcreatorComparisonException $e) {
                     $value = false;
                  }
                  break;
            }
         } else {
            if (self::isVisible($conditionField->getQuestion(), $fields)) {
               switch ($condition->fields['show_condition']) {
                  case PluginFormcreatorCondition::SHOW_CONDITION_NE :
                     if (!$conditionField->isPrerequisites()) {
                        self::$visibility[$itemtype][$itemId] = true;
                        return self::$visibility[$itemtype][$itemId];
                     }
                     try {
                        $value = $conditionField->notEquals($condition->fields['show_value']);
                     } catch (PluginFormcreatorComparisonException $e) {
                        $value = false;
                     }
                     break;

                  case PluginFormcreatorCondition::SHOW_CONDITION_EQ :
                     if (!$conditionField->isPrerequisites()) {
                        self::$visibility[$itemtype][$itemId] = false;
                        return self::$visibility[$itemtype][$itemId];
                     }
                     try {
                        $value = $conditionField->equals($condition->fields['show_value']);
                     } catch (PluginFormcreatorComparisonException $e) {
                        $value = false;
                     }
                     break;

                  case PluginFormcreatorCondition::SHOW_CONDITION_GT:
                     if (!$conditionField->isPrerequisites()) {
                        self::$visibility[$itemtype][$itemId] = false;
                        return self::$visibility[$itemtype][$itemId];
                     }
                     try {
                        $value = $conditionField->greaterThan($condition->fields['show_value']);
                     } catch (PluginFormcreatorComparisonException $e) {
                        $value = false;
                     }
                     break;

                  case PluginFormcreatorCondition::SHOW_CONDITION_LT:
                     if (!$conditionField->isPrerequisites()) {
                        self::$visibility[$itemtype][$itemId] = false;
                        return self::$visibility[$itemtype][$itemId];
                     }
                     try {
                        $value = $conditionField->lessThan($condition->fields['show_value']);
                     } catch (PluginFormcreatorComparisonException $e) {
                        $value = false;
                     }
                     break;

                  case PluginFormcreatorCondition::SHOW_CONDITION_GE:
                     if (!$conditionField->isPrerequisites()) {
                        self::$visibility[$itemtype][$itemId] = false;
                        return self::$visibility[$itemtype][$itemId];
                     }
                     try {
                        $value = ($conditionField->greaterThan($condition->fields['show_value'])
                        || $conditionField->equals($condition->fields['show_value']));
                     } catch (PluginFormcreatorComparisonException $e) {
                        $value = false;
                     }
                     break;

                  case PluginFormcreatorCondition::SHOW_CONDITION_LE:
                     if (!$conditionField->isPrerequisites()) {
                        self::$visibility[$itemtype][$itemId] = false;
                        return self::$visibility[$itemtype][$itemId];
                     }
                     try {
                        $value = ($conditionField->lessThan($condition->fields['show_value'])
                        || $conditionField->equals($condition->fields['show_value']));
                     } catch (PluginFormcreatorComparisonException $e) {
                        $value = false;
                     }
                     break;
                  }
            }
         }
         // Combine all condition with respect of operator precedence
         // AND has precedence over OR and XOR
         if ($currentLogic != PluginFormcreatorCondition::SHOW_LOGIC_AND && $nextLogic == PluginFormcreatorCondition::SHOW_LOGIC_AND) {
            // next condition has a higher precedence operator
            // Save the current computed return and operator to use later
            $lowPrecedenceReturnPart = $return;
            $lowPrecedenceLogic = $currentLogic;
            $return = $value;
         } else {
            switch ($currentLogic) {
               case PluginFormcreatorCondition::SHOW_LOGIC_AND :
                  $return = ($return and $value);
                  break;

               case PluginFormcreatorCondition::SHOW_LOGIC_OR  :
                  $return = ($return or $value);
                  break;

               default :
                  $return = $value;
            }
         }

         if ($currentLogic == PluginFormcreatorCondition::SHOW_LOGIC_AND && $nextLogic != PluginFormcreatorCondition::SHOW_LOGIC_AND) {
            if ($lowPrecedenceLogic == PluginFormcreatorCondition::SHOW_LOGIC_OR) {
               $return = ($return or $lowPrecedenceReturnPart);
            } else {
               $return = ($return xor $lowPrecedenceReturnPart);
            }
         }
      }

      // Ensure the low precedence part is used if last condition has logic == AND
      if ($lowPrecedenceLogic == PluginFormcreatorCondition::SHOW_LOGIC_OR) {
         $return = ($return or $lowPrecedenceReturnPart);
      } else {
         $return = ($return xor $lowPrecedenceReturnPart);
      }

      if ($item->fields['show_rule'] == PluginFormcreatorCondition::SHOW_RULE_HIDDEN) {
         // If the field is hidden by default, show it if condition is true
         self::$visibility[$itemtype][$itemId] = $return;
      } else {
         // else show it if condition is false
         self::$visibility[$itemtype][$itemId] = !$return;
      }

      return self::$visibility[$itemtype][$itemId];
   }

   /**
    * compute visibility of all fields of a form
    *
    * @param array $input     values of all fields of the form
    *
    * @return array
    */
   public static function updateVisibility($input) {
      $form = new PluginFormcreatorForm();
      $form->getFromDB((int) $input['plugin_formcreator_forms_id']);
      $fields = $form->getFields();
      foreach ($fields as $id => $field) {
         $fields[$id]->parseAnswerValues($input, true);
      }

      // Get the visibility for the submit button of the form
      $submitShow = PluginFormcreatorFields::isVisible($form, $fields);

      // Get the visibility result of questions
      $questionToShow = [];
      foreach ($fields as $id => $field) {
         $questionToShow[$id] = PluginFormcreatorFields::isVisible($field->getQuestion(), $fields);
      }

      // Get the visibility result of sections
      $sectionToShow = [];
      $sections = (new PluginFormcreatorSection)->getSectionsFromForm($form->getID());
      foreach($sections as $section) {
         $sectionToShow[$section->getID()] = PluginFormcreatorFields::isVisible($section, $fields);
      }

      return [
         PluginFormcreatorQuestion::class => $questionToShow,
         PluginFormcreatorSection::class => $sectionToShow,
         PluginFormcreatorForm::class => $submitShow,
      ];
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
      $className = self::getFieldClassname($type);
      return is_subclass_of($className, PluginFormcreatorField::class, true);
   }

   /**
    * gets an instance of a field given its type
    *
    * @param string $type type of field to get
    * @param PluginFormcreatorQuestion $question question representing the field
    * @param array $data additional data
    * @return null|PluginFormcreatorField
    */
   public static function getFieldInstance($type, PluginFormcreatorQuestion $question) {
      if (!self::fieldTypeExists($type)) {
         return null;
      }
      $className = self::getFieldClassname($type);
      return new $className($question);
   }
}
