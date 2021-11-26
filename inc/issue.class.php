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

      $task->log("Sync issues from forms answers and tickets");
      $task->setVolume(self::syncIssues());

      return 1;
   }

   /**
    * Sync issues table
    *
    * @return AbstractQuery
    */
   public static function getSyncIssuesRequest() : AbstractQuery {
      // Request which merges tickets and formanswers
      // 1 ticket not linked to a formanswer => 1 issue which is the ticket itemtype
      // 1 form_answer not linked to a ticket => 1 issue which is the formanswer itemtype
      // 1 ticket linked to 1 form_answer => 1 issue which is the ticket itemtype
      // several tickets linked to the same form_answer => 1 issue which is the form_answer itemtype
      $formTable = PluginFormcreatorForm::getTable();
      $formAnswerTable = PluginFormcreatorFormAnswer::getTable();
      $itemTicketTable = Item_Ticket::getTable();
      $ticketFk = Ticket::getForeignKeyField();
      // The columns status of the 2nd part of the UNNION statement
      // must match the same logic as PluginFormcreatorCommon::getTicketStatusForIssue()
      // @see PluginFormcreatorCommon::getTicketStatusForIssue()

      // assistance requests having no or deveral tickets
      $query1 = [
         'SELECT' => [
            new QueryExpression('NULL as `id`'),
            new QueryExpression("IF(`$formAnswerTable`.`name` = '', CONCAT('(', `$formAnswerTable`.`id`, ')'), `$formAnswerTable`.`name`) as `name`"),
            new QueryExpression("CONCAT('f_', `$formAnswerTable`.`id`) as `display_id`"),
            "$formAnswerTable.id as items_id",
            new QueryExpression("'" . PluginFormcreatorFormAnswer::getType() . "' as `itemtype`"),
            $formAnswerTable => [
               'status              as status',
               'request_date        as date_creation',
               'request_date        as date_mod',
               'entities_id         as entities_d',
               'is_recursive        as is_recursive',
               'requester_id        as requester_id',
               'users_id_validator  as users_id_validator',
               'groups_id_validator as groups_id_validator',
               'comment             as comment'
            ],
         ],
         'DISTINCT' => true,
         'FROM' => $formAnswerTable,
         'LEFT JOIN' => [
            $formTable => [
               'FKEY' => [
                  $formTable => 'id',
                  $formAnswerTable => PluginFormcreatorForm::getForeignKeyField(),
               ],
            ],
            $itemTicketTable => [
               'FKEY' => [
                  $itemTicketTable => 'items_id',
                  $formAnswerTable => 'id',
                  ['AND' => [
                     "`$itemTicketTable`.`itemtype`" => PluginFormcreatorFormAnswer::getType()
                  ]]
               ]
            ]
         ],
         'GROUPBY' => ["$formAnswerTable.id"],
         'HAVING' => new QueryExpression("COUNT(`$itemTicketTable`.`$ticketFk`) != 1"),
      ];

      // tickets not generated by Formcreator
      $ticketTable = Ticket::getTable();
      $ticketValidationTable = TicketValidation::getTable();
      $ticketUserTable = Ticket_User::getTable();
      $query2 = [
         'SELECT' => [
            new QueryExpression('NULL as `id`'),
            new QueryExpression("IF(`$ticketTable`.`name` = '', CONCAT('(', `$ticketTable`.`id`, ')'), `$ticketTable`.`name`) as `name`"),
            new QueryExpression("CONCAT('t_', `$ticketTable`.`id`) as `display_id`"),
            "$ticketTable.id as items_id",
            new QueryExpression("'" . Ticket::getType() . "' as `itemtype`"),
            new QueryExpression("IF(`$ticketValidationTable`.`status` IS NULL,
               `$ticketTable`.`status`,
               IF(`$ticketTable`.`global_validation` IN ('" . CommonITILValidation::NONE . "', '" . CommonITILValidation::ACCEPTED . "'),
                  `$ticketTable`.`status`,
                  IF(`$ticketTable`.`status` IN ('" . CommonITILObject::SOLVED . "', '" . CommonITILObject::CLOSED . "'),
                     `$ticketTable`.`status`,
                     IF(`$ticketTable`.`global_validation` = '" . CommonITILValidation::WAITING . "',
                        '" . PluginFormcreatorFormAnswer::STATUS_WAITING . "',
                        '" . PluginFormcreatorFormAnswer::STATUS_REFUSED . "'
                     )
                  )
               )
            ) AS `status`"),
            $ticketTable => [
               'date                                     as date_creation',
               'date_mod                                 as date_mod',
               'entities_id                              as entities_id'
            ],
            new QueryExpression('0                       as is_recursive'),
            "$ticketUserTable.users_id                   as requester_id",
            new QueryExpression("IF(`$ticketValidationTable`.`users_id_validate` IS NULL, 0, `$ticketValidationTable`.`users_id_validate`)  as users_id_validator"),
            new QueryExpression('0                       as groups_id_validator'),
            "$ticketTable.content                        as comment",
         ],
         'DISTINCT' => true,
         'FROM' => $ticketTable,
         'LEFT JOIN' => [
            $itemTicketTable => [
               'FKEY' => [
                  $itemTicketTable => $ticketFk,
                  $ticketTable => 'id',
                  ['AND' => [
                     "`$itemTicketTable`.`itemtype`" => PluginFormcreatorFormAnswer::getType(),
                  ]],
               ],
            ],
            [
               'TABLE' => new QuerySubquery([
                  'SELECT' => '*',
                  'FROM' => new QuerySubquery([
                     'SELECT' => ['users_id', $ticketFk],
                     'DISTINCT' => true,
                     'FROM'  => $ticketUserTable,
                     'WHERE' => [
                        'type' => CommonITILActor::REQUESTER,
                     ],
                     'ORDER' => ['id ASC'],
                  ], 'inner_glpi_tickets_users'),
                  'GROUPBY' => 'tickets_id'
               ], 'glpi_tickets_users'),
               'FKEY' => [
                  $ticketTable => 'id',
                  $ticketUserTable => $ticketFk,
               ],
            ],
            $ticketValidationTable => [
               'FKEY' => [
                  $ticketTable => 'id',
                  $ticketValidationTable => $ticketFk,
               ],
            ],
         ],
         'WHERE' => [
            "$ticketTable.is_deleted" => 0,
         ],
         'GROUPBY' => ["$ticketTable.id"],
         'HAVING' => new QueryExpression("COUNT(`$itemTicketTable`.`items_id`) = 0")
      ];

      // assistance requests having only one generated ticket (use query2)
      $query3 = $query2;
      // replace LEFT JOIN with INNER JOIN to exclude tickets not linked to a Formanswer object
      $query3['INNER JOIN'][$itemTicketTable] = $query3['LEFT JOIN'][$itemTicketTable];
      unset($query3['LEFT JOIN'][$itemTicketTable]);
      // Only 1 relation to a Formanswer object
      $query3['GROUPBY'] = ["$itemTicketTable.items_id"];
      $query3['HAVING'] = new QueryExpression("COUNT(`$itemTicketTable`.`items_id`) = 1");

      // Union of the 3 previous queries
      $union = new QueryUnion([
         new QuerySubquery($query1),
         new QuerySubquery($query2),
         new QuerySubquery($query3)
      ], true);

      return $union;
   }

   /**
    * Sync issues table
    *
    * @return int
    */
   public static function syncIssues() {
      global $DB;
      $volume = 0;

      $result = $DB->request([
         'COUNT' => 'cpt',
         'FROM'  => self::getSyncIssuesRequest()
      ]);
      if ($result === false) {
         return 0;
      }

      $count = ($result->next())['cpt'];
      $table = static::getTable();
      if (countElementsInTable($table) == $count) {
         return 0;
      }

      $volume = 0;
      if ($DB->query("TRUNCATE `$table`")) {
         $rawQuery = self::getSyncIssuesRequest()->getQuery();
         $DB->query("INSERT INTO `$table` SELECT * FROM $rawQuery");
         $volume = 1;
      }

      return $volume;
   }

   public static function hook_update_ticket(CommonDBTM $item) {

   }

   /**
    * @see CommonGLPI::display()
    */
   public function display($options = []) {
      Html::requireJs('tinymce');
      if (plugin_formcreator_replaceHelpdesk() == PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG) {
         $this->displaySimplified($options);
      } else {
         $this->displayExtended($options);
      }
   }

   public function displayExtended($options = []) {
      $itemtype = $this->fields['itemtype'];
      if (!class_exists($itemtype)) {
         PluginFormcreatorCommon::restoreLayout();
         Html::displayNotFoundError();
      }
      $item = new $itemtype();
      if (!$item->getFromDB($this->fields['items_id'])) {
         PluginFormcreatorCommon::restoreLayout();
         Html::displayNotFoundError();
      }

      // if ticket(s) exist(s), show it/them
      $options['_item'] = $item;
      if ($item Instanceof PluginFormcreatorFormAnswer) {
         $item = $this->getTicketsForDisplay($options);
      }
      unset($options['_item']);

      // Header if the item + link to the list of items
      switch ($item::getType()) {
         case self::getType():
            $_SESSION['glpilisturl'][self::getType()] = $this->getSearchURL();
            break;

         case PluginFormcreatorFormAnswer::getType():
            Session::initNavigateListItems(self::getType());
            $_SESSION['glpilisturl'][self::getType()] = $this->getSearchURL();
            break;

         case Ticket::getType():
            $_SESSION['glpilisturl'][self::getType()] = $this->getFormURLWithID($this->getID());
      }
      $this->showNavigationHeader($options);

      if (!$item->canViewItem()) {
         PluginFormcreatorCommon::restoreLayout();
         Html::displayNotFoundError();
      }

      $item->showTabsContent($options);
   }

   /**
    * @see CommonGLPI::display()
    */
   public function displaySimplified($options = []) {
      global $CFG_GLPI;

      $itemtype = $this->fields['itemtype'];
      if (!class_exists($itemtype)) {
         PluginFormcreatorCommon::restoreLayout();
         Html::displayNotFoundError();
      }
      $item = new $itemtype();
      if (!$item->getFromDB($this->fields['items_id'])) {
         PluginFormcreatorCommon::restoreLayout();
         Html::displayNotFoundError();
      }

      // in case of left tab layout, we couldn't see "right error" message
      if ($item->get_item_to_display_tab) {
         if (isset($this->fields['items_id'])
             && $this->fields['items_id']
             && !$item->can($this->fields['items_id'], READ)) {
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

      if (!isset($this->fields['items_id'])) {
         $options['id'] = 0;
      } else {
         $options['id'] = $item->getID();
      }

      $options['_item'] = $item;
      if ($item Instanceof PluginFormcreatorFormAnswer) {
         $item = $this->getTicketsForDisplay($options);
      }
      unset($options['_item']);

      // Header if the item + link to the list of items
      switch ($item::getType()) {
         case self::getType():
            $_SESSION['glpilisturl'][self::getType()] = $this->getSearchURL();
            break;

         case PluginFormcreatorFormAnswer::getType():
            Session::initNavigateListItems(self::getType());
            $_SESSION['glpilisturl'][self::getType()] = $this->getSearchURL();
            break;

         case Ticket::getType():
            $_SESSION['glpilisturl'][self::getType()] = $this->getFormURLWithID($this->getID());
      }
      $this->showNavigationHeader($options);

      // retrieve associated tickets
      $options['_item'] = $item;
      if ($item Instanceof PluginFormcreatorFormAnswer) {
         $item = $this->getTicketsForDisplay($options);
      }
      unset($options['_item']);

      // force recall of ticket in layout
      PluginFormcreatorCommon::saveLayout();
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
      PluginFormcreatorCommon::restoreLayout();
   }

   /**
    * Retrieve how many ticket associated to the current answer
    * @param  array $options must contains at least an _item key, instance for answer
    * @return mixed the provide _item key replaced if needed
    */
   public function getTicketsForDisplay($options) {
      global $DB;

      $item = $options['_item'];
      $rows = $DB->request([
         'FROM'  => Item_Ticket::getTable(),
         'WHERE' => [
            'itemtype' => 'PluginFormcreatorFormAnswer',
            'items_id' => $item->getID() // $item is a PluginFormcreatorFormAnswer
         ],
         'ORDER' => 'tickets_id ASC'
      ]);
      if (count($rows) == 1) {
         // one ticket, replace item
         $ticket = $rows->next();
         $item = new Ticket;
         $item->getFromDB($ticket['tickets_id']);
      } else if (count($rows) > 1) {
         if (isset($options['tickets_id'])) {
            $ticket = Ticket::getById((int) $options['tickets_id']);
            if ($ticket) {
               $item = $ticket;
            }
         } else {
            // multiple tickets, no specified ticket then force ticket tab in form anser
            if (isset($options['tickets_id'])) {
               $ticket = Ticket::getById((int) $options['tickets_id']);
               if ($ticket) {
                  $item = $ticket;
               }
            } else {
               // multiple tickets, no specified ticket then force ticket tab in form anser
               Session::setActiveTab(PluginFormcreatorFormAnswer::class, 'Ticket$1');
            }
         }
      }

      return $item;
   }

   public function rawSearchOptions() {
      $tab = [];
      $hide_technician = false;
      if (!Session::isCron()) {
         $hide_technician = \Entity::getUsedConfig(
            'anonymize_support_agents',
            $_SESSION['glpiactive_entity']
         );
      }

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
         'field'              => 'itemtype',
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

      $newtab = [
         'id'                 => '8',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'linkfield'          => 'requester_id',
         'name'               => __('Requester'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];
      if (!Session::isCron() // no filter for cron
          && Session::getCurrentInterface() == 'helpdesk') {
         $newtab['right']       = 'id';
      }
      $tab[] = $newtab;

      $newtab = [
         'id'                 => '9',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'linkfield'          => 'users_id_validator',
         'name'               => __('Form approver', 'formcreator'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];
      if (!Session::isCron() // no filter for cron
          && Session::getCurrentInterface() == 'helpdesk') {
         $newtab['right']       = 'id';
      }
      $tab[] = $newtab;

      $tab[] = [
         'id'                 => '10',
         'table'              => $this::getTable(),
         'field'              => 'comment',
         'name'               => __('Comment'),
         'datatype'           => 'text',
         'htmltext'           => true,
         'nodisplay'          => true,
         'massiveaction'      => false
      ];

      $newtab = [
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
                     'condition'          => 'AND `REFTABLE`.`items_id` = `NEWTABLE`.`items_id`'
                  ]
               ],
               '1'                  => [
                  'table'              => 'glpi_ticketvalidations'
               ]
            ]
         ]
      ];
      if (!Session::isCron() // no filter for cron
          && Session::getCurrentInterface() == 'helpdesk') {
         $newtab['right']       = 'id';
      }
      $tab[] = $newtab;

      $newtab = [
         'id'                 => '14',
         'table'              => User::getTable(),
         'field'              => 'name',
         'name'               => __('Technician'),
         'datatype'           => 'dropdown',
         'forcegroupby'       => true,
         'massiveaction'      => false,
         'nodisplay'          => $hide_technician,
         'nosearch'           => $hide_technician,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => Ticket_User::getTable(),
               'joinparams'         => [
                  'condition'          => "AND NEWTABLE.`type` = '2'", // Assign
                  'jointype'           => 'child',
                  'beforejoin'         => [
                     'table'              => Ticket::getTable(),
                     'joinparams'         => [
                        'jointype'           => 'itemtype_item_revert',
                        'specific_itemtype'  => Ticket::class,
                     ],
                  ]
               ]
            ]
         ]
      ];
      if (!Session::isCron() // no filter for cron
          && Session::getCurrentInterface() == 'helpdesk') {
         $newtab['right']       = 'id';
      }
      $tab[] = $newtab;

      $tab[] = [
         'id'                 => '15',
         'table'              => Group::getTable(),
         'field'              => 'name',
         'name'               => __('Technician group'),
         'datatype'           => 'dropdown',
         'forcegroupby'       => true,
         'massiveaction'      => false,
         'nodisplay'          => $hide_technician,
         'nosearch'           => $hide_technician,
         'joinparams'         => [
            'temoin' => true,
            'beforejoin'         => [
               'table'              => Group_Ticket::getTable(),
               'joinparams'         => [
                  'condition'          => "AND NEWTABLE.`type` = '2'", // Assign
                  'jointype'           => 'child',
                  'beforejoin'         => [
                     'table'              => Ticket::getTable(),
                     'joinparams'         => [
                        'jointype'           => 'itemtype_item_revert',
                        'specific_itemtype'  => Ticket::class,
                     ],
                  ]
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
         case 'itemtype':
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
            $subItemtype = $data['raw']['itemtype'];
            switch ($subItemtype) {
               case Ticket::class:
                  $ticket = new Ticket();
                  $ticket->getFromDB($id);
                  $content = $ticket->fields['content'];
                  break;

               case PluginFormcreatorFormAnswer::class:
                  $formAnswer = new PluginFormcreatorFormAnswer();
                  $formAnswer->getFromDB($id);
                  $content = $formAnswer->parseTags($formAnswer->getFullForm());
                  break;

               default:
                  $content = '';
            }
            $link = self::getFormURLWithID($id) . "&itemtype=".$data['raw']['itemtype'];
            $link =  self::getFormURLWithID($data['id']);
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
      return [
         'criteria' => [
            [
               'field'      => 4,
               'searchtype' => 'equals',
               'value'      => 'process'
            ],
         ],
         'reset'    => 'reset'
      ];
   }

   static function getWaitingCriteria() {
      return [
         'criteria' => [
            [
               'field'      => 4,
               'searchtype' => 'equals',
               'value'      => Ticket::WAITING
            ],
         ],
         'reset'    => 'reset'
      ];
   }

   static function getValidateCriteria() {
      return [
         'criteria' => [
            [
               'link'       => 'AND',
               'field'      => 4,
               'searchtype' => 'equals',
               'value'      => PluginFormcreatorFormAnswer::STATUS_WAITING,
            ],
         ],
         'reset'    => 'reset'
      ];
   }

   static function getSolvedCriteria() {
      return [
         'criteria' => [
            [
               'field'      => 4,
               'searchtype' => 'equals',
               'value'      => 'old', // see Ticket::getAllStatusArray()
            ],
         ],
         'reset'    => 'reset'
      ];
   }

   static function getTicketSummary(?string $counter = null) {
      $status = [
         Ticket::INCOMING => NOT_AVAILABLE,
         Ticket::WAITING  => NOT_AVAILABLE,
         'to_validate'    => NOT_AVAILABLE,
         Ticket::SOLVED   => NOT_AVAILABLE
      ];

      if ($counter === null || $counter == 'incoming') {
         $searchIncoming = Search::getDatas(PluginFormcreatorIssue::class,
                                          self::getProcessingCriteria());
         if (isset($searchIncoming['data']['totalcount'])) {
            $status[Ticket::INCOMING] = $searchIncoming['data']['totalcount'];
         }
      }

      if ($counter === null || $counter == 'waiting') {
         $searchWaiting = Search::getDatas(PluginFormcreatorIssue::class,
                                          self::getWaitingCriteria());
         if (isset($searchWaiting['data']['totalcount'])) {
            $status[Ticket::WAITING] = $searchWaiting['data']['totalcount'];
         }
      }

      if ($counter === null || $counter == 'to_validate') {
         $searchValidate = Search::getDatas(PluginFormcreatorIssue::class,
                                          self::getValidateCriteria());
         if (isset($searchValidate['data']['totalcount'])) {
            $status['to_validate'] = $searchValidate['data']['totalcount'];
         }
      }

      if ($counter === null || $counter == 'solved') {
         $searchSolved = Search::getDatas(PluginFormcreatorIssue::class,
                                          self::getSolvedCriteria());
         if (isset($searchSolved['data']['totalcount'])) {
            $status[Ticket::SOLVED] = $searchSolved['data']['totalcount'];
         }
      }

      return $status;
   }

   /**
    *
    */
   public function prepareInputForAdd($input) {
      if (!isset($input['items_id']) || !isset($input['itemtype'])) {
         return false;
      }

      if ($input['itemtype'] == PluginFormcreatorFormAnswer::class) {
         $input['display_id'] = 'f_' . $input['items_id'];
      } else if ($input['itemtype'] == 'Ticket') {
         $input['display_id'] = 't_' . $input['items_id'];
      } else {
         return false;
      }

      return $input;
   }

   public function prepareInputForUpdate($input) {
      if (!isset($input['items_id']) || !isset($input['itemtype'])) {
         return false;
      }

      if ($input['itemtype'] == PluginFormcreatorFormAnswer::class) {
         $input['display_id'] = 'f_' . $input['items_id'];
      } else if ($input['itemtype'] == 'Ticket') {
         $input['display_id'] = 't_' . $input['items_id'];
      } else {
         return false;
      }

      return $input;
   }
}
