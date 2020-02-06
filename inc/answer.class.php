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

class PluginFormcreatorAnswer extends CommonDBChild
{
   static public $itemtype = PluginFormcreatorFormAnswer::class;
   static public $items_id = "plugin_formcreator_formanswers_id";

   /**
    * Check if current user have the right to create and modify requests
    *
    * @return boolean True if he can create and modify requests
    */
   public static function canCreate() {
      return true;
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView() {
      return true;
   }

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return _n('Answer', 'Answers', $nb, 'formcreator');
   }

   /**
    * Define how to display a specific value in search result table
    *
    * @param  string $field   Name of the field as define in $this->getSearchOptions()
    * @param  mixed  $values  The value as it is stored in DB
    * @param  array  $options Options (optional)
    * @return mixed           Value to be displayed
    */
   public static function getSpecificValueToDisplay($field, $values, array $options = []) {
      switch ($field) {
         case 'id':
            // Transform the answer into a useful value for user
            //Requires some meda data, then it is expected to get the Answer ID here
            $answer = new PluginFormcreatorAnswer();
            if (!$answer->getFromDB($values['id'])) {
               return NOT_AVAILABLE;
            }
            $question = new PluginFormcreatorQuestion();
            $question->getFromDB($answer->fields['plugin_formcreator_questions_id']);
            $field = PluginFormcreatorFields::getFieldInstance($question->fields['fieldtype'], $question);
            $field->deserializeValue($answer->fields['answer']);
            return $field->getValueForTargetText(false);
            break;
      }

      // Should never happen, just in case
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }
}
