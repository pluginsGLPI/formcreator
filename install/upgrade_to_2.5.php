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

/**
 * Upgrade any version of Formcreator < 2.5.0 to 2.5.0
 * @param Migration $migration
 */
class PluginFormcreatorUpgradeTo2_5 {
   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      $migration->displayMessage("Upgrade to schema version 2.5");

      $this->updateAnswer_2_5($migration);
      $this->updateCategory_2_5($migration);
      $this->updateForm_Answer_2_5($migration);
      $this->updateForm_Profile_2_5($migration);
      $this->updateFormValidator_2_5($migration);
      $this->updateForm_2_5($migration);
      $this->updateHeader_2_5($migration);
      $this->updateIssue_2_5($migration);
      $this->updateQuestionCondition_2_5($migration);
      $this->updateQuestion_2_5($migration);
      $this->updateSection_2_5($migration);
      $this->updateTarget_2_5($migration);
      $this->updateTargetChange_2_5($migration);
      $this->updateTargetChange_Actor_2_5($migration);
      $this->updateTargetTicket_Actor_2_5($migration);
      $this->updateTargetTicket_2_5($migration);
      $this->updateTitle_2_5($migration);

      $migration->executeMigration();
   }

   private function updateAnswer_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of Answers
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_answers");
      // Update field type from previous version (Need answer to be text since text can be WYSIWING).
      $query = "ALTER TABLE  `glpi_plugin_formcreator_answers` CHANGE  `answer` `answer` text;";
      $DB->query($query) or plugin_formcreator_upgrade_error($migration);

      /**
       * Migration of special chars from previous versions
      *
      * @since 0.85-1.2.3
      */
      $query  = "SELECT `id`, `answer` FROM `glpi_plugin_formcreator_answers`";
      $result = $DB->query($query);
      while ($line = $DB->fetch_array($result)) {
         $query_update = "UPDATE `glpi_plugin_formcreator_answers` SET
         `answer` = '".addslashes($line['answer'])."'
                           WHERE `id` = ".$line['id'];
         $DB->query($query_update) or plugin_formcreator_upgrade_error($migration);
      }

      //rename foreign key, to match table plugin_formcreator_forms_answers name
      $migration->changeField('glpi_plugin_formcreator_answers',
            'plugin_formcreator_formanwers_id',
            'plugin_formcreator_forms_answers_id',
            'integer');

      $migration->addKey('glpi_plugin_formcreator_answers', 'plugin_formcreator_forms_answers_id');
      $migration->addKey('glpi_plugin_formcreator_answers', 'plugin_formcreator_question_id');

      $migration->migrationOneTable('glpi_plugin_formcreator_answers');
   }

   function updateCategory_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of Categories
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_categories");

      if ($DB->tableExists('glpi_plugin_formcreator_cats')) {
         $query = "INSERT IGNORE INTO `glpi_plugin_formcreator_categories` (`id`, `name`)
                  SELECT `id`,`name` FROM glpi_plugin_formcreator_cats";
         $DB->query($query);
         $DB->query("DROP TABLE glpi_plugin_formcreator_cats");
      }

      /**
       * Migration of special chars from previous versions
      *
      * @since 0.85-1.2.3
      */
      $query  = "SELECT `id`, `name`, `comment` FROM `glpi_plugin_formcreator_categories`";
      $result = $DB->query($query);
      while ($line = $DB->fetch_array($result)) {
         $query_update = "UPDATE `glpi_plugin_formcreator_categories` SET
                        `name`    = '".plugin_formcreator_encode($line['name'], false)."',
                        `comment` = '".plugin_formcreator_encode($line['comment'], false)."'
                        WHERE `id` = ".$line['id'];
         $DB->query($query_update) or plugin_formcreator_upgrade_error($migration);
      }

      /**
       * Migrate categories to tree structure
      *
      * @since 0.90-1.4
      */
      if (!$DB->fieldExists('glpi_plugin_formcreator_categories', "knowbaseitemcategories_id")) {
         $migration->addField('glpi_plugin_formcreator_categories', 'completename', 'string', [
            'after' => 'comment'
         ]);
         $migration->addField('glpi_plugin_formcreator_categories', 'plugin_formcreator_categories_id', 'integer', [
            'after' => 'completename'
         ]);
         $migration->addField('glpi_plugin_formcreator_categories', 'level', 'integer', [
            'value' => 1,
            'after' => 'plugin_formcreator_categories_id'
         ]);
         $migration->addField('glpi_plugin_formcreator_categories', 'sons_cache', 'longtext', [
            'after' => 'level'
         ]);
         $migration->addField('glpi_plugin_formcreator_categories', 'ancestors_cache', 'longtext', [
            'after' => 'sons_cache'
         ]);
         $migration->addField('glpi_plugin_formcreator_categories', 'knowbaseitemcategories_id', 'integer', [
            'after' => 'ancestors_cache'
         ]);

         $migration->addKey('glpi_plugin_formcreator_categories', 'knowbaseitemcategories_id');
         $migration->addKey('glpi_plugin_formcreator_categories', 'plugin_formcreator_categories_id');
         $migration->migrationOneTable('glpi_plugin_formcreator_categories');
         $query  = "UPDATE `glpi_plugin_formcreator_categories` SET `completename` = `name` WHERE `completename` IS NULL";
         $DB->query($query);
      }
   }

   function updateForm_Answer_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of Form_Answers
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_forms_answers");

      if ($DB->tableExists("glpi_plugin_formcreator_formanswers")) {
         $migration->renameTable('glpi_plugin_formcreator_formanswers', 'glpi_plugin_formcreator_forms_answers');
         $itemTicket_table = 'glpi_items_tickets';
         $query = "UPDATE `$itemTicket_table` SET `itemtype` = 'PluginFormcreatorForm_Answer' WHERE `itemtype` = 'PluginFormcreatorFormanswer'";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }

      /**
       * Migration of special chars from previous versions
      *
      * @since 0.85-1.2.3
      */
      $query  = "SELECT `id`, `comment` FROM `glpi_plugin_formcreator_forms_answers`";
      $result = $DB->query($query);
      while ($line = $DB->fetch_array($result)) {
         $query_update = "UPDATE `glpi_plugin_formcreator_forms_answers` SET
                        `comment` = '" . plugin_formcreator_encode($line['comment']) . "'
                        WHERE `id` = " . $line['id'];
         $DB->query($query_update) or plugin_formcreator_upgrade_error($migration);
      }

      if (!$DB->fieldExists('glpi_plugin_formcreator_forms_answers', 'name')) {
         $query_update = 'ALTER TABLE `glpi_plugin_formcreator_forms_answers` ADD `name` VARCHAR(255) NOT NULL AFTER `id`';
         $DB->query($query_update) or plugin_formcreator_upgrade_error($migration);
      }

      // valdiator_id should not be set for waiting form answers
      $query = "UPDATE `glpi_plugin_formcreator_forms_answers`
               SET `validator_id` = '0' WHERE `status`='waiting'";
      $DB->query($query) or plugin_formcreator_upgrade_error($migration);

      $migration->addKey('glpi_plugin_formcreator_forms_answers', 'plugin_formcreator_forms_id');
      $migration->addKey('glpi_plugin_formcreator_forms_answers', ['entities_id', 'is_recursive']);
      $migration->addKey('glpi_plugin_formcreator_forms_answers', 'requester_id');
      $migration->addKey('glpi_plugin_formcreator_forms_answers', 'validator_id');
      $migration->addField('glpi_plugin_formcreator_forms_answers', 'is_deleted', 'bool');
      $migration->migrationOneTable('glpi_plugin_formcreator_forms_answers');
   }

   function updateForm_Profile_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of Form_Profiles
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_forms_profiles");

      if ($DB->tableExists('glpi_plugin_formcreator_formprofiles')) {
         $migration->renameTable('glpi_plugin_formcreator_formprofiles', 'glpi_plugin_formcreator_forms_profiles');
      }

      // change fk for profiles
      if ($DB->fieldExists('glpi_plugin_formcreator_forms_profiles', 'plugin_formcreator_profiles_id', false)) {
         $migration->changeField('glpi_plugin_formcreator_forms_profiles', 'plugin_formcreator_profiles_id', 'profiles_id', 'integer');
      }

      // redo an id key
      if (!$DB->fieldExists('glpi_plugin_formcreator_forms_profiles', 'id', false)) {
         $DB->query("ALTER TABLE 'glpi_plugin_formcreator_forms_profiles' DROP PRIMARY KEY");
         $migration->addField('glpi_plugin_formcreator_forms_profiles', 'id', 'autoincrement');
         $migration->addKey('glpi_plugin_formcreator_forms_profiles', 'id', 'id', 'PRIMARY KEY');
         $migration->addKey('glpi_plugin_formcreator_forms_profiles',
                           ['plugin_formcreator_forms_id', 'profiles_id'],
                           'unicity',
                           'UNIQUE KEY');
      }

      // add uuid to validator
      if (!$DB->fieldExists('glpi_plugin_formcreator_forms_profiles', 'uuid', false)) {
         $migration->addField('glpi_plugin_formcreator_forms_profiles', 'uuid', 'string');
      }
      $migration->migrationOneTable('glpi_plugin_formcreator_forms_profiles');

      // fill missing uuid
      $DB->update('glpi_plugin_formcreator_forms_profiles', [
         'uuid' => plugin_formcreator_getUuid()
      ], [
         'uuid' => null
      ]);
   }

   function updateFormValidator_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of Form_Validators
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_forms_validators");

      // Convert the old relation in glpi_plugin_formcreator_formvalidators table
      if ($DB->tableExists('glpi_plugin_formcreator_formvalidators')) {
         $table_form = 'glpi_plugin_formcreator_forms';
         $old_table = 'glpi_plugin_formcreator_formvalidators';
         $query = "INSERT INTO `glpi_plugin_formcreator_forms_validators` (`plugin_formcreator_forms_id`, `itemtype`, `items_id`)
                  SELECT
                     `$old_table`.`forms_id`,
                  IF(`validation_required` = '".PluginFormcreatorForm_Validator::VALIDATION_USER."', 'User', 'Group'),
                     `$old_table`.`users_id`
                  FROM `$old_table`
                  LEFT JOIN `$table_form` ON (`$table_form`.`id` = `$old_table`.`forms_id`)
                  WHERE `validation_required` > 1";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
         $migration->displayMessage('Backing up table glpi_plugin_formcreator_formvalidators');
         $migration->renameTable('glpi_plugin_formcreator_formvalidators', 'glpi_plugin_formcreator_formvalidators_backup');
      }

      // add uuid to validator
      if (!$DB->fieldExists('glpi_plugin_formcreator_forms_validators', 'uuid', false)) {
         $migration->addField('glpi_plugin_formcreator_forms_validators', 'uuid', 'string');
         $migration->migrationOneTable('glpi_plugin_formcreator_forms_validators');
      }

      // fill missing uuid
      $DB->update('glpi_plugin_formcreator_forms_validators', [
         'uuid' => plugin_formcreator_getUuid()
      ], [
         'uuid' => null
      ]);
   }

   function updateForm_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of Forms
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_forms");

      // Migration from previous version
      if ($DB->fieldExists('glpi_plugin_formcreator_forms', 'cat', false)
         || !$DB->fieldExists('glpi_plugin_formcreator_forms', 'plugin_formcreator_categories_id', false)) {
               $migration->addField('glpi_plugin_formcreator_forms', 'plugin_formcreator_categories_id',
                                    'integer', ['value' => '0']);
      }

      // Migration from previous version
      if (!$DB->fieldExists('glpi_plugin_formcreator_forms', 'validation_required', false)) {
         $migration->addField('glpi_plugin_formcreator_forms', 'validation_required', 'bool', ['value' => '0']);
      }

      // Migration from previous version
      if (!$DB->fieldExists('glpi_plugin_formcreator_forms', 'requesttype', false)) {
         $migration->addField('glpi_plugin_formcreator_forms', 'access_rights', 'bool', ['value' => '1']);
         $migration->addField('glpi_plugin_formcreator_forms', 'requesttype', 'integer', ['value' => '0']);
         $migration->addField('glpi_plugin_formcreator_forms', 'description', 'string');
         $migration->addField('glpi_plugin_formcreator_forms', 'helpdesk_home', 'bool', ['value' => '0']);
         $migration->addField('glpi_plugin_formcreator_forms', 'is_deleted', 'bool', ['value' => '0']);
      }
      $migration->migrationOneTable('glpi_plugin_formcreator_forms');

      /**
       * Migration of special chars from previous versions
      *
      * @since 0.85-1.2.3
      */
      $query  = "SELECT `id`, `name`, `description`, `content` FROM `glpi_plugin_formcreator_forms`";
      $result = $DB->query($query);
      while ($line = $DB->fetch_array($result)) {
         $query_update = "UPDATE `glpi_plugin_formcreator_forms` SET
                        `name`        = '" . plugin_formcreator_encode($line['name']) . "',
                        `description` = '" . plugin_formcreator_encode($line['description']) . "',
                        `content`     = '" . plugin_formcreator_encode($line['content']) . "'
                        WHERE `id` = " . (int) $line['id'];
         $DB->query($query_update) or plugin_formcreator_upgrade_error($migration);
      }

      /**
       * Add natural language search
      * Add form usage counter
      *
      * @since 0.90-1.4
      */
      // An error may occur if the Search index does not exists
      // This is not critical as we need to (re) create it
      If (isIndex('glpi_plugin_formcreator_forms', 'Search')) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_forms` DROP INDEX `Search`";
         $DB->query($query);
      }

      // Re-add FULLTEXT index
      $query = "ALTER TABLE `glpi_plugin_formcreator_forms` ADD FULLTEXT INDEX `Search` (`name`, `description`)";
      $DB->query($query) or plugin_formcreator_upgrade_error($migration);

      $migration->addField('glpi_plugin_formcreator_forms', 'usage_count', 'integer', [
         'after' => 'validation_required',
         'value' => '0'
      ]);
      $migration->addField('glpi_plugin_formcreator_forms', 'is_default', 'bool', [
         'after' => 'usage_count',
         'value' => '0'
      ]);

      // add uuid to forms
      if (!$DB->fieldExists('glpi_plugin_formcreator_forms', 'uuid', false)) {
         $migration->addField('glpi_plugin_formcreator_forms', 'uuid', 'string', ['after' => 'is_default']);
      }

      $migration->addKey('glpi_plugin_formcreator_forms', 'entities_id');
      $migration->addKey('glpi_plugin_formcreator_forms', 'plugin_formcreator_categories_id');
      $migration->migrationOneTable('glpi_plugin_formcreator_forms');

      // fill missing uuid (force update of forms, see PluginFormcreatorForm::prepareInputForUpdate)
      $DB->update('glpi_plugin_formcreator_forms', [
         'uuid' => plugin_formcreator_getUuid()
      ], [
         'uuid' => null
      ]);
   }

   function updateHeader_2_5(Migration $migration) {
      // Drop Headers table
      $migration->displayMessage("Drop glpi_plugin_formcreator_headers");
      $migration->dropTable('glpi_plugin_formcreator_headers');
   }

   function updateIssue_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of Issues
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_issues");

      $DB->query("DROP VIEW IF EXISTS `glpi_plugin_formcreator_issues`");

      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_issues` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `display_id` VARCHAR(255) NOT NULL,
                     `original_id` INT(11) NOT NULL DEFAULT '0',
                     `sub_itemtype` VARCHAR(100) NOT NULL DEFAULT '',
                     `name` VARCHAR(244) NOT NULL DEFAULT '',
                     `status` VARCHAR(244) NOT NULL DEFAULT '',
                     `date_creation` DATETIME NOT NULL,
                     `date_mod` DATETIME NOT NULL,
                     `entities_id` INT(11) NOT NULL DEFAULT '0',
                     `is_recursive` TINYINT(1) NOT NULL DEFAULT '0',
                     `requester_id` INT(11) NOT NULL DEFAULT '0',
                     `validator_id` INT(11) NOT NULL DEFAULT '0',
                     `comment` TEXT NULL COLLATE 'utf8_unicode_ci',
                     PRIMARY KEY (`id`),
                     INDEX `original_id_sub_itemtype` (`original_id`, `sub_itemtype`),
                     INDEX `entities_id` (`entities_id`),
                     INDEX `requester_id` (`requester_id`),
                     INDEX `validator_id` (`validator_id`)
               )
               COLLATE='utf8_unicode_ci'
               ENGINE=MyISAM";
      $DB->query($query) or die ($DB->error());
      CronTask::Register('PluginFormcreatorIssue', 'SyncIssues', HOUR_TIMESTAMP,
            [
               'comment'   => __('Formcreator - Sync service catalog issues', 'formcreator'),
               'mode'      => CronTask::MODE_EXTERNAL
            ]
      );

      $query = "SELECT DISTINCT
                  NULL                           AS `id`,
                  CONCAT('f_',`fanswer`.`id`)    AS `display_id`,
                  `fanswer`.`id`                 AS `original_id`,
                  'PluginFormcreatorForm_Answer' AS `sub_itemtype`,
                  `f`.`name`                     AS `name`,
                  `fanswer`.`status`             AS `status`,
                  `fanswer`.`request_date`       AS `date_creation`,
                  `fanswer`.`request_date`       AS `date_mod`,
                  `fanswer`.`entities_id`        AS `entities_id`,
                  `fanswer`.`is_recursive`       AS `is_recursive`,
                  `fanswer`.`requester_id`       AS `requester_id`,
                  `fanswer`.`validator_id`       AS `validator_id`,
                  `fanswer`.`comment`            AS `comment`
               FROM `glpi_plugin_formcreator_forms_answers` AS `fanswer`
               LEFT JOIN `glpi_plugin_formcreator_forms` AS `f`
                  ON`f`.`id` = `fanswer`.`plugin_formcreator_forms_id`
               LEFT JOIN `glpi_items_tickets` AS `itic`
                  ON `itic`.`items_id` = `fanswer`.`id`
                  AND `itic`.`itemtype` = 'PluginFormcreatorForm_Answer'
               WHERE `fanswer`.`is_deleted` = '0'
               GROUP BY `original_id`
               HAVING COUNT(`itic`.`tickets_id`) != 1
               UNION
               SELECT DISTINCT
                  NULL                          AS `id`,
                  CONCAT('t_',`tic`.`id`)       AS `display_id`,
                  `tic`.`id`                    AS `original_id`,
                  'Ticket'                      AS `sub_itemtype`,
                  `tic`.`name`                  AS `name`,
                  `tic`.`status`                AS `status`,
                  `tic`.`date`                  AS `date_creation`,
                  `tic`.`date_mod`              AS `date_mod`,
                  `tic`.`entities_id`           AS `entities_id`,
                  0                             AS `is_recursive`,
                  `tic`.`users_id_recipient`    AS `requester_id`,
                  0                             AS `validator_id`,
                  `tic`.`content`               AS `comment`
               FROM `glpi_tickets` AS `tic`
               LEFT JOIN `glpi_items_tickets` AS `itic`
                  ON `itic`.`tickets_id` = `tic`.`id`
                  AND `itic`.`itemtype` = 'PluginFormcreatorForm_Answer'
               WHERE `tic`.`is_deleted` = 0
               GROUP BY `original_id`
               HAVING COUNT(`itic`.`items_id`) <= 1";

      $countQuery = "SELECT COUNT(*) AS `cpt` FROM ($query) AS `issues`";
      $result = $DB->query($countQuery);
      if ($result !== false) {
         $count = $DB->fetch_assoc($result);
         $table = 'glpi_plugin_formcreator_issues';
         if (countElementsInTable($table) != $count['cpt']) {
            if ($DB->query("TRUNCATE `$table`")) {
               $DB->query("INSERT INTO `$table` SELECT * FROM ($query) as `dt`");
            }
         }
      }
   }

   function updateQuestionCondition_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of Question conditions
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_questions_conditions");

      // Migration 0.85-1.0 => 0.85-1.1
      if ($DB->fieldExists('glpi_plugin_formcreator_questions', 'show_type', false)) {
         // Migrate date from "questions" table to "questions_conditions" table
         $query  = "SELECT `id`, `show_type`, `show_field`, `show_condition`, `show_value`
                  FROM `glpi_plugin_formcreator_questions`";
         $result = $DB->query($query);
         while ($line = $DB->fetch_array($result)) {
            $questionId = $line['id'];
            switch ($line['show_type']) {
               case 'hide' :
                  $show_rule = 'hidden';
                  break;
               default:
                  $show_rule = 'always';
            }
            switch ($line['show_condition']) {
               case 'notequal' :
                  $show_condition = '!=';
                  break;
               case 'lower' :
                  $show_condition = '<';
                  break;
               case 'greater' :
                  $show_condition = '>';
                  break;
               default:
                  $show_condition = '==';
            }

            $show_field = empty($line['show_field']) ? 'NULL' : $line['show_field'];

            $query_udate = "UPDATE `glpi_plugin_formcreator_questions` SET
                           `show_rule` = '$show_rule'
                           WHERE `id` = '$questionId'";
            $DB->query($query_udate) or plugin_formcreator_upgrade_error($migration);

            $query_udate = "INSERT INTO `glpi_plugin_formcreator_questions_conditions` SET
                           `plugin_formcreator_questions_id` = '$questionId',
                           `show_field`     = '$show_field',
                           `show_condition` = '$show_condition',
                           `show_value`     = '" . Toolbox::addslashes_deep($line['show_value']) . "'";
            $DB->query($query_udate) or plugin_formcreator_upgrade_error($migration);
         }

         // Delete old fields
         $migration->dropField('glpi_plugin_formcreator_questions', 'show_type');
         $migration->dropField('glpi_plugin_formcreator_questions', 'show_field');
         $migration->dropField('glpi_plugin_formcreator_questions', 'show_condition');
         $migration->dropField('glpi_plugin_formcreator_questions', 'show_value');
      }

      // Migrate "question_conditions" table
      $query  = "SELECT `id`, `show_value`
               FROM `glpi_plugin_formcreator_questions_conditions`";
      $result = $DB->query($query);
      while ($line = $DB->fetch_array($result)) {
         $query_update = "UPDATE `glpi_plugin_formcreator_questions_conditions` SET
                        `show_value` = '" . plugin_formcreator_encode($line['show_value'], false) . "'
                        WHERE `id` = " . $line['id'];
         $DB->query($query_update) or plugin_formcreator_upgrade_error($migration);
      }

      if (!$DB->fieldExists('glpi_plugin_formcreator_questions_conditions', 'order', false)) {
         $migration->addField('glpi_plugin_formcreator_questions_conditions', 'order', 'integer', ['after' => 'show_logic', 'value' => '1']);
         $migration->migrationOneTable('glpi_plugin_formcreator_questions_conditions');
      }

      $enum_logic = "'".implode("', '", ['AND','OR'])."'";
      $current_enum_show_logic = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_questions_conditions', 'show_logic');
      if (count($current_enum_show_logic) != count(['AND','OR'])) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_questions_conditions`
                  CHANGE COLUMN `show_logic` `show_logic`
                  ENUM($enum_logic)
                  NULL DEFAULT NULL";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }

      // add uuid to questions conditions
      if (!$DB->fieldExists('glpi_plugin_formcreator_questions_conditions', 'uuid', false)) {
         $migration->addField('glpi_plugin_formcreator_questions_conditions', 'uuid', 'string');
         $migration->migrationOneTable('glpi_plugin_formcreator_questions_conditions');
      }

      $migration->addKey('glpi_plugin_formcreator_questions_conditions', 'plugin_formcreator_questions_id');

      // fill missing uuid (force update of questions, see PluginFormcreatorQuestoin_Condition::prepareInputForUpdate)
      $DB->update('glpi_plugin_formcreator_questions_conditions', [
         'uuid' => plugin_formcreator_getUuid()
      ], [
         'uuid' => null
      ]);
   }

   function updateQuestion_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of Questions
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_questions");

      // Migration 0.83-1.0 => 0.85-1.0
      if (!$DB->fieldExists('glpi_plugin_formcreator_questions', 'fieldtype', false)) {
         // Migration from previous version
         $query = "ALTER TABLE `glpi_plugin_formcreator_questions`
                  ADD `fieldtype` varchar(30) NOT NULL DEFAULT 'text',
                  ADD `show_type` enum ('show', 'hide') NOT NULL DEFAULT 'show',
                  ADD `show_field` int(11) DEFAULT NULL,
                  ADD `show_condition` enum('equal','notequal','lower','greater') COLLATE utf8_unicode_ci DEFAULT NULL,
                  ADD `show_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  ADD `required` tinyint(1) NOT NULL DEFAULT '0',
                  ADD `show_empty` tinyint(1) NOT NULL DEFAULT '0',
                  ADD `default_values` text COLLATE utf8_unicode_ci,
                  ADD `values` text COLLATE utf8_unicode_ci,
                  ADD `range_min` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
                  ADD `range_max` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
                  ADD `regex` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  CHANGE `content` `description` text COLLATE utf8_unicode_ci NOT NULL,
                  CHANGE `position` `order` int(11) NOT NULL DEFAULT '0';";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);

         // order start from 1 instead of 0
         $DB->query("UPDATE `glpi_plugin_formcreator_questions` SET `order` = `order` + 1;") or plugin_formcreator_upgrade_error($migration);

         // Match new type
         $query  = "SELECT `id`, `type`, `data`, `option`
                  FROM `glpi_plugin_formcreator_questions`";
         $result = $DB->query($query);
         while ($line = $DB->fetch_array($result)) {
            $data     = json_decode($line['data']);
            $options  = json_decode($line['option']);

            $fieldtype = 'text';
            $values    = '';
            $default   = '';
            $regex     = '';
            $required  = 0;

            if (isset($data->value) && !empty($data->value)) {
               if (is_object($data->value)) {
                  foreach ($data->value as $value) {
                     if (!empty($value)) {
                        $values .= urldecode($value) . "\r\n";
                     }
                  }
               } else {
                  $values .= urldecode($data->value);
               }
            }

            switch ($line['type']) {
               case '1':
                  $fieldtype = 'text';

                  if (isset($options->type)) {
                     switch ($options->type) {
                        case '2':
                           $required  = 1;
                           break;
                        case '3':
                           $regex = '[[:alpha:]]';
                           break;
                        case '4':
                           $fieldtype = 'float';
                           break;
                        case '5':
                           $regex = urldecode($options->value);
                           // Add leading and trailing regex marker (automaticaly added in V1)
                           if (substr($regex, 0, 1)  != '/') {
                              $regex = '/' . $regex;
                           }
                           if (substr($regex, -1, 1) != '/') {
                              $regex = $regex . '/';
                           }
                           break;
                        case '6':
                           $fieldtype = 'email';
                           break;
                        case '7':
                           $fieldtype = 'date';
                           break;
                     }
                  }
                  $values = '';
                  break;

               case '2':
                  $fieldtype = 'select';
                  break;

               case '3':
                  $fieldtype = 'checkboxes';
                  break;

               case '4':
                  $fieldtype = 'textarea';
                  if (isset($options->type) && ($options->type == 2)) {
                     $required = 1;
                  }
                  $values = '';
                  break;

               case '5':
                  $fieldtype = 'file';
                  break;

               case '8':
                  $fieldtype = 'select';
                  break;

               case '9':
                  $fieldtype = 'select';
                  break;

               case '10':
                  $fieldtype = 'dropdown';
                  break;

               default :
                  break;
            }

            $query_udate = "UPDATE `glpi_plugin_formcreator_questions` SET
                           `fieldtype`      = '" . $fieldtype . "',
                           `values`         = '" . addslashes(htmlspecialchars($values)) . "',
                           `default_values` = '" . addslashes(htmlspecialchars($default)) . "',
                           `regex`          = '" . $regex . "',
                           `required`       = " . (int) $required . "
                           WHERE `id` = " . $line['id'];
            $DB->query($query_udate) or plugin_formcreator_upgrade_error($migration);
         }

         $migration->dropField('glpi_plugin_formcreator_questions', 'type');
         $migration->dropField('glpi_plugin_formcreator_questions', 'data');
         $migration->dropField('glpi_plugin_formcreator_questions', 'option');
         $migration->dropField('glpi_plugin_formcreator_questions', 'plugin_formcreator_forms_id');
      }

      // Migration 0.85-1.0 => 0.85-1.1
      if ($DB->fieldExists('glpi_plugin_formcreator_questions', 'show_type', false)) {

         // Fix type of section ID
         if (!$DB->fieldExists('glpi_plugin_formcreator_questions', 'show_rule')) {
            $query = "ALTER TABLE  `glpi_plugin_formcreator_questions`
                     CHANGE `plugin_formcreator_sections_id` `plugin_formcreator_sections_id` INT NOT NULL,
                     ADD `show_rule` enum('always','hidden','shown') NOT NULL DEFAULT 'always'";
            $DB->query($query) or plugin_formcreator_upgrade_error($migration);
         }
      }

      /**
       * Migration of special chars from previous versions
      *
      * @since 0.85-1.2.3
      */
      // Migrate "questions" table
      $query  = "SELECT `id`, `name`, `values`, `default_values`, `description`
               FROM `glpi_plugin_formcreator_questions`";
      $result = $DB->query($query);
      while ($line = $DB->fetch_array($result)) {
         $query_update = "UPDATE `glpi_plugin_formcreator_questions` SET
                                 `name`           = '" . addslashes(plugin_formcreator_encode($line['name'])) . "',
                                 `values`         = '" . addslashes(plugin_formcreator_encode($line['values'])) . "',
                                 `default_values` = '" . addslashes(plugin_formcreator_encode($line['default_values'])) . "',
                                 `description`    = '" . addslashes(plugin_formcreator_encode($line['description'])) . "'
                              WHERE `id` = " . $line['id'];
         $DB->query($query_update) or plugin_formcreator_upgrade_error($migration);
      }

      /**
       * Add natural language search
      *
      * @since 0.90-1.4
      */
      // An error may occur if the Search index does not exists
      // This is not critical as we need to (re) create it
      If (isIndex('glpi_plugin_formcreator_questions', 'Search')) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_questions` DROP INDEX `Search`";
         $DB->query($query);
      }

      // Re-add FULLTEXT index
      $query = "ALTER TABLE `glpi_plugin_formcreator_questions` ADD FULLTEXT INDEX `Search` (`name`, `description`)";
      $DB->query($query) or plugin_formcreator_upgrade_error($migration);

      // add uuid to questions
      if (!$DB->fieldExists('glpi_plugin_formcreator_questions', 'uuid', false)) {
         $migration->addField('glpi_plugin_formcreator_questions', 'uuid', 'string');
         $migration->migrationOneTable('glpi_plugin_formcreator_questions');
      }

      $migration->addKey('glpi_plugin_formcreator_questions', 'plugin_formcreator_sections_id');

      // fill missing uuid (force update of questions, see PluginFormcreatorQuestion::prepareInputForUpdate)
      $DB->update('glpi_plugin_formcreator_questions', [
         'uuid' => plugin_formcreator_getUuid()
      ], [
         'uuid' => null
      ]);
   }

   function updateSection_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of Sections
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_sections");

      /**
       * Migration of special chars from previous versions
      *
      * @since 0.85-1.2.3
      */
      $query  = "SELECT `id`, `name`
               FROM `glpi_plugin_formcreator_sections`";
      $result = $DB->query($query);
      while ($line = $DB->fetch_array($result)) {
         $query_update = "UPDATE `glpi_plugin_formcreator_sections` SET
                        `name` = '".plugin_formcreator_encode($line['name'])."'
                        WHERE `id` = ".$line['id'];
         $DB->query($query_update) or plugin_formcreator_upgrade_error($migration);
      }

      // Migration from previous version => Remove useless target field
      if ($DB->fieldExists('glpi_plugin_formcreator_sections', 'plugin_formcreator_targets_id', false)) {
         $migration->dropField('glpi_plugin_formcreator_sections', 'plugin_formcreator_targets_id');
      }

      // Migration from previous version => Rename "position" into "order" and start order from 1 instead of 0
      if ($DB->fieldExists('glpi_plugin_formcreator_sections', 'position', false)) {
         $DB->query("ALTER TABLE `glpi_plugin_formcreator_sections` CHANGE `position` `order` INT(11) NOT NULL DEFAULT '0';");
         $DB->query("UPDATE `glpi_plugin_formcreator_sections` SET `order` = `order` + 1;");
      }

      // Migration from previous version => Update Question table, then create a "description" question from content
      if ($DB->fieldExists('glpi_plugin_formcreator_sections', 'content', false)) {
         // Increment the order of questions which are in a section with a description
         $query = "UPDATE `PluginFormcreatorQuestion`
                  SET `order` = `order` + 1
                  WHERE `plugin_formcreator_sections_id` IN (
                     SELECT `id`
                     FROM `glpi_plugin_formcreator_sections`
                     WHERE `content` != ''
                  )";
         $DB->query($query);

         // Create description from content
         $query = "INSERT INTO `PluginFormcreatorQuestion` (
                     `plugin_formcreator_sections_id`,
                     `fieldtype`,
                     `name`,
                     `description`,
                     `order`
                  )
                  SELECT
                     `id`,
                     'description' AS fieldtype,
                     CONCAT('Description ', `id`) AS name,
                     `content`,
                     1 AS `order`
                  FROM `glpi_plugin_formcreator_sections`
                  WHERE `content` != ''";
         $DB->query($query);

         // Delete content column
         $DB->query("ALTER TABLE `glpi_plugin_formcreator_sections` DROP `content`;");
      }

      // add uuid to sections
      if (!$DB->fieldExists('glpi_plugin_formcreator_sections', 'uuid', false)) {
         $migration->addField('glpi_plugin_formcreator_sections', 'uuid', 'string');
         $migration->migrationOneTable('glpi_plugin_formcreator_sections');
      }

      $migration->addKey('glpi_plugin_formcreator_sections', 'plugin_formcreator_forms_id');

      // fill missing uuid (force update of sections, see PluginFormcreatorSection::prepareInputForUpdate)
      $DB->update('glpi_plugin_formcreator_sections', [
         'uuid' => plugin_formcreator_getUuid()
      ], [
         'uuid' => null
      ]);
   }

   function updateTarget_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of targets
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_targets");

      // Migration to 0.85-1.2.5
      if ($DB->fieldExists('glpi_plugin_formcreator_targets', 'plugin_formcreator_forms_id', false)) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targets`
                  CHANGE `plugin_formcreator_forms_id` `plugin_formcreator_forms_id` INT NOT NULL;";
         $DB->query($query);
      }

      if (!$DB->fieldExists('glpi_plugin_formcreator_targets', 'itemtype', false)) {
         // Migration from version 1.5 to 1.6
         if (!$DB->fieldExists('glpi_plugin_formcreator_targets', 'type', false)) {
            $query = "ALTER TABLE `glpi_plugin_formcreator_targets`
                     ADD `type` tinyint(1) NOT NULL default '2';";
            $DB->query($query);
         }

         // Add new column for link with target items
         $query = "ALTER TABLE `glpi_plugin_formcreator_targets`
                  ADD `itemtype` varchar(100) NOT NULL DEFAULT 'PluginFormcreatorTargetTicket',
                  ADD `items_id` int(11) NOT NULL DEFAULT 0;";
         $DB->query($query);

         // Create ticket template for each configuration in DB
         $query = "SELECT t.`urgency`, t.`priority`, t.`itilcategories_id`, t.`type`, f.`entities_id`
                  FROM `glpi_plugin_formcreator_targets` t, `glpi_plugin_formcreator_forms` f
                  WHERE f.`id` = t.`plugin_formcreator_forms_id`
                  GROUP BY t.`urgency`, t.`priority`, t.`itilcategories_id`, t.`type`, f.`entities_id`";
         $result = $DB->query($query) or plugin_formcreator_upgrade_error($migration);

         $i = 0;
         while ($ligne = $DB->fetch_array($result)) {
            $i++;
            $id = $ligne['urgency'].$ligne['priority'].$ligne['itilcategories_id'].$ligne['type'];

            $template    = new TicketTemplate();
            $template_id = $template->add([
               'name'         => 'Template Formcreator '.$i,
               'entities_id'  => $ligne['entities_id'],
               'is_recursive' => 1,
            ]);

            $predefinedField = new TicketTemplatePredefinedField();

            // Urgency
            if (!empty($ligne['urgency'])) {
               $predefinedField->add([
                  'tickettemplates_id' => $template_id,
                  'num'                => 10,
                  'value'              => $ligne['urgency'],
               ]);
            }

            // Priority
            if (!empty($ligne['priority'])) {
               $predefinedField->add([
                  'tickettemplates_id' => $template_id,
                  'num'                => 3,
                  'value'              => $ligne['priority'],
               ]);
            }

            // Category
            if (!empty($ligne['itilcategories_id'])) {
               $predefinedField->add([
                  'tickettemplates_id' => $template_id,
                  'num'                => 7,
                  'value'              => $ligne['itilcategories_id'],
               ]);
            }

            // Type
            if (!empty($ligne['type'])) {
               $predefinedField->add([
                  'tickettemplates_id' => $template_id,
                  'num'                => 14,
                  'value'              => $ligne['type'],
               ]);
            }

            $_SESSION["formcreator_tmp"]["ticket_template"]["$id"] = $template_id;
         }

         // Install or upgrade of TargetTicket is a prerequisite
         plugin_formcreator_updateTarget_2_5($migration);
         $table_targetticket = getTableForItemType('PluginFormcreatorTargetTicket');

         // Convert targets to ticket templates only if at least one target extsis
         if ($i > 0) {
            // Prepare Mysql CASE For each ticket template
            $mysql_case_template  = "CASE CONCAT(`urgency`, `priority`, `itilcategories_id`, `type`)";
            foreach ($_SESSION["formcreator_tmp"]["ticket_template"] as $id => $value) {
               $mysql_case_template .= " WHEN $id THEN $value ";
            }
            $mysql_case_template .= "END AS `tickettemplates_id`";

            // Create Target ticket
            $query  = "SELECT `id`, `name`, $mysql_case_template, `content`
                     FROM `glpi_plugin_formcreator_targets`;";
            $result = $DB->query($query);
            while ($line = $DB->fetch_array($result)) {
               // Insert target ticket
               $query_insert = "INSERT INTO `$table_targetticket` SET
                              `name` = '".htmlspecialchars($line['name'])."',
                              `tickettemplates_id` = ".$line['tickettemplates_id'].",
                              `comment` = '".htmlspecialchars($line['content'])."'";
               $DB   ->query($query_insert);
               $targetticket_id = $DB->insert_id();

               // Update target with target ticket id
               $query_update = "UPDATE `glpi_plugin_formcreator_targets`
                              SET `items_id` = ".$targetticket_id."
                              WHERE `id` = ".$line['id'];
               $DB->query($query_update);
            }
         }

         // Remove useless column content
         $DB->query("ALTER TABLE `glpi_plugin_formcreator_targets` DROP `content`;");

         /**
          * Migration of special chars from previous versions
          *
          * @since 0.85-1.2.3
          */
         if ($DB->fieldExists($table_targetticket, 'comment')) {
            $query  = "SELECT `id`, `comment`
                     FROM `$table_targetticket`";
            $result = $DB->query($query);
            while ($line = $DB->fetch_array($result)) {
               $query_update = "UPDATE `$table_targetticket` SET
                              `comment` = '".plugin_formcreator_encode($line['comment'])."'
                              WHERE `id` = ".$line['id'];
               $DB->query($query_update) or plugin_formcreator_upgrade_error($migration);
            }
         }
      }

      $migration->addKey('glpi_plugin_formcreator_targets', 'plugin_formcreator_forms_id');
      $migration->addKey('glpi_plugin_formcreator_targets', ['itemtype', 'items_id']);

      // add uuid to targets
      if (!$DB->fieldExists('glpi_plugin_formcreator_targets', 'uuid', false)) {
         $migration->addField('glpi_plugin_formcreator_targets', 'uuid', 'string');
         $migration->migrationOneTable('glpi_plugin_formcreator_targets');
      }

      // fill missing uuid (force update of targets, see PluginFormcreatorTarget::prepareInputForUpdate)
      $DB->update('glpi_plugin_formcreator_targets', [
         'uuid' => plugin_formcreator_getUuid()
      ], [
         'uuid' => null
      ]);
   }

   function updateTargetChange_Actor_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of Change actors
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_targetchanges_actors");

      if (!$DB->tableExists('glpi_plugin_formcreator_targetchanges_actors')) {
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_targetchanges_actors` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `plugin_formcreator_targetchanges_id` int(11) NOT NULL,
                     `actor_role` enum('requester','observer','assigned') COLLATE utf8_unicode_ci NOT NULL,
                     `actor_type` enum('creator','validator','person','question_person','group','question_group','supplier','question_supplier','question_actors') COLLATE utf8_unicode_ci NOT NULL,
                     `actor_value` int(11) DEFAULT NULL,
                     `use_notification` tinyint(1) NOT NULL DEFAULT '1',
                     `uuid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                     PRIMARY KEY (`id`),
                     INDEX `plugin_formcreator_targetchanges_id` (`plugin_formcreator_targetchanges_id`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }

      $enum_actor_type = "'".implode("', '", ['creator','validator','person','question_person','group','question_group','supplier','question_supplier'])."'";
      $enum_actor_role = "'".implode("', '", ['requester','observer','assigned'])."'";

      $current_enum_actor_type = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targetchanges_actors', 'actor_type');
      if (count($current_enum_actor_type) != count(['creator','validator','person','question_person','group','question_group','supplier','question_supplier'])) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges_actors`
                  CHANGE COLUMN `actor_type` `actor_type`
                  ENUM($enum_actor_type)
                  NOT NULL";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }

      $current_enum_role = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targetchanges_actors', 'actor_role');
      if (count($current_enum_role) != count(['requester','observer','assigned'])) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges_actors`
                  CHANGE COLUMN `actor_role` `actor_role`
                  ENUM($enum_actor_role)
                  NOT NULL";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }

      // fill missing uuid
      $DB->update('glpi_plugin_formcreator_targetchanges_actors', [
         'uuid' => plugin_formcreator_getUuid()
      ], [
         'uuid' => null
      ]);
   }

   function updateTargetTicket_Actor_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of target ticket actors
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_targettickets_actors");

      $enum_actor_type = "'".implode("', '", ['creator','validator','person','question_person','group','question_group','supplier','question_supplier'])."'";
      $enum_actor_role = "'".implode("', '", ['requester','observer','assigned'])."'";

      $current_enum_actor_type = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targettickets_actors', 'actor_type');
      if (count($current_enum_actor_type) != count(['creator','validator','person','question_person','group','question_group','supplier','question_supplier'])) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets_actors`
                  CHANGE COLUMN `actor_type` `actor_type`
                  ENUM($enum_actor_type)
                  NOT NULL";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }

      $current_enum_role = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targettickets_actors', 'actor_role');
      if (count($current_enum_role) != count(['requester','observer','assigned'])) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets_actors`
                  CHANGE COLUMN `actor_role` `actor_role`
                  ENUM($enum_actor_role)
                  NOT NULL";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }

      // add uuid to actor
      if (!$DB->fieldExists('glpi_plugin_formcreator_targettickets_actors', 'uuid', false)) {
         $migration->addField('glpi_plugin_formcreator_targettickets_actors', 'uuid', 'string');
         $migration->migrationOneTable('glpi_plugin_formcreator_targettickets_actors');
      }

      // fill missing uuid
      $DB->update('glpi_plugin_formcreator_targettickets_actors', [
         'uuid' => plugin_formcreator_getUuid()
      ], [
         'uuid' => null
      ]);
   }

   function updateTargetTicket_2_5(Migration $migration) {
      global $DB;

      // Legacy upgrade of Target tickets
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_targettickets");

      $enum_destination_entity = "'".implode("', '", ['current','requester','requester_dynamic_first','requester_dynamic_last','form','validator','specific','user','entity'])."'";
      $enum_tag_type           = "'".implode("', '", ['none','questions','specifics','questions_and_specific','questions_or_specific'])."'";
      $enum_due_date_rule      = "'".implode("', '", ['answer','change','calcul'])."'";
      $enum_urgency_rule       = "'".implode("', '", ['none','specific','answer'])."'";
      $enum_category_rule      = "'".implode("', '", ['none','answer'])."'";

      if (!$DB->fieldExists('glpi_plugin_formcreator_targettickets', 'due_date_rule', false)) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets`
                  ADD `due_date_rule` ENUM($enum_due_date_rule) NULL DEFAULT NULL,
                  ADD `due_date_question` INT NULL DEFAULT NULL,
                  ADD `due_date_value` TINYINT NULL DEFAULT NULL,
                  ADD `due_date_period` ENUM('minute', 'hour', 'day', 'month') NULL DEFAULT NULL,
                  ADD `validation_followup` BOOLEAN NOT NULL DEFAULT TRUE;";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }

      // Migration to Formcreator 0.90-1.4
      if (!$DB->fieldExists('glpi_plugin_formcreator_targettickets', 'destination_entity', false)) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets`
                  ADD `destination_entity` ENUM($enum_destination_entity) NOT NULL DEFAULT 'current',
                  ADD `destination_entity_value` int(11) NULL DEFAULT NULL;";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      } else {
         $current_enum_destination_entity = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targettickets', 'destination_entity');
         if (count($current_enum_destination_entity) != count(['current','requester','requester_dynamic_first','requester_dynamic_last','form','validator','specific','user','entity'])) {
            $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets`
                     CHANGE COLUMN `destination_entity` `destination_entity`
                     ENUM($enum_destination_entity)
                     NOT NULL DEFAULT 'current'";
            $DB->query($query) or plugin_formcreator_upgrade_error($migration);
         }
      }
      $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets` ALTER COLUMN `destination_entity` SET DEFAULT 'current'";
      $DB->query($query);

      if (!$DB->fieldExists('glpi_plugin_formcreator_targettickets', 'tag_type', false)) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets`
                  ADD `tag_type` ENUM($enum_tag_type) NOT NULL DEFAULT 'none',
                  ADD `tag_questions` VARCHAR(255) NOT NULL,
                  ADD `tag_specifics` VARCHAR(255) NOT NULL;";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }

      if (!$DB->fieldExists('glpi_plugin_formcreator_targettickets', 'urgency_rule', false)) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets`
                  ADD `urgency_rule` ENUM($enum_urgency_rule) NOT NULL DEFAULT 'none' AFTER `due_date_period`;";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      } else {
         $current_enum_urgency_rule = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targettickets', 'urgency_rule');
         if (count($current_enum_urgency_rule) != count(['none','specific','answer'])) {
            $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets`
                     CHANGE COLUMN `urgency_rule` `urgency_rule`
                     ENUM($enum_urgency_rule)
                     NOT NULL DEFAULT 'none'";
            $DB->query($query) or plugin_formcreator_upgrade_error($migration);
         }
      }
      $migration->addField('glpi_plugin_formcreator_targettickets', 'urgency_question', 'integer', ['after' => 'urgency_rule']);

      if (!$DB->fieldExists('glpi_plugin_formcreator_targettickets', 'category_rule', false)) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets`
                  ADD `category_rule` ENUM($enum_category_rule) NOT NULL DEFAULT 'none' AFTER `tag_specifics`;";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      } else {
         $current_enum_category_rule = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targettickets', 'category_rule');
         if (count($current_enum_category_rule) != count(['none','answer'])) {
            $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets`
                     CHANGE COLUMN `category_rule` `category_rule`
                     ENUM($enum_category_rule)
                     NOT NULL DEFAULT 'none'";
            $DB->query($query) or plugin_formcreator_upgrade_error($migration);
         }
      }
      $migration->addField('glpi_plugin_formcreator_targettickets', 'category_question', 'integer', ['after' => 'category_rule']);

      $migration->addKey('glpi_plugin_formcreator_targettickets', 'tickettemplates_id');
   }

   function updateTitle_2_5(Migration $migration) {
      // Drop Headers table
      $migration->displayMessage("Drop glpi_plugin_formcreator_titles");
      $migration->dropTable('glpi_plugin_formcreator_titles');
   }

   function updateTargetChange_2_5($migration) {
      global $DB;

      // Legacy upgrade of Target changes
      $migration->displayMessage("Upgrade glpi_plugin_formcreator_targetchanges");

      $enum_destination_entity = "'".implode("', '", ['current','requester','requester_dynamic_first','requester_dynamic_last','form','validator','specific','user','entity'])."'";
      $enum_tag_type           = "'".implode("', '", ['none','questions','specifics','questions_and_specific','questions_or_specific'])."'";
      $enum_due_date_rule      = "'".implode("', '", ['answer','change','calcul'])."'";
      $enum_urgency_rule       = "'".implode("', '", ['none','specific','answer'])."'";
      $enum_category_rule      = "'".implode("', '", ['none','answer'])."'";

      if (!$DB->tableExists('glpi_plugin_formcreator_targetchanges')) {
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_targetchanges` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) NOT NULL DEFAULT '',
                  `changetemplates_id` int(11) DEFAULT NULL,
                  `comment` text,
                  `impactcontent` text,
                  `controlistcontent` text,
                  `rolloutplancontent` text,
                  `backoutplancontent` text,
                  `checklistcontent` text,
                  `due_date_rule` enum($enum_due_date_rule) DEFAULT NULL,
                  `due_date_question` int(11) DEFAULT NULL,
                  `due_date_value` tinyint(4) DEFAULT NULL,
                  `due_date_period` enum('minute','hour','day','month') DEFAULT NULL,
                  `urgency_rule` enum($enum_urgency_rule) NOT NULL DEFAULT 'none',
                  `urgency_question` int(11) NOT NULL DEFAULT '0',
                  `validation_followup` tinyint(1) NOT NULL DEFAULT '1',
                  `destination_entity` enum($enum_destination_entity) NOT NULL DEFAULT 'requester',
                  `destination_entity_value` int(11) DEFAULT NULL,
                  `tag_type` enum($enum_tag_type) NOT NULL DEFAULT 'none',
                  `tag_questions` varchar(255) NOT NULL,
                  `tag_specifics` varchar(255) NOT NULL,
                  `category_rule` enum($enum_category_rule) NOT NULL DEFAULT 'none',
                  `category_question` int(11) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  INDEX `changetemplates_id` (`changetemplates_id`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
               ";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }

      if (!$DB->fieldExists('glpi_plugin_formcreator_targetchanges', 'due_date_rule', false)) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges`
                  ADD `due_date_rule` ENUM($enum_due_date_rule) NULL DEFAULT NULL,
                  ADD `due_date_question` INT NULL DEFAULT NULL,
                  ADD `due_date_value` TINYINT NULL DEFAULT NULL,
                  ADD `due_date_period` ENUM('minute', 'hour', 'day', 'month') NULL DEFAULT NULL;";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }

      if (!$DB->fieldExists('glpi_plugin_formcreator_targetchanges', 'destination_entity', false)) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges`
                  ADD `destination_entity` ENUM($enum_destination_entity) NOT NULL DEFAULT 'current',
                  ADD `destination_entity_value` int(11) NULL DEFAULT NULL;";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      } else {
         $current_enum_destination_entity = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targetchanges', 'destination_entity');
         if (count($current_enum_destination_entity) != count(['current','requester','requester_dynamic_first','requester_dynamic_last','form','validator','specific','user','entity'])) {
            $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges`
                     CHANGE COLUMN `destination_entity` `destination_entity`
                     ENUM($enum_destination_entity)
                     NOT NULL DEFAULT 'current'";
            $DB->query($query) or plugin_formcreator_upgrade_error($migration);
         }
      }
      $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges` ALTER COLUMN `destination_entity` SET DEFAULT 'current'";
      $DB->query($query);

      if (!$DB->fieldExists('glpi_plugin_formcreator_targetchanges', 'tag_type', false)) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges`
                  ADD `tag_type` ENUM($enum_tag_type) NOT NULL DEFAULT 'none',
                  ADD `tag_questions` VARCHAR(255) NOT NULL,
                  ADD `tag_specifics` VARCHAR(255) NOT NULL;";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }

      if (!$DB->fieldExists('glpi_plugin_formcreator_targetchanges', 'urgency_rule', false)) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges`
                  ADD `urgency_rule` ENUM($enum_urgency_rule) NOT NULL DEFAULT 'none' AFTER `due_date_period`;";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      } else {
         $current_enum_urgency_rule = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targetchanges', 'urgency_rule');
         if (count($current_enum_urgency_rule) != count(['none','specific','answer'])) {
            $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges`
                     CHANGE COLUMN `urgency_rule` `urgency_rule`
                     ENUM($enum_urgency_rule)
                     NOT NULL DEFAULT 'none'";
            $DB->query($query) or plugin_formcreator_upgrade_error($migration);
         }
      }
      $migration->addField('glpi_plugin_formcreator_targetchanges', 'urgency_question', 'integer', ['after' => 'urgency_rule']);

      if (!$DB->fieldExists('glpi_plugin_formcreator_targetchanges', 'category_rule', false)) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges`
                  ADD `category_rule` ENUM($enum_category_rule) NOT NULL DEFAULT 'none' AFTER `tag_specifics`;";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      } else {
         $current_enum_category_rule = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targetchanges', 'category_rule');
         if (count($current_enum_category_rule) != count(['none','answer'])) {
            $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges`
                     CHANGE COLUMN `category_rule` `category_rule`
                     ENUM($enum_category_rule)
                     NOT NULL DEFAULT 'none'";
            $DB->query($query) or plugin_formcreator_upgrade_error($migration);
         }
      }
      $migration->addField('glpi_plugin_formcreator_targetchanges', 'category_question', 'integer', ['after' => 'category_rule']);
   }
}
