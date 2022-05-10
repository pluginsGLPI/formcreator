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
class PluginFormcreatorUpgradeTo2_13 {
   /** @var Migration */
   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      $this->migration = $migration;
      $this->migrateEntityConfig();
      $this->addDefaultFormListMode();
      $this->addDashboardVisibility();
      $this->fixRootEntityConfig();
      $this->migrateFkToUnsignedInt();
      $this->addFormAnswerTitle();
      $this->defaultValuesForTargets();
      $this->migrateItemtypeInQuestion();
      $this->addTargetValidationSetting();
      $this->addFormVisibility();
      $this->addRequestSourceSetting();
      $this->addEntityOption();
   }

   public function addEntityOption() {
      global $DB;
      $table = 'glpi_plugin_formcreator_entityconfigs';

      if (!$DB->fieldExists($table, 'is_search_issue_visible')) {
         $this->migration->addField($table, 'is_search_issue_visible', 'integer', ['after' => 'is_header_visible', 'value' => '-2']);
         $this->migration->addPostQuery("UPDATE `glpi_plugin_formcreator_entityconfigs` SET `is_search_issue_visible`= 1 WHERE `entities_id` = 0");
      }

      if (!$DB->fieldExists($table, 'tile_design')) {
         $this->migration->addField($table, 'tile_design', 'integer', ['after' => 'is_search_issue_visible', 'value' => '-2']);
         $this->migration->addPostQuery("UPDATE `glpi_plugin_formcreator_entityconfigs` SET `tile_design`= '0' WHERE `entities_id` = 0");
      }
   }

   public function addFormAnswerTitle() {
      $table = 'glpi_plugin_formcreator_forms';

      $this->migration->addField($table, 'formanswer_name', 'string', ['after' => 'show_rule', 'value' => '']);
      $this->migration->addPostQuery("UPDATE `$table` SET `formanswer_name`=`name`");
   }

   public function defaultValuesForTargets() {
      $tables = [
         'glpi_plugin_formcreator_targettickets',
         'glpi_plugin_formcreator_targetchanges',
      ];
      $fieleds = [
         'tag_questions',
         'tag_specifics',
      ];
      foreach ($tables as $table) {
         foreach ($fieleds as $field) {
            $this->migration->changeField($table, $field, $field, 'string', ['value' => '']);
         }
      }
   }

   public function migrateItemtypeInQuestion() {
      global $DB;

      $table = 'glpi_plugin_formcreator_questions';
      $this->migration->addField($table, 'itemtype', 'string', ['value' => '', 'after' => 'default_values']);
      $this->migration->migrationOneTable($table);

      $request = $DB->request([
         'SELECT' => ['id','values'],
         'FROM'   => $table,
         'WHERE'  => [
            'fieldtype' => ['dropdown', 'glpiselect'],
         ]
      ]);
      foreach ($request as $row) {
         $values = $row['values'];
         $decodedValues = json_decode($values, JSON_OBJECT_AS_ARRAY);
         if (!is_array($decodedValues)) {
            if (strlen($values) < 1) {
               continue;
            }
            $itemtype = $values;
            $values = '';
         } else {
            if (!isset($decodedValues['itemtype'])) {
               continue;
            }
            $itemtype = $decodedValues['itemtype'];
            unset($decodedValues['itemtype']);
            $values = '';
            if (count($decodedValues) > 0) {
               $values = json_encode($decodedValues);
            }
         }
         $DB->update($table, [
            'itemtype' => $itemtype,
            'values'   => $values,
         ], [
            'id' => $row['id']
         ]);
      }
   }

   protected function addTargetValidationSetting() {
      $table = 'glpi_plugin_formcreator_targetchanges';
      $this->migration->addField($table, 'commonitil_validation_rule', 'integer', ['value' => '1', 'after' => 'category_question']);
      $this->migration->addField($table, 'commonitil_validation_question', 'string', ['after' => 'commonitil_validation_rule']);

      $table = 'glpi_plugin_formcreator_targettickets';
      $this->migration->addField($table, 'commonitil_validation_rule', 'integer', ['value' => '1', 'after' => 'location_question']);
      $this->migration->addField($table, 'commonitil_validation_question', 'string', ['after' => 'commonitil_validation_rule']);
   }

   protected function addFormVisibility() {
      // Add is_visible on forms
      $table = 'glpi_plugin_formcreator_forms';
      $this->migration->addField($table, 'is_visible', 'bool', ['value' => 1, 'after' => 'formanswer_name']);
   }

   protected function addDashboardVisibility() {
      $table = 'glpi_plugin_formcreator_entityconfigs';
      $this->migration->addField($table, 'is_dashboard_visible', 'integer', ['after' => 'is_search_visible', 'value' => '-2']);

      $this->migration->addPostQuery("UPDATE `glpi_plugin_formcreator_entityconfigs` SET `is_dashboard_visible`=1 WHERE `entities_id`=0");
   }

   protected function migrateEntityConfig() {
      global $DB;

      $table = 'glpi_plugin_formcreator_entityconfigs';

      if ($DB->fieldExists($table, 'entities_id')) {
         // Already migrated
         return;
      }

      $this->migration->addField($table, 'entities_id', 'int unsigned not null default 0', ['after' => 'id']);
      $this->migration->migrationOneTable($table);
      $DB->queryOrDie("UPDATE `$table` SET `entities_id`=`id`");
      $this->migration->addKey($table, 'entities_id', 'unicity', 'UNIQUE');
   }

   /**
    * Fix possible invalid root entity config
    *
    * @return void
    */
   private function fixRootEntityConfig(): void {
      global $DB;

      $table = 'glpi_plugin_formcreator_entityconfigs';
      $DB->update($table, [
         'replace_helpdesk' => new QueryExpression("IF(`replace_helpdesk` = -2, 0, `replace_helpdesk`)"),
         'default_form_list_mode' => new QueryExpression("IF(`default_form_list_mode` = -2, 0, `default_form_list_mode`)"),
         'sort_order' => new QueryExpression("IF(`sort_order` = -2, 0, `sort_order`)"),
         'is_kb_separated' => new QueryExpression("IF(`is_kb_separated` = -2, 0, `is_kb_separated`)"),
         'is_search_visible' => new QueryExpression("IF(`is_search_visible` = -2, 1, `is_search_visible`)"),
         'is_header_visible' => new QueryExpression("IF(`is_header_visible` = -2, 1, `is_header_visible`)"),
      ], [
         'entities_id' => 0,
      ]);
   }

   protected function migrateFkToUnsignedInt() {
      global $DB;

      $table = 'glpi_plugin_formcreator_formanswers';
      $DB->queryOrDie("UPDATE `$table` SET `requester_id` = 0 WHERE `requester_id` IS NULL");

      $table = 'glpi_plugin_formcreator_targetchanges';
      $DB->queryOrDie("UPDATE `$table` SET `due_date_question` = 0 WHERE `due_date_question` IS NULL");
      $DB->queryOrDie("UPDATE `$table` SET `destination_entity_value` = 0 WHERE `destination_entity_value` IS NULL");

      $table = 'glpi_plugin_formcreator_targettickets';
      $DB->queryOrDie("UPDATE `$table` SET `due_date_question` = 0 WHERE `due_date_question` IS NULL");
      $DB->queryOrDie("UPDATE `$table` SET `destination_entity_value` = 0 WHERE `destination_entity_value` IS NULL");

      $table = 'glpi_plugin_formcreator_targets_actors';
      $DB->queryOrDie("UPDATE `$table` SET `actor_value` = 0 WHERE `actor_value` IS NULL");

      $tables = [
         'glpi_plugin_formcreator_formanswers' => [
            'plugin_formcreator_forms_id',
            'requester_id',
         ],
         'glpi_plugin_formcreator_forms_profiles' => [
            'plugin_formcreator_forms_id',
            'profiles_id',
         ],
         'glpi_plugin_formcreator_forms_validators' => [
            'plugin_formcreator_forms_id',
            'items_id',
         ],
         'glpi_plugin_formcreator_questions' => [
            'plugin_formcreator_sections_id',
         ],
         'glpi_plugin_formcreator_sections' => [
            'plugin_formcreator_forms_id',
         ],
         'glpi_plugin_formcreator_targetchanges' => [
            'due_date_question',
            'urgency_question',
            'destination_entity_value',
            'category_question',
            'sla_question_tto',
            'sla_question_ttr',
            'ola_question_tto',
            'ola_question_ttr',
         ],
         'glpi_plugin_formcreator_targettickets' => [
            'type_question',
            'due_date_question',
            'urgency_question',
            'destination_entity_value',
            'category_question',
            'associate_question',
            'location_question',
            'sla_question_tto',
            'sla_question_ttr',
            'ola_question_tto',
            'ola_question_ttr',
         ],
         'glpi_plugin_formcreator_targets_actors' => [
            'items_id',
            'actor_value',
         ],
         'glpi_plugin_formcreator_questionregexes' => [
            'plugin_formcreator_questions_id',
         ],
         'glpi_plugin_formcreator_questionranges' => [
            'plugin_formcreator_questions_id',
         ],
      ];

      foreach ($tables as $table => $fields) {
         foreach ($fields as $field) {
            if ($field == 'id') {
               $type = 'autoincrement';
            } else {
               $type = "INT " . DBConnection::getDefaultPrimaryKeySignOption() . " NOT NULL DEFAULT 0";
            }
            $this->migration->changeField($table, $field, $field, $type);
         }
      }

      $table = 'glpi_plugin_formcreator_entityconfigs';
      $rows = $DB->request([
         'COUNT' => 'c',
         'FROM' => $table,
         'WHERE' => ['id' => 0]
      ]);
      $count = $rows !== null ?$rows->current()['c'] : null;
      if ($count !== null) {
         if ($count == 1) {
            $rows = $DB->request([
               'SELECT' => ['MAX' => 'id AS max_id'],
               'FROM' => $table,
            ]);
            $newId = (int) ($rows->current()['max_id'] + 1);
            $DB->query("UPDATE `$table` SET `id`='$newId' WHERE `id` = 0");
         }
      }
      $this->migration->changeField($table, 'id', 'id', 'int ' . DBConnection::getDefaultPrimaryKeySignOption() . ' not null auto_increment');
   }

   public function addRequestSourceSetting(): void {
      global $DB;

      $table = 'glpi_plugin_formcreator_targettickets';

      if (!$DB->fieldExists($table, 'source_rule')) {
         $this->migration->addField($table, 'source_rule', 'integer', ['after' => 'target_name']);
         $this->migration->addField($table, 'source_question', 'integer', ['after' => 'source_rule']);
         $this->migration->migrationOneTable($table);
         $formcreatorSourceId = PluginFormcreatorCommon::getFormcreatorRequestTypeId();
         $DB->queryOrDie("UPDATE `$table` SET `source_rule` = '1', `source_question` = '$formcreatorSourceId'");
      }
   }

   public function addDefaultFormListMode() {
      $table = 'glpi_plugin_formcreator_entityconfigs';

      $this->migration->addField($table, 'default_form_list_mode', 'int not null default -2', ['after' => 'replace_helpdesk']);
      $this->migration->migrationOneTable($table);

      $this->migration->addPostQuery("UPDATE `glpi_plugin_formcreator_entityconfigs` SET `default_form_list_mode`=0 WHERE `entities_id`=0");
   }
}
