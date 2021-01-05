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

/**
 * @engine inline
 */
class PluginFormcreatorTargetTicket extends CommonTestCase {

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);

      $this->login('glpi', 'glpi');
   }

   public function testTargetTicketActors() {
      // Create a form with a target ticket
      $form = $this->getForm();

      $targetTicket = new \PluginFormcreatorTargetTicket();
      $targetTicket->add([
         'name'                        => 'a target',
         'plugin_formcreator_forms_id' => $form->getID()
      ]);
      $targetTicket->getFromDB($targetTicket->getID());
      $this->boolean($targetTicket->isNewItem())->isFalse();

      // find the actors created by default
      $requesterActor = new \PluginFormcreatorTarget_Actor();
      $observerActor = new \PluginFormcreatorTarget_Actor();
      $targetTicketId = $targetTicket->getID();

      $requesterActor->getFromDBByCrit([
         'AND' => [
            'itemtype'   => $targetTicket->getType(),
            'items_id'   => $targetTicketId,
            'actor_role' => \PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER,
            'actor_type' => \PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHOR,
         ]
      ]);
      $observerActor->getFromDBByCrit([
         'AND' => [
            'itemtype'   => $targetTicket->getType(),
            'items_id'   => $targetTicketId,
            'actor_role' => \PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER,
            'actor_type' => \PluginFormcreatorTarget_Actor::ACTOR_TYPE_VALIDATOR
         ]
      ]);
      $this->boolean($requesterActor->isNewItem())->isFalse();
      $this->boolean($observerActor->isNewItem())->isFalse();

      // check the settings of the default actors
      $this->integer((int) $requesterActor->getField('use_notification'))
         ->isEqualTo(1);
      $this->integer((int) $observerActor->getField('use_notification'))
         ->isEqualTo(1);
   }

   public function testUrgency() {
      global $DB;

      // Create a form with a urgency question and 2 target tickets
      $form = $this->getForm([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => __METHOD__,
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $section = $this->getSection([
         'plugin_formcreator_forms_id' => $form->getID(),
         'name'                        => 'a section',
      ]);
      $this->boolean($section->isNewItem())->isFalse();

      $question = $this->getQuestion([
         'plugin_formcreator_sections_id' => $section->getID(),
         'name'                           => 'custom urgency',
         'fieldtype'                      => 'urgency',
         'default_values'                 => '3',
      ]);
      $this->boolean($question->isNewItem())->isFalse();

      $targetTicket1 = $this->getTargetTicket([
         'plugin_formcreator_forms_id' => $form->getID(),
         'name'                  => 'urgency from answer',
         'target_name'           => 'urgency from answer',
         'content'               => '##FULLFORM##',
         'itemtype'              => \PluginFormcreatorTargetTicket::class,
         'urgency_rule'          => \PluginFormcreatorAbstractTarget::URGENCY_RULE_ANSWER,
         'urgency_question'      => $question->getID(),
      ]);
      $this->boolean($targetTicket1->isNewItem())->isFalse();

      $targetTicket2 = $this->getTargetTicket([
         'plugin_formcreator_forms_id' => $form->getID(),
         'name'                  => 'default urgency',
         'target_name'           => 'default urgency',
         'content'               => '##FULLFORM##',
         'itemtype'              => \PluginFormcreatorTargetTicket::class,
         'urgency_rule'          => \PluginFormcreatorAbstractTarget::URGENCY_RULE_NONE,
         'urgency_question'      => '',
      ]);
      $this->boolean($targetTicket2->isNewItem())->isFalse();

      // create a formanswer
      $saveFormData = [
         'plugin_formcreator_forms_id' => $form->getID(),
         'formcreator_field_' . $question->getID() => '5',
      ];
      $formAnswer = new \PluginFormcreatorFormAnswer();
      $form->getFromDB($form->getID());
      $formAnswer->add($saveFormData);
      // Let's assume thre are no previous formanswers for this foreign key
      $formAnswer->getFromDbByCrit([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      $rows = $DB->request([
         'SELECT' => ['tickets_id'],
         'FROM'   => \Item_Ticket::getTable(),
         'WHERE'  => [
            'itemtype' => \PluginFormcreatorFormAnswer::class,
            'items_id' => $formAnswer->getID(),
         ]
      ]);
      $this->variable($rows)->isNotNull();
      foreach ($rows as $row) {
         $ticket = new \Ticket();
         $ticket->getFromDB($row['tickets_id']);
         $this->boolean($ticket->isNewItem())->isFalse();
         if ($ticket->fields['name'] == 'urgency from answer') {
            $this->integer((int) $ticket->fields['urgency'])->isEqualTo(5);
         } else if ($ticket->fields['name'] == 'default urgency') {
            // expected medium urgency
            $this->integer((int) $ticket->fields['urgency'])->isEqualTo(3);
         } else {
            throw new \RuntimeException('Unexpected ticket');
         }
      }
   }
}
