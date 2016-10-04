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

      // create view who merge tickets and formanswers
      $query = "CREATE OR REPLACE VIEW `glpi_plugin_formcreator_issues` AS

                   SELECT CONCAT(1,`fanswer`.`id`)      AS `id`,
                          `fanswer`.`id`                AS `original_id`,
                          'PluginFormcreatorFormanswer' AS `sub_itemtype`,
                          `f`.`name`                    AS `name`,
                          `fanswer`.`status`            AS `status`,
                          `fanswer`.`request_date`      AS `date_creation`,
                          `fanswer`.`request_date`      AS `date_mod`,
                          `fanswer`.`entities_id`       AS `entities_id`,
                          `fanswer`.`is_recursive`      AS `is_recursive`,
                          `fanswer`.`requester_id`      AS `requester_id`,
                          `fanswer`.`validator_id`      AS `validator_id`,
                          `fanswer`.`comment`           AS `comment`
                   FROM `glpi_plugin_formcreator_formanswers` AS `fanswer`
                   JOIN `glpi_plugin_formcreator_forms` AS `f`
                      ON`f`.`id` = `fanswer`.`plugin_formcreator_forms_id`
                   LEFT JOIN `glpi_items_tickets` AS `itic`
                      ON `itic`.`items_id` = `fanswer`.`id`
                      AND `itic`.`itemtype` = 'PluginFormcreatorFormanswer'

                   UNION

                   SELECT CONCAT(2,`tic`.`id`)          AS `id`,
                          `tic`.`id`                    AS `original_id`,
                          'Ticket'                      AS `sub_itemtype`,
                          `tic`.`name`                  AS `name`,
                          `tic`.`status`                AS `status`,
                          `tic`.`date`                  AS `date_creation`,
                          `tic`.`date_mod`              AS `date_mod`,
                          `tic`.`entities_id`           AS `entities_id`,
                          0                             AS `is_recursive`,
                          `tic`.`users_id_recipient`    AS `requester_id`,
                          ''                            AS `validator_id`,
                          ''                            AS `comment`
                   FROM `glpi_tickets` AS `tic`
                   LEFT JOIN `glpi_items_tickets` AS `itic`
                      ON `itic`.`tickets_id` = `tic`.`id`
                      AND `itic`.`itemtype` = 'PluginFormcreatorFormanswer'
                   WHERE ISNULL(`itic`.`items_id`)";
      $DB->query($query) or die ($DB->error());
   }

   /**
    * {@inheritDoc}
    * @see CommonGLPI::display()
    */
   public function display($options = array()) {
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

      // Timeline
      $formanswerId = $options['id'];
      $item_ticket = new Item_Ticket();
      $rows = $item_ticket->find("`itemtype` = 'PluginFormcreatorFormanswer'
                                  AND `items_id` = $formanswerId", "`tickets_id` ASC");

      // force recall of ticket
      $old_layout = $_SESSION['glpilayout'];
      $_SESSION['glpilayout'] = "lefttab";

      if ($item instanceof Ticket) {
         //Tickets without form associated
         echo "<div class='timeline_box'>";
         $rand = mt_rand();
         $item->showTimelineForm($rand);
         $item->showTimeline($rand);
         echo "</div>";

      } else if (count($rows) == 0) {
         // No ticket asociated to this issue
         // Show the form answers
         echo '<div class"center">';
         $item->showForm($item->getID(), $options);
         echo '</div>';

      } else {
         // There is at least one ticket for this issue

         // Show the timelines of this issue
         $ticketIds = array();
         foreach ($rows as $id => $row) {
            $ticketIds[] = $row['tickets_id'];
         }
         $ticketSequence = array_flip($ticketIds);
         if (!isset($_GET['tid'])) {
            $ticketId = $ticketIds[0];
         } else {
            $ticketId = Toolbox::cleanInteger($_GET['tid']);
         }
         if (!in_array($ticketId, $ticketIds)) {
            Html::displayNotFoundError();
         }
         $ticketIndex = $ticketSequence[$ticketId];
         $previousTicketId = $ticketIndex > 0 ? $ticketIds[$ticketIndex - 1] : 0;
         $nextTicketId = $ticketIndex < count($ticketIds) - 1 ? $ticketIds[$ticketIndex + 1] : 0;
         $firstTicketId = $ticketId != $ticketIds[0] ? $ticketIds[0] : 0;
         $lastTicketId = $ticketId != $ticketIds[count($ticketIds) - 1] ? $ticketIds[count($ticketIds) - 1] : 0;

         $ticket = new Ticket();
         if (!$ticket->getFromDB($ticketId)) {
            Html::displayNotFoundError();
         } else {
            // Header to navigate through tickets in a single formanswer
            // This happens when a form has several ticket targets

            $nav_url = $CFG_GLPI['root_doc'].
                       '/plugins/formcreator/front/issue.form.php'.
                       '?id='.$formanswerId.
                       '&sub_itemtype='.$options['sub_itemtype'];
            echo '<div class="plugin_formcreator_threadBrowser">';
            if ($firstTicketId == 0) {
               echo '<span class="plugin_formcreator_first">'
                  .'<img src="' . $CFG_GLPI['root_doc'] . '/pics/first_off.png" alt="'
                  . __('First'). '" title="'
                  . __('First'). '" class="pointer"></span>';
            } else {
               echo '<span class="plugin_formcreator_first"><a href="' .$nav_url. '&stid=' . $firstTicketId . '">'
                  .'<img src="' . $CFG_GLPI['root_doc'] . '/pics/first.png" alt="'
                  . __('First'). '" title="'
                  . __('First'). '" class="pointer"></a></span>';
            }

            if ($previousTicketId == 0) {
               echo '<span class="plugin_formcreator_left">'
                     .'<img src="' . $CFG_GLPI['root_doc'] . '/pics/left_off.png" alt="'
                     . __('First'). '" title="'
                     . __('First'). '" class="pointer"></span>';
            } else {
               echo '<span class="plugin_formcreator_left"><a href="' . $nav_url. '&tid=' . $previousTicketId . '">'
                     .'<img src="' . $CFG_GLPI['root_doc'] . '/pics/left.png" alt="'
                     . __('Previous'). '" title="'
                     . __('Previous'). '" class="pointer"></a></span>';
            }

            if ($lastTicketId == 0) {
               echo '<span class="plugin_formcreator_last">'
                     .'<img src="' . $CFG_GLPI['root_doc'] . '/pics/last_off.png" alt="'
                     . __('First'). '" title="'
                     . __('First'). '" class="pointer"></span>';
            } else {
               echo '<span class="plugin_formcreator_last"><a href="' . $nav_url . '&tid=' . $lastTicketId . '">'
                     .'<img src="' . $CFG_GLPI['root_doc'] . '/pics/last.png" alt="'
                     . __('Last'). '" title="'
                     . __('Last'). '" class="pointer"></a></span>';
            }

            if ($nextTicketId == 0) {
               echo '<span class="plugin_formcreator_right">'
                     .'<img src="' . $CFG_GLPI['root_doc'] . '/pics/right_off.png" alt="'
                           . __('First'). '" title="'
                                 . __('First'). '" class="pointer"></span>';
               } else {
               echo '<span class="plugin_formcreator_right"><a href="' .$nav_url . '&tid=' . $nextTicketId . '">'
                     .'<img src="' . $CFG_GLPI['root_doc'] . '/pics/right.png" alt="'
                     . __('Next'). '" title="'
                     . __('Next'). '" class="pointer"></a></span>';
            }
            echo '<div class="navigationheader big b">'.
                 Html::makeTitle(__('Threads', 'formcreator'), $ticketIndex, count($ticketIds)).
                 '</div>';

            echo '</div>';

            echo "<div class='timeline_box'>";
            $rand = mt_rand();
            $ticket->showTimelineForm($rand);
            $ticket->showTimeline('123456');
            echo "</div>";
         }
      }
      $_SESSION['glpilayout'] = $old_layout;
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
            'table'         => $this->getTable(),
            'field'         => 'name',
            'name'          => __('Name'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
         ),
         '2' => array(
            'table'         => $this->getTable(),
            'field'         => 'id',
            'name'          => __('ID'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
         ),
         '3' => array(
            'table'         => $this->getTable(),
            'field'         => 'sub_itemtype',
            'name'          => __('Type'),
            'searchtype'    => array('equals', 'notequals'),
            'datatype'      => 'specific',
            'massiveaction' => false,
         ),
         '4' => array(
            'table'         => $this->getTable(),
            'field'         => 'status',
            'name'          => __('Status'),
            'searchtype'    => array('equals', 'notequals'),
            'datatype'      => 'specific',
            'massiveaction' => false,
         ),
         '5' => array(
            'table'         => $this->getTable(),
            'field'         => 'date_creation',
            'name'          => __('Opening date'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
         ),
         '6' => array(
            'table'         => $this->getTable(),
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
            'name'          => __('Approver'),
            'datatype'      => 'dropdown',
            'massiveaction' => false,
         ),
         '10' => array(
            'table'         => $this->getTable(),
            'field'         => 'comment',
            'name'          => __('Comment'),
            'datatype'      => 'string',
            'massiveaction' => false,
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
                                                 'PluginFormcreatorFormanswer' => __('Form answer', 'formcreator')),
                                           array('display' => false,
                                                 'value'   => $values[$field]));
         case 'status' :
            $ticket_opts = Ticket::getAllStatusArray(true);
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
         $id = substr($data['raw']['id'], 1);
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

               case 'PluginFormcreatorFormanswer':
                  return PluginFormcreatorFormanswer::getSpecificValueToDisplay('status', $data['raw']["ITEM_$num"]);
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
      return Ticket::getAllStatusArray($withmetaforsearch);
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

}
