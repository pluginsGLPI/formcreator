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

trait PluginFormcreatorConditionnableTrait
{

   /**
    * Check validity of conditions
    *
    * @param array $input conditions to check as sent by the browser
    * @return boolean
    */
   public function checkConditions(array $input): bool {
      // All arrays of condition exists
      if (!isset($input['plugin_formcreator_questions_id']) || !isset($input['show_condition'])
         || !isset($input['show_value']) || !isset($input['show_logic'])) {
         return false;
      }

      if (!is_array($input['plugin_formcreator_questions_id']) || !is_array($input['show_condition'])
         || !is_array($input['show_value']) || !is_array($input['show_logic'])) {
         return false;
      }

      if (!(count($input['plugin_formcreator_questions_id']) == count($input['show_condition'])
            && count($input['show_value']) == count($input['show_logic'])
            && count($input['plugin_formcreator_questions_id']) == count($input['show_value']))) {
         return false;
      }

      while (count($input['show_condition']) > 0) {
         $showCondition    = html_entity_decode(array_shift($input['show_condition']));
         $value            = array_shift($input['show_value']);
         if ($showCondition == PluginFormcreatorCondition::SHOW_CONDITION_REGEX) {
            $regex = Toolbox::stripslashes_deep($value);
            $success = PluginFormcreatorCommon::checkRegex($regex);
            if (!$success) {
               Session::addMessageAfterRedirect(__('The regular expression is invalid', 'formcreator'), false, ERROR);
               return false;
            }
         }
      }

      return true;
   }

   public function checkConditionSettings(array $input): bool {
      if (!isset($input['show_rule'])) {
         // Inconsistency if show_rule set but no condition set
         return !isset($input['_conditions']);
      }
      $showRule = $input['show_rule'];
      if ($showRule == PluginFormcreatorCondition::SHOW_RULE_ALWAYS) {
         return false;
      }

      if (!isset($input['_conditions']) || count($input['_conditions']) < 1) {
         return false;
      }

      $input = $input['_conditions'];
      // All arrays of condition exists
      if (!isset($input['plugin_formcreator_questions_id']) || !isset($input['show_condition'])
         || !isset($input['show_value']) || !isset($input['show_logic'])) {
         return  false;
      }

      if (!is_array($input['plugin_formcreator_questions_id']) || !is_array($input['show_condition'])
         || !is_array($input['show_value']) || !is_array($input['show_logic'])) {
         return false;
      }

      if (!(count($input['plugin_formcreator_questions_id']) == count($input['show_condition'])
         && count($input['show_value']) == count($input['show_logic'])
         && count($input['plugin_formcreator_questions_id']) == count($input['show_value']))) {
         return false;
      }

      return true;
   }

   public function updateConditions($input) : bool {
      $itemtype = $this->getType();
      $itemId = $this->getID();

      if (!isset($this->input['show_rule']) && isset($this->fields['show_rule']) && $this->fields['show_rule'] == PluginFormcreatorCondition::SHOW_RULE_ALWAYS) {
         // rule not changed and is currently "always show"
         $this->deleteConditions();
         return true;
      }
      if (!isset($this->input['show_rule']) && isset($this->fields['show_rule']) && $this->fields['show_rule'] != PluginFormcreatorCondition::SHOW_RULE_ALWAYS) {
         // Rule not changed and is not "always show"
         return true;
      }
      if (isset($this->input['show_rule']) && $this->input['show_rule'] == PluginFormcreatorCondition::SHOW_RULE_ALWAYS) {
         // rule changed to "always show"
         $this->deleteConditions();
         return true;
      }

      // Delete all existing conditions for the question
      $this->deleteConditions();

      $input = $input['_conditions'];

      // Arrays all have the same count and have at least one item
      $questionFk = PluginFormcreatorQuestion::getForeignKeyField();
      $order = 0;
      while (isset($input[$questionFk]) && count($input[$questionFk]) > 0) {
         $order++;
         $value            = array_shift($input['show_value']);
         $questionID       = (int) array_shift($input[$questionFk]);
         $showCondition    = html_entity_decode(array_shift($input['show_condition']));
         $showLogic        = array_shift($input['show_logic']);
         $condition        = new PluginFormcreatorCondition();
         $condition->add([
            'itemtype'                        => $itemtype,
            'items_id'                        => $itemId,
            $questionFk                       => $questionID,
            'show_condition'                  => $showCondition,
            'show_value'                      => $value,
            'show_logic'                      => $showLogic,
            'order'                           => $order,
         ]);
         if ($condition->isNewItem()) {
            return false;
         }
      }

      return true;
   }

   private function deleteConditions() {
      $condition = new PluginFormcreatorCondition();
      $condition->deleteByCriteria([
         'itemtype' => $this->getType(),
         'items_id' => $this->getID(),
      ]);
   }
}
