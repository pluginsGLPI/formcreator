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
class PluginFormcreatorUpgradeTo2_11 {
   /** @var Migration */
   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      $this->migration = $migration;

      // rows / columns for sections
      $table = 'glpi_plugin_formcreator_questions';
      $migration->changeField($table, 'order', 'row', 'integer');
      $migration->addField($table, 'col', 'integer', ['after' => 'row']);
      $migration->addField($table, 'width', 'integer', ['after' => 'col']);
      $migration->addPostQuery("UPDATE `$table` SET `width`='4' WHERE `width` < '1'");
      // Reorder questions from 0 instead of 1
      $migration->migrationOneTable($table);
      $result = $DB->query("SELECT glpi_plugin_formcreator_sections.id FROM glpi_plugin_formcreator_sections
         INNER JOIN glpi_plugin_formcreator_questions ON (glpi_plugin_formcreator_sections.id = glpi_plugin_formcreator_questions.plugin_formcreator_sections_id)
         GROUP BY glpi_plugin_formcreator_sections.id
         HAVING MIN(glpi_plugin_formcreator_questions.`row`) > 0");
      foreach ($result as $row) {
         $DB->update($table, [
            'row' => new QueryExpression("`row` - 1")
         ],
         [
            'plugin_formcreator_sections_id' => $row['id']
         ]);
      }

      // add uuid to targetchanges
      $table = 'glpi_plugin_formcreator_targetchanges';
      $migration->addField($table, 'uuid', 'string', ['after' => 'category_question']);
      $migration->migrationOneTable($table);

      $request = [
         'SELECT' => 'id',
         'FROM' => $table,
      ];
      foreach ($DB->request($request) as $row) {
         $id = $row['id'];
         $uuid = plugin_formcreator_getUuid();
         $DB->query("UPDATE `$table`
            SET `uuid`='$uuid'
            WHERE `id`='$id'"
         ) or plugin_formcreator_upgrade_error($migration);
      }

      $this->migration->changeField('glpi_plugin_formcreator_questions', 'values', 'values', 'mediumtext');
      $this->migration->changeField('glpi_plugin_formcreator_questions', 'default_values', 'default_values', 'mediumtext');

      $this->migrateCheckboxesAndMultiselect();
      $this->migrateRadiosAndSelect();

      $tables = [
         'glpi_plugin_formcreator_targettickets',
         'glpi_plugin_formcreator_targetchanges'
      ];
      foreach ($tables as $table) {
         // Add SLA
         $migration->addField(
            $table,
            "sla_rule",
            "integer",
            ['value' => 1, 'after' => 'show_rule']
         );
         $migration->addField(
            $table,
            "sla_question_tto",
            "integer",
            ['value' => 0, 'after' => 'sla_rule']
         );
         $migration->addField(
            $table,
            "sla_question_ttr",
            "integer",
            ['value' => 0, 'after' => 'sla_question_tto']
         );

         // Add OLA
         $migration->addField(
            $table,
            "ola_rule",
            "integer",
            ['value' => 1, 'after' => 'sla_question_ttr']
         );
         $migration->addField(
            $table,
            "ola_question_tto",
            "integer",
            ['value' => 0, 'after' => 'ola_rule']
         );
         $migration->addField(
            $table,
            "ola_question_ttr",
            "integer",
            ['value' => 0, 'after' => 'ola_question_tto']
         );
         $migration->migrationOneTable($table);
      }

      // Move uuid field at last position
      $table = 'glpi_plugin_formcreator_targettickets';
      $migration->addPostQuery("ALTER TABLE `$table` MODIFY `uuid` varchar(255) DEFAULT NULL AFTER `ola_question_ttr`");
      $migration->migrationOneTable($table);
      $migration->changeField($table, 'type_rule', 'type_rule', 'integer', ['value' => '0']);

      // sort setting in entityes
      $table = 'glpi_plugin_formcreator_entityconfigs';
      if (!$DB->fieldExists($table, 'sort_order')) {
         // Write default settigns only if the columns must be created
         $migration->addPostQuery("UPDATE `$table`
            INNER JOIN `glpi_entities` ON (`$table`.`id` = `glpi_entities`.`id`)
            SET `sort_order` = '-2'
            WHERE `level` > '1'"
         );
      }
      $migration->addField($table, 'sort_order', 'integer', ['after' => 'replace_helpdesk']);

      // Remove unused column
      $table = 'glpi_plugin_formcreator_forms';
      $migration->dropField($table, 'requesttype');

      // Merge targettickets_actors and targetchanges_actors
      // Need a new table now
      $DB->query("CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_targets_actors` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `itemtype` varchar(255) DEFAULT NULL,
         `items_id` int(11) NOT NULL,
         `actor_role` int(11) NOT NULL DEFAULT '1',
         `actor_type` int(11) NOT NULL DEFAULT '1',
         `actor_value` int(11) DEFAULT NULL,
         `use_notification` tinyint(1) NOT NULL DEFAULT '1',
         `uuid` varchar(255) DEFAULT NULL,
         PRIMARY KEY (`id`),
         INDEX `item` (`itemtype`, `items_id`)
       ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
      $this->migrateTargetTicket_Actor();
      $this->migrateTargetChange_Actor();
      $this->addCaptchaOption();
      $this->addKbModeOption();
      $this->disableAutomaticAction();
   }

   /**
    * Migrate checkboxes and multiselect data to JSON
    *
    * @return void
    */
   public function migrateCheckboxesAndMultiselect() {
      global $DB;

      // Migrate default value
      $questionTable = 'glpi_plugin_formcreator_questions';
      $request = [
         'SELECT' => ['id', 'default_values', 'values'],
         'FROM' => $questionTable,
         'WHERE' => ['fieldtype' => ['checkboxes', 'multiselect']],
      ];
      foreach ($DB->request($request) as $row) {
         $newValues = $row['values'];
         if (json_decode($row['values']) === null) {
            $newValues = json_encode(explode("\r\n", $row['values']), JSON_OBJECT_AS_ARRAY+JSON_UNESCAPED_UNICODE);
         }
         $newValues = Toolbox::addslashes_deep($newValues);
         $newDefault = $row['default_values'];
         if (json_decode($row['default_values']) === null) {
            $newDefault = json_encode(explode("\r\n", $row['default_values']), JSON_OBJECT_AS_ARRAY+JSON_UNESCAPED_UNICODE);
         }
         $newDefault = Toolbox::addslashes_deep($newDefault);
         $DB->update($questionTable, ['values' => $newValues, 'default_values' => $newDefault], ['id' => $row['id']]);
      }

      // Migrate answers
      $answerTable = 'glpi_plugin_formcreator_answers';
      $request = [
         'SELECT' => ["$answerTable.id", 'answer'],
         'FROM' => $answerTable,
         'INNER JOIN' => [
            $questionTable => [
               'FKEY' => [
                  $questionTable => 'id',
                  $answerTable => 'plugin_formcreator_questions_id',
               ]
            ]
         ],
         'WHERE' => ['fieldtype' => ['checkboxes', 'multiselect']],
      ];
      foreach ($DB->request($request) as $row) {
         $newAnswer = $row['answer'];
         if (json_decode($row['answer']) === null) {
            $newAnswer = json_encode(explode("\r\n", $row['answer']), JSON_OBJECT_AS_ARRAY+JSON_UNESCAPED_UNICODE);
            $newAnswer = Toolbox::addslashes_deep($newAnswer);
            $DB->update($answerTable, ['answer' => $newAnswer], ['id' => $row['id']]);
         }
      }
   }

   /**
    * Migrate radios and select data to JSON
    *
    * @return void
    */
   public function migrateRadiosAndSelect() {
      global $DB;

      // Migrate default value
      $questionTable = 'glpi_plugin_formcreator_questions';
      $request = [
         'SELECT' => ['id', 'default_values', 'values'],
         'FROM' => $questionTable,
         'WHERE' => ['fieldtype' => ['radios', 'select']],
      ];
      foreach ($DB->request($request) as $row) {
         $newValues = $row['values'];
         if (json_decode($row['values']) === null) {
            $newValues = json_encode(explode("\r\n", $row['values']), JSON_OBJECT_AS_ARRAY+JSON_UNESCAPED_UNICODE);
            $newValues = Toolbox::addslashes_deep($newValues);
            $DB->update($questionTable, ['values' => $newValues], ['id' => $row['id']]);
         }
      }
   }

   public function migrateTargetTicket_Actor() {
      global $DB;

      $table = 'glpi_plugin_formcreator_targettickets_actors';
      if (!$DB->tableExists($table)) {
         return;
      }
      $DB->query(
      "INSERT INTO `glpi_plugin_formcreator_targets_actors`
         (`itemtype`, `items_id`, `actor_role`, `actor_type`, `actor_value`, `use_notification`, `uuid`)
         SELECT 'PluginFormcreatorTargetTicket', `plugin_formcreator_targettickets_id`, `actor_role`, `actor_type`, `actor_value`, `use_notification`, `uuid`
         FROM `$table`"
      );
      $this->migration->backupTables([$table]);
   }

   public function migrateTargetChange_Actor() {
      global $DB;

      $table = 'glpi_plugin_formcreator_targetchanges_actors';
      if (!$DB->tableExists($table)) {
         return;
      }

      $DB->query(
         "INSERT INTO `glpi_plugin_formcreator_targets_actors`
            (`itemtype`, `items_id`, `actor_role`, `actor_type`, `actor_value`, `use_notification`, `uuid`)
            SELECT 'PluginFormcreatorTargetChange', `plugin_formcreator_targetchanges_id`, `actor_role`, `actor_type`, `actor_value`, `use_notification`, `uuid`
            FROM `$table`"
      );
      $this->migration->backupTables([$table]);
   }

   public function addCaptchaOption() {
      $table = 'glpi_plugin_formcreator_forms';
      $this->migration->addField($table, 'is_captcha_enabled', 'bool', ['after' => 'is_default']);
   }

   public function addKbModeOption() {
      $table = 'glpi_plugin_formcreator_entityconfigs';
      $this->migration->addField($table, 'is_kb_separated', 'integer', ['after' => 'sort_order']);
   }

   /**
    * Deprecate SyncIssues automatic action
    * SyncIssues should be now used only for fresh instals or rebuild of corrupted issues table
    *
    * @return void
    */
   public function disableAutomaticAction() {
      $cronTask = new CronTask();
      $cronTask->getFromDBByCrit([
         'itemtype' => 'PluginFormcreatorISsue',
         'name'     => 'SyncIssues'
      ]);
      if ($cronTask->isNewItem()) {
         return;
      }
      $cronTask->update([
         'id'    => $cronTask->getID(),
         'state' => '0'
      ]);
   }

   public function isResyncIssuesRequired() {
      return false;
   }
}
