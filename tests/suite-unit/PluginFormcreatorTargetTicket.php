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
 *
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorTargetTicket extends CommonTestCase {

   /**
    * Tests that deleting a target ticket of a form also deletes relations between tickets and generated tickets
    *
    * @covers PluginFormcreatorTargetTicket::pre_deleteItem
    */
   public function testDeleteLinkedTickets() {
      global $CFG_GLPI;

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      // setup the test
      $ticket = new \Ticket();
      $ticket->add([
         'name'               => 'ticket',
         'content'            => 'help !',
         'users_id_recipient' => '0',
      ]);
      $this->boolean($ticket->isNewItem())->isFalse();

      $form = new \PluginFormcreatorForm();
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      $form->add([
         'name' => 'a form'
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $target_1 = new \PluginFormcreatorTarget();
      $target_1->add([
         'name'      => 'target 1',
         $formFk     => $form->getID(),
         'itemtype'  => \PluginFormcreatorTargetTicket::class,
      ]);
      $this->boolean($target_1->isNewItem())->isFalse();

      $target_2 = new \PluginFormcreatorTarget();
      $target_2->add([
         'name'      => 'target 2',
         $formFk     => $form->getID(),
         'itemtype'  => \PluginFormcreatorTargetTicket::class,
      ]);
      $this->boolean($target_2->isNewItem())->isFalse();

      $targetTicket_1 = new \PluginFormcreatorTargetTicket();
      $targetTicket_1->getFromDB($target_1->getField('items_id'));
      $this->boolean($targetTicket_1->isNewItem())->isFalse();

      $targetTicket_2 = new \PluginFormcreatorTargetTicket();
      $targetTicket_2->getFromDB($target_2->getField('items_id'));
      $this->boolean($targetTicket_2->isNewItem())->isFalse();

      $targetTicketFk = \PluginFormcreatorTargetTicket::getForeignKeyField();
      $item_targetticket_1 = new \PluginFormcreatorItem_TargetTicket();
      $item_targetticket_1->add([
         $targetTicketFk   => $targetTicket_1->getID(),
         'link'            => \Ticket_Ticket::LINK_TO,
         'itemtype'        => \Ticket::class,
         'items_id'        => $ticket->getID(),
      ]);
      $this->boolean($item_targetticket_1->isNewItem())->isFalse();

      $item_targetticket_2 = new \PluginFormcreatorItem_TargetTicket();
      $item_targetticket_2->add([
         $targetTicketFk   => $targetTicket_1->getID(),
         'link'            => \Ticket_Ticket::LINK_TO,
         'itemtype'        => \PluginFormcreatorTargetTicket::class,
         'items_id'        => $targetTicket_2->getID(),
      ]);
      $this->boolean($item_targetticket_2->isNewItem())->isFalse();

      // delete the target ticket
      $target_1->delete(['id' => $target_1->getID()]);

      // Check the linked ticket or target ticket are deleted
      $this->boolean($item_targetticket_1->getFromDB($item_targetticket_1->getID()))->isFalse();
      $this->boolean($item_targetticket_2->getFromDB($item_targetticket_2->getID()))->isFalse();
   }
}