<?php
/**
 * LICENSE
 *
 * Copyright © 2011-2018 Teclib'
 *
 * This file is part of Formcreator Plugin for GLPI.
 *
 * Formcreator is a plugin that allow creation of custom, easy to access forms
 * for users when they want to create one or more GLPI tickets.
 *
 * Formcreator Plugin for GLPI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2018 Teclib
 * @license   GPLv2 https://www.gnu.org/licenses/gpl2.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ------------------------------------------------------------------------------
 */
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorComposite
{
   private $item_targetTicket;

   private $targets = [];

   private $ticket_ticket;

   public function __construct(PluginFormcreatorItem_TargetTicket $item_targetTicket, Ticket_Ticket $ticket_ticket) {
      $this->item_targetTicket = $item_targetTicket;
      $this->ticket_ticket = $ticket_ticket;
   }

   /**
    * Add a target and generated target
    *
    * @param PluginFormcreatorTargetBase $target
    * @param CommonDBTM $generatedTarget
    */
   public function addTarget(PluginFormcreatorTargetBase $target, CommonDBTM $generatedTarget) {
      $itemtype = get_class($target);
      $this->targets[$itemtype][$target->getID()] = $generatedTarget;
   }

   public function buildCompositeRelations() {
      if (isset($this->targets['PluginFormcreatorTargetTicket'])) {
         foreach ($this->targets['PluginFormcreatorTargetTicket'] as $targetId => $generatedObject) {
            $rows = $this->item_targetTicket->find("`plugin_formcreator_targettickets_id` = '$targetId'");
            foreach ($rows as $row) {
               switch ($row['itemtype']) {
                  case 'Ticket':
                     $this->ticket_ticket->add([
                        'link' => $row['link'],
                        'tickets_id_1' => $generatedObject->getID(),
                        'tickets_id_2' => $row['items_id'],
                     ]);
                     break;

                  case 'PluginFormcreatorTargetTicket':
                     $ticket = $this->targets['PluginFormcreatorTargetTicket'][$row['items_id']];
                     $this->ticket_ticket->add([
                        'link' => $row['link'],
                        'tickets_id_1' => $generatedObject->getID(),
                        'tickets_id_2' => $ticket->getID(),
                     ]);
                     break;
               }
            }
         }
      }
   }
}
