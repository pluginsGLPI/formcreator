<?php

/**
 *
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
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace Glpi\Plugin\Formcreator;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

use Migration;
use Config;
use Session;
use CronTask;
use Toolbox;
use Glpi\Dashboard\Dashboard;
use Glpi\Dashboard\Item as Dashboard_Item;
use Glpi\Dashboard\Right as Dashboard_Right;
use Glpi\System\Diagnostic\DatabaseSchemaIntegrityChecker;
use Ramsey\Uuid\Uuid;
use Notification;
use NotificationTemplate;
use NotificationTemplateTranslation;
use NotificationTarget;
use Notification_NotificationTemplate;
use Item_Ticket;
use Log;
use DisplayPreference;
use Profile;

/**
 * Unified install/upgrade class for Formcreator plugin
 * Handles incremental upgrades from any version to EOL 3.0.0
 */
class Install {
   protected $migration;

   /**
    * array of upgrade steps key => value
    * key   is the version to upgrade from
    * value is the version to upgrade to
    *
    * Exemple: an entry '2.0' => '2.1' tells that versions 2.0
    * are upgradable to 2.1
    *
    * When possible avoid schema upgrade between bugfix releases. The schema
    * version contains major.minor numbers only. If an upgrade of the schema
    * occurs between bugfix releases, then the upgrade will start from the
    * major.minor.0 version up to the end of the the versions list.
    * Exemple: if previous version is 2.6.1 and current code is 2.6.3 then
    * the upgrade will start from 2.6.0 to 2.6.3 and replay schema changes
    * between 2.6.0 and 2.6.1. This means that upgrade must be _repeatable_.
    *
    * @var array
    */
   private $upgradeSteps = [
      '0.0'    => '2.5',
      '2.5'    => '2.6',
      '2.6'    => '2.6.1',
      '2.6.1'  => '2.6.3',
      '2.6.3'  => '2.7',
      '2.7'    => '2.8',
      '2.8'    => '2.8.1',
      '2.8.1'  => '2.9',
      '2.9'    => '2.10',
      '2.10'   => '2.10.2',
      '2.10.2' => '2.11',
      '2.11'   => '2.11.3',
      '2.11.3' => '2.12',
      '2.12'   => '2.12.1',
      '2.12.1' => '2.12.5',
      '2.12.5' => '2.13',
      '2.13'   => '2.13.1',
      '2.13.1' => '2.13.3',
      '2.13.3' => '2.13.4',
      '2.13.4' => '2.13.5',
      '2.13.5' => '2.13.6',
      '2.13.6' => '2.13.7',
      '2.13.7' => '2.13.10',
      '2.13.10' => '3.0.0',  // NEW: EOL transition
   ];

   protected bool $resyncIssues = false;

   /**
    * Install the plugin
    * @param Migration $migration
    * @param array $args arguments passed to CLI
    * @return bool
    */
   public function install(Migration $migration, $args = []): bool {
      $this->migration = $migration;
      $this->installSchema();
      Config::setConfigurationValues('formcreator', ['schema_version' => PLUGIN_FORMCREATOR_SCHEMA_VERSION]);

      return true;
   }

