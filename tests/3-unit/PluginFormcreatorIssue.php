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

namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorIssue extends CommonTestCase {
   public function beforeTestMethod($method) {
      global $CFG_GLPI;
      switch ($method) {
         case 'testGetSyncIssuesRequest':
            $this->login('glpi', 'glpi');
            $CFG_GLPI['use_notifications'] = 0;
            break;
      }
   }

   public function providerGetsyncIssuesRequest_simpleTicket() {
      $ticket = new \Ticket();
      $ticket->add([
         'name'    => 'a ticket',
         'content' => 'foo',
         'status'  =>  \Ticket::INCOMING
      ]);
      $this->boolean($ticket->isNewItem())->isFalse();
      $ticket->getFromDB($ticket->getID());

      return [
         [
            'item' => $ticket,
            'expected' => [
               'sub_itemtype'  => \Ticket::getType(),
               'original_id'   => $ticket->getID(),
               'display_id'    => 't_' . $ticket->getID(),
               'name'          => $ticket->fields['name'],
               'status'        => $ticket->fields['status'],
               'requester_id'  => $ticket->fields['users_id_recipient'],
               'date_creation' => $ticket->fields['date'],
               'date_mod'      => $ticket->fields['date_mod'],
            ],
         ],
      ];
   }

   public function providerGetsyncIssuesRequest_simpleFormanswers() {
      $form = $this->getForm();
      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($formAnswer->isNewItem())->isFalse();
      $formAnswer->getFromDB($formAnswer->getID());
      return [
         [
            'item' => $formAnswer,
            'expected' => [
               'sub_itemtype'  => \PluginFormcreatorFormAnswer::getType(),
               'original_id'   => $formAnswer->getID(),
               'display_id'    => 'f_' . $formAnswer->getID(),
               'name'          => $formAnswer->fields['name'],
               'status'        => $formAnswer->fields['status'],
               'requester_id'  => $formAnswer->fields['requester_id'],
               'date_creation' => $formAnswer->fields['request_date'],
               'date_mod'      => $formAnswer->fields['request_date'],
            ],
         ],
      ];
   }

   public function providerGetSyncIssuesRequest_formAnswerWithSeveralTickets() {
      $form = $this->getForm();
      $targetTicket1 = new \PluginFormcreatorTargetTicket();
      $targetTicket1->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'name' => 'foo',
      ]);
      $this->boolean($targetTicket1->isNewItem())->isFalse();
      $targetTicket2 = new \PluginFormcreatorTargetTicket();
      $targetTicket2->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'name' => 'bar',
      ]);
      $this->boolean($targetTicket2->isNewItem())->isFalse();

      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($formAnswer->isNewItem())->isFalse();
      $formAnswer->getFromDB($formAnswer->getID());
      return [
         [
            'item' => $formAnswer,
            'expected' => [
               'sub_itemtype'  => \PluginFormcreatorFormAnswer::getType(),
               'original_id'   => $formAnswer->getID(),
               'display_id'    => 'f_' . $formAnswer->getID(),
               'name'          => $formAnswer->fields['name'],
               'status'        => $formAnswer->fields['status'],
               'requester_id'  => $formAnswer->fields['requester_id'],
               'date_creation' => $formAnswer->fields['request_date'],
               'date_mod'      => $formAnswer->fields['request_date'],
            ],
         ],
      ];
   }

   public function providerGetSyncIssuesRequest_formAnswerWithOneTickets() {
      $form = $this->getForm();
      $targetTicket1 = new \PluginFormcreatorTargetTicket();
      $targetTicket1->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'name' => 'foo',
      ]);
      $this->boolean($targetTicket1->isNewItem())->isFalse();

      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($formAnswer->isNewItem())->isFalse();
      $formAnswer->getFromDB($formAnswer->getID());

      /** @var \Ticket */
      $ticket = array_pop($formAnswer->targetList);
      $this->object($ticket)->isInstanceOf(\Ticket::class);
      return [
         [
            'item' => $formAnswer,
            'expected' => [
               'sub_itemtype'  => \PluginFormcreatorFormAnswer::getType(),
               'original_id'   => $ticket->getID(),
               'display_id'    => 't_' . $ticket->getID(),
               'name'          => $formAnswer->fields['name'],
               'status'        => $formAnswer->fields['status'],
               'requester_id'  => $formAnswer->fields['requester_id'],
               'date_creation' => $formAnswer->fields['request_date'],
               'date_mod'      => $formAnswer->fields['request_date'],
            ],
         ],
      ];
   }

   public function providerGetSyncIssuesRequest() {
      return array_merge(
         $this->providerGetSyncIssuesRequest_formAnswerWithSeveralTickets(),
         $this->providerGetsyncIssuesRequest_simpleTicket(),
         $this->providerGetsyncIssuesRequest_simpleFormanswers()
      );
   }

   /**
    * @dataProvider providerGetSyncIssuesRequest
    *
    * @return void
    */
   public function testGetSyncIssuesRequest($item, $expected) {
      global $DB;

      // Find the row matching the issue in re-populate query
      // Implicitly tests sub_itemtype and original_id columns
      $request = $this->getTestedClassName()::getSyncIssuesRequest();
      $result = $DB->request([
         'FROM'  => $request,
         'WHERE' => [
            'sub_itemtype' => $item->getType(),
            'original_id'  => $item->getID(),
         ]
      ]);
      $this->object($result)->isInstanceOf(\DBmysqlIterator::class);
      $row = $result->next();
      $this->array($row);

      // Test all fields described in expectations
      foreach ($expected as $key => $field) {
         $this->variable($row[$key])->isEqualTo($field);
      }
   }
}