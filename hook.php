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

/**
 * Install all necessary elements for the plugin
 * @return boolean True if success
 */
function plugin_formcreator_install() {
   spl_autoload_register('plugin_formcreator_autoload');

   $version   = plugin_version_formcreator();
   $migration = new Migration($version['version']);
   require_once(__DIR__ . '/install/install.php');
   $install = new PluginFormcreatorInstall();
   if (!$install->isPluginInstalled()
      || isset($_SESSION['plugin_formcreator']['cli'])
      && $_SESSION['plugin_formcreator']['cli'] == 'force-install') {
      return $install->install($migration);
   }
   return $install->upgrade($migration);
}

/**
 * Uninstall previously installed elements of the plugin
 *
 * @return boolean True if success
 */
function plugin_formcreator_uninstall() {
   require_once(__DIR__ . '/install/install.php');
   $install = new PluginFormcreatorInstall();
   $install->uninstall();
}

/**
 * Define Dropdown tables to be manage in GLPI :
 */
function plugin_formcreator_getDropdown() {
   return [
      'PluginFormcreatorCategory' => _n('Form category', 'Form categories', 2, 'formcreator'),
   ];
}


function plugin_formcreator_addDefaultSelect($itemtype) {
   switch ($itemtype) {
      case PluginFormcreatorIssue::class:
         return "`glpi_plugin_formcreator_issues`.`sub_itemtype`, ";
   }
   return "";
}


function plugin_formcreator_addDefaultJoin($itemtype, $ref_table, &$already_link_tables) {
   $join = "";
   switch ($itemtype) {
      case PluginFormcreatorIssue::class:
         $join = Search::addDefaultJoin("Ticket", "glpi_tickets", $already_link_tables);
         $join = str_replace('`glpi_tickets`.`id`', '`glpi_plugin_formcreator_issues`.`original_id`', $join);
         $join = str_replace('`glpi_tickets`', '`glpi_plugin_formcreator_issues`', $join);
         $join = str_replace('`users_id_recipient`', '`requester_id`', $join);
   }
   return $join;
}


function plugin_formcreator_canValidate() {
   return Session::haveRight('ticketvalidation', TicketValidation::VALIDATEINCIDENT)
      || Session::haveRight('ticketvalidation', TicketValidation::VALIDATEREQUEST);
}

/**
 * Undocumented function
 *
 * @param string $itemtype
 * @return string
 */
function plugin_formcreator_getCondition($itemtype) {
   $table = $itemtype::getTable();
   if ($itemtype == PluginFormcreatorFormAnswer::class) {
      if (Session::haveRight('config', UPDATE)) {
         return '';
      }
      if (plugin_formcreator_canValidate()) {
         $groupUser = new Group_User();
         $groups = $groupUser->getUserGroups($_SESSION['glpiID']);
         $condition = " (`$table`.`users_id_validator` =". $_SESSION['glpiID'];
         if (count($groups) < 1) {
            $condition .= ")";
         } else {
            $groupIDs = [];
            foreach ($groups as $group) {
               $groupIDs[] = $group['id'];
            }
            $groupIDs = implode(',', $groupIDs);
            $condition .= " OR `$table`.`groups_id_validator` IN ($groupIDs) )";
         }
         return $condition;
      }
   }

   return " `$table`.`requester_id` = " . $_SESSION['glpiID'];
}

/**
 * Define specific search request
 *
 * @param  String $itemtype    Itemtype for the search engine
 * @return String          Specific search request
 */
