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

interface PluginFormcreatorFieldInterface
{
   /**
    * gets the localized name of the field
    * @return string
    */
   public static function getName();

   public static function canRequire();

   /**
    * Are the prerequisites met to use this field ?
    */
   public function isPrerequisites();

   /**
    * get HTML for specific properties of the field type at design time
    *
    * @return array
    */
   public function getDesignSpecializationField();

   /**
    * Is the field valid for the given value?
    * @return boolean True if the field has a valid value, false otherwise
    */
   public function isValid();

   /**
    * Is the field required?
    * @return boolean
    */
   public function isRequired();

   /**
    * Serialize a value for save in the database
    * Used to save a default value or a value
    *
    * @return string JSON encoded string
    */
   public function serializeValue();

   /**
    * Deserialize a JSON encoded value or default value
    * Used to retrieve the default value from a question
    * or the value of an answer
    *
    * @param string $value
    */
   public function deserializeValue($value);

   /**
    * Get the value of the field for display in the form designer
    *
    * @return string
    */
   public function getValueForDesign();

   /**
    * Get the value of the field for display in a target
    *
    * @param  boolean $richText    Enable rich text mode for field rendering
    * @return string
    */
   public function getValueForTargetText($richText);

   /**
    * Move uploaded files and make Document items
    */
   public function moveUploads();

   /**
    * Gets the documents IDs
    *
    * @return integer[]
    */
   public function getDocumentsForTarget();

   /**
    * Transform input to properly save it in the database
    * @param array $input data to transform before save
    * @return array|false input data to save or false if data is rejected
    */
   public function prepareQuestionInputForSave($input);

   /**
    * Do the argument has an user input ?
    * @param array $input answers of all questions of the form
    * @return boolean
    */
   public function hasInput($input);

   /**
    * Read the value of the field from answers
    * @param array $input answers of all questions of the form
    * @param boolean $nonDestructive for File field, ensure that the file uploads imported as document
    * @return boolean true on sucess, false otherwise
    */
   public function parseAnswerValues($input, $nonDestructive = false);

   /**
    * Prepares an answer value for output in a target object
    * @param  string|array $input the answer to format for a target (ticket or change)
    * @return string
    */
   //public function prepareQuestionInputForTarget($input);

   /**
    * Gets the parameters of the field
    * @return PluginFormcreatorQuestionParameter[]
    */
   public function getEmptyParameters();

   /**
    * Gets parameters of the field with their settings
    * @return PluginFormcreatorQuestionParameter[]
    */
   public function getParameters();

   /**
    * Gets the name of the field type
    * @return string
    */
   public function getFieldTypeName();

   /**
    * Adds parameters of the field into the database
    * @param PluginFormcreatorQuestion $question question of the field
    * @param array $input data of parameters
    */
   public function addParameters(PluginFormcreatorQuestion $question, array $input);

   /**
    * Updates parameters of the field into the database
    * @param PluginFormcreatorQuestion $question question of the field
    * @param array $input data of parameters
    */
   public function updateParameters(PluginFormcreatorQuestion $question, array $input);

   /**
    * Deletes all parameters of the field applied to the question
    * @param PluginFormcreatorQuestion $question
    * @return boolean true if success, false otherwise
    */
   public function deleteParameters(PluginFormcreatorQuestion $question);

   /**
    * Tests if the given value equals the field value
    * @return boolean True if the value equals the field value
    */
   public function equals($value);

   /**
    * Tests if the given value is not equal to field value
    * @return boolean True if the value is not equal to the field value
    */
   public function notEquals($value);

   /**
    * Tests if the given value is greater than the field value
    * @return boolean True if the value is greater than the field value
    */
   public function greaterThan($value);

   /**
    * Tests if the given value is less than the field value
    * @return boolean True if the value is less than the field value
    */
   public function LessThan($value);

   /**
    * Is the field compatible with anonymous form ?
    * @return boolean true if the field can work with anonymous forms
    */
   public function isAnonymousFormCompatible();

   /**
    * Gets HTML code for the icon of a field
    */
   public function getHtmlIcon();
}
