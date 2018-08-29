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
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
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

   /**
    * @engine inline
    */
   public function testTargetTicketActors() {
      $form = new \PluginFormcreatorForm();
      $form->add([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => __METHOD__,
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $target = new \PluginFormcreatorTarget();
      $target->add([
         'name'                  => 'a target',
         'itemtype'              => \PluginFormcreatorTargetTicket::class,
         'plugin_formcreator_forms_id' => $form->getID()
      ]);
      $this->boolean($target->isNewItem())->isFalse();
      $this->integer((int) $target->getField('plugin_formcreator_forms_id'))
         ->isEqualTo((int) $form->getID());
      $this->string($target->getField('itemtype'))
         ->isEqualTo(\PluginFormcreatorTargetTicket::class);

      $targetTicket = $target->getField('items_id');
      $targetTicket = new \PluginFormcreatorTargetTicket();
      $targetTicket->getFromDB($target->getField('items_id'));
      $this->boolean($targetTicket->isNewItem())->isFalse();
      $this->string($targetTicket
         ->getField('name'))
         ->isEqualTo($target->getField('name'));

      $requesterActor = new \PluginFormcreatorTargetTicket_Actor();
      $observerActor = new \PluginFormcreatorTargetTicket_Actor();
      $targetTicketId = $targetTicket->getID();

      $requesterActor->getFromDBByCrit([
         'AND' => [
            'plugin_formcreator_targettickets_id' => $targetTicketId,
            'actor_role' => 'requester',
            'actor_type' => 'creator'
         ]
      ]);
      $observerActor->getFromDBByCrit([
         'AND' => [
            'plugin_formcreator_targettickets_id' => $targetTicketId,
            'actor_role' => 'observer',
            'actor_type' => 'validator'
         ]
      ]);

      $this->boolean($requesterActor->isNewItem())->isFalse();
      $this->boolean($observerActor->isNewItem())->isFalse();
      $this->integer((int) $requesterActor->getField('use_notification'))
         ->isEqualTo(1);
      $this->integer((int) $observerActor->getField('use_notification'))
         ->isEqualTo(1);
   }

   public function testUrgency() {
      $form = new \PluginFormcreatorForm();
      $formId = $form->add([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => __METHOD__,
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $sectionsData = [
         [
            'name'                  => 'a section',
            'questions'             =>  [
               [
                  'name'                  => 'text question',
                  'fieldtype'             => 'text',
                  '_parameters' => [
                     'text' => [
                     'regex' => ['regex' => ''],
                     'range' => ['min' => '', 'max' => ''],
                     ]
                  ],
               ],
               [
                  'name'                  => 'custom urgency',
                  'fieldtype'             => 'urgency',
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
            if (isset($questionData['show_rule']) && $questionData['show_rule'] != 'always') {
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
            'itemtype'              => 'PluginFormcreatorTargetTicket',
            'urgency_rule'          => 'answer',
            'urgency_question'      => 'custom urgency',
            'expected'              => 5
         ],
         [
            'name'                  => 'target 2',
            'itemtype'              => 'PluginFormcreatorTargetTicket',
            'urgency_rule'          => 'none',
            'urgency_question'      => '',
            'expected'              => 3
         ]
      ];
      foreach ($targetTicketsData as $targetData) {
         // Create target
         $targetData['plugin_formcreator_forms_id'] = $formId;
         $target = new \PluginFormcreatorTarget();
         $target->add($targetData);
         $this->boolean($target->isNewItem())->isFalse();

         // Create target ticket
         $itemtype = $target->getField('itemtype');
         $targetTicket = new $itemtype();
         $targetTicket->getFromDB($target->getField('items_id'));
         $this->boolean($targetTicket->isNewItem())->isFalse();

         // Find urgency question
         if (!empty($targetData['urgency_question'])) {
            $questionName = $targetData['urgency_question'];
            $question = new \PluginFormcreatorQuestion();
            $table_section = \PluginFormcreatorSection::getTable();
            $table_form = \PluginFormcreatorForm::getTable();
            $table_question = \PluginFormcreatorQuestion::getTable();
            if (!method_exists($question, 'getFromDBByRequest')) {
               $question->getFromDBByQuery("LEFT JOIN `$table_section` `s` ON (`s`.`id` = `plugin_formcreator_sections_id`)
                  LEFT JOIN `$table_form` `f` ON (`f`.`id` = `s`.`plugin_formcreator_forms_id`)
                  WHERE `$table_question`.`name` = '$questionName' AND `plugin_formcreator_forms_id` = '$formId'");
            } else {
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
            }
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
         $this->boolean($targetTicket->update($targetTicketData))->isTrue();
      }

      $saveFormData = [];
      foreach ($urgencyQuestions as $question) {
         if ($question['question'] !== null) {
            $saveFormData['formcreator_field_' . $question['question']->getID()] = $question['expected'];
         }
      }
      $saveFormData['formcreator_form'] = $form->getID();
      $form->saveForm($saveFormData);
      $formAnswer = new \PluginFormcreatorForm_Answer();
      $formAnswer->getFromDbByCrit([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      // Check urgency for each target ticket
      foreach ($urgencyQuestions as $question) {
         $targetTicket = $question['targetTicket'];
         $targetName = $targetTicket->getField('name');
         $itemTicket = new \Item_Ticket();
         $tickets = [];
         $rows = $itemTicket->find("`itemtype` = 'PluginFormcreatorForm_Answer'
            AND `items_id` = '" . $formAnswer->getID() . "'");
         foreach ($rows as $row) {
            $tickets[] = $row['tickets_id'];
         }
         $ticket = new \Ticket();
         $ticket->getFromDBByCrit([
            'name' => $targetName,
            'id'   => $tickets
         ]);
         $this->boolean($ticket->isNewItem())->isFalse();
         $this->integer((int) $ticket->getField('urgency'))->isEqualTo($question['expected']);
      }
   }
}