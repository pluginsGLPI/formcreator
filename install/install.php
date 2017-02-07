<?php
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

      return true;
   }

   /**
    * Upgrade the plugin
    */
   public function upgrade(Migration $migration) {
      $this->migration = $migration;
      $fromVersion = $this->getSchemaVersion();

      $this->installSchema();

      switch ($fromVersion) {
         case '0.0':
            //Any schema version below 2.5
            require_once(__DIR__ . '/update_0.0_2.5.php');
            plugin_formcreator_update_2_5($migration);

         default:
            $this->migration->executeMigration();
      }
      $this->configureExistingEntities();
      $this->createRequestType();
      $this->createDefaultDisplayPreferences();

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
      $config = Config::getConfigurationValues('formcreator', array('schema_version'));
      if (!isset($config['schema_version'])) {
         // No schema version in GLPI config, then this is older than 2.5
         return '0.0';
      }

      // Version found in GLPI config
      return $config['schema_version'];
   }

   /**
    * is the plugin already isntalled ?
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
         $entityConfig = new self();
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

      if ($DB->numrows($result) > 0) {
         list($requesttype) = $DB->fetch_array($result);
      } else {
         $query = "INSERT INTO `glpi_requesttypes` SET `name` = 'Formcreator';";
         $DB->query($query) or die ($DB->error());
         $requesttype = $DB->insert_id();
      }
   }

   protected function createDefaultDisplayPreferences() {
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

   protected function createNotifications() {
      $this->migration->displayMessage("create notifications");

      $notifications = array(
            'plugin_formcreator_form_created' => array(
                  'name'     => __('A form has been created', 'formcreator'),
                  'subject'  => __('Your request has been saved', 'formcreator'),
                  'content'  => __('Hi,\nYour request from GLPI has been successfully saved with number ##formcreator.request_id## and transmitted to the helpdesk team.\nYou can see your answers onto the following link:\n##formcreator.validation_link##', 'formcreator'),
                  'notified' => self::AUTHOR,
            ),
            'plugin_formcreator_need_validation' => array(
                  'name'     => __('A form need to be validate', 'formcreator'),
                  'subject'  => __('A form from GLPI need to be validate', 'formcreator'),
                  'content'  => __('Hi,\nA form from GLPI need to be validate and you have been choosen as the validator.\nYou can access it by clicking onto this link:\n##formcreator.validation_link##', 'formcreator'),
                  'notified' => self::APPROVER,
            ),
            'plugin_formcreator_refused'         => array(
                  'name'     => __('The form is refused', 'formcreator'),
                  'subject'  => __('Your form has been refused by the validator', 'formcreator'),
                  'content'  => __('Hi,\nWe are sorry to inform you that your form has been refused by the validator for the reason below:\n##formcreator.validation_comment##\n\nYou can still modify and resubmit it by clicking onto this link:\n##formcreator.validation_link##', 'formcreator'),
                  'notified' => self::AUTHOR,
            ),
            'plugin_formcreator_accepted'        => array(
                  'name'     => __('The form is accepted', 'formcreator'),
                  'subject'  => __('Your form has been accepted by the validator', 'formcreator'),
                  'content'  => __('Hi,\nWe are pleased to inform you that your form has been accepted by the validator.\nYour request will be considered soon.', 'formcreator'),
                  'notified' => self::AUTHOR,
            ),
            'plugin_formcreator_deleted'         => array(
                  'name'     => __('The form is deleted', 'formcreator'),
                  'subject'  => __('Your form has been deleted by an administrator', 'formcreator'),
                  'content'  => __('Hi,\nWe are sorry to inform you that your request cannot be considered and has been deleted by an administrator.', 'formcreator'),
                  'notified' => self::AUTHOR,
            ),
      );

      // Create the notification template
      $notification        = new Notification();
      $notification_target = new NotificationTarget();
      $template            = new NotificationTemplate();
      $translation         = new NotificationTemplateTranslation();
      foreach ($notifications as $event => $datas)
      {
         // Check if notification already exists
         $exists = $notification->find("itemtype = 'PluginFormcreatorForm_Answer' AND event = '$event'");

         // If it doesn't exists, create it
         if (count($exists) == 0) {
            $template_id = $template->add(array(
                  'name'     => Toolbox::addslashes_deep($datas['name']),
                  'comment'  => '',
                  'itemtype' => 'PluginFormcreatorForm_Answer',
            ));

            // Add a default translation for the template
            $translation->add(array(
                  'notificationtemplates_id' => $template_id,
                  'language'                 => '',
                  'subject'                  => Toolbox::addslashes_deep($datas['subject']),
                  'content_text'             => Toolbox::addslashes_deep($datas['content']),
                  'content_html'             => '<p>'.str_replace('\n', '<br />', Toolbox::addslashes_deep($datas['content'])).'</p>',
            ));

            // Create the notification
            $notification_id = $notification->add(array(
                  'name'                     => Toolbox::addslashes_deep($datas['name']),
                  'comment'                  => '',
                  'entities_id'              => 0,
                  'is_recursive'             => 1,
                  'is_active'                => 1,
                  'itemtype'                 => 'PluginFormcreatorForm_Answer',
                  'notificationtemplates_id' => $template_id,
                  'event'                    => $event,
                  'mode'                     => 'mail',
            ));

            // Add default notification targets
            $notification_target->add(array(
                  "items_id"         => $datas['notified'],
                  "type"             => Notification::USER_TYPE,
                  "notifications_id" => $notification_id,
            ));
         }
      }
   }

   protected function deleteNotifications() {
      global $DB;

      $this->migration->displayMessage("Delete notifications");

      // Define DB tables
      $table_targets      = getTableForItemType('NotificationTarget');
      $table_notification = getTableForItemType('Notification');
      $table_translations = getTableForItemType('NotificationTemplateTranslation');
      $table_templates    = getTableForItemType('NotificationTemplate');

      // Delete translations
      $query = "DELETE FROM `$table_translations`
      WHERE `notificationtemplates_id` IN (
      SELECT `id` FROM $table_templates WHERE `itemtype` = 'PluginFormcreatorForm_Answer')";
      $DB->query($query);

      // Delete notification templates
      $query = "DELETE FROM `$table_templates`
      WHERE `itemtype` = 'PluginFormcreatorForm_Answer'";
      $DB->query($query);

      // Delete notification targets
      $query = "DELETE FROM `$table_targets`
      WHERE `notifications_id` IN (
      SELECT `id` FROM $table_notification WHERE `itemtype` = 'PluginFormcreatorForm_Answer')";
      $DB->query($query);

      // Delete notifications
      $query = "DELETE FROM `$table_notification`
      WHERE `itemtype` = 'PluginFormcreatorForm_Answer'";
      $DB->query($query);
   }

   protected function deleteTicketRelation() {
      global $DB, $CFG_GLPI;

      $this->migration->displayMessage("Delete Ticket / Form_Answer relation");

      // Delete relations with tickets with email notifications disabled
      $use_mailing = $CFG_GLPI['use_mailing'];
      $CFG_GLPI['use_mailing'] = '0';

      $item_ticket = new Item_Ticket();
      $item_ticket->deleteByCriteria(array('itemtype' => 'PluginFormcreatorForm_Answer'));

      $CFG_GLPI['use_mailing'] = $use_mailing;
   }

   protected function deleteTables() {
      global $DB;

      // Drop tables
      $itemtypes = array(
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
      );

      foreach ($itemtypes as $itemtype) {
         $table = getTableForItemType($itemtype);
         $this->migration->displayMessage("Drop $table");
         $log = new Log();
         $log->deleteByCriteria(array('itemtype' => $itemtype));

         $displayPreference = new DisplayPreference();
         $displayPreference->deleteByCriteria(array('itemtype' => $itemtype));

         $DB->query("DROP TABLE IF EXISTS `$table`");
      }

      // Drop views
      $this->migration->displayMessage("Drop glpi_plugin_formcreator_issues");
      $DB->query('DROP VIEW IF EXISTS `glpi_plugin_formcreator_issues`');

      $displayPreference = new DisplayPreference();
      $displayPreference->deleteByCriteria(array('itemtype' => 'PluginFormCreatorIssue'));
   }

   /**
    *
    */
   public function uninstall() {
      $this->deleteTicketRelation();
      $this->deleteTables();
   }
}