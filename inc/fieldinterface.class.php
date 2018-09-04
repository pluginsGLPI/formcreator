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

interface PluginFormcreatorFieldInterface
{
   /**
    * gets the localized name of the field
    * @return string
    */
   public static function getName();
   public static function getPrefs();
   public static function getJSFields();

   /**
    * Transform input to properly save it in the database
    * @param array $input data to transform before save
    * @return array|false input data to save or false if data is rejected
    */
   public function prepareQuestionInputForSave($input);

   /**
    * Prepares an answer value for output in a target object
    * @param  string|array $input the answer to format for a target (ticket or change)
    * @return string
    */
   public function prepareQuestionInputForTarget($input);

   /**
    * Prepares a default value or set of values for question edition
    *
    * @param  string $input
    * @return string
    */
   public function prepareQuestionValuesForEdit($input);

   /**
    * Gets the parameters of the field
    * @return PluginFormcreatorQuestionParameter[]
    */
   public function getEmptyParameters();

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
}
