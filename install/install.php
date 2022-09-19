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

use Glpi\Dashboard\Dashboard;
use Glpi\Dashboard\Item as Dashboard_Item;
use Glpi\Dashboard\Right as Dashboard_Right;
use Glpi\System\Diagnostic\DatabaseSchemaIntegrityChecker;
use Ramsey\Uuid\Uuid;

class PluginFormcreatorInstall {
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

      $this->configureExistingEntities();
      $this->createRequestType();
      $this->createDefaultDisplayPreferences();
      $this->createCronTasks();
      $this->createNotifications();
      $this->createMiniDashboard();
      Config::setConfigurationValues('formcreator', ['schema_version' => PLUGIN_FORMCREATOR_SCHEMA_VERSION]);

      $task = new CronTask();
      PluginFormcreatorIssue::cronSyncIssues($task);

      return true;
   }

   /**
    * Upgrade the plugin
    * @param Migration $migration
    * @param array $args arguments passed to CLI
    * @return bool
    */
   public function upgrade(Migration $migration, $args = []): bool {
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

      // Check schema of tables before upgrading
      $oldVersion = Config::getConfigurationValue('formcreator', 'previous_version');
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
                  __('The database schema is not consistent with the installed Formcreator %s. To see the logs run the command %s', 'formcreator'),
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

      $this->migration = $migration;
      if (isset($args['force-upgrade']) && $args['force-upgrade'] === true) {
         // Might return false
         $fromSchemaVersion = array_search(PLUGIN_FORMCREATOR_SCHEMA_VERSION, $this->upgradeSteps);
      } else {
         $fromSchemaVersion = $this->getSchemaVersion();
      }

      if (version_compare($fromSchemaVersion, '2.5') < 0) {
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
      $this->configureExistingEntities();
      $this->createRequestType();
      $this->createDefaultDisplayPreferences();
      $this->createCronTasks();
      $this->createMiniDashboard();
      Config::setConfigurationValues('formcreator', ['schema_version' => PLUGIN_FORMCREATOR_SCHEMA_VERSION]);
      ob_get_flush();

      if ($this->resyncIssues) {
         // An upgrade step requires a resync of the issues
         $task = new CronTask();
         PluginFormcreatorIssue::cronSyncIssues($task);
      }

      $lazyCheck = false;
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
            __('The database schema is not consistent with the installed Formcreator %s. To see the logs enable the plugin and run the command %s', 'formcreator'),
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
      $includeFile = __DIR__ . "/upgrade_to_$toVersion.php";
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
      global $DB;

      $result = $DB->query("SHOW TABLES LIKE 'glpi_plugin_formcreator_%'");
      if ($result) {
         if ($DB->numrows($result) > 0) {
            return true;
         }
      }

      return false;
   }

   protected function installSchema() {
      global $DB;

      $dbFile = plugin_formcreator_getSchemaPath();
      if (!$DB->runFile($dbFile)) {
         $this->migration->displayWarning("Error creating tables : " . $DB->error(), true);
         die('Giving up');
      }
   }

   protected function configureExistingEntities() {
      global $DB;

      /** Value -2 is "inheritance from parent" @see PluginFormcreatorEntityconfig::CONFIG_PARENT */
      $query = "INSERT INTO glpi_plugin_formcreator_entityconfigs
                  (entities_id, replace_helpdesk, default_form_list_mode, sort_order, is_kb_separated, is_search_visible, is_dashboard_visible, is_header_visible, is_search_issue_visible, tile_design)
               SELECT ent.id,
                  IF(ent.id = 0, 0, -2),
                  IF(ent.id = 0, 0, -2),
                  IF(ent.id = 0, 0, -2),
                  IF(ent.id = 0, 0, -2),
                  IF(ent.id = 0, 0, -2),
                  IF(ent.id = 0, 1, -2),
                  IF(ent.id = 0, 0, -2),
                  IF(ent.id = 0, 1, -2),
                  IF(ent.id = 0, 0, -2)
                FROM glpi_entities ent
                LEFT JOIN glpi_plugin_formcreator_entityconfigs conf
                  ON ent.id = conf.entities_id
                WHERE conf.entities_id IS NULL";
      $result = $DB->query($query);
      if (!$result) {
         Toolbox::logInFile('sql-errors', $DB->error());
         die ($DB->error());
      }
   }

   protected function createRequestType() {
      global $DB;

      $query  = "SELECT id FROM `glpi_requesttypes` WHERE `name` LIKE 'Formcreator';";
      $result = $DB->query($query) or die ($DB->error());

      if (!$DB->numrows($result) > 0) {
         $query = "INSERT INTO `glpi_requesttypes` SET `name` = 'Formcreator';";
         $DB->query($query) or die ($DB->error());
         $DB->insertId();
      }
   }

   protected function createDefaultDisplayPreferences() {
      $this->migration->updateDisplayPrefs([
         'PluginFormcreatorFormAnswer' => [2, 3, 4, 5, 6],
         'PluginFormcreatorForm'       => [30, 3, 10, 7, 8, 9],
         'PluginFormcreatorIssue'      => [1, 2, 4, 5, 6, 7, 8],
      ]);
   }

   /**
    * Declares the notifications that the plugin handles
    */
   protected function createNotifications() {
      global $DB;

      $notifications = [
         'plugin_formcreator_form_created' => [
            'name'     => __('A form has been created', 'formcreator'),
            'subject'  => __('Your request has been saved', 'formcreator'),
            'content'  => __('Hi,\nYour request from GLPI has been successfully saved with number ##formcreator.request_id## and transmitted to the helpdesk team.\nYou can see your answers onto the following link:\n##formcreator.validation_link##', 'formcreator'),
            'notified' => PluginFormcreatorNotificationTargetFormAnswer::AUTHOR,
         ],
         'plugin_formcreator_need_validation' => [
            'name'     => __('A form need to be validate', 'formcreator'),
            'subject'  => __('A form from GLPI need to be validate', 'formcreator'),
            'content'  => __('Hi,\nA form from GLPI need to be validate and you have been choosen as the validator.\nYou can access it by clicking onto this link:\n##formcreator.validation_link##', 'formcreator'),
            'notified' => PluginFormcreatorNotificationTargetFormAnswer::APPROVER,
         ],
         'plugin_formcreator_refused'         => [
            'name'     => __('The form is refused', 'formcreator'),
            'subject'  => __('Your form has been refused by the validator', 'formcreator'),
            'content'  => __('Hi,\nWe are sorry to inform you that your form has been refused by the validator for the reason below:\n##formcreator.validation_comment##\n\nYou can still modify and resubmit it by clicking onto this link:\n##formcreator.validation_link##', 'formcreator'),
            'notified' => PluginFormcreatorNotificationTargetFormAnswer::AUTHOR,
         ],
         'plugin_formcreator_accepted'        => [
            'name'     => __('The form is accepted', 'formcreator'),
            'subject'  => __('Your form has been accepted by the validator', 'formcreator'),
            'content'  => __('Hi,\nWe are pleased to inform you that your form has been accepted by the validator.\nYour request will be considered soon.', 'formcreator'),
            'notified' => PluginFormcreatorNotificationTargetFormAnswer::AUTHOR,
         ],
         'plugin_formcreator_deleted'         => [
            'name'     => __('The form is deleted', 'formcreator'),
            'subject'  => __('Your form has been deleted by an administrator', 'formcreator'),
            'content'  => __('Hi,\nWe are sorry to inform you that your request cannot be considered and has been deleted by an administrator.', 'formcreator'),
            'notified' => PluginFormcreatorNotificationTargetFormAnswer::AUTHOR,
         ],
      ];

      // Create the notification template
      $notification                       = new Notification();
      $template                           = new NotificationTemplate();
      $translation                        = new NotificationTemplateTranslation();
      $notification_target                = new NotificationTarget();
      $notification_notificationTemplate  = new Notification_NotificationTemplate();

      foreach ($notifications as $event => $data) {
         // Check if notification already exists
         $exists = $DB->request([
            'COUNT'  => 'cpt',
            'FROM'   => $notification::getTable(),
            'WHERE'  => [
               'itemtype' => 'PluginFormcreatorFormAnswer',
               'event'    => $event,
            ]
         ])->current();

         // If it doesn't exists, create it
         if ($exists['cpt'] == 0) {
            $template_id = $template->add([
               'name'     => Toolbox::addslashes_deep($data['name']),
               'comment'  => '',
               'itemtype' => PluginFormcreatorFormAnswer::class,
            ]);

            // Add a default translation for the template
            $translation->add([
               'notificationtemplates_id' => $template_id,
               'language'                 => '',
               'subject'                  => Toolbox::addslashes_deep($data['subject']),
               'content_text'             => Toolbox::addslashes_deep($data['content']),
               'content_html'             => '<p>'.str_replace('\n', '<br />', Toolbox::addslashes_deep($data['content'])).'</p>',
            ]);

            // Create the notification
            $notification_id = $notification->add([
               'name'                     => Toolbox::addslashes_deep($data['name']),
               'comment'                  => '',
               'entities_id'              => 0,
               'is_recursive'             => 1,
               'is_active'                => 1,
               'itemtype'                 => PluginFormcreatorFormAnswer::class,
               'notificationtemplates_id' => $template_id,
               'event'                    => $event,
               'mode'                     => 'mail',
            ]);

            $notification_notificationTemplate->add([
               'notifications_id'         => $notification_id,
               'notificationtemplates_id' => $template_id,
               'mode'                     => Notification_NotificationTemplate::MODE_MAIL,
            ]);

            // Add default notification targets
            $notification_target->add([
               "items_id"         => $data['notified'],
               "type"             => Notification::USER_TYPE,
               "notifications_id" => $notification_id,
            ]);
         }
      }
   }

   protected function deleteNotifications() {
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

         $DB->query("DROP TABLE IF EXISTS `$table`");
      }

      // Drop views
      $DB->query('DROP VIEW IF EXISTS `glpi_plugin_formcreator_issues`');

      $displayPreference = new DisplayPreference();
      $displayPreference->deleteByCriteria(['itemtype' => PluginFormCreatorIssue::class]);
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
      $this->deleteMiniDashboard();

      $config = new Config();
      $config->deleteByCriteria(['context' => 'formcreator']);
   }

   /**
    * Create cron tasks
    */
   protected function createCronTasks() {
      CronTask::Register(PluginFormcreatorIssue::class, 'SyncIssues', HOUR_TIMESTAMP,
         [
            'comment'   => __('Formcreator - Sync service catalog issues', 'formcreator'),
            'mode'      => CronTask::MODE_EXTERNAL,
            'state'     => '0', // Deprecated since 2.11
         ]
      );
   }

   protected function createMiniDashboard() {
      $this->createMiniDashboardBigNumbers();
      // $this->createMiniDashboardSummary();
   }

   protected function createMiniDashboardSummary() {
      $dashboard = new Dashboard();

      if ($dashboard->getFromDB('plugin_formcreator_issue_summary') !== false) {
         // The dashboard already exists, nothing to create
         return;
      }

      $dashboard->add([
         'key'     => 'plugin_formcreator_issue_summary',
         'name'    => 'Assistance requests summary',
         'context' => 'mini_core',
      ]);

      if ($dashboard->isNewItem()) {
         // Failed to create the dashboard
         return;
      };

      $item = new Dashboard_Item();
      $item->addForDashboard($dashboard->fields['id'], [[
         'card_id' => 'plugin_formcreator_issues_summary',
         'gridstack_id' => 'plugin_formcreator_issues_summary_' . Uuid::uuid4(),
         'x'       => 10,
         'y'       => 0,
         'width'   => 12,
         'height'  => 2,
         'card_options' => [
            'color'        => '#FAFAFA',
            'widgettype'   => 'summaryNumbers',
            'use_gradient' => '0',
            'point_labels' => '0',
            'limit'        => '7',
         ],
      ]]);

      $this->adRightsToMiniDashboard($dashboard->fields['id']);
   }

   protected function createMiniDashboardBigNumbers() {
      $dashboard = new Dashboard();

      if ($dashboard->getFromDB('plugin_formcreator_issue_counters') !== false) {
         // The dashboard already exists, nothing to create
         return;
      }

      $dashboard->add([
         'key'     => 'plugin_formcreator_issue_counters',
         'name'    => 'Assistance requests counts',
         'context' => 'mini_core',
      ]);

      if ($dashboard->isNewItem()) {
         // Failed to create the dashboard
         return;
      };

      $commonOptions = [
         'widgettype'   => 'bigNumber',
         'use_gradient' => '0',
         'point_labels' => '0',
      ];
      $cards = [
         'plugin_formcreator_all_issues'      => [
            'color' => '#ffd957'
         ],
         'plugin_formcreator_incoming_issues' => [
            'color' => '#6fd169'
         ],
         'plugin_formcreator_assigned_issues' => [
            'color' => '#eaf4f7'
         ],
         'plugin_formcreator_waiting_issues'   => [
            'color' => '#ffcb7d'
         ],
         'plugin_formcreator_validate_issues'  => [
            'color' => '#6298d5'
         ],
         'plugin_formcreator_solved_issues'    => [
            'color' => '#d7d7d7'
         ],
         'plugin_formcreator_closed_issues'    => [
            'color' => '#515151'
         ],
      ];

      // With counters
      $x = 2;
      $w = 3; // Width
      $h = 1; // Height
      $s = 1; // space between widgets
      $y = 0;
      foreach ($cards as $key => $options) {
         $item = new Dashboard_Item();
         $item->addForDashboard($dashboard->fields['id'], [[
            'card_id' => $key,
            'gridstack_id' => $key . '_' . Uuid::uuid4(),
            'x'       => $x,
            'y'       => $y,
            'width'   => $w,
            'height'  => $h,
            'card_options' => array_merge($commonOptions, $options),
         ]]);
         $x += ($w + $s);
      }

      $this->adRightsToMiniDashboard($dashboard->fields['id']);
   }

   protected function adRightsToMiniDashboard(int $dashboardId) {
      // Give rights to all self service profiles
      $profile = new Profile();
      $helpdeskProfiles = $profile->find([
         'interface' => 'helpdesk',
      ]);
      foreach ($helpdeskProfiles as $helpdeskProfile) {
         $dashboardRight = new Dashboard_Right();
         $dashboardRight->add([
            'dashboards_dashboards_id' => $dashboardId,
            'itemtype'                 => Profile::getType(),
            'items_id'                => $helpdeskProfile['id'],
         ]);
      }
   }

   public function deleteMiniDashboard(): bool {
      $dashboard = new Dashboard();

      if ($dashboard->getFromDB('plugin_formcreator_issue_counters') === false) {
         // The dashboard does not exists, nothing to delete
         return true;
      }

      $dashboard->delete([
         'key' => 'plugin_formcreator_issue_counters'
      ]);
      if ($dashboard->getFromDB('plugin_formcreator_issue_counters') !== false) {
         // Failed to delete the dashboard
         return false;
      }

      return true;
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
         return false;
      }

      return true;
   }
}
