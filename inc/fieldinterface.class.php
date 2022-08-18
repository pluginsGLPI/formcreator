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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

interface PluginFormcreatorFieldInterface
{
   /**
    * Get the localized name of the field
    *
    * @return string
    */
   public static function getName(): string;

   /**
    * Can the fild be required ?
    *
    * @return boolean true if yes, false otherwise
    */
   public static function canRequire(): bool;

   /**
    * Are the prerequisites met to use this field ?
    *
    * @return boolean true if prerequisites met, false otherwise
    */
   public function isPrerequisites(): bool;

   /**
    * Show HTML to design a question
    *
    * @return array
    */
   public function showForm(array $options): void;

   /**
    * Is the field valid for the given value?
    *
    * @return boolean True if the field has a valid value, false otherwise
    */
   public function isValid(): bool;

   /**
    * Check if a value is valid for the field type
    *
    * @param string|array $value
    * @return boolean true if valid, false otherwise
    */
   public function isValidValue($value): bool;

   /**
    * Is the field required?
    *
    * @return boolean
    */
   public function isRequired(): bool;

   /**
    * Serialize a value for save in the database
    * Used to save a default value or a value
    *
    * @return string JSON encoded string
    */
   public function serializeValue(PluginFormcreatorFormAnswer $formanswer): string;

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
   public function getValueForDesign(): string;

   /**
    * Get the value of the field for display in a target
    *
    * @param string  $domain      locales domain
    * @param boolean $richText    Enable rich text mode for field rendering
    *
    * @return string
    */
   public function getValueForTargetText($domain, $richText): ?string;

   /**
    * Get the valoe of the field for output via the API
    *
    * @return string|array
    */
   public function getValueForApi();

   /**
    * Move uploaded files and make Document items
    */
   public function moveUploads();

   /**
    * Gets the documents IDs
    *
    * @return integer[]
    */
   public function getDocumentsForTarget(): array;

   /**
    * Transform input to properly save it in the database
    * @param array $input data to transform before save
    *
    * @return array|false input data to save or false if data is rejected
    */
   public function prepareQuestionInputForSave($input);

   /**
    * Do the argument has an user input ?
    * @param array $input answers of all questions of the form
    *
    * @return boolean
    */
   public function hasInput($input): bool;

   /**
    * Read the value of the field from answers
    * @param array $input answers of all questions of the form
    * @param bool $nonDestructive for File field, ensure that the file uploads imported as document
    *
    * @return boolean true on sucess, false otherwise
    */
   public function parseAnswerValues($input, $nonDestructive = false): bool;

   /**
    * Prepares an answer value for output in a target object
    * @param  string|array $input the answer to format for a target (ticket or change)
    *
    * @return string
    */
   //public function prepareQuestionInputForTarget($input);

   /**
    * Gets the parameters of the field
    *
    * @return PluginFormcreatorAbstractQuestionParameter[]
    */
   public function getEmptyParameters(): array;

   /**
    * Gets parameters of the field with their settings
    *
    * @return PluginFormcreatorAbstractQuestionParameter[]
    */
   public function getParameters(): array;

   /**
    * Gets the name of the field type
    *
    * @return string
    */
   public function getFieldTypeName(): string;

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
    *
    * @return boolean true if success, false otherwise
    */
   public function deleteParameters(PluginFormcreatorQuestion $question): bool;

   /**
    * Tests if the given value equals the field value
    *
    * @return boolean True if the value equals the field value
    */
   public function equals($value): bool;

   /**
    * Tests if the given value is not equal to field value
    *
    * @return boolean True if the value is not equal to the field value
    */
   public function notEquals($value): bool;

   /**
    * Tests if the given value is greater than the field value
    *
    * @return boolean True if the value is greater than the field value
    */
   public function greaterThan($value): bool;

   /**
    * Tests if the given value is less than the field value
    *
    * @return boolean True if the value is less than the field value
    */
   public function LessThan($value): bool;

   /**
    * Tests if the given value is match with regex
    *
    * @return boolean True if the value is match with regex
    */
   public function regex($value): bool;

   /**
    * Is the field compatible with anonymous form ?
    *
    * @return boolean true if the field can work with public forms
    */
   public function isPublicFormCompatible(): bool;

   /**
    * Gets HTML code for the icon of a field
    */
   public function getHtmlIcon();

   /**
    * get HTML code of rendered question for service catalog
    * @param string $domain  Translation domain of the form
    * @param boolean $canEdit true if the user can edit the answer
    * @return string HTML code
    */
   public function getRenderedHtml($domain, $canEdit = true): string;

   /**
    * Is the field editable ?
    * Must return true if the field is editable by nature (i.e. a text box)
    * or false if it is not editable by nature (i.e. a description field)
    *
    * @return boolean
    */
   public function isEditableField(): bool;

   /**
    * Is the field visible ?
    * Must return trie if the field is visible by nature (i.e. a text botx, a description field)
    * or false if it is invisible by nature (i.e. a hostname or ip field)
    *
    * @return boolean
    */
   public function isVisibleField(): bool;

   /**
    * Get all translatable strings
    * @return array translatable strings under keys 'string' and 'text'
    */
   public function getTranslatableStrings(array $options = []): array;

   public function getQuestion();

   /**
    * get the HTML to show the field
    *
    * @param string $domain translation domain
    * @param boolean $canEdit true if the field is editable
    * @return string HTML of the field
    */
   public function show(string $domain, bool $canEdit = true): string;

   /**
    * Set the form answer containing the value of the field
    *
    * @param PluginFormcreatorFormAnswer $form_answer
    * @return void
    */
   public function setFormAnswer(PluginFormcreatorFormAnswer $form_answer): void;
}