   /**
    * Upgrade the plugin
    * @param Migration $migration
    * @param array $args arguments passed to CLI
    * @return bool
    */
   public function upgrade(Migration $migration, $args = []): bool {
      /** @var \DBmysql $DB */
      global $DB;

      if (version_compare(GLPI_VERSION, '9.5') >= 0) {
         $iterator = $DB->getMyIsamTables();
         $hasMyisamTables = false;
         foreach ($iterator as $table) {
            if (strpos($table['TABLE_NAME'], 'glpi_plugin_formcreator_') === 0) {
               $hasMyisamTables = true;
               break;
            }
         }
         if ($hasMyisamTables) {
            // Need to convert myisam tables into innodb first
            $message = sprintf(
               __('Upgrade tables to innoDB; run %s', 'formcreator'),
               'php bin/console glpi:migration:myisam_to_innodb'
            );
            if (isCommandLine()) {
               echo $message . PHP_EOL;
            } else {
               Session::addMessageAfterRedirect($message, false, ERROR);
            }
            return false;
         }
      }

      $this->migration = $migration;
      $oldVersion = Config::getConfigurationValue('formcreator', 'previous_version');
      // Force fix of signed columns to reduce upgrade errors frequency
      // This assumes that all modified columns exist in the database
      if ($oldVersion !== null && version_compare($oldVersion, '2.13.0') >= 0) {
         $this->migrateFkToUnsignedInt();
      }

      // Check schema of tables before upgrading
      if (!isset($args['skip-db-check'])) {
         if ($oldVersion !== null) {
            $checkResult = true;
            if (version_compare($oldVersion, '2.13.0') >= 0) {
               $checkResult = $this->checkSchema(
                  $oldVersion,
                  false,
                  false,
                  false,
                  false,
                  false,
                  false
               );
            }
            if (!$checkResult) {
               $message = sprintf(
                  __('The database schema is not consistent with the previous version of Formcreator %s. To see the logs run the command %s', 'formcreator'),
                  $oldVersion,
                  'bin/console glpi:plugin:install formcreator -f'
               );
               if (!isCommandLine()) {
                  Session::addMessageAfterRedirect($message, false, ERROR);
               } else {
                  echo $message . PHP_EOL;
                  echo sprintf(
                     __('To ignore the inconsistencies and upgrade anyway run %s', 'formcreator'),
                     'bin/console glpi:plugin:install formcreator -f -p skip-db-check'
                  ) . PHP_EOL;
               }
               return false;
            }
         }
      }

      if (isset($args['force-upgrade']) && $args['force-upgrade'] === true) {
         // Might return false
         $fromSchemaVersion = array_search(PLUGIN_FORMCREATOR_SCHEMA_VERSION, $this->upgradeSteps);
      } else {
         $fromSchemaVersion = $this->getSchemaVersion();
      }

      if (version_compare($fromSchemaVersion ?? '0.0', '2.5') < 0) {
         $message = __('Upgrade from version older than 2.5.0 is no longer supported. Please upgrade to GLPI 9.5.7, upgrade Formcreator to version 2.12.5, then upgrade again to GLPI 10 or later and Formcreator 2.13 or later.', 'formcreator');
         if (isCommandLine()) {
            echo $message;
         } else {
            Session::addMessageAfterRedirect(
               $message,
               true,
               ERROR
            );
         }
         return false;
      }

      $this->resyncIssues = false;

      ob_start();
      while ($fromSchemaVersion && isset($this->upgradeSteps[$fromSchemaVersion])) {
         $this->upgradeOneStep($this->upgradeSteps[$fromSchemaVersion]);
         $fromSchemaVersion = $this->upgradeSteps[$fromSchemaVersion];
      }
      $this->migration->executeMigration();

      // if the schema contains new tables
      $this->installSchema();
      Config::setConfigurationValues('formcreator', ['schema_version' => PLUGIN_FORMCREATOR_SCHEMA_VERSION]);

      ob_get_flush();      $lazyCheck = false;
      // $lazyCheck = (version_compare($oldVersion, '2.13.0') < 0);
      // Check schema of tables after upgrade
      $checkResult = $this->checkSchema(
         PLUGIN_FORMCREATOR_VERSION,
         false,
         $lazyCheck,
         $lazyCheck,
         $lazyCheck,
         $lazyCheck,
         $lazyCheck
      );
      if (!$checkResult) {
         $message = sprintf(
            __('The database schema is not consistent with the current version of Formcreator %s. To see the logs enable the plugin and run the command %s', 'formcreator'),
            PLUGIN_FORMCREATOR_VERSION,
            'bin/console glpi:database:check_schema_integrity -p formcreator'
         );
         if (!isCommandLine()) {
            Session::addMessageAfterRedirect($message, false, ERROR);
         } else {
            echo $message . PHP_EOL;
         }
      } else {
         if (isCommandLine()) {
            echo __('The tables of the plugin passed the schema integrity check.', 'formcreator') . PHP_EOL;
         }
      }

      return true;
   }

