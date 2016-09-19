<?php
class PluginFormcreatorIssue extends PluginFormcreatorFormanswer {

   /**
    * Return the table used to store this object
    *
    * @return string
    **/
   public static function getTable() {
      if (static::$notable) {
         return '';
      }

      if (empty($_SESSION['glpi_table_of'][get_called_class()])) {
         $_SESSION['glpi_table_of'][get_called_class()] = PluginFormcreatorFormanswer::getTable();
      }

      return $_SESSION['glpi_table_of'][get_called_class()];
   }

   public static function install(Migration $migration) {
      global $DB;

      // Create standard search options
      $query = "INSERT IGNORE INTO `glpi_displaypreferences` (`id`, `itemtype`, `num`, `rank`, `users_id`) VALUES
               (NULL, '".__CLASS__."', 3, 3, 0);";
      $DB->query($query) or die ($DB->error());
   }

   /**
    * {@inheritDoc}
    * @see CommonGLPI::display()
    */
   public function display($options = array()) {
      global $CFG_GLPI;

      if (isset($options['id'])
            && !$this->isNewID($options['id'])) {
         if (!$this->getFromDB($options['id'])) {
            Html::displayNotFoundError();
         }
      }

      // in case of lefttab layout, we couldn't see "right error" message
      if ($this->get_item_to_display_tab) {
         if (isset($_GET["id"]) && $_GET["id"] && !$this->can($_GET["id"], READ)) {
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
      $rows = $item_ticket->find("`itemtype` = 'PluginFormcreatorFormanswer' AND `items_id` = $formanswerId", "`tickets_id` ASC");
      if (count($rows) == 0) {
         // No ticket asociated to this issue
         // Show the form answers
         $this->showForm($this->getID(), $options);
      } else {
         // There is at least one ticket for this issue
         // Show the timelines of this issue
         $ticketId = null;
         foreach ($rows as $id => $row) {
            $ticketId = $row['tickets_id'];
         }
         $ticket = new Ticket();
         if (!$ticket->getFromDB($ticketId)) {
            Html::displayNotFoundError();
         } else {
            // Header to navigate through tickets in a single formanswer
            // This happens when a form has several ticket targets

            echo '';
            echo '';


            echo "<div class='timeline_box'>";
            $rand = mt_rand();
            $ticket->showTimelineForm($rand);
            $ticket->showTimeline('123456');
            echo "</div>";
         }
      }
   }

}