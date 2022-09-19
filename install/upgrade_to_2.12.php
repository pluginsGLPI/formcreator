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
class PluginFormcreatorUpgradeTo2_12 {
   /** @var Migration */
   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      $this->migration = $migration;

      // Convert datetime to timestamp
      $table = 'glpi_plugin_formcreator_formanswers';
      $migration->changeField($table, 'request_date', 'request_date', 'timestamp');

      $table = 'glpi_plugin_formcreator_issues';
      $migration->changeField($table, 'date_creation', 'date_creation', 'timestamp');
      $migration->changeField($table, 'date_mod', 'date_mod', 'timestamp');

      $this->changeDropdownTreeSettings();

      $table = 'glpi_plugin_formcreator_entityconfigs';
      $this->migration->addField($table, 'is_search_visible', 'integer', ['after' => 'is_kb_separated']);
      $this->migration->addField($table, 'is_header_visible', 'integer', ['after' => 'is_search_visible']);
      $this->migration->addField($table, 'header', 'text', ['after' => 'is_header_visible']);

      $this->migrateReferenceEntity();

      $table = 'glpi_plugin_formcreator_forms';
      $this->migration->changeField($table, 'language', 'language', 'string', ['value' => '', 'after' => 'is_active']);

      $this->normalizeIssues();

      $table = 'glpi_plugin_formcreator_targetchanges';
      $this->migration->addField($table, 'changetemplates_id', 'integer', ['value' => '0', 'after' => 'target_name']);

      $table = 'glpi_plugin_formcreator_targettickets';
      $DB->query("UPDATE `$table` SET `tickettemplates_id`='0' WHERE `tickettemplates_id` IS NULL");
      $this->migration->changeField($table, 'tickettemplates_id', 'tickettemplates_id', 'integer', ['value' => '0', 'after' => 'type_question']);
   }

   /**
    * Convert values field of question from form
    * {"itemtype":"ITILCategory","show_ticket_categories_depth":"0","show_ticket_categories_root":"6354"}
    * to form
    * {"itemtype":"ITILCategory","show_tree_depth":-1,"show_tree_root":false}
    *
    * @return void
    */
   public function changeDropdownTreeSettings() {
      global $DB;

      $table = 'glpi_plugin_formcreator_questions';

      $request = [
         'SELECT' => ['id', 'values'],
         'FROM' => $table,
         'WHERE' => ['fieldtype' => ['dropdown']],
      ];
      foreach ($DB->request($request) as $row) {
         $newValues = $row['values'];
         $values = json_decode($row['values'], JSON_OBJECT_AS_ARRAY);
         if ($values === null) {
            continue;
         }
         $newValues = $values;
         unset($newValues['show_ticket_categories_root']);
         unset($newValues['show_ticket_categories_depth']);
         $newValues['show_tree_root'] = $values['show_ticket_categories_root'] ?? '';
         $newValues['show_tree_depth'] = $values['show_ticket_categories_depth'] ?? '-1';
         $newValues = json_encode($newValues);
         $DB->update($table, ['values' => $newValues], ['id' => $row['id']]);
      }
   }

   public function migrateReferenceEntity() {
      global $DB;

      $questionTable = 'glpi_plugin_formcreator_questions';
      $request = [
         'SELECT' => ['id', 'default_values', 'values'],
         'FROM' => $questionTable,
         'WHERE' => ['fieldtype' => ['droprown']],
      ];
      foreach ($DB->request($request) as $row) {
         $newAnswer = json_decode($row['values']);
         if ($newAnswer === null) {
            $newAnswer = ['itemtype' => $row['answer']];
            $newAnswer['entity_restrict'] = 2;
         } else {
            if (!isset($newAnswer['entity_restrict'])) {
               $newAnswer['entity_restrict'] = 'form';
            }
            switch ($newAnswer['entity_restrict']) {
               case 'user':
                  $newAnswer['entity_restrict'] = 1;
                  break;
               case 'both':
                  $newAnswer['entity_restrict'] = 3;
                  break;
               default:
                  $newAnswer['entity_restrict'] = 2;
                  break;
            }
         }
         $newAnswer = json_encode($newAnswer, JSON_OBJECT_AS_ARRAY);
         $newAnswer = Toolbox::addslashes_deep($newAnswer);
         $DB->update($questionTable, ['values' => $newAnswer], ['id' => $row['id']]);
      }
   }

   public function normalizeIssues() {
      $table = 'glpi_plugin_formcreator_issues';
      $this->migration->changeField($table, 'original_id', 'items_id', 'integer');
      $this->migration->changeField($table, 'sub_itemtype', 'itemtype', 'string', ['value' => '']);
      $this->migration->dropKey($table, 'original_id_sub_itemtype');
      $this->migration->addKey($table, ['itemtype', 'items_id'], 'item');
   }

   public function isResyncIssuesRequired() {
      return false;
   }
}