   /**
    * Proceed to upgrade of the plugin to the given version
    *
    * @param string $toVersion
    */
   protected function upgradeOneStep($toVersion) {
      ini_set("max_execution_time", "0");
      ini_set("memory_limit", "-1");

      $suffix = str_replace('.', '_', $toVersion);
      $includeFile = __DIR__ . "/../install/upgrade_to_$toVersion.php";
      if (!is_readable($includeFile) || !is_file($includeFile)) {
         return;
      }

      include_once $includeFile;
      $updateClass = "PluginFormcreatorUpgradeTo$suffix";
      $this->migration->addNewMessageArea("Upgrade to $toVersion");
      $upgradeStep = new $updateClass();
      $upgradeStep->upgrade($this->migration);
      $this->migration->executeMigration();
      if (method_exists($upgradeStep, 'isResyncIssuesRequired')) {
         $this->resyncIssues = $this->resyncIssues || $upgradeStep->isResyncIssuesRequired();
      }
   }

   /**
    * Find the version of the plugin
    *
    * @return string|NULL
    */
   protected function getSchemaVersion() {
      if ($this->isPluginInstalled()) {
         return $this->getSchemaVersionFromGlpiConfig();
      }

      return null;
   }

   /**
    * Find version of the plugin in GLPI's config
    *
    * @return string
    */
   protected function getSchemaVersionFromGlpiConfig() {
      /** @var \DBmysql $DB */
      global $DB;

      $config = Config::getConfigurationValues('formcreator', ['schema_version']);
      if (!isset($config['schema_version'])) {
         // No schema version in GLPI config, then this is older than 2.5
         if ($DB->tableExists('glpi_plugin_formcreator_items_targettickets')) {
            // Workaround bug #794 where schema version was not saved
            return '2.6';
         }
         return '0.0';
      }

      // Version found in GLPI config
      return $config['schema_version'];
   }

   /**
    * is the plugin already installed ?
    *
    * @return boolean
    */
   public function isPluginInstalled() {
      /** @var \DBmysql $DB */
      global $DB;

      $result = $DB->doQuery("SHOW TABLES LIKE 'glpi_plugin_formcreator_%'");
      if ($result) {
         if ($DB->numrows($result) > 0) {
            return true;
         }
      }

      return false;
   }

   protected function installSchema() {
      /** @var \DBmysql $DB */
      global $DB;

      $dbFile = plugin_formcreator_getSchemaPath();
      if (!$DB->runFile($dbFile)) {
         $this->migration->displayWarning("Error creating tables : " . $DB->error(), true);
         die('Giving up');
      }
   }

   protected function deleteNotifications() {
      /** @var \DBmysql $DB */
      global $DB;

      $itemtypes = [
         PluginFormcreatorFormAnswer::class,
      ];

      if (count($itemtypes) == 0) {
         return;
      }

      // Delete translations
      $translation = new NotificationTemplateTranslation();
      $translation->deleteByCriteria([
         'INNER JOIN' => [
            NotificationTemplate::getTable() => [
               'FKEY' => [
                  NotificationTemplateTranslation::getTable() => NotificationTemplate::getForeignKeyField(),
                  NotificationTemplate::getTable() => NotificationTemplate::getIndexName()
               ]
            ]
         ],
         'WHERE' => [
            NotificationTemplate::getTable() . '.itemtype' => $itemtypes
         ]
      ]);

      // Delete notification templates
      $template = new NotificationTemplate();
      $template->deleteByCriteria(['itemtype' => $itemtypes]);

      // Delete notification targets
      $target = new NotificationTarget();
      $target->deleteByCriteria([
         'INNER JOIN' => [
            Notification::getTable() => [
               'FKEY' => [
                  NotificationTarget::getTable() => Notification::getForeignKeyField(),
                  Notification::getTable() => Notification::getIndexName()
               ]
            ]
         ],
         'WHERE' => [
            Notification::getTable() . '.itemtype' => $itemtypes
         ],
      ]);

      // Delete notifications and their templates
      $notification = new Notification();
      $notification_notificationTemplate = new Notification_NotificationTemplate();
      $rows = $DB->request([
         'SELECT' => ['id'],
         'FROM'   => $notification::getTable(),
         'WHERE'  => [
            'itemtype' => $itemtypes
         ]
      ]);
      foreach ($rows as $row) {
         $notification_notificationTemplate->deleteByCriteria(['notifications_id' => $row['id']]);
         $notification->delete($row);
      }

      $notification->deleteByCriteria(['itemtype' => $itemtypes]);
   }

