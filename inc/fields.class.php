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
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

use GlpiPlugin\Formcreator\Exception\ComparisonException;
use GlpiPlugin\Formcreator\Field\UndefinedField;
use Xylemical\Expressions\Math\BcMath;
use Xylemical\Expressions\Context;
use Xylemical\Expressions\ExpressionFactory;
use Xylemical\Expressions\Evaluator;
use Xylemical\Expressions\Lexer;
use Xylemical\Expressions\Parser;

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

      foreach (glob(dirname(__FILE__).'/field/*field.class.php') as $class_file) {
         $matches = null;
         preg_match("#field/(.+)field\.class.php$#", $class_file, $matches);

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
      foreach (glob(dirname(__FILE__).'/field/*field.class.php') as $class_file) {
         $matches = null;
         preg_match("#field/(.+)field\.class.php$#", $class_file, $matches);
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
         if ($classname == UndefinedField::class) {
            continue;
         }

         $tab_field_types_name[$field_type] = $classname::getName();
      }

      asort($tab_field_types_name);

      return $tab_field_types_name;
   }

   /**
    * Reset the cache of visibility of hide-able items
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
    * @param   PluginFormcreatorFieldInterface[] $fields     Array of fields instances (question id => instance)
    * @return  boolean                 If true the question should be visible
    */
   public static function isVisible(PluginFormcreatorConditionnableInterface $item, $fields) {
      /** @var CommonDBTM $item */
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
      $getParentVisibility = function() use ($item, $fields) {
         // Check if item has a condtionnable visibility parent
         if ($item instanceof CommonDBChild) {
            $interfaces = class_implements($item::$itemtype);
            if (in_array(PluginFormcreatorConditionnableInterface::class, $interfaces)) {
               if ($parent = $item->getItem(true, false)) {
                  $parentItemtype = $parent->getType();
                  $parentId = $parent->getID();
                  if ($parent->getType() == PluginFormcreatorForm::class) {
                     // the condition for form is only for its submit button. A form is always visible
                     self::$visibility[$parentItemtype][$parentId] = true;
                     return self::$visibility[$parentItemtype][$parentId];
                  }
                  // Use visibility of the parent item
                  self::$visibility[$parentItemtype][$parentId] = self::isVisible($parent, $fields);
                  return self::$visibility[$parentItemtype][$parentId];
               }
            }
         }
         return true;
      };

      // If the field is always shown
      if ($item->fields['show_rule'] == PluginFormcreatorCondition::SHOW_RULE_ALWAYS) {
         self::$visibility[$itemtype][$itemId] = $getParentVisibility();
         return self::$visibility[$itemtype][$itemId];
      }

      // Get conditions to show or hide the item
      $condition = new PluginFormcreatorCondition();
      $conditions = $condition->getConditionsFromItem($item);
      if ($getParentVisibility() === false) {
         // No condition defined or parent hidden
         self::$visibility[$itemtype][$itemId] = false;
         return self::$visibility[$itemtype][$itemId];
      }
      if (count($conditions) < 1) {
         switch ($item->fields['show_rule']) {
            case PluginFormcreatorCondition::SHOW_RULE_HIDDEN:
               self::$visibility[$itemtype][$itemId] = false;
               return self::$visibility[$itemtype][$itemId];
               break;

            case PluginFormcreatorCondition::SHOW_RULE_SHOWN:
               self::$visibility[$itemtype][$itemId] = true;
               return self::$visibility[$itemtype][$itemId];
               break;

            default:
               // This should not happen : inconsistency in the database
               trigger_error("Inconsistency detected in conditions: show rule set but no condition found, $itemtype ID=$itemId", E_USER_WARNING);
               self::$visibility[$itemtype][$itemId] = $getParentVisibility();
               return self::$visibility[$itemtype][$itemId];
               break;
         }
      }

      $expression = [];
      foreach ($conditions as $condition) {
         $value = false;
         if (!isset($fields[$condition->fields['plugin_formcreator_questions_id']])) {
            // The field does not exists, give up and make the field visible
            return true;
         }
         $conditionField = $fields[$condition->fields['plugin_formcreator_questions_id']];
         if (in_array($condition->fields['show_condition'], [PluginFormcreatorCondition::SHOW_CONDITION_QUESTION_VISIBLE, PluginFormcreatorCondition::SHOW_CONDITION_QUESTION_INVISIBLE])) {
            switch ($condition->fields['show_condition']) {
               case PluginFormcreatorCondition::SHOW_CONDITION_QUESTION_VISIBLE:
                  if (!$conditionField->isPrerequisites()) {
                     self::$visibility[$itemtype][$itemId] = false;
                     return self::$visibility[$itemtype][$itemId];
                  }
                  try {
                     $value = self::isVisible($conditionField->getQuestion(), $fields);
                  } catch (ComparisonException $e) {
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
                  } catch (ComparisonException $e) {
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
                     } catch (ComparisonException $e) {
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
                     } catch (ComparisonException $e) {
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
                     } catch (ComparisonException $e) {
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
                     } catch (ComparisonException $e) {
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
                     } catch (ComparisonException $e) {
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
                     } catch (ComparisonException $e) {
                        $value = false;
                     }
                     break;

                  case PluginFormcreatorCondition::SHOW_CONDITION_REGEX:
                     if (!$conditionField->isPrerequisites()) {
                        self::$visibility[$itemtype][$itemId] = false;
                        return self::$visibility[$itemtype][$itemId];
                     }
                     try {
                        $value = $conditionField->regex($condition->fields['show_value']);
                     } catch (ComparisonException $e) {
                        $value = false;
                     }
                     break;
               }
            }
         }
         $expression[] = PluginFormcreatorCondition::getEnumShowLogic()[$condition->fields['show_logic']];
         $expression[] = $value ? '1' : '0';
      }
      // Drop the first logic operator as it is irrelevant
      array_shift($expression);
      $expression = implode(' ', $expression);

      $math = new BcMath();
      $factory = new ExpressionFactory($math);
      $lexer = new Lexer($factory);
      $parser = new Parser($lexer);
      $evaluator = new Evaluator();
      $context = new Context();

      $tokens = $parser->parse($expression);
      self::$visibility[$itemtype][$itemId] = $evaluator->evaluate($tokens, $context) ? true : false;

      if ($item->fields['show_rule'] == PluginFormcreatorCondition::SHOW_RULE_SHOWN) {
         // Reverse condition
         self::$visibility[$itemtype][$itemId] = !self::$visibility[$itemtype][$itemId];
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
      $form = PluginFormcreatorCommon::getForm();
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
      foreach ($sections as $section) {
         $sectionToShow[$section->getID()] = PluginFormcreatorFields::isVisible($section, $fields);
      }

      return [
         PluginFormcreatorQuestion::class => $questionToShow,
         PluginFormcreatorSection::class  => $sectionToShow,
         PluginFormcreatorForm::class     => $submitShow,
      ];
   }

   /**
    * gets the classname for a field given its type
    *
    * @param string $type type of field to test for existence
    * @return string
    */
   public static function getFieldClassname($type) {
      return 'GlpiPlugin\\Formcreator\\Field\\' . ucfirst($type) . 'Field';
   }

   /**
    * checks if a field type exists
    *
    * @param string $type type of field to test for existence
    * @return boolean
    */
   public static function fieldTypeExists(string $type): bool {
      $className = self::getFieldClassname($type);
      return is_subclass_of($className, PluginFormcreatorAbstractField::class, true);
   }

   /**
    * gets an instance of a field given its type
    *
    * @param string $type type of field to get
    * @param PluginFormcreatorQuestion $question question representing the field
    * @return null|PluginFormcreatorAbstractField
    */
   public static function getFieldInstance(string $type, PluginFormcreatorQuestion $question): ?PluginFormcreatorAbstractField {
      if (!self::fieldTypeExists($type)) {
         return null;
      }
      $className = self::getFieldClassname($type);
      return new $className($question);
   }
}
