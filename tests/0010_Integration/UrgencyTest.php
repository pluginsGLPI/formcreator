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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class UrgencyTest extends SuperAdminTestCase
{

   protected $formData;
   protected $sectionData;
   protected $targetTicketData;

   public function setUp() {
      parent::setUp();

      $this->formData = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'a form',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => 0
      );

      $this->sectionData = [
         [
            'name'                  => 'a section',
            'questions'             => [
               [
                  'name'                  => 'text question',
                  'fieldtype'             => 'text',
                  '_parameters'     => [
                     'text' => [
                        'range' => [
                           'range_min' => '',
                           'range_max' => '',
                        ],
                        'regex' => [
                           'regex' => ''
                        ]
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

      $this->targetTicketData = [
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
   }

   public function testInitCreateForm() {
      $form = new PluginFormcreatorForm();
      $formId = $form->add($this->formData);
      $this->assertFalse($form->isNewItem());

      foreach ($this->sectionData as $sectionData) {
         // Keep questions data set apart from sections data
         $questionsData = $sectionData['questions'];
         unset($sectionData['questions']);

         // Create section
         $sectionData['plugin_formcreator_forms_id'] = $form->getID();
         $section = new PluginFormcreatorSection();
         $section->add($sectionData);
         $this->assertFalse($section->isNewItem());
         $sectionId = $section->getID();
         foreach ($questionsData as $questionData) {
            // Create question
            $questionData ['plugin_formcreator_sections_id'] = $section->getID();
            $question = new PluginFormcreatorQuestion();
            $question->add($questionData);
            $this->assertFalse($question->isNewItem(), $_SESSION['MESSAGE_AFTER_REDIRECT']);

            $questionData['id'] = $question->getID();
            if (isset($questionData['show_rule']) && $questionData['show_rule'] != 'always') {
               $showFieldName = $questionData['show_field'];
               $showfield = new PluginFormcreatorQuestion();
               $showfield->getFromDBByCrit([
                  'AND' => [
                     'plugin_formcreator_sections_id' => $sectionId,
                     'name'                           => $showFieldName
                  ]
               ]);
               $question->updateConditions($questionData);
            }
            $question->updateParameters($questionData);
         }
      }

      return $form;
   }

   /**
    * @depends testInitCreateForm
    * @param PluginFormcreatorForm $urgencyQuestions
    */
   public function testInitCreateTargetTicket(PluginFormcreatorForm $form) {
      $urgencyQuestions = [];
      $formId = $form->getID();
      foreach ($this->targetTicketData as $targetData) {
         // Create target
         $targetData['plugin_formcreator_forms_id'] = $formId;
         $target = new PluginFormcreatorTarget();
         $target->add($targetData);
         $this->assertFalse($target->isNewItem());

         // Create target ticket
         $itemtype = $target->getField('itemtype');
         $targetTicket = new $itemtype();
         $targetTicket->getFromDB($target->getField('items_id'));
         $this->assertFalse($targetTicket->isNewItem());

         // Find urgency question
         if (!empty($targetData['urgency_question'])) {
            $questionName = $targetData['urgency_question'];
            $question = new PluginFormcreatorQuestion();
            $table_section = PluginFormcreatorSection::getTable();
            $table_form = PluginFormcreatorForm::getTable();
            $table_question = PluginFormcreatorQuestion::getTable();
            if (!method_exists($question, 'getFromDBByRequest')) {
               $question->getFromDBByQuery("LEFT JOIN `$table_section` `s` ON (`s`.`id` = `plugin_formcreator_sections_id`)
                  LEFT JOIN `$table_form` `f` ON (`f`.`id` = `s`.`plugin_formcreator_forms_id`)
                  WHERE `$table_question`.`name` = '$questionName' AND `plugin_formcreator_forms_id` = '$formId'");
            } else {
               $question->getFromDBByRequest([
                  'LEFT JOIN' => [
                     $table_section => [
                        'FKEY' => [
                           $table_section   => 'id',
                           $table_question  => 'plugin_formcreator_sections_id'
                        ]
                     ],
                     $table_form => [
                        'FKEY' => [
                           $table_form    => 'id',
                           $table_section => 'plugin_formcreator_forms_id'
                        ]
                     ]
                  ],
                  'WHERE' => [
                     'AND' => [
                        $table_question . '.name'    => $questionName,
                        'plugin_formcreator_forms_id' => $formId,
                     ]
                  ]
               ]);
            }
            $this->assertFalse($question->isNewItem());
            $questionId = $question->getID();
            $urgencyQuestions[] = array(
                  'question'     => $question,
                  'targetTicket' => $targetTicket,
                  'expected'     => $targetData['expected']
            );
         } else {
            $urgencyQuestions[] = array(
                  'question'     => null,
                  'targetTicket' => $targetTicket,
                  'expected'     => $targetData['expected']
            );
         }

         // Update target ticket
         $targetTicketData = $targetTicket->fields;
         $targetTicketData['id'] = $targetTicket->getID();
         $targetTicketData['title'] = $targetTicketData['name'];
         $targetTicketData['urgency_rule'] = $targetData['urgency_rule'];
         $targetTicketData['_urgency_question'] = $questionId;
         $this->assertTrue($targetTicket->update($targetTicketData));
      }

      return $urgencyQuestions;
   }

   /**
    * @depends testInitCreateForm
    * @depends testInitCreateTargetTicket
    * @param PluginFormcreatorForm $form
    * @param array $urgencyQuestions
    */
   public function testSendForm(PluginFormcreatorForm $form, $urgencyQuestions) {
      $saveFormData = [];
      foreach ($urgencyQuestions as $question) {
         if ($question['question'] !== null) {
            $saveFormData['formcreator_field_' . $question['question']->getID()] = $question['expected'];
         }
      }
      $saveFormData['formcreator_form'] = $form->getID();
      $form->saveForm($saveFormData);

      // Check urgency for each target ticket
      foreach ($urgencyQuestions as $question) {
         $targetTicket = $question['targetTicket'];
         $targetName = $targetTicket->getField('name');
         $ticket = new Ticket();
         $ticket->getFromDBByCrit(['name' => $targetName]);
         $this->assertFalse($ticket->isNewItem());
         $this->assertEquals($question['expected'], $ticket->getField('urgency'));
      }
   }

}