   protected function deleteTicketRelation() {
      /** @var array $CFG_GLPI */
      global $CFG_GLPI;

      // Delete relations with tickets with email notifications disabled
      $use_mailing = $CFG_GLPI['use_notifications'] == 1;
      $CFG_GLPI['use_notifications'] = 0;

      $item_ticket = new Item_Ticket();
      $item_ticket->deleteByCriteria(['itemtype' => PluginFormcreatorFormAnswer::class]);

      $CFG_GLPI['use_notifications'] = $use_mailing ? '1' : '0';
   }

   /**
    * Cleanups the database from plugin's itemtypes (tables and relations)
    */
   protected function deleteTables() {
      /** @var \DBmysql $DB */
      global $DB;

      // Keep  these itemtypes as string because classes might not be available (if plugin is inactive)
      $itemtypes = [
         'PluginFormcreatorAnswer',
         'PluginFormcreatorCategory',
         'PluginFormcreatorEntityconfig',
         'PluginFormcreatorFormAnswer',
         'PluginFormcreatorForm_Profile',
         'PluginFormcreatorForm_User',
         'PluginFormcreatorForm_Group',
         'PluginFormcreatorForm_Validator',
         'PluginFormcreatorForm',
         'PluginFormcreatorCondition',
         'PluginFormcreatorQuestion',
         'PluginFormcreatorSection',
         'PluginFormcreatorTargetChange',
         'PluginFormcreatorTargetProblem',
         'PluginFormcreatorTargetTicket',
         'PluginFormcreatorTarget_Actor',
         'PluginFormcreatorItem_TargetTicket',
         'PluginFormcreatorIssue',
         'PluginFormcreatorQuestionDependency',
         'PluginFormcreatorQuestionRange',
         'PluginFormcreatorQuestionRegex',
         'PluginFormcreatorForm_Language',
      ];

      foreach ($itemtypes as $itemtype) {
         $table = getTableForItemType($itemtype);
         $log = new Log();
         $log->deleteByCriteria(['itemtype' => $itemtype]);

         $displayPreference = new DisplayPreference();
         $displayPreference->deleteByCriteria(['itemtype' => $itemtype]);

         $DB->doQuery("DROP TABLE IF EXISTS `$table`");
      }

      // Drop views
      $DB->doQuery('DROP VIEW IF EXISTS `glpi_plugin_formcreator_issues`');

      $displayPreference = new DisplayPreference();
      $displayPreference->deleteByCriteria(['itemtype' => 'PluginFormcreatorIssue']);
   }

   /**
    * http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
    * @param string $haystack
    * @param string $needle
    */
   protected function endsWith($haystack, $needle) {
      // search foreward starting from end minus needle length characters
      return $needle === '' || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
   }

   /**
    *
    */
   public function uninstall() {
      $this->deleteTicketRelation();
      $this->deleteTables();
      $this->deleteNotifications();

      $config = new Config();
      $config->deleteByCriteria(['context' => 'formcreator']);
   }







