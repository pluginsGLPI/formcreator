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
      $this->fixTables();
      $this->updateShortText();
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
      $this->fixissues();
      $this->migrateTablesToDynamic();
   }

   /**
    * Fix possible inconsistencies accumulated over years from 2.5.0 to 2.12.5
    * At the end of this method the schema shall match the version 2.12.5
    * except for the type of IDs and FK which are immediateli migrated to unsigned int
    * Modifications are immediately applied to tables as the state of the schema is the expected
    * basis for changes required to migrate to 2.13.0
    *
    * @return void
    */
   public function fixTables(): void {
      global $DB;

      // Based on schema from version 2.12.5, try to fix some harlmess inconsistencies
      // To avoid annoying warnings, and contrary to the original 2.12.5 schema, the foreign keys are updated
      // with unsigned integer, with assumption that the admin aloready migrated IDs
      // and FK to unsigned with the GLPI Core CLI command

      $unsignedIntType = "INT UNSIGNED NOT NULL DEFAULT '0'";

      $table = 'glpi_plugin_formcreator_answers';
      $this->migration->changeField($table, 'plugin_formcreator_formanswers_id', 'plugin_formcreator_formanswers_id', $unsignedIntType);
      $this->migration->changeField($table, 'plugin_formcreator_questions_id', 'plugin_formcreator_questions_id', $unsignedIntType);
      $this->migration->dropKey($table, 'plugin_formcreator_question_id');
      $this->migration->addKey($table, 'plugin_formcreator_questions_id', 'plugin_formcreator_questions_id');
      $this->migration->migrationOneTable($table);

      $table = 'glpi_plugin_formcreator_forms';
      $this->migration->changeField($table, 'name', 'name', 'string', ['value' => '']);
      $this->migration->changeField($table, 'description', 'description', 'string');
      $this->migration->migrationOneTable($table);

      $table = 'glpi_plugin_formcreator_formanswers';
      $DB->update(
         $table,
         ['users_id_validator' => '0'],
         ['users_id_validator' => null]
      );
      $DB->update(
         $table,
         ['groups_id_validator' => '0'],
         ['groups_id_validator' => null]
      );
      $this->migration->changeField($table, 'users_id_validator', 'users_id_validator', $unsignedIntType);
      $this->migration->changeField($table, 'groups_id_validator', 'groups_id_validator', $unsignedIntType);
      $this->migration->changeField($table, 'name', 'name', 'string', ['value' => '']);
      $this->migration->migrationOneTable($table);

      $table = 'glpi_plugin_formcreator_questions';
      $DB->update(
         $table,
         ['name' => ''],
         ['name' => null]
      );
      $this->migration->changeField($table, 'name', 'name', 'string', ['value' => '']);
      $this->migration->changeField($table, 'description', 'description', 'mediumtext');
      // Assume the content of the 2 following columns is out of date
      // because they should have been migrated in version 2.7.0
      $this->migration->dropField($table, 'range_min');
      $this->migration->dropField($table, 'range_max');
      $this->migration->migrationOneTable($table);

      $table = 'glpi_plugin_formcreator_issues';
      $this->migration->addField($table, 'users_id_validator', 'integer', ['after' => 'requester_id']);
      $this->migration->addField($table, 'groups_id_validator', 'integer', ['after' => 'users_id_validator']);
      $this->migration->addKey($table, 'users_id_validator', 'users_id_validator');
      $this->migration->addKey($table, 'groups_id_validator', 'groups_id_validator');
      $this->migration->changeField($table, 'itemtype', 'itemtype', 'string', ['value' => '']);

      $table = 'glpi_plugin_formcreator_sections';
      $DB->update(
         $table,
         ['name' => ''],
         ['name' => null]
      );
      $this->migration->changeField($table, 'name', 'name', 'string', ['value' => '']);
      $this->migration->migrationOneTable($table);

      $table = 'glpi_plugin_formcreator_targettickets';
      $DB->update(
         $table,
         ['destination_entity_value' => '0'],
         ['destination_entity_value' => null]
      );
      $DB->update(
         $table,
         ['sla_question_tto' => '0'],
         ['sla_question_tto' => null]
      );
      $DB->update(
         $table,
         ['sla_question_ttr' => '0'],
         ['sla_question_ttr' => null]
      );
      $DB->update(
         $table,
         ['ola_question_tto' => '0'],
         ['ola_question_tto' => null]
      );
      $DB->update(
         $table,
         ['ola_question_ttr' => '0'],
         ['ola_question_ttr' => null]
      );
      $DB->update(
         $table,
         ['sla_rule' => '0'],
         ['sla_rule' => null]
      );
      $DB->update(
         $table,
         ['ola_rule' => '0'],
         ['ola_rule' => null]
      );
      $this->migration->changeField($table, 'validation_followup', 'validation_followup', 'bool', ['after' => 'urgency_question', 'value' => '1']);
      $this->migration->changeField($table, 'destination_entity', 'destination_entity', 'integer', ['after' => 'validation_followup', 'value' => '1']);
      $this->migration->changeField($table, 'destination_entity_value', 'destination_entity_value', $unsignedIntType, ['after' => 'destination_entity', 'default' => '1']);
      $this->migration->changeField($table, 'tag_type', 'tag_type', 'integer', ['after' => 'destination_entity_value', 'value' => '1']);
      $this->migration->changeField($table, 'tag_questions', 'tag_questions', 'string', ['after' => 'tag_type']);
      $this->migration->changeField($table, 'tag_specifics', 'tag_specifics', 'string', ['after' => 'tag_questions']);
      $this->migration->changeField($table, 'category_rule', 'category_rule', 'integer', ['after' => 'tag_specifics', 'value' => '1']);
      $this->migration->changeField($table, 'category_question', 'category_question', 'integer', ['after' => 'category_rule']);
      $this->migration->changeField($table, 'associate_rule', 'associate_rule', 'integer', ['after' => 'category_question', 'value' => '1']);
      $this->migration->changeField($table, 'associate_question', 'associate_question', $unsignedIntType, ['after' => 'associate_rule']);
      $this->migration->changeField($table, 'location_rule', 'location_rule', 'integer', ['after' => 'associate_question', 'value' => '1']);
      $this->migration->changeField($table, 'location_question', 'location_question', $unsignedIntType, ['after' => 'location_rule']);
      $this->migration->changeField($table, 'show_rule', 'show_rule', 'integer', ['after' => 'location_question', 'value' => '1']);
      $this->migration->changeField($table, 'sla_rule', 'sla_rule', 'integer', ['after' => 'show_rule', 'value' => '1']);
      $this->migration->changeField($table, 'sla_question_tto', 'sla_question_tto', $unsignedIntType, ['after' => 'sla_rule']);
      $this->migration->changeField($table, 'sla_question_ttr', 'sla_question_ttr', $unsignedIntType, ['after' => 'sla_question_tto']);
      $this->migration->changeField($table, 'ola_rule', 'ola_rule', 'integer', ['after' => 'sla_question_ttr', 'value' => '1']);
      $this->migration->changeField($table, 'ola_question_tto', 'ola_question_tto', $unsignedIntType, ['after' => 'ola_rule']);
      $this->migration->changeField($table, 'ola_question_ttr', 'ola_question_ttr', $unsignedIntType, ['after' => 'ola_question_tto']);
      $this->migration->changeField($table, 'uuid', 'uuid', 'string', ['after' => 'ola_question_ttr']);
      $this->migration->migrationOneTable($table);

      $table = 'glpi_plugin_formcreator_questiondependencies';
      if ($DB->tableExists($table)) {
         // Table may be created at the very end when upgrading from < 2.12
         $this->migration->changeField($table, 'plugin_formcreator_questions_id', 'plugin_formcreator_questions_id', $unsignedIntType);
         $this->migration->changeField($table, 'plugin_formcreator_questions_id_2', 'plugin_formcreator_questions_id_2', $unsignedIntType);
         $this->migration->migrationOneTable($table);
      }

      $table = 'glpi_plugin_formcreator_forms_languages';
      if ($DB->tableExists($table)) {
         // Table may be created at the very end when upgrading from < 2.12
         $this->migration->changeField($table, 'plugin_formcreator_forms_id', 'plugin_formcreator_forms_id', $unsignedIntType);
         $this->migration->migrationOneTable($table);
      }
   }

   public function updateShortText() {
      $table = 'glpi_plugin_formcreator_categories';
      $this->migration->changeField($table, 'comment', 'comment', 'mediumtext');

      $table = 'glpi_plugin_formcreator_formanswers';
      $this->migration->changeField($table, 'comment', 'comment', 'mediumtext');

      $table = 'glpi_plugin_formcreator_questionregexes';
      $this->migration->changeField($table, 'regex', 'regex', 'mediumtext');
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
         'glpi_plugin_formcreator_answers' => [
            'plugin_formcreator_formanswers_id',
            'plugin_formcreator_questions_id',
         ],
         'glpi_plugin_formcreator_formanswers' => [
            'plugin_formcreator_forms_id',
            'requester_id',
            'users_id_validator',
            'groups_id_validator',
         ],
         'glpi_plugin_formcreator_forms_languages' => [
            'plugin_formcreator_forms_id',
         ],
         'glpi_plugin_formcreator_forms_profiles' => [
            'plugin_formcreator_forms_id',
            'profiles_id',
         ],
         'glpi_plugin_formcreator_forms_validators' => [
            'plugin_formcreator_forms_id',
            'items_id',
         ],
         'glpi_plugin_formcreator_issues' => [
            'users_id_recipient',
            'plugin_formcreator_categories_id',
         ],
         'glpi_plugin_formcreator_questions' => [
            'plugin_formcreator_sections_id',
         ],
         'glpi_plugin_formcreator_questiondependencies' => [
            'plugin_formcreator_questions_id',
            'plugin_formcreator_questions_id_2',
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
         if (!$DB->tableExists($table)) {
            continue;
         }
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

   public function fixissues() {
      $table = 'glpi_plugin_formcreator_issues';

      $this->migration->changeField($table, 'name', 'name', 'string', ['after' => 'id', 'nodefault' => true]);
      $this->migration->changeField($table, 'status', 'status', 'string', ['value' => '']);
   }

   public function isResyncIssuesRequired() {
      return false;
   }

   public function migrateTablesToDynamic() {
      global $DB;

      // all tables in previous release of Formcreator (2.12.5)
      $tables = [
         'glpi_plugin_formcreator_answers',
         'glpi_plugin_formcreator_categories',
         'glpi_plugin_formcreator_entityconfigs',
         'glpi_plugin_formcreator_forms',
         'glpi_plugin_formcreator_formanswers',
         'glpi_plugin_formcreator_forms_profiles',
         'glpi_plugin_formcreator_forms_validators',
         'glpi_plugin_formcreator_questions',
         'glpi_plugin_formcreator_conditions',
         'glpi_plugin_formcreator_sections',
         'glpi_plugin_formcreator_targetchanges',
         'glpi_plugin_formcreator_targettickets',
         'glpi_plugin_formcreator_targets_actors',
         'glpi_plugin_formcreator_issues',
         'glpi_plugin_formcreator_items_targettickets',
         'glpi_plugin_formcreator_questiondependencies',
         'glpi_plugin_formcreator_questionregexes',
         'glpi_plugin_formcreator_questionranges',
         'glpi_plugin_formcreator_forms_languages',
      ];

      foreach ($tables as $table) {
         if (!$DB->tableExists($table)) {
            continue;
         }
         $DB->query("ALTER TABLE `$table` ROW_FORMAT = DYNAMIC");
      }
   }
}
