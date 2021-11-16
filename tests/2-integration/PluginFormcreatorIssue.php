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

   public function testAddTicket() {
      $this->login('post-only', 'postonly');
      // Create a form with a target ticket
      $form = $this->getForm();
      $this->getTargetTicket([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
      ]);

      // answer the form
      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswer->add([\PluginFormcreatorForm::getForeignKeyField() => $form->getID()]);
      // Get the generated ticket
      $ticket = array_pop($formAnswer->targetList);
      $this->object($ticket);
      $this->boolean($ticket->isNewItem())->isFalse();
      $this->integer((int) $ticket->fields['status'])->isEqualTo(\CommonITILObject::INCOMING);

      // find the issue for the ticket
      $issue = $this->newTestedInstance();
      $issue->getFromDBByCrit([
         'itemtype' => \Ticket::getType(),
         'items_id'  => $ticket->getID(),
      ]);
      $this->boolean($issue->isNewItem())->isFalse();

      // Check the status has been updated
      $this->integer((int) $issue->fields['status'])->isEqualTo(\CommonITILObject::INCOMING);
   }

   public function testUpdateTicket() {
      $this->login('post-only', 'postonly');
      // Create a form with a target ticket
      $form = $this->getForm();
      $this->getTargetTicket([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
      ]);

      //Aanswer the form
      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswer->add([\PluginFormcreatorForm::getForeignKeyField() => $form->getID()]);

      // Get the generated ticket
      $ticket = array_pop($formAnswer->targetList);
      $this->object($ticket);
      $this->boolean($ticket->isNewItem())->isFalse();
      $this->integer((int) $ticket->fields['status'])->isEqualTo(\CommonITILObject::INCOMING);

      $this->login('glpi', 'glpi');
      $success = $ticket->update([
         'id' => $ticket->getID(),
         '_itil_assign' => [
            '_type' => strtolower(\User::getType()),
            \User::getForeignKeyField() => 2, // glpi
            'use_notification'  => 1,
         ],
      ]);
      $this->boolean($success)->isTrue();

      // find the issue for the ticket
      $issue = $this->newTestedInstance();
      $issue->getFromDBByCrit([
         'itemtype' => \Ticket::getType(),
         'items_id'  => $ticket->getID(),
      ]);
      $this->boolean($issue->isNewItem())->isFalse();

      // Check the status has been updated
      $this->integer((int) $issue->fields['status'])->isEqualTo(\CommonITILObject::ASSIGNED);
   }

   public function testDeleteTicket() {
      $this->login('glpi', 'glpi');
      $form = $this->getForm();
      $this->getTargetTicket([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
      ]);

      // answer the form
      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswer->add([\PluginFormcreatorForm::getForeignKeyField() => $form->getID()]);
      // Get the generated ticket
      $ticket = array_pop($formAnswer->targetList);
      $this->object($ticket);
      $this->boolean($ticket->isNewItem())->isFalse();

      // find the issue for the ticket
      $issue = $this->newTestedInstance();
      $issue->getFromDBByCrit([
         'itemtype' => \Ticket::getType(),
         'items_id'  => $ticket->getID(),
      ]);
      $this->boolean($issue->isNewItem())->isFalse();

      $ticket->delete([
         'id' => $ticket->getID()
      ], 1);

      // Check the issue has been deleted
      $success = $issue->getFromDB($issue->getID());
      $this->boolean($success)->isFalse();

   }
}
