<?php
class PluginFormcreatorIssue extends CommonDBTM {
   static $rightname = 'ticket';

   public static function getTypeName($nb = 0) {
      return _n('Issue', 'Issues', $nb, 'formcreator');
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

      $item->showTabsContent();

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
         $item->showTabsContent();
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
            'field'         => 'id',
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
}
