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
   ];

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
      $this->migration = $migration;
      if (isset($args['force-upgrade']) && $args['force-upgrade'] === true) {
         // Might return false
         $fromSchemaVersion = array_search(PLUGIN_FORMCREATOR_SCHEMA_VERSION, $this->upgradeSteps);
      } else {
         $fromSchemaVersion = $this->getSchemaVersion();
      }

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
      Config::setConfigurationValues('formcreator', ['schema_version' => PLUGIN_FORMCREATOR_SCHEMA_VERSION]);

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
      if (is_readable($includeFile) && is_file($includeFile)) {
         include_once $includeFile;
         $updateClass = "PluginFormcreatorUpgradeTo$suffix";
         $this->migration->addNewMessageArea("Upgrade to $toVersion");
         $upgradeStep = new $updateClass();
         $upgradeStep->upgrade($this->migration);
         $this->migration->executeMigration();
         $this->migration->displayMessage('Done');
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

      $this->migration->displayMessage("create database schema");

      $dbFile = __DIR__ . '/mysql/plugin_formcreator_empty.sql';
      if (!$DB->runFile($dbFile)) {
         $this->migration->displayWarning("Error creating tables : " . $DB->error(), true);
         die('Giving up');
      }
   }

   protected function configureExistingEntities() {
      global $DB;

      $this->migration->displayMessage("Configure existing entities");

      $query = "INSERT INTO glpi_plugin_formcreator_entityconfigs
                  (id, replace_helpdesk, sort_order, is_kb_separated, is_search_visible, is_header_visible)
               SELECT ent.id,
                  IF(ent.id = 0, 0, ".PluginFormcreatorEntityconfig::CONFIG_PARENT."),
                  IF(ent.id = 0, 0, ".PluginFormcreatorEntityconfig::CONFIG_PARENT."),
                  IF(ent.id = 0, 0, ".PluginFormcreatorEntityconfig::CONFIG_PARENT."),
                  IF(ent.id = 0, 0, ".PluginFormcreatorEntityconfig::CONFIG_PARENT."),
                  IF(ent.id = 0, 0, ".PluginFormcreatorEntityconfig::CONFIG_PARENT.")
                FROM glpi_entities ent
                LEFT JOIN glpi_plugin_formcreator_entityconfigs conf
                  ON ent.id = conf.id
                WHERE conf.id IS NULL";
      $result = $DB->query($query);
      if (!$result) {
         Toolbox::logInFile('sql-errors', $DB->error());
         die ($DB->error());
      }
   }

   protected function createRequestType() {
      global $DB;

      $this->migration->displayMessage("create request type");

      $query  = "SELECT id FROM `glpi_requesttypes` WHERE `name` LIKE 'Formcreator';";
      $result = $DB->query($query) or die ($DB->error());

      if (!$DB->numrows($result) > 0) {
         $query = "INSERT INTO `glpi_requesttypes` SET `name` = 'Formcreator';";
         $DB->query($query) or die ($DB->error());
         $DB->insertId();
      }
   }

   protected function createDefaultDisplayPreferences() {
      $this->migration->displayMessage("create default display preferences");
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

      $this->migration->displayMessage("create notifications");

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
         ])->next();

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
         'PluginFormcreatorForm_Validator',
         'PluginFormcreatorForm',
         'PluginFormcreatorCondition',
         'PluginFormcreatorQuestion',
         'PluginFormcreatorSection',
         'PluginFormcreatorTargetChange',
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
}