function plugin_formcreator_addDefaultWhere($itemtype) {
   $condition = '';
   switch ($itemtype) {
      case PluginFormcreatorIssue::class:
         $condition = Search::addDefaultWhere(Ticket::class);
         if ($condition == '') {
            $condition = "(`glpi_plugin_formcreator_issues`.`users_id_validator` = '" . Session::getLoginUserID() . "')";
         } else {
            $condition = str_replace('`glpi_tickets`', '`glpi_plugin_formcreator_issues`', $condition);
            $condition = str_replace('`users_id_recipient`', '`requester_id`', $condition);
            $condition = "($condition OR `glpi_plugin_formcreator_issues`.`users_id_validator` = '" . Session::getLoginUserID() . "')";
         }
         break;

      case PluginFormcreatorFormAnswer::class:
         if (isset($_SESSION['formcreator']['form_search_answers'])
             && $_SESSION['formcreator']['form_search_answers']) {
            // Context is displaying the answers for a given form
            $table = $itemtype::getTable();
            $formFk = PluginFormcreatorForm::getForeignKeyField();
            $condition = "`$table`.`$formFk` = ".
                         $_SESSION['formcreator']['form_search_answers'];
         } else {
            $condition = plugin_formcreator_getCondition($itemtype);
         }
         break;
   }
   return $condition;
}


function plugin_formcreator_addLeftJoin($itemtype, $ref_table, $new_table, $linkfield, &$already_link_tables) {
   $join = "";
   switch ($itemtype) {
      case PluginFormcreatorIssue::class:
         if ($new_table == 'glpi_ticketvalidations') {
            foreach ($already_link_tables as $table) {
               if (strpos($table, $new_table) === 0) {
                  $AS = $table;
               }
            }
            $join = " LEFT JOIN `$new_table` AS `$AS` ON (`$ref_table`.`tickets_id` = `$AS`.`tickets_id`) ";
         }

         if ($new_table == 'glpi_groups') {
            foreach ($already_link_tables as $table) {
               if (strpos($table, $new_table) === 0) {
                  $ref = explode('.', $table);
                  $AS = $ref[0];
                  $fk = getForeignKeyFieldForTable($ref[0]);
                  if (count($ref) > 1) {
                     $AS = $ref[0];
                     $fk = $ref[1];
                  }
               }
            }
            $join = " LEFT JOIN `$new_table` AS `$AS` ON (`$ref_table`.`$fk` = `$AS`.`id`) ";
         }

         if ($new_table == 'glpi_users' &&  $linkfield == 'users_id') {
            foreach ($already_link_tables as $table) {
               if (strpos($table, $new_table) === 0) {
                  $AS = $table;
               }
            }
            $join = " LEFT JOIN `$new_table` AS `$AS` ON (`$ref_table`.`users_id` = `$AS`.`id`) ";
         }
      break;
   }

   return $join;
}


function plugin_formcreator_addWhere($link, $nott, $itemtype, $ID, $val, $searchtype) {
   $searchopt = &Search::getOptions($itemtype);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   switch ($table.".".$field) {
      case "glpi_plugin_formcreator_issues.status" :
         $tocheck = [];
         /** @var CommonITILObject $item  */
         if ($item = getItemForItemtype($itemtype)) {
            switch ($val) {
               case 'all':
                  $tocheck = array_keys($item->getAllStatusArray());
                  break;

               case Ticket::INCOMING:
                  $tocheck = $item->getNewStatusArray();
                  break;

               case 'process' :
                  // getProcessStatusArray should be an abstract method of CommonITILObject
                  $tocheck = $item->getProcessStatusArray();
                  break;

               case 'notclosed' :
                  $tocheck = $item->getAllStatusArray();
                  foreach ($item->getClosedStatusArray() as $status) {
                     unset($tocheck[$status]);
                  }
                  $tocheck = array_keys($tocheck);
                  break;

               case 'old' :
                  $tocheck = array_merge($item->getSolvedStatusArray(),
                                         $item->getClosedStatusArray());
                  break;

               case 'notold' :
                  $tocheck = $item->getAllStatusArray();
                  foreach ($item->getSolvedStatusArray() as $status) {
                     unset($tocheck[$status]);
                  }
                  foreach ($item->getClosedStatusArray() as $status) {
                     unset($tocheck[$status]);
                  }
                  unset($tocheck[PluginFormcreatorFormAnswer::STATUS_REFUSED]);

                  $tocheck = array_keys($tocheck);
                  break;
            }
         }

         if (count($tocheck) == 0) {
            $statuses = $item->getAllStatusArray();
            if (isset($statuses[$val])) {
               $tocheck = [$val];
            }
         }

         if (count($tocheck)) {
            if ($nott) {
               return $link." `$table`.`$field` NOT IN ('".implode("','", $tocheck)."')";
            }
            return $link." `$table`.`$field` IN ('".implode("','", $tocheck)."')";
         }
         break;
   }
}


