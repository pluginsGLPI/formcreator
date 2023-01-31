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
   die("Sorry. You can'qt access this file directly");
}

use Glpi\RichText\RichText;
class PluginFormcreatorIssue extends CommonDBTM {
   static $rightname = 'ticket';

   protected static $showTitleInNavigationHeader = true;

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

      // assistance requests having no or several tickets
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
               'comment             as comment',
               'requester_id        as users_id_recipient'
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
            new QueryExpression("IF(`$ticketTable`.`global_validation` IN ('" . CommonITILValidation::NONE . "', '" . CommonITILValidation::ACCEPTED . "'),
               `$ticketTable`.`status`,
               IF(`$ticketTable`.`status` IN ('" . CommonITILObject::SOLVED . "', '" . CommonITILObject::CLOSED . "'),
                  `$ticketTable`.`status`,
                  IF(`$ticketTable`.`global_validation` = '" . CommonITILValidation::WAITING . "',
                     '" . PluginFormcreatorFormAnswer::STATUS_WAITING . "',
                     '" . PluginFormcreatorFormAnswer::STATUS_REFUSED . "'
                  )
               )
            ) AS `status`"),
            $ticketTable => [
               'date                                     as date_creation',
               'date_mod                                 as date_mod',
               'entities_id                              as entities_id'
            ],
            new QueryExpression('0                       as is_recursive'),
            new QueryExpression("COALESCE(`$ticketUserTable`.`users_id`, 0) as `requester_id`"),
            "$ticketTable.content                        as comment",
            'users_id_recipient                          as users_id_recipient'
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
               'TABLE' => new QuerySubQuery([
                  'SELECT' => ['users_id', $ticketFk],
                  'DISTINCT' => true,
                  'FROM'  => $ticketUserTable,
                  'WHERE' => [
                     'type' => CommonITILActor::REQUESTER,
                  ],
                  'GROUPBY' => 'tickets_id',
                  'ORDER' => ['id ASC']
               ], $ticketUserTable),
               'FKEY' => [
                  $ticketTable => 'id',
                  $ticketUserTable => $ticketFk,
               ],
            ],
         ],
         'WHERE' => [
            "$ticketTable.is_deleted" => 0,
         ],
         'GROUPBY' => ["$ticketTable.id"],
         'HAVING' => new QueryExpression("COUNT(`$itemTicketTable`.`items_id`) = 0")
      ];

      // assistance requests having only one generated ticket (reuse query2)
      $query3 = [
         'SELECT'     => $query2['SELECT'],
         'FROM'       => $query2['FROM'],
         'INNER JOIN' => [$itemTicketTable => $query2['LEFT JOIN'][$itemTicketTable]],
         'LEFT JOIN'  => [
            $query2['LEFT JOIN'][0], // This is the TABLE => [...] subquery
            // $ticketValidationTable => $query2['LEFT JOIN'][$ticketValidationTable],
         ],
         'WHERE'      => $query2['WHERE'],
         'GROUPBY'    => ["$itemTicketTable.items_id"],
         // Only 1 relation to a Formanswer object
         'HAVING'     => new QueryExpression("COUNT(`$itemTicketTable`.`items_id`) = 1"),
      ];

      // Union of the 3 previous queries
      $union = new QueryUnion([
         new QuerySubQuery($query1),
         new QuerySubQuery($query2),
         new QuerySubQuery($query3)
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

      $row = $result->current();
      $count = $row['cpt'];
      $table = static::getTable();
      if (countElementsInTable($table) == $count) {
         return 0;
      }

      $volume = 0;
      if ($DB->truncate($table) !== false) {
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
      if (!isset($this->fields['itemtype'])) {
         Html::displayNotFoundError();
      }
      $itemtype = $this->fields['itemtype'];
      if (!class_exists($itemtype)) {
         Html::displayNotFoundError();
      }
      /** @var CommonDBTM $item */
      $item = new $itemtype();
      if (!$item->getFromDB($this->fields['items_id'])) {
         Html::displayNotFoundError();
      }
      if (plugin_formcreator_replaceHelpdesk() == PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG) {
         $this->displaySimplified($item, $options);
      } else {
         $this->displayExtended($item, $options);
      }
   }

   /**
    * Display an issue in extended service catalog mode
    *
    * @see CommonGLPI::display()
    *
    * @param CommonDBTM $item actual item to show
    * @param array $options
    * @return void
    */
   public function displayExtended(CommonDBTM $item, $options = []): void {
      // if ticket(s) exist(s), show it/them
      $options['_item'] = $item;
      if ($item Instanceof PluginFormcreatorFormAnswer) {
         $item = $this->getTicketsForDisplay($options);
      }
      unset($options['_item']);

      // Header of the item + link to the list of items
      $this->setListUrl($item);
      $this->showNavigationHeader($options);

      if (!$item->canViewItem()) {
         Html::displayNotFoundError();
      }

      $item->showTabsContent($options);
   }

   /**
    * Display an issue in simplified service catalog mode
    *
    * @see CommonGLPI::display()
    *
    * @param CommonDBTM $item actual item to show
    * @param array $options
    * @return void
    */
   public function displaySimplified(CommonDBTM $item, $options = []): void {
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
               Html::redirect(Central::getFormURL());
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

      // Header of the item + link to the list of items
      $this->setListUrl($item);
      $this->showNavigationHeader($options);

      // retrieve associated tickets
      $options['_item'] = $item;
      if ($item Instanceof PluginFormcreatorFormAnswer) {
         $item = $this->getTicketsForDisplay($options);
      }
      unset($options['_item']);

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
               $satisfaction->showSatisactionForm($item);
            } else {
               echo "<p class='center b'>".__('Satisfaction survey expired')."</p>";
            }
         }

         echo "<div class='tab-content p-2 flex-grow-1 card '>";
         echo '<style>';
         echo '.itil-right-side { display: none !important }';
         echo '.itil-right-side { width: 0 !important }';
         echo '.itil-left-side { width: 100% !important }';
         echo '#itil-object-container .form-buttons span { display: none !important }';
         echo '#itil-object-container .form-buttons { flex: inherit; width: auto}';
         echo "#itil-object-container .timeline-buttons { flex: 1 1 auto }";
         // The following line becomes useless with GLPI 10.0.5 as the save button of side panel does no longer show for extended service catalog
         // To drop when GLPI 10.0.5 is the minimum version
         echo "#itil-object-container .form-buttons button[type='submit'][name='update'] { display: none }";
         echo '</style>';
         $item->showForm($item->getID());
         echo "</div>";
      } else {
         // No ticket associated to this issue or multiple tickets
         // Show the form answers
         echo '<div class"center">';
         $item->showTabsContent($options);
         echo '</div>';
      }
   }

   public function setListUrl(CommonDBTM $item) {
      global $DB;

      switch ($item::getType()) {
         case self::getType():
            $_SESSION['glpilisturl'][self::getType()] = $this->getSearchURL();
            break;

         case PluginFormcreatorFormAnswer::getType():
            Session::initNavigateListItems(self::getType());
            $_SESSION['glpilisturl'][self::getType()] = $this->getSearchURL();
            break;

         case Ticket::getType():
            $iterator = $DB->request([
               'COUNT' => 'count',
               'FROM' => Item_Ticket::getTable(),
               'WHERE' => [
                  'items_id' => new QuerySubQuery([
                     'SELECT' => 'items_id',
                     'FROM'   => Item_Ticket::getTable(),
                     'WHERE'  => [
                        'itemtype' => PluginFormcreatorFormAnswer::getType(),
                        'tickets_id' => $item->getID(),
                     ],
                  ]),
               ]
            ]);
            $count = 0;
            if ($iterator->count() == 1) {
               $count = $iterator->current()['count'];
            }
            if ($count > 1) {
               $_SESSION['glpilisturl'][self::getType()] = $this->getFormURLWithID($this->getID());
            } else {
               $_SESSION['glpilisturl'][self::getType()] = $this->getSearchURL();
            }
      }
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
            'itemtype' => PluginFormcreatorFormAnswer::getType(),
            'items_id' => $item->getID() // $item is a PluginFormcreatorFormAnswer
         ],
         'ORDER' => 'tickets_id ASC'
      ]);
      if (count($rows) == 1) {
         // one ticket, replace item
         $ticket = $rows->current();
         $item = new Ticket;
         $item->getFromDB($ticket['tickets_id']);
      } else if (count($rows) > 1) {
         if (isset($options['tickets_id'])) {
            $ticket = Ticket::getById((int) $options['tickets_id']);
            if ($ticket) {
               $item = $ticket;
            }
         } else {
            if (isset($options['tickets_id'])) {
               // multiple tickets, ticket specified, then substitute the ticket to the form answer
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
      $hide_technician_group = false;
      if (!Session::isCron()) {
         $user = new User();
         if (empty($user->getAnonymizedName(Session::getActiveEntity()))) {
            $hide_technician = true;
         }
         $group = new Group();
         if (empty($group->getAnonymizedName(Session::getActiveEntity()))) {
            $hide_technician_group = true;
         }
      }

      $tab[] = [
         'id'                 => 'common',
         'name'               => $this->getTypeName(Session::getPluralNumber()),
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => self::getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'additionalfields'   => [
            '0'                  => 'display_id'
         ]
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => self::getTable(),
         'field'              => 'display_id',
         'name'               => __('ID'),
         'datatype'           => 'string',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => self::getTable(),
         'field'              => 'itemtype',
         'name'               => _n('Type', 'Types', 1),
         'searchtype'         => [
            '0'                  => 'equals',
            '1'                  => 'notequals'
         ],
         'datatype'           => 'specific',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => self::getTable(),
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
         'table'              => self::getTable(),
         'field'              => 'date_creation',
         'name'               => __('Opening date'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => self::getTable(),
         'field'              => 'date_mod',
         'name'               => __('Last update'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => _n('Entity', 'Entities', 1),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      $newtab = [
         'id'                 => '8',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'linkfield'          => 'requester_id',
         'name'               => _n('Requester', 'Requesters', 1),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];
      if (!Session::isCron() // no filter for cron
          && Session::getCurrentInterface() == 'helpdesk') {
         $newtab['right']       = 'id';
      }
      $tab[] = $newtab;

      if (Plugin::isPluginActive(PLUGIN_FORMCREATOR_ADVANCED_VALIDATION)) {
         $newtab = PluginAdvformIssue::rawSearchOptionFormApprover();
      } else {
         $newtab = [
            'id'                 => '9',
            'table'              => User::getTable(),
            'field'              => 'name',
            'linkfield'          => 'users_id_validator',
            'name'               => __('Form approver', 'formcreator'),
            'datatype'           => 'dropdown',
            'massiveaction'      => false,
            'joinparams'         => [
               'beforejoin'          => [
                  'table'                => PluginFormcreatorFormAnswer::getTable(),
                  'joinparams'           => [
                     'jointype'          => 'itemtype_item_revert',
                     'specific_itemtype'  => PluginFormcreatorFormAnswer::class,
                  ]
               ],
            ],
         ];
      }
      if (!Session::isCron() // no filter for cron
          && Session::getCurrentInterface() == 'helpdesk') {
         $newtab['right']       = 'id';
      }
      $tab[] = $newtab;

      $tab[] = [
         'id'                 => '10',
         'table'              => self::getTable(),
         'field'              => 'comment',
         'name'               => __('Comment'),
         'datatype'           => 'text',
         'htmltext'           => true,
         'nodisplay'          => true,
         'massiveaction'      => false
      ];

      $newtab = [
         'id'                 => '11',
         'table'              => User::getTable(),
         'field'              => 'name',
         'linkfield'          => 'users_id_validate',
         'name'               => __('Ticket approver', 'formcreator'),
         'datatype'           => 'itemlink',
         'right'              => [
            '0'                  => 'validate_request',
            '1'                  => 'validate_incident'
         ],
         'forcegroupby'       => true,
         'massiveaction'      => false,
         'joinparams'         => [
            'beforejoin'         => [
               [
                  'table'              => TicketValidation::getTable(),
                  'joinparams'         => [
                     'jointype'           => 'child',
                     'beforejoin'         => [
                        'table'              => Ticket::getTable(),
                        'joinparams'         => [
                           'jointype'        => 'itemtype_item_revert',
                           'specific_itemtype'  => Ticket::class,
                        ]
                     ]
                  ]
               ],
               [
                  'table'              => TicketValidation::getTable(),
                  'joinparams'         => [
                     'jointype'           => 'child',
                     'beforejoin'         => [
                        'table'              => Ticket::getTable(),
                        'joinparams'      => [
                           'jointype'        => 'empty',
                           'condition'       => [
                              new \QueryExpression(
                                 '1=1'
                              ),
                           ],
                           'beforejoin'      => [
                              'table'           => Item_Ticket::getTable(),
                              'joinparams'      => [
                                 'jointype'        => 'itemtype_item',
                                 'specific_itemtype' => PluginFormcreatorFormAnswer::class,
                                 'beforejoin'      => [
                                    'table'           => PluginFormcreatorFormAnswer::getTable(),
                                    'joinparams'      => [
                                       'jointype'          => 'itemtype_item_revert',
                                       'specific_itemtype' => PluginFormcreatorFormAnswer::class,
                                    ],
                                 ],
                              ],
                           ],
                        ],
                     ]
                  ]
               ],
            ],
         ]
      ];
      if (version_compare(GLPI_VERSION, '10.1') >= 0) {
         $newtab['linkfield'] = 'items_id_target';
         $newtab['condition'] = [
            'REFTABLE.itemtype_target' => User::class,
         ];
      }
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
         'massiveaction'      => false,
         'nodisplay'          => $hide_technician,
         'nosearch'           => $hide_technician,
         'right'              => 'interface',
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
      $tab[] = $newtab;

      if (!Session::isCron() // no filter for cron
          && Session::getCurrentInterface() != 'helpdesk') {
         $tab[] = [
            'id'                 => '15',
            'table'              => Group::getTable(),
            'field'              => 'name',
            'name'               => __('Technician group'),
            'datatype'           => 'dropdown',
            'forcegroupby'       => true,
            'massiveaction'      => false,
            'nodisplay'          => $hide_technician_group,
            'nosearch'           => $hide_technician_group,
            'condition'          => ['is_assign' => 1],
            'joinparams'         => [
               'beforejoin'         => [
                  'table'              => Group_Ticket::getTable(),
                  'joinparams'         => [
                     'condition'          => [
                        'NEWTABLE.type' => CommonITILActor::ASSIGN,
                     ],
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
      }

      if (Plugin::isPluginActive(PLUGIN_FORMCREATOR_ADVANCED_VALIDATION)) {
         $newtab = PluginAdvformIssue::rawSearchOptionFormApproverGroup();
      } else {
         $newtab = [
            'id'                 => '16',
            'table'              => Group::getTable(),
            'field'              => 'completename',
            'linkfield'          => 'groups_id_validator',
            'name'               => __('Form approver group', 'formcreator'),
            'datatype'           => 'itemlink',
            'massiveaction'      => false,
            'joinparams'         => [
               'beforejoin'          => [
                  'table'                => PluginFormcreatorFormAnswer::getTable(),
                  'joinparams'           => [
                     'jointype'          => 'itemtype_item_revert',
                     'specific_itemtype'  => PluginFormcreatorFormAnswer::class,
                  ]
               ],
            ],
         ];
      }
      $tab[] = $newtab;

      if (version_compare(GLPI_VERSION, '10.1') >= 0) {
         $newtab = [
            'id'                 => '17',
            'table'              => Group::getTable(),
            'field'              => 'name',
            'linkfield'          => 'items_id_target',
            'name'               => __('Ticket approver group', 'formcreator'),
            'datatype'           => 'itemlink',
            'condition'          => [
               'REFTABLE.itemtype_target' => User::class,
            ],
            'right'              => [
               '0'                  => 'validate_request',
               '1'                  => 'validate_incident'
            ],
            'forcegroupby'       => true,
            'massiveaction'      => false,
            'joinparams'         => [
               'beforejoin'         => [
                  'table'              => TicketValidation::getTable(),
                  'joinparams'         => [
                     'jointype'           => 'child',
                     'beforejoin'         => [
                        'table'              => Ticket::getTable(),
                        'joinparams'         => [
                           'jointype'        => 'itemtype_item_revert',
                           'specific_itemtype'  => Ticket::class,
                        ]
                     ]
                  ]
               ],
            ]
         ];
         $tab[] = $newtab;
      }

      if (Plugin::isPluginActive(PLUGIN_FORMCREATOR_ADVANCED_VALIDATION)) {
         foreach (PluginAdvformIssue::rawSearchOptions() as $so) {
            $tab[] = $so;
         }
      }

      $tab[] = [
         'id'                 => '42',
         'table'              => User::getTable(),
         'field'              => 'name',
         'name'               => __('Ticket requester', 'formcreator'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown',
         'forcegroupby'       => true,
         'joinparams'         => [
            'jointype'        => 'empty',
            'beforejoin'      => [
               'table'           => Ticket_User::getTable(),
               'joinparams'      => [
                  'jointype'        => 'child',
                  'condition'       => [
                     'NEWTABLE.type' => CommonITILActor::REQUESTER,
                  ],
                  'beforejoin'      => [
                     'table'           => Ticket::getTable(),
                     'joinparams'      => [
                        'jointype'        => 'empty',
                        'condition'       => [
                           new \QueryExpression(
                              '1=1'
                           ),
                        ],
                        'beforejoin'      => [
                           'table'           => Item_Ticket::getTable(),
                           'joinparams'      => [
                              'jointype'        => 'itemtype_item',
                              'specific_itemtype' => PluginFormcreatorFormAnswer::class,
                              'beforejoin'      => [
                                 'table'           => PluginFormcreatorFormAnswer::getTable(),
                                 'joinparams'      => [
                                    'jointype'          => 'itemtype_item_revert',
                                    'specific_itemtype' => PluginFormcreatorFormAnswer::class,
                                 ],
                              ],
                           ],
                        ],
                     ],
                  ],
               ],
            ],
         ],
      ];

      $tab[] = [
         'id'                 => '43',
         'table'              => User::getTable(),
         'field'              => 'name',
         'name'               => __('Ticket observer', 'formcreator'),
         'massiveaction'      => false,
         'nosearch'           => true,
         'datatype'           => 'dropdown',
         'forcegroupby'       => true,
         'joinparams'         => [
            'jointype'        => 'empty',
            'beforejoin'      => [
               'table'           => Ticket_User::getTable(),
               'joinparams'      => [
                  'jointype'        => 'child',
                  'condition'       => [
                     'NEWTABLE.type' => CommonITILActor::OBSERVER,
                  ],
                  'beforejoin'      => [
                     'table'           => Ticket::getTable(),
                     'joinparams'      => [
                        'jointype'        => 'empty',
                        'condition'       => [
                           new \QueryExpression(
                              '1=1'
                           ),
                        ],
                        'beforejoin'      => [
                           'table'           => Item_Ticket::getTable(),
                           'joinparams'      => [
                              'jointype'        => 'itemtype_item',
                              'specific_itemtype' => PluginFormcreatorFormAnswer::class,
                              'beforejoin'      => [
                                 'table'           => PluginFormcreatorFormAnswer::getTable(),
                                 'joinparams'      => [
                                    'jointype'          => 'itemtype_item_revert',
                                    'specific_itemtype' => PluginFormcreatorFormAnswer::class,
                                 ],
                              ],
                           ],
                        ],
                     ],
                  ],
               ],
            ],
         ],
      ];

      $tab[] = [
         'id'                 => '44',
         'table'              => User::getTable(),
         'field'              => 'name',
         'name'               => __('Ticket technician', 'formcreator'),
         'massiveaction'      => false,
         'nosearch'           => true,
         'datatype'           => 'dropdown',
         'forcegroupby'       => true,
         'joinparams'         => [
            'jointype'        => 'empty',
            'beforejoin'      => [
               'table'           => Ticket_User::getTable(),
               'joinparams'      => [
                  'jointype'        => 'child',
                  'condition'       => [
                     'NEWTABLE.type' => CommonITILActor::ASSIGN,
                  ],
                  'beforejoin'      => [
                     'table'           => Ticket::getTable(),
                     'joinparams'      => [
                        'jointype'        => 'empty',
                        'condition'       => [
                           new \QueryExpression(
                              '1=1'
                           ),
                        ],
                        'beforejoin'      => [
                           'table'           => Item_Ticket::getTable(),
                           'joinparams'      => [
                              'jointype'        => 'itemtype_item',
                              'specific_itemtype' => PluginFormcreatorFormAnswer::class,
                              'beforejoin'      => [
                                 'table'           => PluginFormcreatorFormAnswer::getTable(),
                                 'joinparams'      => [
                                    'jointype'          => 'itemtype_item_revert',
                                    'specific_itemtype' => PluginFormcreatorFormAnswer::class,
                                 ],
                              ],
                           ],
                        ],
                     ],
                  ],
               ],
            ],
         ],
      ];

      return $tab;
   }

   public function getForbiddenStandardMassiveAction() {
      return [
         'purge',
         'clone',
         'update',
         'add_transfer_list',
         'amend_comment',
      ];
   }

   public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'itemtype':
            return Dropdown::showFromArray($name,
                                           [Ticket::class                      => Ticket::getTypeName(1),
                                            PluginFormcreatorFormAnswer::class => PluginFormcreatorFormAnswer::getTypeName(1)],
                                           ['display' => false,
                                            'value'   => $values[$field]]);
         case 'status' :
            $ticket_opts = Ticket::getAllStatusArray(true);
            $ticket_opts = $ticket_opts + PluginFormcreatorFormAnswer::getStatuses();
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
            $content = '';
            switch ($subItemtype) {
               case Ticket::class:
                  $ticket = new Ticket();
                  if (!$ticket->getFromDB($id)) {
                     trigger_error(sprintf("Ticket ID %s not found", $id), E_USER_WARNING);
                     break;
                  }
                  $content = $ticket->fields['content'];
                  break;

               case PluginFormcreatorFormAnswer::class:
                  $formAnswer = PluginFormcreatorCommon::getFormAnswer();
                  if (!$formAnswer->getFromDB($id)) {
                     trigger_error(sprintf("Formanswer ID %s not found", $id), E_USER_WARNING);
                     break;
                  }
                  $content = $formAnswer->parseTags($formAnswer->getFullForm());
                  break;
            }
            $link = self::getFormURLWithID($data['id']);

            // Show "final" item id in the URL
            if (Toolbox::isCommonDBTM($subItemtype)) {
               $link .= "&" . $subItemtype::getForeignKeyField() . "=$id";
            }

            $key = 'id';
            $tooltip = Html::showToolTip(RichText::getEnhancedHtml($content), [
               'applyto'        => $itemtype.$data['raw'][$key],
               'display'        => false,
               'images_gallery' => false
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
      return [...Ticket::getClosedStatusArray(), PluginFormcreatorFormAnswer::STATUS_ACCEPTED];
   }

   static function getSolvedStatusArray() {
      return [...Ticket::getSolvedStatusArray(), PluginFormcreatorFormAnswer::STATUS_REFUSED];
   }

   static function getNewStatusArray() {
      return [Ticket::INCOMING];
   }

   static function getPendingStatusArray() {
      return [Ticket::WAITING, PluginFormcreatorFormAnswer::STATUS_WAITING];
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

   static function getAllCriteria() {
      return ['criteria' => [['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => 'all'],
                           ],
              'reset'    => 'reset'];
   }

   static function getNewCriteria() {
      return ['criteria' => [['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => Ticket::INCOMING],
                           ],
              'reset'    => 'reset'];
   }

   static function getAssignedCriteria() {
      return ['criteria' => [['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => 'process'],
                           ],
              'reset'    => 'reset'];
   }

   static function getWaitingCriteria() {
      return ['criteria' => [['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => Ticket::WAITING],
                           ],
              'reset'    => 'reset'];
   }

   static function getValidateCriteria() {
      return ['criteria' => [['link'       => 'AND',
                              'field' => 4,
                              'searchtype' => 'equals',
                              'value'      => PluginFormcreatorFormAnswer::STATUS_WAITING,
                              ],
                            ],
              'reset'    => 'reset'];
   }

   static function getSolvedCriteria() {
      return ['criteria' => [['link'       => 'AND',
                              'field' => 4,
                              'searchtype' => 'equals',
                              'value'      => Ticket::SOLVED, // see Ticket::getAllStatusArray()
                              ],
                            ],
              'reset'    => 'reset'];
   }

   static function getClosedCriteria() {
      return ['criteria' => [['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => Ticket::CLOSED],
                           ],
              'reset'    => 'reset'];
   }

   static function getOldCriteria() {
      return ['criteria' => [
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
      ],
      'reset'    => 'reset'];
   }

   /**
    *
    */
   public function prepareInputForAdd($input) {
      if (!isset($input['items_id']) || !isset($input['itemtype'])) {
         return false;
      }

      $input['users_id_recipient'] = Session::getLoginUserID();

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

   public static function nbIssues(array $params): array {
      $default_params = [
         'label'                 => "",
         'icon'                  => Ticket::getIcon(),
         'apply_filters'         => [],
      ];
      $params = array_merge($default_params, $params);

      switch ($params['status']) {
         case 'all':
            $searchCriteria = PluginFormcreatorIssue::getAllCriteria();
            $params['icon']  = "";
            break;

         case 'incoming':
            $searchCriteria = PluginFormcreatorIssue::getNewCriteria();
            $params['icon']  = Ticket::getIcon();
            break;

         case 'waiting':
            $searchCriteria = PluginFormcreatorIssue::getWaitingCriteria();
            $params['icon']  = "fas fa-pause-circle";
            break;

         case 'assigned':
            $searchCriteria = PluginFormcreatorIssue::getAssignedCriteria();
            $params['icon']  = "fas fa-users";
            break;

         case 'validate':
            $params['icon']  = "far fa-eye";
            $searchCriteria = PluginFormcreatorIssue::getValidateCriteria();
            break;

         case 'solved':
            $params['icon']  = "far fa-check-square";
            $searchCriteria = PluginFormcreatorIssue::getSolvedCriteria();
            break;

         case 'closed':
            $params['icon']  = "fas fa-archive";
            $searchCriteria = PluginFormcreatorIssue::getClosedCriteria();
            break;

         case 'old':
            $params['icon']  = "fas fa-archive";
            $searchCriteria = PluginFormcreatorIssue::getOldCriteria();
            break;
      }
      $searchWaiting = Search::getDatas(
         PluginFormcreatorIssue::class,
         $searchCriteria
      );
      $count = 0;
      if (isset($searchWaiting['data']['totalcount'])) {
         $count = $searchWaiting['data']['totalcount'];
      }

      $url = self::getSearchURL();
      $url .= '?' . Toolbox::append_params($searchCriteria);
      return [
         'number'     => $count,
         'url'        => $url,
         'label'      => $params['label'],
         'icon'       => $params['icon'],
         's_criteria' => $searchCriteria,
         'itemtype'   => 'Ticket',
      ];
   }

   public static function getIssuesSummary(array $params = []) {
      $default_params = [
         'label'         => "",
         'icon'          => "",
         'apply_filters' => [],
      ];
      $params = array_merge($default_params, $params);

      $all      = self::nbIssues($params + ['status' => 'all']);
      $new      = self::nbIssues($params + ['status' => 'incoming']);
      $waiting  = self::nbIssues($params + ['status' => 'waiting']);
      $incoming = self::nbIssues($params + ['status' => 'assigned']);
      $validate = self::nbIssues($params + ['status' => 'validate']);
      $solved   = self::nbIssues($params + ['status' => 'solved']);
      $closed   = self::nbIssues($params + ['status' => 'closed']);

      return [
         'data' => [
            [
               'number' => $all['number'],
               'label'  => __("All", 'formcreator'),
               'url'    => $all['url'],
               // 'color'  => '#3bc519',
            ],
            [
               'number' => $new['number'],
               'label'  => __("New"),
               'url'    => $new['url'],
               'color'  => '#3bc519',
            ],
            [
               'number' => $incoming['number'],
               'label'  => __("Assigned"),
               'url'    => $incoming['url'],
               'color'  => '#f1cd29',
            ],
            [
               'number' => $waiting['number'],
               'label'  => __("Waiting"),
               'url'    => $waiting['url'],
               'color'  => '#f1a129',
            ],
            [
               'number' => $validate['number'],
               'label'  => __("To validate"),
               'url'    => $validate['url'],
               'color'  => '#266ae9',
            ],
            [
               'number' => $solved['number'],
               'label'  => __("Solved"),
               'url'    => $solved['url'],
               'color'  => '#edc949',
            ],
            [
               'number' => $closed['number'],
               'label'  => __("Closed"),
               'url'    => $closed['url'],
               'color'  => '#555555',
            ],
         ],
         // 'label' => '$params['label']',
         'icon'  => $params['icon'],
      ];
   }
}
