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
class PluginFormcreatorUpgradeTo2_8 {
   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      // add item association rule
      $table = 'glpi_plugin_formcreator_targettickets';
      $enum_associate_rule = "'".implode("', '", array_keys(PluginFormcreatorTargetTicket::getEnumAssociateRule()))."'";
      if (!$DB->fieldExists($table, 'associate_rule', false)) {
         $migration->addField(
            $table,
            'associate_rule',
            "ENUM($enum_associate_rule) NOT NULL DEFAULT 'none'",
            ['after' => 'category_question']
         );
      } else {
         $current_enum_associate_rule = PluginFormcreatorCommon::getEnumValues($table, 'associate_rule');
         if (count($current_enum_associate_rule) != count($enum_associate_rule)) {
            $migration->changeField(
               $table,
               'location_rule',
               'location_rule',
               "ENUM($enum_associate_rule) NOT NULL DEFAULT 'none'",
               ['after' => 'category_question']
            );
         }
      }
      $migration->addField($table, 'associate_question', 'integer', ['after' => 'associate_rule']);

      // Rename the plugin
      $plugin = new Plugin();
      $plugin->getFromDBbyDir('formcreator');
      $success = $plugin->update([
         'id' => $plugin->getID(),
         'name' => 'Form Creator',
      ]);

      // Remove enum for formanswer
      $table = 'glpi_plugin_formcreator_formanswers';
      $count = (new DBUtils())->countElementsInTable(
         $table,
         [
            'status' => ['waiting', 'accepted', 'refused']
         ]
      );
      if ($count > 0) {
         $migration->addField(
            $table,
            'new_status',
            'integer', [
               'after' => 'request_date', 'default_value' => '1'
            ]
         );
         $migration->migrationOneTable($table);
         $DB->update(
            $table,
            ['new_status' => 101], // @see PluginFormcreator::STATUS_WAITING
            ['status' => 'waiting']
         );
         $DB->update(
            $table,
            ['new_status' => 102], // @see PluginFormcreator::STATUS_REFUSED
            ['status' => 'refused']
         );
         $DB->update(
            $table,
            ['new_status' => 103], // @see PluginFormcreator::STATUS_ACCEPTED
            ['status' => 'accepted']
         );
         $migration->changeField(
            $table,
            'new_status',
            'status', 'integer', [
               'after' => 'request_date',
               'default_value' => '1'
            ]
         );
      }

      // Remove enum for question
      $table = 'glpi_plugin_formcreator_questions';
      $count = (new DBUtils())->countElementsInTable(
         $table,
         [
            'show_rule' => ['always', 'hidden', 'shown']
         ]
      );
      if ($count > 0) {
         $migration->addField(
            $table,
            'new_show_rule',
            'integer', [
               'after' => 'order', 'default_value' => '1'
            ]
         );
         $migration->migrationOneTable($table);
         $DB->update(
            $table,
            ['new_show_rule' => 1], // @see PluginFormcreatorQuestion::SHOW_RULE_ALWAYS
            ['show_rule' => 'always']
         );
         $DB->update(
            $table,
            ['new_show_rule' => 2], // @see PluginFormcreatorQuestion::SHOW_RULE_HIDDEN
            ['show_rule' => 'hidden']
         );
         $DB->update(
            $table,
            ['new_stanew_show_ruletus' => 3], // @see PluginFormcreatorQuestion::SHOW_RULE_SHOWN
            ['show_rule' => 'shown']
         );
         $migration->changeField(
            $table,
            'new_show_rule',
            'show_rule',
            'integer', [
               'after' => 'order',
               'default_value' => '1'
            ]
         );
      }

      // Remove show_logic  enum for question conditions
      $table = 'glpi_plugin_formcreator_questions_conditions';
      $count = (new DBUtils())->countElementsInTable(
         $table,
         [
            'show_logic' => ['AND', 'OR']
         ]
      );
      if ($count > 0) {
         $migration->addField(
            $table,
            'new_show_logic',
            'integer', [
               'after' => 'show_value'
            ]
         );
         $migration->migrationOneTable($table);
         $DB->update(
            $table,
            ['new_show_logic' => 1], // @see PluginFormcreatorQuestion_Condition::SHOW_LOGIC_AND
            ['show_logic' => 'AND']
         );
         $DB->update(
            $table,
            ['new_show_logic' => 2], // @see PluginFormcreatorQuestion_Condition::SHOW_LOGIC_OR
            ['show_logic' => 'OR']
         );
         $migration->changeField(
            $table,
            'new_show_logic',
            'show_logic',
            'integer', [
               'after' => 'show_value'
            ]
         );
      }

      // Remove show_condition  enum for question conditions
      $table = 'glpi_plugin_formcreator_questions_conditions';
      $count = (new DBUtils())->countElementsInTable(
         $table,
         [
            'show_condition' => ['==', '!=', '<', '>', '<=', '>=']
         ]
      );
      if ($count > 0) {
         $migration->addField(
            $table,
            'new_show_condition',
            'integer', [
               'after' => 'show_field'
            ]
         );
         $migration->migrationOneTable($table);
         $DB->update(
            $table,
            ['new_show_condition' => 1], // @see PluginFormcreatorQuestion_Condition::SHOW_CONDITION_EQ
            ['show_condition' => '==']
         );
         $DB->update(
            $table,
            ['new_show_condition' => 2], // @see PluginFormcreatorQuestion_Condition::SHOW_CONDITION_NE
            ['show_condition' => '!=']
         );
         $DB->update(
            $table,
            ['new_show_condition' => 3], // @see PluginFormcreatorQuestion_Condition::SHOW_CONDITION_LT
            ['show_condition' => '<']
         );
         $DB->update(
            $table,
            ['new_show_condition' => 4], // @see PluginFormcreatorQuestion_Condition::SHOW_CONDITION_GT
            ['show_condition' => '>']
         );
         $DB->update(
            $table,
            ['new_show_condition' => 5], // @see PluginFormcreatorQuestion_Condition::SHOW_CONDITION_LE
            ['show_condition' => '<=']
         );
         $DB->update(
            $table,
            ['new_show_condition' => 6], // @see PluginFormcreatorQuestion_Condition::SHOW_CONDITION_GE
            ['show_condition' => '>=']
         );
         $migration->changeField(
            $table,
            'new_show_condition',
            'show_condition',
            'integer', [
               'after' => 'show_field'
            ]
         );
      }
   }
}