   /**
    * Check the schema of all tables of the plugin against the expected schema of the given version
    *
    * @return boolean
    */
   public function checkSchema(
      string $version,
      bool $strict = true,
      bool $ignore_innodb_migration = false,
      bool $ignore_timestamps_migration = false,
      bool $ignore_utf8mb4_migration = false,
      bool $ignore_dynamic_row_format_migration = false,
      bool $ignore_unsigned_keys_migration = false
   ): bool {
      /** @var \DBmysql $DB */
      global $DB;

      $schemaFile = plugin_formcreator_getSchemaPath($version);

      $checker = new DatabaseSchemaIntegrityChecker(
         $DB,
         $strict,
         $ignore_innodb_migration,
         $ignore_timestamps_migration,
         $ignore_utf8mb4_migration,
         $ignore_dynamic_row_format_migration,
         $ignore_unsigned_keys_migration
      );

      try {
         $differences = $checker->checkCompleteSchema($schemaFile, true, 'plugin:formcreator');
      } catch (\Throwable $e) {
         $message = __('Failed to check the sanity of the tables!', 'formcreator');
         if (isCommandLine()) {
            echo $message . PHP_EOL;
         } else {
            Session::addMessageAfterRedirect($message, false, ERROR);
         }
         return false;
      }

      if (count($differences) > 0) {
         foreach ($differences as $table_name => $difference) {
            $message = null;
            switch ($difference['type']) {
               case DatabaseSchemaIntegrityChecker::RESULT_TYPE_ALTERED_TABLE:
                  $message = sprintf(__('Table schema differs for table "%s".'), $table_name);
                  break;
               case DatabaseSchemaIntegrityChecker::RESULT_TYPE_MISSING_TABLE:
                  $message = sprintf(__('Table "%s" is missing.'), $table_name);
                  break;
               case DatabaseSchemaIntegrityChecker::RESULT_TYPE_UNKNOWN_TABLE:
                  $message = sprintf(__('Unknown table "%s" has been found in database.'), $table_name);
                  break;
            }
            echo $message . PHP_EOL;
            echo $difference['diff'] . PHP_EOL;
         }
         return false;
      }

      return true;
   }

   /**
    * Upgrade columns containing foreign keys to unsigned int
    * picked from upgrade to 2.13.0, duplicated here to reduce upgrade errors
    * when checking the DB schema.
    *
    * @return void
    */
   protected function migrateFkToUnsignedInt() {
      /** @var \DBmysql $DB */
      global $DB;

      $table = 'glpi_plugin_formcreator_formanswers';
      if ($DB->tableExists($table) && $DB->fieldExists($table, 'requester_id')) {
         $DB->doQuery("UPDATE `$table` SET `requester_id` = 0 WHERE `requester_id` IS NULL");
      }

      $table = 'glpi_plugin_formcreator_targetchanges';
      if ($DB->tableExists($table) && $DB->fieldExists($table, 'due_date_question')) {
         $DB->doQuery("UPDATE `$table` SET `due_date_question` = 0 WHERE `due_date_question` IS NULL");
      }
      if ($DB->tableExists($table) && $DB->fieldExists($table, 'destination_entity_value')) {
         $DB->doQuery("UPDATE `$table` SET `destination_entity_value` = 0 WHERE `destination_entity_value` IS NULL");
      }
      $table = 'glpi_plugin_formcreator_targettickets';
      if ($DB->tableExists($table) && $DB->fieldExists($table, 'due_date_question')) {
         $DB->doQuery("UPDATE `$table` SET `due_date_question` = 0 WHERE `due_date_question` IS NULL");
      }
      if ($DB->tableExists($table) && $DB->fieldExists($table, 'destination_entity_value')) {
         $DB->doQuery("UPDATE `$table` SET `destination_entity_value` = 0 WHERE `destination_entity_value` IS NULL");
      }
      $table = 'glpi_plugin_formcreator_targets_actors';
      if ($DB->tableExists($table) && $DB->fieldExists($table, 'actor_value')) {
         $DB->doQuery("UPDATE `$table` SET `actor_value` = 0 WHERE `actor_value` IS NULL");
      }

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
            $type = 'INT ' . \DBConnection::getDefaultPrimaryKeySignOption() . ' NOT NULL DEFAULT 0';
            if (!$DB->fieldExists($table, $field)) {
               continue;
            }
            $this->migration->changeField($table, $field, $field, $type);
         }
      }

      $table = 'glpi_plugin_formcreator_entityconfigs';
      if ($DB->tableExists($table)) {
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
               $DB->doQuery("UPDATE `$table` SET `id`='$newId' WHERE `id` = 0");
            }
         }
         $this->migration->changeField($table, 'id', 'id', 'int ' . \DBConnection::getDefaultPrimaryKeySignOption() . ' not null auto_increment');
      }
   }
}