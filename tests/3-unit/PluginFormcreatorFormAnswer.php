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

use Change;
use Change_Item;
use CommonITILObject;
use Document;
use Document_Item;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;
use Group;
use Group_User;
use Item_Ticket;
use PluginFormcreatorAnswer;
use PluginFormcreatorCondition;
use PluginFormcreatorForm;
use PluginFormcreatorFields;
use PluginFormcreatorIssue;
use PluginFormcreatorSection;
use PluginFormcreatorTargetTicket;
use PluginFormcreatorTargetChange;
use PluginFormcreatorTargetProblem;
use Problem;
use Session;
use PluginFormcreatorForm_Validator;
use Ticket;
use TicketValidation;
use Toolbox;
use User;

class PluginFormcreatorFormAnswer extends CommonTestCase {
   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testSaveForm':
         case 'testGetFullForm':
         case 'testCanValidate':
         case 'testIsFieldVisible':
         case 'testPost_UpdateItem':
         case 'testPrepareInputForAdd':
         case 'testGetTargets':
         case 'testGetGeneratedTargets':
         case 'testGetAggregatedStatus':
         case 'testStatus':
            $this->login('glpi', 'glpi');
      }
   }

   public function providerPrepareInputForAdd() {
      $question = $this->getQuestion(['fieldtype' => 'text']);
      $form = new PluginFormcreatorForm();
      $form = PluginFormcreatorForm::getByItem($question);
      $this->boolean($form->isNewItem())->isFalse();
      $success = $form->update([
         'id' => $form->getID(),
         'formanswer_name' => '##answer_' . $question->getID() . '##',
      ]);
      $this->boolean($success)->isTrue();

      $testedClassName = $this->getTestedClassName();
      $data = [
         'form FK required' => [
            'input' => [],
            'expected' => false,
            'expectedMessage' => '',
         ],
         'tags parsing in name' => [
            'input' => [
               'plugin_formcreator_forms_id' => $form->getID(),
               'formcreator_field_' . $question->getID() => 'foo',
            ],
            'expected' => [
               'plugin_formcreator_forms_id'             => $form->getID(),
               'formcreator_field_' . $question->getID() => 'foo',
               'name'                                    => 'foo',
               'entities_id'                             => Session::getActiveEntity(),
               'is_recursive'                            => 0,
               'requester_id'                            => Session::getLoginUserID(),
               'users_id_validator'                      => 0,
               'groups_id_validator'                     => 0,
               'status'                                  => $testedClassName::STATUS_ACCEPTED,
               'request_date'                            => $_SESSION['glpi_currenttime'],
               'comment'                                 => '',
            ],
            'expectedMessage' => '',
         ],
      ];

      $question = $this->getQuestion(['fieldtype' => 'text']);
      $form = new PluginFormcreatorForm();
      $form = PluginFormcreatorForm::getByItem($question);
      $this->boolean($form->isNewItem())->isFalse();
      $user = new User();
      $user->getFromDBbyName('tech');
      $success = $form->update([
         'id'                  => $form->getID(),
         'validation_required' => PluginFormcreatorForm::VALIDATION_USER,
         '_validator_users'    => [$user->getID()] // glpi
      ]);
      $this->boolean($success)->isTrue();

      $data['unique validator user autoselection'] = [
         'input' => [
            'plugin_formcreator_forms_id' => $form->getID(),
            'formcreator_field_' . $question->getID() => 'foo',
         ],
         'expected' => [
            'plugin_formcreator_forms_id'             => $form->getID(),
            'formcreator_field_' . $question->getID() => 'foo',
            'name'                                    => $form->fields['name'],
            'entities_id'                             => Session::getActiveEntity(),
            'is_recursive'                            => 0,
            'requester_id'                            => Session::getLoginUserID(),
            'formcreator_validator'                   => $user::getType() . '_' . $user->getID(),
            'users_id_validator'                      => $user->getID(),
            'groups_id_validator'                     => 0,
            'status'                                  => $testedClassName::STATUS_APPROVAL,
            'request_date'                            => $_SESSION['glpi_currenttime'],
            'comment'                                 => '',
         ],
         'expectedMessage' => '',
      ];

      return $data;
   }

   /**
    * @dataProvider providerPrepareInputForAdd
    *
    * @param array $input
    * @param [type] $expected
    * @return void
    */
   public function testPrepareInputForAdd(array $input, $expected, $expectedMessage) {
      $instance = $this->newTestedInstance();
      $output = $instance->prepareInputForAdd($input);
      if ($expected === false) {
         $this->boolean($output)->isFalse();
         if ($expectedMessage != '') {
            $this->sessionHasMessage($expectedMessage, ERROR);
         }
         return;
      }

      $this->array($output)->isEqualTo($expected);
   }

   public function providerGetFullForm() {
      $form = $this->getForm();
      $section1 = $this->getSection([
         PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
         'name' => Toolbox::addslashes_deep("section 1"),
      ]);
      $question1 = $this->getQuestion([
         PluginFormcreatorSection::getForeignKeyField() => $section1->getID(),
         'name' => Toolbox::addslashes_deep("radios for section"),
         'fieldtype'  => 'radios',
         'values'     => 'yes\r\nno',
      ]);
      $question2 = $this->getQuestion([
         PluginFormcreatorSection::getForeignKeyField() => $section1->getID(),
         'name' => Toolbox::addslashes_deep("radios for question"),
         'fieldtype'  => 'radios',
         'values'     => 'yes\r\nno',
      ]);
      $section2 = $this->getSection([
         PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
         'name' => Toolbox::addslashes_deep("section 2"),
         'show_rule' => PluginFormcreatorCondition::SHOW_RULE_HIDDEN,
         '_conditions' => [
            'plugin_formcreator_questions_id' => [$question1->getID()],
            'show_condition' => [PluginFormcreatorCondition::SHOW_CONDITION_EQ],
            'show_value'     => ['yes'],
            'show_logic'     => [PluginFormcreatorCondition::SHOW_LOGIC_AND],
         ]
      ]);
      $question3 = $this->getQuestion([
         PluginFormcreatorSection::getForeignKeyField() => $section2->getID(),
         'name' => Toolbox::addslashes_deep("text"),
         'fieldtype'  => 'text',
         'values'     => 'hello',
         'show_rule' => PluginFormcreatorCondition::SHOW_RULE_HIDDEN,
         '_conditions' => [
            'plugin_formcreator_questions_id' => [$question2->getID()],
            'show_condition' => [PluginFormcreatorCondition::SHOW_CONDITION_EQ],
            'show_value'     => ['yes'],
            'show_logic'     => [PluginFormcreatorCondition::SHOW_LOGIC_AND],
         ]
      ]);

      return [
         // fullForm matches all question and section names
         [
            'answers' => [
               PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
               'formcreator_field_' . $question1->getID() => 'yes',
               'formcreator_field_' . $question2->getID() => 'yes',
               'formcreator_field_' . $question3->getID() => 'foo',
            ],
            'expected' => function($output) use($section1, $section2, $question1, $question2, $question3) {
               $this->string($output)->contains($section1->fields['name']);
               $this->string($output)->contains('##question_' . $question1->getID() . '##');
               $this->string($output)->contains('##question_' . $question2->getID() . '##');
               $this->string($output)->contains($section2->fields['name']);
               $this->string($output)->contains('##question_' . $question3->getID() . '##');
            }
         ],
         // fullForm matches only visible section names
         [
            'answers' => [
               PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
               'formcreator_field_' . $question1->getID() => 'no',
               'formcreator_field_' . $question2->getID() => 'yes',
               'formcreator_field_' . $question3->getID() => 'foo',
            ],
            'expected' => function($output) use($section1, $section2, $question1, $question2, $question3) {
               $this->string($output)->contains($section1->fields['name']);
               $this->string($output)->contains('##question_' . $question1->getID() . '##');
               $this->string($output)->contains('##question_' . $question2->getID() . '##');
               $this->string($output)->notContains($section2->fields['name']);
               $this->string($output)->notContains('##question_' . $question3->getID() . '##');
            }
         ],
         // fullForm matches only visible question names
         [
            'answers' => [
               PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
               'formcreator_field_' . $question1->getID() => 'yes',
               'formcreator_field_' . $question2->getID() => 'no',
               'formcreator_field_' . $question3->getID() => 'foo',
            ],
            'expected' => function($output) use($section1, $section2, $question1, $question2, $question3) {
               $this->string($output)->contains($section1->fields['name']);
               $this->string($output)->contains('##question_' . $question1->getID() . '##');
               $this->string($output)->contains('##question_' . $question2->getID() . '##');
               $this->string($output)->contains($section2->fields['name']);
               $this->string($output)->notContains('##question_' . $question3->getID() . '##');
            }
         ],
      ];
   }

   /**
    * @dataProvider providerGetFullForm
    */
   public function testGetFullForm($answers, $expected) {
      $instance = $this->newTestedInstance();
      $output = $instance->add($answers);
      $this->boolean($instance->isNewItem())->isFalse();
      PluginFormcreatorFields::resetVisibilityCache();
      $output = $instance->getFullForm(true);
      $expected($output);
   }

   public function testSaveForm() {
      global $CFG_GLPI;

      // disable notifications as we may fail in some case (not the purpose of this test btw)
      $use_notifications = $CFG_GLPI['use_notifications'];
      $CFG_GLPI['use_notifications'] = 0;

      // prepare a form with targets
      $question = $this->getQuestion();
      $form = new PluginFormcreatorForm();
      $form  = PluginFormcreatorForm::getByItem($question);
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $this->getTargetTicket([
         $formFk => $form->getID(),
      ]);
      $this->getTargetChange([
         $formFk => $form->getID(),
      ]);

      // prepare input
      $answer = 'test answer to question';
      $input = [
         $formFk => $form->getID(),
         'formcreator_field_'.$question->getID() => $answer
      ];

      // send form answer
      $formAnswer = $this->newTestedInstance();
      $formAnswerId = $formAnswer->add($input);
      $this->boolean($formAnswer->isNewItem())->isFalse();

      // check existence of generated target
      // - ticket
      $item_ticket = new Item_Ticket;
      $this->boolean($item_ticket->getFromDBByCrit([
         'itemtype' => $formAnswer::getType(),
         'items_id' => $formAnswerId,
      ]))->isTrue();
      $ticket = new Ticket;
      $this->boolean($ticket->getFromDB($item_ticket->fields['tickets_id']))->isTrue();
      $this->string($ticket->fields['content'])->contains($answer);

      // - change
      $change_item = new Change_Item;
      $this->boolean($change_item->getFromDBByCrit([
         'itemtype' => $formAnswer::getType(),
         'items_id' => $formAnswerId,
      ]))->isTrue();
      $change = new Change;
      $this->boolean($change->getFromDB($change_item->fields['changes_id']))->isTrue();
      $this->string($change->fields['content'])->contains($answer);

      // - issue
      $issue = new PluginFormcreatorIssue;
      $this->boolean($issue->getFromDBByCrit([
        'itemtype' => Ticket::class,
        'items_id'  => $ticket->getID()
      ]))->isTrue();

      $CFG_GLPI['use_notifications'] = $use_notifications;
   }

   public function providerCanValidate() {
      $validatorUserId = 5; // normal
      $form1 = $this->getForm([
         'validation_required' => PluginFormcreatorForm::VALIDATION_USER,
         '_validator_users' => $validatorUserId
      ]);
      $this->boolean($form1->isNewItem())->isFalse();

      $group = new Group();
      $group->add([
         'name' => $this->getUniqueString(),
      ]);
      $this->boolean($group->isNewItem())->isFalse();
      $groupUser = new Group_User();
      $groupUser->add([
         'users_id' => $validatorUserId,
         'groups_id' => $group->getID(),
      ]);
      $form2 = $this->getForm([
         'validation_required' => PluginFormcreatorForm::VALIDATION_GROUP,
         '_validator_groups' => $group->getID()
      ]);
      $this->boolean($form2->isNewItem())->isFalse();

      return [
         'having validate incident right, validator user can validate' => [
            'right'     => TicketValidation::VALIDATEINCIDENT,
            'validator' => $validatorUserId,
            'userId'    => $validatorUserId,
            'form'      => $form1,
            'expected'  => true,
         ],
         'having validate incident right, member of a validator group can validate' => [
            'right'     => TicketValidation::VALIDATEINCIDENT,
            'validator' => $group->getID(),
            'userId'    => $validatorUserId,
            'form'      => $form2,
            'expected'  => true,
         ],
         'having validate incident right, not a validator user cannot validate' => [
            'right'     => TicketValidation::VALIDATEINCIDENT,
            'validator' => $group->getID(),
            'userId'    => 2, // glpi
            'form'      => $form2,
            'expected'  => false,
         ],
         'having validate request right, member of a validator group can validate' => [
            'right'     => TicketValidation::VALIDATEREQUEST,
            'validator' => $group->getID(),
            'userId'    => $validatorUserId,
            'form'      => $form2,
            'expected'  => true,
         ],
         'having validate request right and validate incident, member of a validator group can validate' => [
            'right'     => TicketValidation::VALIDATEREQUEST | TicketValidation::VALIDATEINCIDENT,
            'validator' => $group->getID(),
            'userId'    => $validatorUserId,
            'form'      => $form2,
            'expected'  => true,
         ],
         'having validate request right and validate incident, not member of a validator group can validate' => [
            'right'     => TicketValidation::VALIDATEREQUEST | TicketValidation::VALIDATEINCIDENT,
            'validator' => $group->getID(),
            'userId'    => 2, // glpi
            'form'      => $form2,
            'expected'  => false,
         ],
         'having no validation right, member of a validator group cannot validate' => [
            'right'     => 0,
            'validator' => $group->getID(),
            'userId'    => $validatorUserId,
            'form'      => $form2,
            'expected'  => false,
         ],
         'having no validation right, a validator user cannot validate' => [
            'right'     => 0,
            'validator' => $group->getID(),
            'userId'    => $validatorUserId,
            'form'      => $form2,
            'expected'  => false,
         ],
      ];
   }

   /**
    * @dataProvider providerCanValidate
    */
   public function testCanValidate($right, $validator, $userId, $form, $expected) {
      // Save answers for a form
      $instance = $this->newTestedInstance();
      $input = [
         'plugin_formcreator_forms_id' => $form->getID(),
         'formcreator_validator' => $validator,
      ];
      $fields = $form->getFields();
      foreach ($fields as $id => $question) {
         $fields[$id]->parseAnswerValues($input);
      }
      $formAnswerId = $instance->add($input);

      // test canValidate
      $_SESSION['glpiID'] = $userId;
      $_SESSION['glpiactiveprofile']['ticketvalidation'] = $right;
      $instance = $this->newTestedInstance();
      $instance->getFromDB($formAnswerId);
      $this->boolean($instance->isNewItem())->isFalse();
      $output = $instance->canValidate();
      $this->boolean($output)->isEqualTo($expected);
   }

   public function testIsFieldVisible() {
      $instance = $this->newTestedInstance();

      // Check exceptions are properly thrown
      $this->exception(
         function() use ($instance) {
            $instance->isFieldVisible(42);
         }
      )->isInstanceOf(\RuntimeException::class);
      $this->string($this->exception->getMessage())->isEqualTo('Instance is empty');

      // Check exceptions are properly thrown
      $form = $this->getForm();
      $instance->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      $this->exception(
         function() use ($instance) {
            $instance->isFieldVisible(42);
         }
      )->isInstanceOf(\RuntimeException::class);
      $this->string($this->exception->getMessage())->isEqualTo('Question not found');
   }

   public function testPost_UpdateItem() {
      $question = $this->getQuestion(['fieldtype' => 'text']);
      $form = new PluginFormcreatorForm;
      $form = PluginFormcreatorForm::getByItem($question);
      // $formValidator = new PluginFormcreatorForm_Validator();
      // $formValidator->add([
      //    'plugin_formcreator_forms_id' => $form->getID(),
      //    'itemtype'                    => User::class,
      //    'items_id'                    => Session::getLoginUserID(),
      // ]);
      $form->update([
         'id' => $form->getID(),
         'validation_required' => PluginFormcreatorForm::VALIDATION_USER,
         '_validator_users' => Session::getLoginUserID(),
      ]);

      /**
       * Test updating a simple form answer
       */

      // Setup test
      $instance = $this->newTestedInstance();
      $formAnswerId = $instance->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'formcreator_validator'       => Session::getLoginUserID(),
         'formcreator_field_' . $question->getID() => 'foo',
      ]);
      $this->integer((int) $formAnswerId);
      $answer = new PluginFormcreatorAnswer();
      $answer->getFromDBByCrit([
         'plugin_formcreator_formanswers_id' => $instance->getID(),
         'plugin_formcreator_questions_id'  => $question->getID(),
      ]);
      $this->boolean($answer->isNewItem())->isFalse();
      $this->string($answer->fields['answer'])->isEqualTo('foo');

      // check the answer is kept when accepting without edition
      $instance = $this->newTestedInstance();
      $instance->getFromDB($formAnswerId);
      $this->boolean($instance->isNewItem())->isFalse();
      $testedClassName = $this->getTestedClassName();
      $input = [
         'plugin_formcreator_forms_id'             => $form->getID(),
         'accept_formanswer'                       => 'accept',
         'status'                                  => $testedClassName::STATUS_ACCEPTED,
      ];
      $input = $instance->prepareInputForUpdate($input);
      $this->array($input)->size->isGreaterThan(0);
      $instance->input = $input;
      $instance->post_updateItem();
      $answer = new PluginFormcreatorAnswer();
      $answer->getFromDBByCrit([
         'plugin_formcreator_formanswers_id' => $instance->getID(),
         'plugin_formcreator_questions_id'  => $question->getID(),
      ]);
      $this->boolean($answer->isNewItem())->isFalse();
      $this->string($answer->fields['answer'])->isEqualTo('foo');

      // check the answer is actually changed when accepting with edition
      $instance = $this->newTestedInstance();
      $instance->getFromDB($formAnswerId);
      $this->boolean($instance->isNewItem())->isFalse();
      $input = [
         'plugin_formcreator_forms_id'             => $form->getID(),
         'accept_formanswer'                       => 'accept',
         'status'                                  => $testedClassName::STATUS_ACCEPTED,
         'formcreator_field_' . $question->getID() => 'bar',
      ];
      $input = $instance->prepareInputForUpdate($input);
      $this->array($input)->size->isGreaterThan(0);
      $instance->input = $input;
      $instance->post_updateItem();
      $answer = new PluginFormcreatorAnswer();
      $answer->getFromDBByCrit([
         'plugin_formcreator_formanswers_id' => $instance->getID(),
         'plugin_formcreator_questions_id'  => $question->getID(),
      ]);
      $this->boolean($answer->isNewItem())->isFalse();
      $this->string($answer->fields['answer'])->isEqualTo('bar');
   }

   public function testGetTargets() {
      global $CFG_GLPI;

      $CFG_GLPI['use_notifications'] = 0;

      // Prepare test context
      // A form with 2 targets of each available type
      // and a form answer for this form
      $form = $this->getForm();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $targets = [];

      $targets[] = $this->getTargetTicket([
         $formFk => $form->getID(),
      ]);
      $targets[] = $this->getTargetTicket([
         $formFk => $form->getID(),
      ]);

      $targets[] = $this->getTargetChange([
         $formFk => $form->getID(),
      ]);
      $targets[] = $this->getTargetChange([
         $formFk => $form->getID(),
      ]);

      $targets[] = $this->getTargetProblem([
         $formFk => $form->getID(),
      ]);
      $targets[] = $this->getTargetProblem([
         $formFk => $form->getID(),
      ]);

      $instance = $this->newTestedInstance();
      $instance->add([
         $formFk => $form->getID(),
      ]);

      $output = $instance->getGeneratedTargets();

      $this->array($output)->hasSize(count($targets));
      $typeCount = [
         Ticket::getType()  => 0,
         Change::getType()  => 0,
         Problem::getType() => 0,
      ];
      foreach ($output as $generatedTarget) {
         $typeCount[$generatedTarget::getType()]++;
      }
      $this->array($typeCount)->isEqualTo([
         Ticket::getType()  => 2,
         Change::getType()  => 2,
         Problem::getType() => 2,
      ]);
   }

   public function testGetGeneratedTargets() {
      $form = $this->getForm();
      $targets = [];
      $targets[1] = $this->getTargetTicket([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $targets[2] = $this->getTargetChange([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $targets[3] = $this->getTargetProblem([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      $instance = $this->newTestedInstance();
      $output = $instance->getGeneratedTargets();
      $this->array($output)->hasSize(0);

      $instance->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($instance->isNewItem())->isFalse();
      $generatedTargets = $instance->targetList;

      $output = $instance->getGeneratedTargets();
      $this->array($instance->targetList)->hasSize(count($generatedTargets));
      $this->array($output)->hasSize(3);

      $output = $instance->getGeneratedTargets([PluginFormcreatorTargetTicket::getType()]);
      $this->array($output)->hasSize(1);

      $output = $instance->getGeneratedTargets([PluginFormcreatorTargetChange::getType()]);
      $this->array($output)->hasSize(1);

      $output = $instance->getGeneratedTargets([PluginFormcreatorTargetProblem::getType()]);
      $this->array($output)->hasSize(1);
   }

   public function testGetAggregatedStatus() {
      // When no target defined
      $form = $this->getForm();
      $instance = $this->newTestedInstance();
      $instance->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($instance->isNewItem())->isFalse();
      $output = $instance->getAggregatedStatus();
      $this->variable($output)->isNull();

      // When several targets
      $form = $this->getForm();
      $targetTickets = [];
      for ($i = 1; $i <= 3; $i++) {
         $targetTickets[$i] = $this->getTargetTicket([
            'plugin_formcreator_forms_id' => $form->getID(),
         ]);
      }

      $instance = $this->newTestedInstance();
      $instance->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      $tickets = $instance->targetList;

      $tickets[0]->update([
         'id'     => $tickets[0]->getID(),
         'status' => CommonITILObject::INCOMING,
      ]);
      $tickets[1]->update([
         'id'     => $tickets[1]->getID(),
         'status' => CommonITILObject::INCOMING,
      ]);
      $tickets[2]->update([
         'id'     => $tickets[2]->getID(),
         'status' => CommonITILObject::INCOMING,
      ]);
      $output = $instance->getAggregatedStatus();
      $this->integer($output)->isEqualTo(CommonITILObject::INCOMING);

      $tickets[0]->update([
         'id'     => $tickets[0]->getID(),
         'status' => CommonITILObject::ASSIGNED,
      ]);
      $tickets[1]->update([
         'id'     => $tickets[1]->getID(),
         'status' => CommonITILObject::INCOMING,
      ]);
      $tickets[2]->update([
         'id'     => $tickets[2]->getID(),
         'status' => CommonITILObject::INCOMING,
      ]);
      $output = $instance->getAggregatedStatus();
      $this->integer($output)->isEqualTo(CommonITILObject::INCOMING);

      $tickets[0]->update([
         'id'     => $tickets[0]->getID(),
         'status' => CommonITILObject::SOLVED,
      ]);
      $tickets[1]->update([
         'id'     => $tickets[1]->getID(),
         'status' => CommonITILObject::SOLVED,
      ]);
      $tickets[2]->update([
         'id'     => $tickets[2]->getID(),
         'status' => CommonITILObject::WAITING,
      ]);
      $output = $instance->getAggregatedStatus();
      $this->integer($output)->isEqualTo(CommonITILObject::WAITING);

      $tickets[0]->update([
         'id'     => $tickets[0]->getID(),
         'status' => CommonITILObject::SOLVED,
      ]);
      $tickets[1]->update([
         'id'     => $tickets[1]->getID(),
         'status' => CommonITILObject::PLANNED,
      ]);
      $tickets[2]->update([
         'id'     => $tickets[2]->getID(),
         'status' => CommonITILObject::WAITING,
      ]);
      $output = $instance->getAggregatedStatus();
      $this->integer($output)->isEqualTo(CommonITILObject::WAITING);

      $tickets[0]->update([
         'id'     => $tickets[0]->getID(),
         'status' => CommonITILObject::SOLVED,
      ]);
      $tickets[1]->update([
         'id'     => $tickets[1]->getID(),
         'status' => CommonITILObject::SOLVED,
      ]);
      $tickets[2]->update([
         'id'     => $tickets[2]->getID(),
         'status' => CommonITILObject::INCOMING,
      ]);
      $output = $instance->getAggregatedStatus();
      $this->integer($output)->isEqualTo(CommonITILObject::INCOMING);

      $tickets[0]->update([
         'id'     => $tickets[0]->getID(),
         'status' => CommonITILObject::INCOMING,
      ]);
      $tickets[1]->update([
         'id'     => $tickets[1]->getID(),
         'status' => CommonITILObject::ASSIGNED,
      ]);
      $tickets[2]->update([
         'id'     => $tickets[2]->getID(),
         'status' => CommonITILObject::PLANNED,
      ]);
      $output = $instance->getAggregatedStatus();
      $this->integer($output)->isEqualTo(CommonITILObject::PLANNED);

      $tickets[0]->update([
         'id'     => $tickets[0]->getID(),
         'status' => CommonITILObject::SOLVED,
      ]);
      $tickets[1]->update([
         'id'     => $tickets[1]->getID(),
         'status' => CommonITILObject::SOLVED,
      ]);
      $tickets[2]->update([
         'id'     => $tickets[2]->getID(),
         'status' => CommonITILObject::CLOSED,
      ]);
      $output = $instance->getAggregatedStatus();
      $this->integer($output)->isEqualTo(CommonITILObject::SOLVED);

      $tickets[0]->update([
         'id'     => $tickets[0]->getID(),
         'status' => CommonITILObject::CLOSED,
      ]);
      $tickets[1]->update([
         'id'     => $tickets[1]->getID(),
         'status' => CommonITILObject::CLOSED,
      ]);
      $tickets[2]->update([
         'id'     => $tickets[2]->getID(),
         'status' => CommonITILObject::CLOSED,
      ]);
      $output = $instance->getAggregatedStatus();
      $this->integer($output)->isEqualTo(CommonITILObject::CLOSED);
   }

   public function testGetFileProperties() {
      $question = $this->getQuestion([
         'fieldtype' => 'file',
      ]);
      $form = PluginFormcreatorForm::getByItem($question);
      $this->boolean($form->isNewItem())->isFalse();

      $fieldKey = 'formcreator_field_' . $question->getID();
      $filename = '5e5e92ffd9bd91.44444444upload55555555.txt';
      $tag = '3e29dffe-0237ea21-5e5e7034b1d1a1.33333333';
      copy(dirname(__DIR__) . '/fixture/upload.txt', GLPI_TMP_DIR . '/' . $filename);
      $formAnswer = $this->getFormAnswer([
         'plugin_formcreator_forms_id' => $form->getID(),
         "_{$fieldKey}" => [
            $filename,
         ],
         "_prefix_{$fieldKey}" => [
            '5e5e92ffd9bd91.44444444',
         ],
         "_tag_{$fieldKey}" => [
            $tag,
         ],
      ]);

      $documentItem = new Document_Item();
      $documentItem->getFromDBByCrit([
         'itemtype' => $formAnswer->getType(),
         'items_id' => $formAnswer->getID(),
      ]);
      $this->boolean($documentItem->isNewItem())->isFalse();
      $document = Document::getById($documentItem->fields['documents_id']);
      $output = $formAnswer->getFileProperties();
      $this->array($output)->isIdenticalTo([
         '_filename'     => [
            $question->getID() => [
               $document->fields['filename'],
            ],
         ],
         '_tag_filename' => [
            $question->getID() => [
               $document->fields['tag'],
            ],
         ],
      ]);
   }

   public function testGetFromDbByTicket() {
      // Create a form answer
      $targetTicket = $this->getTargetTicket();
      $form = PluginFormcreatorForm::getByItem($targetTicket);
      $expected = $this->newTestedInstance();
      $expected->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($expected->isNewItem())->isFalse();

      $ticket = $expected->targetList[0] ?? null;
      $this->object($ticket)->isInstanceOf(Ticket::class);

      $instance = $this->newTestedInstance();
      // Check the method works with an Ticket instance
      $output = $instance->getFromDbByTicket($ticket);
      $this->boolean($output)->isTrue();
      $this->integer($instance->getID())->isEqualTo($expected->getID());

      // Check the method works with a Ticket ID
      $output = $instance->getFromDbByTicket($ticket->getID());
      $this->boolean($output)->isTrue();
      $this->integer($instance->getID())->isEqualTo($expected->getID());
   }

   public function providerParseTags() {
      // Test a single text
      $question = $this->getQuestion([
         'fieldtype' => 'textarea',
      ]);
      $form = PluginFormcreatorForm::getByItem($question);
      // Text as received in prepareInputForAdd (GLPI 10.0.6)
      $text = '&#60;p&#62; &#60;/p&#62;\r\n&#60;p&#62; &#60;/p&#62;';

      $fieldKey = 'formcreator_field_' . $question->getID();
      $formAnswer = $this->getFormAnswer([
         'plugin_formcreator_forms_id' => $form->getID(),
         $fieldKey => $text,
      ]);

      yield [
         'instance' => $formAnswer,
         'template' => '<p>##answer_' . $question->getID() . '##</p>',
         'expected' => '&#60;p&#62;' . $text . '&#60;/p&#62;',
      ];

      // Test a text with an embeddd image
      $question = $this->getQuestion([
         'fieldtype' => 'textarea',
      ]);
      $form = PluginFormcreatorForm::getByItem($question);
      // Text as received in prepareInputForAdd (GLPI 10.0.6)
      $text = '&#60;p&#62;&#60;img id=\"20a8c58a-761764d0-63e0ff1245d9f4.97274571\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAACCAIAAAASFvFNAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAEUlEQVQImWP8v5QBApgYYAAAHsMBqH3ykQkAAAAASUVORK5CYII=\" data-upload_id=\"0.7092882231779103\"&#62;&#60;/p&#62;';

      $fieldKey = 'formcreator_field_' . $question->getID();
      $filename = '5e5e92ffd9bd91.44444444upload55555555.txt';
      $tag = '3e29dffe-0237ea21-5e5e7034b1d1a1.33333333';
      copy(dirname(__DIR__) . '/fixture/upload.txt', GLPI_TMP_DIR . '/' . $filename);
      $formAnswer = $this->getFormAnswer([
         'plugin_formcreator_forms_id' => $form->getID(),
         $fieldKey => $text,
         "_{$fieldKey}" => [
            $filename,
         ],
         "_prefix_{$fieldKey}" => [
            '5e5e92ffd9bd91.44444444',
         ],
         "_tag_{$fieldKey}" => [
            $tag,
         ],
      ]);

      yield [
         'instance' => $formAnswer,
         'template' => '<p>##answer_' . $question->getID() . '##</p>',
         'expected' => '&#60;p&#62;' . $text . '&#60;/p&#62;',
      ];
   }

   /**
    * @dataProvider providerParseTags
    */
   public function testParseTags($instance, $template, $expected) {
      $ticket = new PluginFormcreatorTargetTicket();

      $output = $instance->parseTags($template, $ticket, true);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerCanViewItem() {
      $this->login('glpi', 'glpi');
      $form = $this->getForm();
      $formAnswer = $this->getFormAnswer([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->logout();

      yield 'Not authenticated' => [
         'formAnswer' => $formAnswer,
         'expected'   => false,
      ];

      $this->login('glpi', 'glpi');

      yield 'User granted to edit forms' => [
         'formAnswer' => $formAnswer,
         'expected'   => true,
      ];

      $this->login('normal', 'normal');
      $formAnswer = $this->getFormAnswer([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      yield 'User is the requester' => [
         'formAnswer' => $formAnswer,
         'expected'   => true,
      ];

      $this->login('tech', 'tech');

      yield 'User is not the requester' => [
         'formAnswer' => $formAnswer,
         'expected'   => false,
      ];

      $form->update([
         'id' => $form->getID(),
         'validation_required' => PluginFormcreatorForm_Validator::VALIDATION_USER,
         '_validator_users' => [
            User::getIdByName('tech'),
         ],
      ]);
      $formAnswer = $this->getFormAnswer([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      yield 'User is the validator' => [
         'formAnswer' => $formAnswer,
         'expected'   => true,
      ];

      $this->login('normal', 'normal');

      yield 'User is not the validator' => [
         'formAnswer' => $formAnswer,
         'expected'   => false,
      ];

      $group = $this->getGlpiCoreItem(Group::class, [
         'name' => 'group' . $this->getUniqueString()
      ]);
      $user = $this->getGlpiCoreItem(User::class, [
         'name' => 'user' . $this->getUniqueString(),
         'password' => 'password',
         'password2' => 'password',
      ]);

      $form->update([
         'id' => $form->getID(),
         'validation_required' => PluginFormcreatorForm_Validator::VALIDATION_GROUP,
         '_validator_groups' => [
            $group->getID(),
         ],
      ]);
      $this->login('normal', 'normal');
      $formAnswer = $this->getFormAnswer([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->login($user->fields['name'], 'password');

      yield 'User is not a member of validator group' => [
         'formAnswer' => $formAnswer,
         'expected'   => false,
      ];

      $groupUser = new Group_User();
      $groupUser->add([
         'groups_id' => $group->getID(),
         'users_id' => $user->getID(),
      ]);

      yield 'User is a member of validator group' => [
         'formAnswer' => $formAnswer,
         'expected'   => true,
      ];
   }

   /**
    * @dataProvider providerCanViewItem
    */
   public function testCanViewItem($formAnswer, bool $expected) {
      /** @var \PluginFormcreatorFormAnswer $formAnswer */
      $output = $formAnswer->canViewItem();
      $this->boolean($output)->isEqualTo($expected);
   }
}
