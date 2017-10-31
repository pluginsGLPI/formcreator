<?php
/**
 * LICENSE
 *
 * Copyright © 2011-2018 Teclib'
 *
 * This file is part of Formcreator Plugin for GLPI.
 *
 * Formcreator is a plugin that allow creation of custom, easy to access forms
 * for users when they want to create one or more GLPI tickets.
 *
 * Formcreator Plugin for GLPI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2018 Teclib
 * @license   GPLv2 https://www.gnu.org/licenses/gpl2.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ------------------------------------------------------------------------------
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
   public function prepareQuestionInputForTarget($input);

   /**
    * Gets the parameters of the field
    * @return PluginFormcreatorQuestionParameter[]
    */
   public function getUsedParameters();

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
