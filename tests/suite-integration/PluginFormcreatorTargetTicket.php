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
 * @copyright Copyright © 2011 - 2020 Teclib'
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
      $requesterActor = new \PluginFormcreatorTargetTicket_Actor();
      $observerActor = new \PluginFormcreatorTargetTicket_Actor();
      $targetTicketId = $targetTicket->getID();

      $requesterActor->getFromDBByCrit([
         'AND' => [
            'plugin_formcreator_targettickets_id' => $targetTicketId,
            'actor_role' => \PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER,
            'actor_type' => \PluginFormcreatorTarget_Actor::ACTOR_TYPE_CREATOR,
         ]
      ]);
      $observerActor->getFromDBByCrit([
         'AND' => [
            'plugin_formcreator_targettickets_id' => $targetTicketId,
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

      $form = $this->getForm([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => __METHOD__,
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ]);
      $formId = $form->getID();
      $this->boolean($form->isNewItem())->isFalse();

      $sectionsData = [
         [
            'name'                  => 'a section',
            'questions'             =>  [
               [
                  'name'                  => 'custom urgency',
                  'fieldtype'             => 'urgency',
                  'default_values'        => '3',
               ],
            ],
         ],
      ];
      foreach ($sectionsData as $sectionData) {
         // Keep questions data set apart from sections data
         $questionsData = $sectionData['questions'];
         unset($sectionData['questions']);

         // Create section
         $sectionData['plugin_formcreator_forms_id'] = $form->getID();
         $section = new \PluginFormcreatorSection();
         $section->add($sectionData);
         $this->boolean($section->isNewItem())->isFalse();
         $sectionId = $section->getID();
         foreach ($questionsData as $questionData) {
            // Create question
            $questionData ['plugin_formcreator_sections_id'] = $section->getID();
            $question = new \PluginFormcreatorQuestion();
            $question->add($questionData);
            $this->boolean($question->isNewItem())->isFalse(json_encode($_SESSION['MESSAGE_AFTER_REDIRECT'], JSON_PRETTY_PRINT));
            $question->updateParameters($questionData);
            $questionData['id'] = $question->getID();
            if (isset($questionData['show_rule']) && $questionData['show_rule'] !=\PluginFormcreatorCondition::SHOW_RULE_ALWAYS) {
               $showFieldName = $questionData['show_field'];
               $showfield = new \PluginFormcreatorQuestion();
               $showfield->getFromDBByCrit([
                  'AND' => [
                     'plugin_formcreator_sections_id' => $sectionId,
                     'name' => $showFieldName
                  ]
               ]);
               $question->updateConditions($questionData);
            }
         }
      }

      $urgencyQuestions = [];
      $formId = $form->getID();
      $targetTicketsData = [
         [
            'name'                  => 'target 1',
            'plugin_formcreator_forms_id' => $formId,
            'content'               => '##FULLFORM##',
            'itemtype'              => \PluginFormcreatorTargetTicket::class,
            'urgency_rule'          => \PluginFormcreatorTargetBase::URGENCY_RULE_ANSWER,
            'urgency_question'      => 'custom urgency',
            'expected'              => '5'
         ],
         [
            'name'                  => 'target 2',
            'plugin_formcreator_forms_id' => $formId,
            'content'               => '##FULLFORM##',
            'itemtype'              => \PluginFormcreatorTargetTicket::class,
            'urgency_rule'          => \PluginFormcreatorTargetBase::URGENCY_RULE_NONE,
            'urgency_question'      => '',
            'expected'              => '3'
         ]
      ];
      foreach ($targetTicketsData as $targetData) {
         // Create target ticket
         $itemtype = $targetData['itemtype'];
         $targetTicket = new $itemtype();
         $targetTicket->add($targetData);
         $this->boolean($targetTicket->isNewItem())->isFalse();

         // Find urgency question
         if (!empty($targetData['urgency_question'])) {
            $questionName = $targetData['urgency_question'];
            $question = new \PluginFormcreatorQuestion();
            $question->getFromDBByRequest([
               'LEFT JOIN' => [
                  \PluginFormcreatorSection::getTable() => [
                     'FKEY' => [
                        \PluginFormcreatorSection::getTable() => 'id',
                        \PluginFormcreatorQuestion::getTable() => \PluginFormcreatorSection::getForeignKeyField()
                     ]
                  ],
                  \PluginFormcreatorForm::getTable() => [
                     'FKEY' => [
                           \PluginFormcreatorForm::getTable() => 'id',
                           \PluginFormcreatorSection::getTable() => \PluginFormcreatorForm::getForeignKeyField()
                     ]
                  ]
               ],
               'WHERE' => [
                  'AND' => [
                     \PluginFormcreatorQuestion::getTable() . '.name' => $questionName,
                     \PluginFormcreatorForm::getForeignKeyField() => $formId,
                  ]
               ]
            ]);
            $this->boolean($question->isNewItem())->isFalse();
            $questionId = $question->getID();
            $urgencyQuestions[] = [
               'question'     => $question,
               'targetTicket' => $targetTicket,
               'expected'     => $targetData['expected']
            ];
         } else {
            $urgencyQuestions[] = [
               'question'     => null,
               'targetTicket' => $targetTicket,
               'expected'     => $targetData['expected']
            ];
         }

         // Update target ticket
         $targetTicketData = $targetTicket->fields;
         $targetTicketData['id'] = $targetTicket->getID();
         $targetTicketData['title'] = $targetTicketData['name'];
         $targetTicketData['urgency_rule'] = $targetData['urgency_rule'];
         $targetTicketData['_urgency_question'] = $questionId;
         $targetTicketData['destination_entity'] = 'NULL';
         $targetTicketData['category_rule'] = '';
         $targetTicketData['location_rule'] = '';
         $targetTicketData['type_rule'] = \PluginFormcreatorTargetTicket::REQUESTTYPE_SPECIFIC;
         $targetTicketData['_type_specific'] = \Ticket::INCIDENT_TYPE;
         $this->boolean($targetTicket->update($targetTicketData))->isTrue();
      }

      $saveFormData = [];
      foreach ($urgencyQuestions as $question) {
         if ($question['question'] !== null) {
            $saveFormData['formcreator_field_' . $question['question']->getID()] = $question['expected'];
         }
      }
      $saveFormData['plugin_formcreator_forms_id'] = $form->getID();
      $formAnswer = new \PluginFormcreatorFormAnswer();
      $form->getFromDB($form->getID());
      $formAnswer->add($saveFormData);
      $formAnswer->getFromDbByCrit([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      // Check urgency for each target ticket
      foreach ($urgencyQuestions as $question) {
         $targetTicket = $question['targetTicket'];
         $targetName = $targetTicket->getField('name');
         $tickets = [];
         $rows = $DB->request([
            'SELECT' => ['tickets_id'],
            'FROM'   => \Item_Ticket::getTable(),
            'WHERE'  => [
               'itemtype' => \PluginFormcreatorFormAnswer::class,
               'items_id' => $formAnswer->getID()
            ]
         ]);
         $this->integer($rows->count())->isGreaterThan(0);
         foreach ($rows as $row) {
            $tickets[] = $row['tickets_id'];
         }
         $ticket = new \Ticket();
         $ticket->getFromDBByCrit([
            'name' => $targetName,
            'id'   => $tickets
         ]);
         $this->boolean($ticket->isNewItem())->isFalse();
         $this->integer((int) $ticket->fields['urgency'])->isEqualTo($question['expected']);
      }
   }
}
