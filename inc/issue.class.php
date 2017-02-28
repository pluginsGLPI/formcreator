<?php
class PluginFormcreatorIssue extends CommonDBTM {
   static $rightname = 'ticket';

   public static function getTypeName($nb = 0) {
      return _n('Issue', 'Issues', $nb, 'formcreator');
   }

   public static function install(Migration $migration) {
      global $DB;

      // Create standard search options
      $cls = __CLASS__;
      $displayprefs = new DisplayPreference;
      $found_dprefs = $displayprefs->find("`itemtype` = '$cls'");
      if (count($found_dprefs) == 0) {
         $query = "INSERT IGNORE INTO `glpi_displaypreferences`
                     (`id`, `itemtype`, `num`, `rank`, `users_id`)
                  VALUES
                     (NULL, '$cls', 1, 1, 0),
                     (NULL, '$cls', 2, 2, 0),
                     (NULL, '$cls', 4, 3, 0),
                     (NULL, '$cls', 5, 4, 0),
                     (NULL, '$cls', 6, 5, 0),
                     (NULL, '$cls', 7, 6, 0),
                     (NULL, '$cls', 8, 7, 0)
                     ";
         $DB->query($query) or die ($DB->error());
      }

      $result = $DB->query("SHOW FULL TABLES LIKE 'glpi_plugin_formcreator_issues'");
      if ($DB->numrows($result) == 1) {
         $row = $DB->fetch_assoc($result);
         if ($row['Table_type'] == 'VIEW') {
            // THe table is actually a view (version (v2.4.0) drop it
            $migration->dropTable('glpi_plugin_formcreator_issues');
            $migration->migrationOneTable('glpi_plugin_formcreator_issues');
         }
      }

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
            array(
                  'comment'   => __('Formcreator - Sync service catalog issues', 'formcreator'),
                  'mode'      => CronTask::MODE_EXTERNAL
            )
      );