function plugin_formcreator_AssignToTicket($types) {
   $types[PluginFormcreatorFormAnswer::class] = PluginFormcreatorFormAnswer::getTypeName();

   return $types;
}


function plugin_formcreator_MassiveActions($itemtype) {

   switch ($itemtype) {
      case PluginFormcreatorForm::class:
         return [
            'PluginFormcreatorForm' . MassiveAction::CLASS_ACTION_SEPARATOR . 'Duplicate' => _x('button', 'Duplicate'),
            'PluginFormcreatorForm' . MassiveAction::CLASS_ACTION_SEPARATOR . 'Transfert' => __('Transfer'),
            'PluginFormcreatorForm' . MassiveAction::CLASS_ACTION_SEPARATOR . 'Export' => _sx('button', 'Export'),
         ];
   }
   return [];
}


function plugin_formcreator_giveItem($itemtype, $ID, $data, $num) {
   switch ($itemtype) {
      case PluginFormcreatorIssue::class:
         return PluginFormcreatorIssue::giveItem($itemtype, $ID, $data, $num);
         break;
   }

   return "";
}

function plugin_formcreator_hook_add_ticket(CommonDBTM $item) {
   global $CFG_GLPI, $DB;

   if (!($item instanceof Ticket)) {
      return;
   }
   if (isset($CFG_GLPI['plugin_formcreator_disable_hook_create_ticket'])) {
      return;
   }

   // run this hook only if the plugin is not generating tickets
   $requester = $DB->request([
      'SELECT' => 'users_id',
      'FROM' => Ticket_User::getTable(),
      'WHERE' => [
         'tickets_id' => $item->getID(),
         'type' =>  '1',
      ],
      'ORDER' => ['id'],
      'LIMIT' => '1',
   ])->next();
   if ($requester === null) {
      $requester = [
         'users_id' => 0,
      ];
   }

   $validationStatus = PluginFormcreatorCommon::getTicketStatusForIssue($item);

   $issue = new PluginFormcreatorIssue();
   $issue->add([
      'original_id'        => $item->getID(),
      'sub_itemtype'       => 'Ticket',
      'name'               => addslashes($item->fields['name']),
      'status'             => $validationStatus['status'],
      'date_creation'      => $item->fields['date'],
      'date_mod'           => $item->fields['date_mod'],
      'entities_id'        => $item->fields['entities_id'],
      'is_recursive'       => '0',
      'requester_id'       => $requester['users_id'],
      'users_id_validator' => $validationStatus['user'],
      'comment'            => addslashes($item->fields['content']),
   ]);
}

function plugin_formcreator_hook_update_ticket(CommonDBTM $item) {
   if (!($item instanceof Ticket)) {
      return;
   }

   $id = $item->getID();

   $validationStatus = PluginFormcreatorCommon::getTicketStatusForIssue($item);

   $issue = new PluginFormcreatorIssue();
   $issue->getFromDBByCrit([
      'AND' => [
         'sub_itemtype' => Ticket::class,
         'original_id'  => $id
      ]
   ]);
   $issue->update([
      'id'                 => $issue->getID(),
      'original_id'        => $id,
      'display_id'         => "t_$id",
      'sub_itemtype'       => 'Ticket',
      'name'               => addslashes($item->fields['name']),
      'status'             => $validationStatus['status'],
      'date_creation'      => $item->fields['date'],
      'date_mod'           => $item->fields['date_mod'],
      'entities_id'        => $item->fields['entities_id'],
      'is_recursive'       => '0',
      'requester_id'       => $item->fields['users_id_recipient'],
      'users_id_validator' => $validationStatus['user'],
      'comment'            => addslashes($item->fields['content']),
   ]);
}

