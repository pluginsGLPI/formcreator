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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
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
    * Install the plugin
    * @param Migration $migration
    *
    * @return void
    */
   public function install(Migration $migration) {
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
    */
   public function upgrade(Migration $migration) {
      $this->migration = $migration;
      $fromSchemaVersion = $this->getSchemaVersion();

      $this->installSchema();

      // All cases are run starting from the one matching the current schema version
      switch ($fromSchemaVersion) {
         case '0.0':
         case '2.5':
            //Any schema version below or equal 2.5
            require_once(__DIR__ . '/update_0.0_2.5.php');
            plugin_formcreator_update_2_5($this->migration);

         case '2.6':
            //Any schema version below or equal 2.6
            require_once(__DIR__ . '/update_2.5_2.6.php');
            plugin_formcreator_update_2_6($this->migration);

            require_once(__DIR__ . '/update_2.6_2.6.1.php');
            plugin_formcreator_update_2_6_1($this->migration);

            require_once(__DIR__ . '/update_2.6.2_2.6.3.php');
            plugin_formcreator_update_2_6_3($this->migration);
         default:
            // Must be the last case
            if ($this->endsWith(PLUGIN_FORMCREATOR_VERSION, "-dev")) {
               if (is_readable(__DIR__ . "/update_dev.php") && is_file(__DIR__ . "/update_dev.php")) {
                  include_once __DIR__ . "/update_dev.php";
                  $updateDevFunction = 'plugin_formcreator_update_dev';
                  if (function_exists($updateDevFunction)) {
                     $updateDevFunction($this->migration);
                  }
               }
            }
      }
      $this->migration->executeMigration();
      $this->configureExistingEntities();
      $this->createRequestType();
      $this->createDefaultDisplayPreferences();
      $this->createCronTasks();
      Config::setConfigurationValues('formcreator', ['schema_version' => PLUGIN_FORMCREATOR_SCHEMA_VERSION]);

      return true;
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

      $config = Config::getConfigurationValues('formcreator', array('schema_version'));
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

      $query = "SELECT `id` FROM `glpi_entities`
                WHERE `id` NOT IN (
                   SELECT `id` FROM `glpi_plugin_formcreator_entityconfigs`
                )";
      $result = $DB->query($query);
      if (!$result) {
         Toolbox::logInFile('sql-errors', $DB->error());
         die ($DB->error());
      }
      while ($row = $DB->fetch_assoc($result)) {
         $entityConfig = new PluginFormcreatorEntityconfig();
         $entityConfig->add([
               'id'                 => $row['id'],
               'replace_helpdesk'   => ($row['id'] == 0) ? 0 : PluginFormcreatorEntityconfig::CONFIG_PARENT
         ]);
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
         $DB->insert_id();
      }
   }

   protected function createDefaultDisplayPreferences() {
      global $DB;
      $this->migration->displayMessage("create default display preferences");

      // Create standard display preferences
      $displayprefs = new DisplayPreference();
      $found_dprefs = $displayprefs->find("`itemtype` = 'PluginFormcreatorForm_Answer'");
      if (count($found_dprefs) == 0) {
         $query = "INSERT IGNORE INTO `glpi_displaypreferences`
                   (`id`, `itemtype`, `num`, `rank`, `users_id`) VALUES
                   (NULL, 'PluginFormcreatorForm_Answer', 2, 2, 0),
                   (NULL, 'PluginFormcreatorForm_Answer', 3, 3, 0),
                   (NULL, 'PluginFormcreatorForm_Answer', 4, 4, 0),
                   (NULL, 'PluginFormcreatorForm_Answer', 5, 5, 0),
                   (NULL, 'PluginFormcreatorForm_Answer', 6, 6, 0)";
         $DB->query($query) or die ($DB->error());
      }

      $displayprefs = new DisplayPreference;
      $found_dprefs = $displayprefs->find("`itemtype` = 'PluginFormcreatorForm'");
      if (count($found_dprefs) == 0) {
         $query = "INSERT IGNORE INTO `glpi_displaypreferences`
                   (`id`, `itemtype`, `num`, `rank`, `users_id`) VALUES
                   (NULL, 'PluginFormcreatorForm', 30, 1, 0),
                   (NULL, 'PluginFormcreatorForm', 3, 2, 0),
                   (NULL, 'PluginFormcreatorForm', 10, 3, 0),
                   (NULL, 'PluginFormcreatorForm', 7, 4, 0),
                   (NULL, 'PluginFormcreatorForm', 8, 5, 0),
                   (NULL, 'PluginFormcreatorForm', 9, 6, 0);";
         $DB->query($query) or die ($DB->error());
      }

      $displayprefs = new DisplayPreference;
      $found_dprefs = $displayprefs->find("`itemtype` = 'PluginFormcreatorIssue'");
      if (count($found_dprefs) == 0) {
         $query = "INSERT IGNORE INTO `glpi_displaypreferences`
                   (`id`, `itemtype`, `num`, `rank`, `users_id`) VALUES
                   (NULL, 'PluginFormcreatorIssue', 1, 1, 0),
                   (NULL, 'PluginFormcreatorIssue', 2, 2, 0),
                   (NULL, 'PluginFormcreatorIssue', 4, 3, 0),
                   (NULL, 'PluginFormcreatorIssue', 5, 4, 0),
                   (NULL, 'PluginFormcreatorIssue', 6, 5, 0),
                   (NULL, 'PluginFormcreatorIssue', 7, 6, 0),
                   (NULL, 'PluginFormcreatorIssue', 8, 7, 0)";
         $DB->query($query) or die ($DB->error());
      }
   }

   /**
    * Declares the notifications that the plugin handles
    */
   protected function createNotifications() {
      $this->migration->displayMessage("create notifications");

      $notifications = [
            'plugin_formcreator_form_created' => [
               'name'     => __('A form has been created', 'formcreator'),
               'subject'  => __('Your request has been saved', 'formcreator'),
               'content'  => __('Hi,\nYour request from GLPI has been successfully saved with number ##formcreator.request_id## and transmitted to the helpdesk team.\nYou can see your answers onto the following link:\n##formcreator.validation_link##', 'formcreator'),
               'notified' => PluginFormcreatorNotificationTargetForm_answer::AUTHOR,
            ],
            'plugin_formcreator_need_validation' => [
               'name'     => __('A form need to be validate', 'formcreator'),
               'subject'  => __('A form from GLPI need to be validate', 'formcreator'),
               'content'  => __('Hi,\nA form from GLPI need to be validate and you have been choosen as the validator.\nYou can access it by clicking onto this link:\n##formcreator.validation_link##', 'formcreator'),
               'notified' => PluginFormcreatorNotificationTargetForm_answer::APPROVER,
            ],
            'plugin_formcreator_refused'         => [
               'name'     => __('The form is refused', 'formcreator'),
               'subject'  => __('Your form has been refused by the validator', 'formcreator'),
               'content'  => __('Hi,\nWe are sorry to inform you that your form has been refused by the validator for the reason below:\n##formcreator.validation_comment##\n\nYou can still modify and resubmit it by clicking onto this link:\n##formcreator.validation_link##', 'formcreator'),
               'notified' => PluginFormcreatorNotificationTargetForm_answer::AUTHOR,
            ],
            'plugin_formcreator_accepted'        => [
               'name'     => __('The form is accepted', 'formcreator'),
               'subject'  => __('Your form has been accepted by the validator', 'formcreator'),
               'content'  => __('Hi,\nWe are pleased to inform you that your form has been accepted by the validator.\nYour request will be considered soon.', 'formcreator'),
               'notified' => PluginFormcreatorNotificationTargetForm_answer::AUTHOR,
            ],
            'plugin_formcreator_deleted'         => [
               'name'     => __('The form is deleted', 'formcreator'),
               'subject'  => __('Your form has been deleted by an administrator', 'formcreator'),
               'content'  => __('Hi,\nWe are sorry to inform you that your request cannot be considered and has been deleted by an administrator.', 'formcreator'),
               'notified' => PluginFormcreatorNotificationTargetForm_answer::AUTHOR,
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
         $exists = $notification->find("itemtype = 'PluginFormcreatorForm_Answer' AND event = '$event'");

         // If it doesn't exists, create it
         if (count($exists) == 0) {
            $template_id = $template->add([
               'name'     => Toolbox::addslashes_deep($data['name']),
               'comment'  => '',
               'itemtype' => 'PluginFormcreatorForm_Answer',
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
               'itemtype'                 => 'PluginFormcreatorForm_Answer',
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
            NotificationTemplate::getTable() . '.itemtype' => PluginFormcreatorForm_Answer::class
         ]
      ]);

      // Delete notification templates
      $template = new NotificationTemplate();
      $template->deleteByCriteria(['itemtype' => 'PluginFormcreatorForm_Answer']);

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
            Notification::getTable() . '.itemtype' => PluginFormcreatorForm_Answer::class
         ],
      ]);

      // Delete notifications and their templates
      $notification = new Notification();
      $notification_notificationTemplate = new Notification_NotificationTemplate();
      $rows = $notification->find("`itemtype` = 'PluginFormcreatorForm_Answer'");
      foreach ($rows as $row) {
         $notification_notificationTemplate->deleteByCriteria(['notifications_id' => $row['id']]);
         $notification->delete($row);
      }

      $notification = new Notification();
      $notification->deleteByCriteria(['itemtype' => 'PluginFormcreatorForm_Answer']);
   }

   protected function deleteTicketRelation() {
      global $CFG_GLPI;

      // Delete relations with tickets with email notifications disabled
      $use_mailing = PluginFormcreatorCommon::isNotificationEnabled();
      PluginFormcreatorCommon::setNotification(false);

      $item_ticket = new Item_Ticket();
      $item_ticket->deleteByCriteria(['itemtype' => 'PluginFormcreatorForm_Answer']);

      PluginFormcreatorCommon::setNotification($use_mailing);
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
         'PluginFormcreatorForm_Answer',
         'PluginFormcreatorForm_Profile',
         'PluginFormcreatorForm_Validator',
         'PluginFormcreatorForm',
         'PluginFormcreatorQuestion_Condition',
         'PluginFormcreatorQuestion',
         'PluginFormcreatorSection',
         'PluginFormcreatorTarget',
         'PluginFormcreatorTargetChange_Actor',
         'PluginFormcreatorTargetChange',
         'PluginFormcreatorTargetTicket_Actor',
         'PluginFormcreatorTargetTicket',
         'PluginFormcreatorItem_TargetTicket',
         'PluginFormcreatorIssue',
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
      $displayPreference->deleteByCriteria(['itemtype' => 'PluginFormCreatorIssue']);
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
            'mode'      => CronTask::MODE_EXTERNAL
         ]
      );
   }
}
