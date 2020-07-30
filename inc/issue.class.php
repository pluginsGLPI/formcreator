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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorIssue extends CommonDBTM {
   static $rightname = 'ticket';

   public static function getTypeName($nb = 0) {
      return _n('Issue', 'Issues', $nb, 'formcreator');
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
            return ['description' => __('Update issue data from tickets and form answers', 'formcreator')];
      }
   }

   /**
    *
    * @param CronTask $task
    *
    * @return number
    */
   public static function cronSyncIssues(CronTask $task) {
      global $DB;

      $task->log("Sync issues from forms answers and tickets");
      $volume = 0;

      // Request which merges tickets and formanswers
      // 1 ticket not linked to a formanswer => 1 issue which is the ticket sub_itemtype
      // 1 form_answer not linked to a ticket => 1 issue which is the formanswer sub_itemtype
      // 1 ticket linked to 1 form_answer => 1 issue which is the ticket sub_itemtype
      // several tickets linked to the same form_answer => 1 issue which is the form_answer sub_itemtype
      $query = "SELECT DISTINCT
                  NULL                            AS `id`,
                  `f`.`name`                      AS `name`,
                  CONCAT('f_',`fanswer`.`id`)     AS `display_id`,
                  `fanswer`.`id`                  AS `original_id`,
                  'PluginFormcreatorFormAnswer'   AS `sub_itemtype`,
                  `fanswer`.`status`              AS `status`,
                  `fanswer`.`request_date`        AS `date_creation`,
                  `fanswer`.`request_date`        AS `date_mod`,
                  `fanswer`.`entities_id`         AS `entities_id`,
                  `fanswer`.`is_recursive`        AS `is_recursive`,
                  `fanswer`.`requester_id`        AS `requester_id`,
                  `fanswer`.`users_id_validator`  AS `users_id_validator`,
                  `fanswer`.`groups_id_validator` AS `groups_id_validator`,
                  `fanswer`.`comment`             AS `comment`
               FROM `glpi_plugin_formcreator_formanswers` AS `fanswer`
               LEFT JOIN `glpi_plugin_formcreator_forms` AS `f`
                  ON`f`.`id` = `fanswer`.`plugin_formcreator_forms_id`
               LEFT JOIN `glpi_items_tickets` AS `itic`
                  ON `itic`.`items_id` = `fanswer`.`id`
                  AND `itic`.`itemtype` = 'PluginFormcreatorFormAnswer'
               GROUP BY `original_id`
               HAVING COUNT(`itic`.`tickets_id`) != 1

               UNION

               SELECT DISTINCT
                  NULL                          AS `id`,
                  `tic`.`name`                  AS `name`,
                  CONCAT('t_',`tic`.`id`)       AS `display_id`,
                  `tic`.`id`                    AS `original_id`,
                  'Ticket'                      AS `sub_itemtype`,
                  if(`tv`.`status` IS NULL,`tic`.`status`, if(`tv`.`status` = 2, 101, if(`tv`.`status` = 3, `tic`.`status`, 102))) AS `status`,
                  `tic`.`date`                  AS `date_creation`,
                  `tic`.`date_mod`              AS `date_mod`,
                  `tic`.`entities_id`           AS `entities_id`,
                  0                             AS `is_recursive`,
                  `tu`.`users_id`               AS `requester_id`,
                  `tv`.`users_id_validate`      AS `users_id_validator`,
                  0                             AS `groups_id_validator`,
                  `tic`.`content`               AS `comment`
               FROM `glpi_tickets` AS `tic`
               LEFT JOIN `glpi_items_tickets` AS `itic`
                  ON `itic`.`tickets_id` = `tic`.`id`
                  AND `itic`.`itemtype` = 'PluginFormcreatorFormAnswer'
               LEFT JOIN (
                  SELECT DISTINCT `users_id`, `tickets_id`
                  FROM `glpi_tickets_users` AS `tu`
                  WHERE `tu`.`type` = '"  . CommonITILActor::REQUESTER . "'
                  ORDER BY `id` ASC
               ) AS `tu` ON (`tic`.`id` = `tu`.`tickets_id`)
               LEFT JOIN `glpi_ticketvalidations` as `tv`
                  ON (`tic`.`id` = `tv`.`tickets_id`)
               WHERE `tic`.`is_deleted` = 0
               GROUP BY `original_id`
               HAVING COUNT(`itic`.`items_id`) <= 1";

      $countQuery = "SELECT COUNT(*) AS `cpt` FROM ($query) AS `issues`";
      $result = $DB->query($countQuery);
      if ($result !== false) {
         if (version_compare(GLPI_VERSION, '9.5') < 0) {
            $fa = 'fetch_assoc';
         } else {
            $fa = 'fetchAssoc';
         }
         $count = $DB->$fa($result);
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
    * @see CommonGLPI::display()
    */
   public function display($options = []) {
      $itemtype = $options['sub_itemtype'];
      if (!in_array($itemtype, [Ticket::class, PluginFormcreatorFormAnswer::class])) {
         html::displayRightError();
      }
      Html::requireJs('tinymce');
      if (plugin_formcreator_replaceHelpdesk() == PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG) {
         $this->displaySimplified($options);
      } else {
         $this->displayExtended($options);
      }
   }

   public function displayExtended($options = []) {
      $item = new $options['sub_itemtype'];

      if (isset($options['id'])
            && !$item->isNewID($options['id'])) {
         if (!$item->getFromDB($options['id'])) {
            Html::displayNotFoundError();
         }
      }

      // if ticket(s) exist(s), show it/them
      $options['_item'] = $item;
      if ($item Instanceof PluginFormcreatorFormAnswer) {
         $item = $this->getTicketsForDisplay($options);
      }
      unset($options['_item']);

      $item->showTabsContent($options);
   }

   /**
    * @see CommonGLPI::display()
    */
   public function displaySimplified($options = []) {
      global $CFG_GLPI;

      $item = new $options['sub_itemtype'];

      if (isset($options['id'])
          && !$item->isNewID($options['id'])) {
         if (!$item->getFromDB($options['id'])) {
            Html::displayNotFoundError();
         }
      }

      // in case of left tab layout, we couldn't see "right error" message
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
      if ($item Instanceof PluginFormcreatorFormAnswer) {
         $item = $this->getTicketsForDisplay($options);
      }
      unset($options['_item']);

      // force recall of ticket in layout
      $old_layout = $_SESSION['glpilayout'];
      $_SESSION['glpilayout'] = "lefttab";

      if ($item instanceof Ticket) {
         //Tickets without form associated or single ticket for an answer
         $satisfaction = new TicketSatisfaction();
         if ($satisfaction->getFromDB($options['id'])) {
            // show survey form, if any
            // @see Ticket::displayTabContentForItem()
            $duration = Entity::getUsedConfig('inquest_duration', $item->fields['entities_id']);
            $date2    = strtotime($satisfaction->fields['date_begin']);
            if (($duration == 0)
                || (strtotime("now") - $date2) <= $duration*DAY_TIMESTAMP) {
               $satisfaction->showForm($item);
            } else {
               echo "<p class='center b'>".__('Satisfaction survey expired')."</p>";
            }
         }

         echo "<div class='timeline_box'>";
         $rand = mt_rand();
         $item->showTimelineForm($rand);
         $item->showTimeline($rand);
         echo "</div>";
      } else {
         // No ticket associated to this issue or multiple tickets
         // Show the form answers
         echo '<div class"center">';
         $item->showTabsContent($options);
         echo '</div>';
      }

      // restore layout
      $_SESSION['glpilayout'] = $old_layout;
   }

   /**
    * Retrieve how many ticket associated to the current answer
    * @param  array $options must contains at least an _item key, instance for answer
    * @return mixed the provide _item key replaced if needed
    */
   public function getTicketsForDisplay($options) {
      global $DB;

      $item = $options['_item'];
      $formanswerId = $options['id'];
      $rows = $DB->request([
         'FROM'  => Item_Ticket::getTable(),
         'WHERE' => [
            'itemtype' => 'PluginFormcreatorFormAnswer',
            'items_id' => $formanswerId
         ],
         'ORDER' => 'tickets_id ASC'
      ]);
      if (count($rows) == 1) {
         // one ticket, replace item
         $ticket = $rows->next();
         $item = new Ticket;
         $item->getFromDB($ticket['tickets_id']);
      } else if (count($rows) > 1) {
         // multiple tickets, force ticket tab in form anser
         Session::setActiveTab(PluginFormcreatorFormAnswer::class, 'Ticket$1');
      }

      return $item;
   }

   public function rawSearchOptions() {
      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => __('Issue', 'formcreator')
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this::getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'forcegroupby'       => true,
         'additionalfields'   => [
            '0'                  => 'display_id'
         ]
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this::getTable(),
         'field'              => 'display_id',
         'name'               => __('ID'),
         'datatype'           => 'string',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => $this::getTable(),
         'field'              => 'sub_itemtype',
         'name'               => __('Type'),
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'datatype'           => 'specific',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this::getTable(),
         'field'              => 'status',
         'name'               => __('Status'),
         'searchtype'         => [
            '0'                  => 'equals'
         ],
         'datatype'           => 'specific',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this::getTable(),
         'field'              => 'date_creation',
         'name'               => __('Opening date'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this::getTable(),
         'field'              => 'date_mod',
         'name'               => __('Last update'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __('Entity'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'linkfield'          => 'requester_id',
         'name'               => __('Requester'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '9',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'linkfield'          => 'users_id_validator',
         'name'               => __('Form approver', 'formcreator'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '10',
         'table'              => $this::getTable(),
         'field'              => 'comment',
         'name'               => __('Comment'),
         'datatype'           => 'text',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '11',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'linkfield'          => 'users_id_validate',
         'name'               => __('Ticket approver', 'formcreator'),
         'datatype'           => 'dropdown',
         'right'              => [
            '0'                  => 'validate_request',
            '1'                  => 'validate_incident'
         ],
         'forcegroupby'       => false,
         'massiveaction'      => false,
         'joinparams'         => [
            'beforejoin'         => [
               '0'                  => [
                  'table'              => 'glpi_items_tickets',
                  'joinparams'         => [
                     'jointype'           => 'itemtypeonly',
                     'specific_itemtype'  => PluginFormcreatorFormAnswer::class,
                     'condition'          => 'AND `REFTABLE`.`original_id` = `NEWTABLE`.`items_id`'
                  ]
               ],
               '1'                  => [
                  'table'              => 'glpi_ticketvalidations'
               ]
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '14',
         'table'              => User::getTable(),
         'field'              => 'name',
         'linkfield'          => 'users_id',
         'name'               => __('Technician'),
         'datatype'           => 'dropdown',
         'forcegroupby'       => false,
         'massiveaction'      => false,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => Ticket_User::getTable(),
               'linkfield'          => 'original_id',
               'joinparams'         => [
                  'jointype'           => 'empty',
               ]
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '15',
         'table'              => Group::getTable(),
         'field'              => 'name',
         'linkfield'          => 'groups_id',
         'name'               => __('Technician group'),
         'datatype'           => 'dropdown',
         'forcegroupby'       => false,
         'massiveaction'      => false,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => Group_Ticket::getTable(),
               'linkfield'          => 'original_id',
               'joinparams'         => [
                  'jointype'           => 'empty',
               ]
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '16',
         'table'              => 'glpi_groups',
         'field'              => 'completename',
         'name'               => __('Form approver group', 'formcreator'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'linkfield'          => 'groups_id_validator',
      ];

      return $tab;
   }

   public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'sub_itemtype':
            return Dropdown::showFromArray($name,
                                           [Ticket::class                      => __('Ticket'),
                                            PluginFormcreatorFormAnswer::class => __('Form answer', 'formcreator')],
                                           ['display' => false,
                                            'value'   => $values[$field]]);
         case 'status' :
            $ticket_opts = Ticket::getAllStatusArray(true);
            $ticket_opts[PluginFormcreatorFormAnswer::STATUS_WAITING] = __('Not validated', 'formcreator');
            $ticket_opts[PluginFormcreatorFormAnswer::STATUS_REFUSED] = __('Refused', 'formcreator');
            return Dropdown::showFromArray($name, $ticket_opts, ['display' => false,
                                                                 'value'   => $values[$field]]);
            break;

      }

      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   static function getDefaultSearchRequest() {
      $search = ['criteria' => [0 => ['field'      => 4,
                                      'searchtype' => 'equals',
                                      'value'      => 'notclosed']],
                 'sort'     => 6,
                 'order'    => 'DESC'];

      if (Session::haveRight(self::$rightname, Ticket::READALL)) {
         $search['criteria'][0]['value'] = 'notold';
      }
      return $search;
   }

   public static function giveItem($itemtype, $option_id, $data, $num) {
      $searchopt = &Search::getOptions($itemtype);
      $table = $searchopt[$option_id]["table"];
      $field = $searchopt[$option_id]["field"];

      $rawColumn = 'ITEM_PluginFormcreatorIssue_1_display_id';
      if (isset($data['raw'][$rawColumn])) {
         $matches = null;
         preg_match('/[tf]+_([0-9]*)/', $data['raw'][$rawColumn], $matches);
         $id = $matches[1];
      }

      switch ("$table.$field") {
         case "glpi_plugin_formcreator_issues.name":
            $name = $data[$num][0]['name'];
            $subItemtype = $data['raw']['sub_itemtype'];
            switch ($subItemtype) {
               case Ticket::class:
                  $ticket = new Ticket();
                  $ticket->getFromDB($id);
                  $content = $ticket->fields['content'];
                  break;

               // TODO : need some code refactor to properly provide qtip
               // case PluginFormcreatorFormAnswer::class:
               //       $formAnswer = new PluginFormcreatorFormAnswer();
               //       $formAnswer->getFromDB($id);
               //       $content = $formAnswer->getFullForm();
               //       // TODO : need to replace tags before creating the qtip
               //       break;
               default:
                  $content = '';
            }
            $link = self::getFormURLWithID($id) . "&sub_itemtype=".$data['raw']['sub_itemtype'];
            $key = 'id';
            $tooltip = Html::showToolTip(nl2br(Html::Clean($content)), [
               'applyto' => $itemtype.$data['raw'][$key],
               'display' => false,
            ]);
            return '<a id="' . $itemtype.$data['raw'][$key] . '" href="' . $link . '">'
               . sprintf(__('%1$s %2$s'), $name, $tooltip)
               . '</a>';

         case "glpi_plugin_formcreator_issues.id":
            return $data['raw']['id'];

         case "glpi_plugin_formcreator_issues.status":
            if ($data['raw']["ITEM_$num"] > 100) {
               // The status matches tle values of a FormAnswer
               $elements = PluginFormcreatorFormAnswer::getStatuses();
               return PluginFormcreatorFormAnswer::getSpecificValueToDisplay('status', $data['raw']["ITEM_$num"])
                  ." ".__($elements[$data['raw']["ITEM_$num"]], 'formcreator');
            }
            $status = Ticket::getStatus($data['raw']["ITEM_$num"]);
            return Ticket::getStatusIcon($data['raw']["ITEM_$num"])." ".$status;
            break;
      }

      return '';
   }

   static function getClosedStatusArray() {
      return Ticket::getClosedStatusArray();
   }

   static function getSolvedStatusArray() {
      return Ticket::getSolvedStatusArray();
   }

   static function getNewStatusArray() {
      return [Ticket::INCOMING, PluginFormcreatorFormAnswer::STATUS_WAITING, PluginFormcreatorFormAnswer::STATUS_ACCEPTED, PluginFormcreatorFormAnswer::STATUS_REFUSED];
   }

   static function getProcessStatusArray() {
      return Ticket::getProcessStatusArray();
   }

   static function getReopenableStatusArray() {
      return Ticket::getReopenableStatusArray();
   }

   static function getAllStatusArray($withmetaforsearch = false) {
      $ticket_status = Ticket::getAllStatusArray($withmetaforsearch);
      $form_status = [PluginFormcreatorFormAnswer::STATUS_WAITING, PluginFormcreatorFormAnswer::STATUS_ACCEPTED, PluginFormcreatorFormAnswer::STATUS_REFUSED];
      $form_status = array_combine($form_status, $form_status);
      $all_status = $ticket_status + $form_status;
      return $all_status;
   }

   static function getProcessingCriteria() {
      $currentUser = Session::getLoginUserID();
      return ['criteria' => [['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => 'process'],
                             ['field'      => 8,
                             'searchtype'  => 'equals',
                             'value'       => $currentUser]],
              'reset'    => 'reset'];
   }

   static function getWaitingCriteria() {
      $currentUser = Session::getLoginUserID();
      return ['criteria' => [['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => Ticket::WAITING],
                              ['field'      => 8,
                              'searchtype'  => 'equals',
                              'value'       => $currentUser]],
              'reset'    => 'reset'];
   }

   static function getValidateCriteria() {
      return ['criteria' => [['link'       => 'AND',
                              'field' => 4,
                              'searchtype' => 'equals',
                              'value'      => PluginFormcreatorFormAnswer::STATUS_WAITING,
                              ],
                             ['link' => 'AND'] + self::getMeAsValidatorCriteria()
                            ],
              'reset'    => 'reset'];
   }

   static function getSolvedCriteria() {
      $currentUser = Session::getLoginUserID();
      return ['criteria' => [['link'       => 'AND',
                              'field'      => 8,
                              'searchtype'  => 'equals',
                              'value'       => $currentUser,
                              ],
                              ['link'       => 'AND',
                              'criteria' => [[
                               'link'       => 'AND',
                               'field' => 4,
                               'searchtype' => 'equals',
                               'value'      => 'old', // see Ticket::getAllStatusArray()
                              ],
                              ['field' => 4,
                               'searchtype' => 'equals',
                               'value'      => PluginFormcreatorFormAnswer::STATUS_REFUSED,
                               'link'       => 'OR']
                             ]],
                             ['link'       => 'OR',
                             'criteria' => [[
                              'link'       => 'AND',
                              'field'      => 9,
                              'searchtype' => 'equals',
                              'value'      => $currentUser,
                             ],
                             ['link'       => 'OR',
                              'field'      => 16,
                              'searchtype' => 'equals',
                              'value'      => 'mygroups',
                             ],
                            ]],
                            ['link'       => 'AND',
                               'field' => 4,
                               'searchtype' => 'equals',
                               'value'      => PluginFormcreatorFormAnswer::STATUS_REFUSED,
                            ]],
              'reset'    => 'reset'];
   }

   public static function getMeAsValidatorCriteria() {
      $currentUser = Session::getLoginUserID();
      return ['criteria'   => [[
         'link'       => 'AND',
         'field'      => 9,
         'searchtype' => 'equals',
         'value'      => $currentUser,
        ],
        ['link'       => 'OR',
         'field'      => 16,
         'searchtype' => 'equals',
         'value'      => 'mygroups',
        ],
        ['link'       => 'OR',
         'field'      => 11,
         'searchtype' => 'equals',
         'value'      => $currentUser,
        ]]
      ];
   }

   static function getTicketSummary() {
      $status = [
         Ticket::INCOMING => 0,
         Ticket::WAITING => 0,
         'to_validate' => 0,
         Ticket::SOLVED => 0
      ];

      $searchIncoming = Search::getDatas(PluginFormcreatorIssue::class,
                                         self::getProcessingCriteria());
      $status[Ticket::INCOMING] = NOT_AVAILABLE;
      if (isset($searchIncoming['data']['totalcount'])) {
         $status[Ticket::INCOMING] = $searchIncoming['data']['totalcount'];
      }

      $searchWaiting = Search::getDatas(PluginFormcreatorIssue::class,
                                         self::getWaitingCriteria());
      $status[Ticket::WAITING] = NOT_AVAILABLE;
      if (isset($searchWaiting['data']['totalcount'])) {
         $status[Ticket::WAITING] = $searchWaiting['data']['totalcount'];
      }

      $searchValidate = Search::getDatas(PluginFormcreatorIssue::class,
                                         self::getValidateCriteria());
      $status['to_validate'] = NOT_AVAILABLE;
      if (isset($searchValidate['data']['totalcount'])) {
         $status['to_validate'] = $searchValidate['data']['totalcount'];
      }

      $searchSolved = Search::getDatas(PluginFormcreatorIssue::class,
                                         self::getSolvedCriteria());
      $status[Ticket::SOLVED] = NOT_AVAILABLE;
      if (isset($searchSolved['data']['totalcount'])) {
         $status[Ticket::SOLVED] = $searchSolved['data']['totalcount'];
      }

      return $status;
   }

   /**
    *
    */
   public function prepareInputForAdd($input) {
      if (!isset($input['original_id']) || !isset($input['sub_itemtype'])) {
         return false;
      }

      if ($input['sub_itemtype'] == PluginFormcreatorFormAnswer::class) {
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

      if ($input['sub_itemtype'] == PluginFormcreatorFormAnswer::class) {
         $input['display_id'] = 'f_' . $input['original_id'];
      } else if ($input['sub_itemtype'] == 'Ticket') {
         $input['display_id'] = 't_' . $input['original_id'];
      } else {
         return false;
      }

      return $input;
   }
}