function plugin_formcreator_hook_delete_ticket(CommonDBTM $item) {
   global $DB;

   if (!($item instanceof Ticket)) {
      return;
   }

   $id = $item->getID();

   // mark formanswers as deleted
   $iterator = $DB->request([
      'SELECT' => ['id'],
      'FROM'   => Item_Ticket::getTable(),
      'WHERE'  => [
         'itemtype'   => 'PluginFormcreatorFormAnswer',
         'tickets_id' => $id,
      ]
   ]);
   foreach ($iterator as $row) {
      $form_answer = new PluginFormcreatorFormAnswer();
      $form_answer->update([
         'id'           => $row['id'],
         'is_deleted'   => 1,
      ]);
   }

   // delete issue
   $issue = new PluginFormcreatorIssue();
   $issue->deleteByCriteria([
      'display_id'   => "t_$id",
      'sub_itemtype' => 'Ticket'
   ], 1);
}

function plugin_formcreator_hook_restore_ticket(CommonDBTM $item) {
   plugin_formcreator_hook_add_ticket($item);
}

function plugin_formcreator_hook_purge_ticket(CommonDBTM $item) {
   if ($item instanceof Ticket) {
      $id = $item->getID();

      $issue = new PluginFormcreatorIssue();
      $issue->deleteByCriteria([
         'display_id'   => "t_$id",
         'sub_itemtype' => 'Ticket'
      ], 1);
   }
}

function plugin_formcreator_hook_pre_purge_targetTicket(CommonDBTM $item) {
   $item->pre_purgeItem();
}

function plugin_formcreator_hook_pre_purge_targetChange(CommonDBTM $item) {
   $item->pre_purgeItem();
}

function plugin_formcreator_hook_update_ticketvalidation(CommonDBTM $item) {
   $ticket = new Ticket();
   $ticket->getFromDB($item->fields['tickets_id']);
   if ($ticket->isNewItem()) {
      // Should not happen
      return;
   }

   $status = PluginFormcreatorCommon::getTicketStatusForIssue($ticket);

   $issue = new PluginFormcreatorIssue();
   $issue->getFromDBByCrit([
      'sub_itemtype' => Ticket::getType(),
      'original_id'  => $item->fields['tickets_id']
   ]);
   if ($issue->isNewItem()) {
      return;
   }
   $issue->update(['status' => $status['status']] + $issue->fields);
}

function plugin_formcreator_hook_purge_ticketvalidation(CommonDBTM $item) {
   plugin_formcreator_hook_update_ticketvalidation($item);
}

function plugin_formcreator_dynamicReport($params) {
   switch ($params['item_type']) {
      case PluginFormcreatorFormAnswer::class;
         if ($url = parse_url($_SERVER['HTTP_REFERER'])) {
            if (strpos($url['path'],
                       Toolbox::getItemTypeFormURL("PluginFormcreatorForm")) !== false) {
               parse_str($url['query'], $query);
               if (isset($query['id'])) {
                  $item = new PluginFormcreatorForm;
                  $item->getFromDB($query['id']);
                  PluginFormcreatorFormAnswer::showForForm($item, $params);
                  return true;
               }
            }
         }
         break;
   }

   return false;
}

/**
 * Hook for timeline_actions; display a new action for a CommonITILObject
 * @see CommonITILObject
 *
 * @return void
 */
function plugin_formcreator_timelineActions($options) {
   $item = $options['item'];
   if (!$item->canDeleteItem()) {
      return;
   }

   if (!(isset($_SESSION['glpiactiveprofile']) &&
       $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk')) {
      return;
   }
   echo "<li class='plugin_formcreator_cancel_my_ticket' onclick='".
      "javascript:plugin_formcreator_cancelMyTicket(".$item->fields['id'].");'>"
      ."<i class='fa'></i>".__('Cancel my ticket', 'formcreator')."</li>";
}