      $task = new CronTask();
      static::cronSyncIssues($task);
   }

   /**
    * get Cron description parameter for this class
    *
    * @param $name string name of the task
    *
    * @return array of string
    */
   static function cronInfo($name) {
      switch ($name) {
         case 'SyncIssues':
            return array('description' => __('Update issue data from tickets and form answers', 'formcreator'));
      }
   }

   /**
    *
    * @param unknown $task
    *
    * @return number
    */
   public static function cronSyncIssues($task) {
      global $DB;

      $task->log("Disable expired trial accounts");
      $volume = 0;

      // Request which merges tickets and formanswers
      // 1 ticket not linked to a form_answer => 1 issue which is the ticket sub_itemtype
      // 1 form_answer not linked to a ticket => 1 issue which is the form_answer sub_itemtype
      // 1 ticket linked to 1 form_answer => 1 issue which is the ticket sub_itemtype
      // several tickets linked to the same form_answer => 1 issue which is the form_answer sub_itemtype
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
         $table = static::getTable();
         if (countElementsInTable($table) != $count['cpt']) {
            if ($DB->query("TRUNCATE `$table`")) {
               $DB->query("INSERT INTO `$table` SELECT * FROM ($query) as `dt`");
               $volume = 1;
            }
         }
      }
      $task->setVolume($volume);

      return 1;
   }

   public static function hook_update_ticket(CommonDBTM $item) {

   }

   /**
    * {@inheritDoc}
    * @see CommonGLPI::display()
    */
   public function display($options = array()) {
      $itemtype = $options['sub_itemtype'];
      if (!in_array($itemtype, array('Ticket', 'PluginFormcreatorForm_Answer'))) {
         html::displayRightError();
      }
      if (plugin_formcreator_replaceHelpdesk() == PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG) {
         $this->displaySimplified($options);
      } else {
         $this->displayExtended($options);
      }
   }

   public function displayExtended($options = array()) {
      $item = new $options['sub_itemtype'];

      if (isset($options['id'])
            && !$item->isNewID($options['id'])) {
         if (!$item->getFromDB($options['id'])) {
            Html::displayNotFoundError();
         }
      }

      // if ticket(s) exist(s), show it/them
      $options['_item'] = $item;
      $item = $this->getTicketsForDisplay($options);

      $item->addDivForTabs();

    }

   /**
    * {@inheritDoc}
    * @see CommonGLPI::display()
    */
   public function displaySimplified($options = array()) {
      global $CFG_GLPI;

      $item = new $options['sub_itemtype'];

      if (isset($options['id'])
          && !$item->isNewID($options['id'])) {
         if (!$item->getFromDB($options['id'])) {
            Html::displayNotFoundError();
         }
      }

      // in case of lefttab layout, we couldn't see "right error" message
      if ($item->get_item_to_display_tab) {
         if (isset($options["id"])
             && $options["id"]
             && !$item->can($options["id"], READ)) {
            // This triggers from a profile switch.
            // If we don't have right, redirect instead to central page
            if (isset($_SESSION['_redirected_from_profile_selector'])
                && $_SESSION['_redirected_from_profile_selector']) {
               unset($_SESSION['_redirected_from_profile_selector']);
               Html::redirect($CFG_GLPI['root_doc']."/front/central.php");
            }

            html::displayRightError();
         }
      }

      if (!isset($options['id'])) {
         $options['id'] = 0;
      }

      // Header if the item + link to the list of items
      $this->showNavigationHeader($options);

      // retrieve associated tickets
      $options['_item'] = $item;
      $item = $this->getTicketsForDisplay($options);

      // force recall of ticket in layout
      $old_layout = $_SESSION['glpilayout'];
      $_SESSION['glpilayout'] = "lefttab";

      if ($item instanceof Ticket) {
         //Tickets without form associated or single ticket for an answer
         echo "<div class='timeline_box'>";
         $rand = mt_rand();
         $item->showTimelineForm($rand);
         $item->showTimeline($rand);
         echo "</div>";
      } else {
         // No ticket asociated to this issue or multiple tickets
         // Show the form answers
         echo '<div class"center">';
         $item->addDivForTabs();
         echo '</div>';
      }

      // restore layout
      $_SESSION['glpilayout'] = $old_layout;
   }

   /**
    * Retrieve how many ticket associated to the current answer
    * @param  array $options must contains at least an _item key, instance for answer
    * @return the provide _item key replaced if needed
    */
   public function getTicketsForDisplay($options) {
      $item = $options['_item'];
      $formanswerId = $options['id'];
      $item_ticket = new Item_Ticket();
      $rows = $item_ticket->find("`itemtype` = 'PluginFormcreatorForm_Answer'
                                  AND `items_id` = $formanswerId", "`tickets_id` ASC");

       if (count($rows) == 1) {
         // one ticket, replace item
         $ticket = array_shift($rows);
         $item = new Ticket;
         $item->getFromDB($ticket['tickets_id']);
      } else if (count($rows) > 1) {
         // multiple tickets, force ticket tab in form anser
         Session::setActiveTab('PluginFormcreatorForm_Answer', 'Ticket$1');
      }

      return $item;
   }

      /**
    * Define search options for forms
    *
    * @return Array Array of fields to show in search engine and options for each fields
    */
   public function getSearchOptions() {
      return array(
         __('Issue', 'formcreator'),
         '1' => array(
            'table'         => self::getTable(),
            'field'         => 'name',
            'name'          => __('Name'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
         ),
         '2' => array(
            'table'         => self::getTable(),
            'field'         => 'display_id',
            'name'          => __('ID'),
            'datatype'      => 'itemlink',
            'forcegroupby'  => true,
            'massiveaction' => false,
         ),
         '3' => array(
            'table'         => self::getTable(),
            'field'         => 'sub_itemtype',
            'name'          => __('Type'),
            'searchtype'    => array('equals', 'notequals'),
            'datatype'      => 'specific',
            'massiveaction' => false,
         ),
         '4' => array(
            'table'         => self::getTable(),
            'field'         => 'status',
            'name'          => __('Status'),
            'searchtype'    => array('equals', 'notequals'),
            'datatype'      => 'specific',
            'massiveaction' => false,
         ),
         '5' => array(
            'table'         => self::getTable(),
            'field'         => 'date_creation',
            'name'          => __('Opening date'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
         ),
         '6' => array(
            'table'         => self::getTable(),
            'field'         => 'date_mod',
            'name'          => __('Last update'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
         ),
         '7' => array(
            'table'         => "glpi_entities",
            'field'         => 'completename',
            'name'          => __('Entity'),
            'datatype'      => 'dropdown',
            'massiveaction' => false,
         ),
         '8' => array(
            'table'         => 'glpi_users',
            'field'         => 'name',
            'linkfield'     => 'requester_id',
            'name'          => __('Requester'),
            'datatype'      => 'dropdown',
            'massiveaction' => false,
         ),
         '9' => array(
            'table'         => 'glpi_users',
            'field'         => 'name',
            'linkfield'     => 'validator_id',
            'name'          => __('Form approver'),
            'datatype'      => 'dropdown',
            'massiveaction' => false,
         ),
         '10' => array(
            'table'         => self::getTable(),
            'field'         => 'comment',
            'name'          => __('Comment'),
            'datatype'      => 'string',
            'massiveaction' => false,
         ),
         '11' => array(
            'table'         => 'glpi_users',
            'field'         => 'name',
            'linkfield'     => 'users_id_validate',
            'name'          => __('Ticket approver'),
            'datatype'      => 'dropdown',
            'right'         => array('validate_request', 'validate_incident'),
            'forcegroupby'  => false,
            'massiveaction' => false,
            'joinparams'    => array(
               'beforejoin' => array(
                  array(
                     'table' => 'glpi_items_tickets',
                     'joinparams' => array(
                        'jointype'           => 'itemtypeonly',
                        'specific_itemtype'  => 'PluginFormcreatorForm_Answer',
                        'condition'          => 'AND `REFTABLE`.`original_id` = `NEWTABLE`.`items_id`'
                     )
                  ),
                  array(
                     'table' => 'glpi_ticketvalidations',
                        // no join type in search engine match our need. See plugin_formcreator_addLeftJoin
                  )
               )
            )
         ),
      );
   }

   public static function getSpecificValueToSelect($field, $name='', $values='', array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'sub_itemtype':
            return Dropdown::showFromArray($name,
                                           array('Ticket'                      => __('Ticket'),
                                                 'PluginFormcreatorForm_Answer' => __('Form answer', 'formcreator')),
                                           array('display' => false,
                                                 'value'   => $values[$field]));
         case 'status' :
            $ticket_opts = Ticket::getAllStatusArray(true);
            $ticket_opts['waiting'] = __('Not validated');
            $ticket_opts['refused'] = __('Refused');
            return Dropdown::showFromArray($name, $ticket_opts, array('display' => false,
                                                                      'value'   => $values[$field]));
            break;

      }

      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }



   static function getDefaultSearchRequest() {

      $search = array('criteria' => array(0 => array('field'      => 4,
                                                     'searchtype' => 'equals',
                                                     'value'      => 'notclosed')),
                      'sort'     => 6,
                      'order'    => 'DESC');

      if (Session::haveRight(self::$rightname, Ticket::READALL)) {
         $search['criteria'][0]['value'] = 'notold';
      }
     return $search;
   }

   public static function giveItem($itemtype, $ID, $data, $num) {
      $searchopt=&Search::getOptions($itemtype);
      $table=$searchopt[$ID]["table"];
      $field=$searchopt[$ID]["field"];

      if (isset($data['raw']['id'])) {
         $id = substr($data['raw']['id'], 2);
      }

      switch ("$table.$field") {
         case "glpi_plugin_formcreator_issues.name":
            $name = $data['raw']["ITEM_$num"];
            return "<a href='".FORMCREATOR_ROOTDOC."/front/issue.form.php?id=".$id."&sub_itemtype=".$data['raw']['sub_itemtype']."'>$name</a>";
            break;

         case "glpi_plugin_formcreator_issues.id":
            return $data['raw']['id'];
            break;

         case "glpi_plugin_formcreator_issues.status":
            switch($data['raw']['sub_itemtype']) {
               case 'Ticket':
                  $status = Ticket::getStatus($data['raw']["ITEM_$num"]);
                  return "<img src='".Ticket::getStatusIconURL($data['raw']["ITEM_$num"])."'
                               alt=\"$status\" title=\"$status\">&nbsp;$status";
                  break;

               case 'PluginFormcreatorForm_Answer':
                  return PluginFormcreatorForm_Answer::getSpecificValueToDisplay('status', $data['raw']["ITEM_$num"]);
                  break;
            }
            break;
      }

      return "";
   }


   static function getClosedStatusArray() {
      return Ticket::getClosedStatusArray();
   }

   static function getSolvedStatusArray() {
      return Ticket::getSolvedStatusArray();
   }

   static function getNewStatusArray() {
      return array(Ticket::INCOMING, 'waiting', 'accepted', 'refused');
   }

   static function getProcessStatusArray() {
      return Ticket::getProcessStatusArray();
   }

   static function getReopenableStatusArray() {
      return Ticket::getReopenableStatusArray();
   }

   static function getAllStatusArray($withmetaforsearch=false) {
      $ticket_status = Ticket::getAllStatusArray($withmetaforsearch);
      $form_status = array('waiting', 'accepted', 'refused');
      $form_status = array_combine($form_status, $form_status);
      $all_status = $ticket_status + $form_status;
      return $all_status;
   }

   static function getIncomingCriteria() {
      return ['criteria' => [['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => 'process',
                              'value'      => 'notold']],
              'reset'    => 'reset'];
   }

   static function getWaitingCriteria() {
      return ['criteria' => [['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => 'process',
                              'value'      => Ticket::WAITING]],
              'reset'    => 'reset'];
   }

   static function getValidateCriteria() {
      return ['criteria' => [['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => 'process',
                              'value'      => 'notclosed',
                              'link'       => 'AND'],
                             ['field' => 9,
                              'searchtype' => 'equals',
                              'value'      => 'process',
                              'value'      => $_SESSION['glpiID'],
                              'link'       => 'AND'],
                             ['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => 'process',
                              'value'      => 'notclosed',
                              'link'       => 'OR'],
                             ['field' => 11,
                              'searchtype' => 'equals',
                              'value'      => 'process',
                              'value'      => $_SESSION['glpiID'],
                              'link'       => 'AND']],
              'reset'    => 'reset'];
   }

   static function getSolvedCriteria() {
      return ['criteria' => [['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => 'old']],
              'reset'    => 'reset'];
   }

   static function getTicketSummary() {
      global $DB;

      $status = [
         Ticket::INCOMING => 0,
         Ticket::WAITING => 0,
         'to_validate' => 0,
         Ticket::SOLVED => 0
      ];

      $searchIncoming = Search::getDatas('PluginFormcreatorIssue',
                                         self::getIncomingCriteria());
      if ($searchIncoming['data']['totalcount'] > 0) {
         $status[Ticket::INCOMING] = $searchIncoming['data']['totalcount'];
      }

      $searchWaiting = Search::getDatas('PluginFormcreatorIssue',
                                         self::getWaitingCriteria());
      if ($searchWaiting['data']['totalcount'] > 0) {
         $status[Ticket::WAITING] = $searchWaiting['data']['totalcount'];
      }

      $searchValidate = Search::getDatas('PluginFormcreatorIssue',
                                         self::getValidateCriteria());
      if ($searchValidate['data']['totalcount'] > 0) {
         $status['to_validate'] = $searchValidate['data']['totalcount'];
      }

      $searchSolved = Search::getDatas('PluginFormcreatorIssue',
                                         self::getSolvedCriteria());
      if ($searchSolved['data']['totalcount'] > 0) {
         $status[Ticket::SOLVED] = $searchSolved['data']['totalcount'];
      }

      return $status;
   }

   /**
    * Database table uninstallation for the item type
    *
    * @return boolean True on success
    */
   public static function uninstall()
   {
      global $DB;

      $DB->query('DROP VIEW IF EXISTS `glpi_plugin_formcreator_issues`');
      $displayPreference = new DisplayPreference();
      $displayPreference->deleteByCriteria(array('itemtype' => 'PluginFormCreatorIssue'));

      return true;
   }

   /**
    *
    */
   public function prepareInputForAdd($input) {
      if (!isset($input['original_id']) || !isset($input['sub_itemtype'])) {
         return false;
      }

      if ($input['sub_itemtype'] == 'PluginFormcreatorForm_Answer') {
         $input['display_id'] = 'f_' . $input['original_id'];
      } else if ($input['sub_itemtype'] == 'Ticket') {
         $input['display_id'] = 't_' . $input['original_id'];
      } else {
         return false;
      }

      return $input;
   }

   public function prepareInputForUpdate($input) {
      if (!isset($input['original_id']) || !isset($input['sub_itemtype'])) {
         return false;
      }

      if ($input['sub_itemtype'] == 'PluginFormcreatorForm_Answer') {
         $input['display_id'] = 'f_' . $input['original_id'];
      } else if ($input['sub_itemtype'] == 'Ticket') {
         $input['display_id'] = 't_' . $input['original_id'];
      } else {
         return false;
      }

      return $input;
   }
}
