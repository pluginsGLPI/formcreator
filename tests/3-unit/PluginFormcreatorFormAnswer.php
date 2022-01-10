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
use PluginFormcreatorForm;

class PluginFormcreatorFormAnswer extends CommonTestCase {
   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testSaveForm':
         case 'testGetFullForm':
         case 'testCanValidate':
         case 'testIsFieldVisible':
         case 'testPost_UpdateItem':
            $this->login('glpi', 'glpi');
      }
   }

   public function providerGetFullForm() {
      $form = $this->getForm();
      $section1 = $this->getSection([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
         'name' => \Toolbox::addslashes_deep("section 1"),
      ]);
      $question1 = $this->getQuestion([
         \PluginFormcreatorSection::getForeignKeyField() => $section1->getID(),
         'name' => \Toolbox::addslashes_deep("radios for section"),
         'fieldtype'  => 'radios',
         'values'     => 'yes\r\nno',
      ]);
      $question2 = $this->getQuestion([
         \PluginFormcreatorSection::getForeignKeyField() => $section1->getID(),
         'name' => \Toolbox::addslashes_deep("radios for question"),
         'fieldtype'  => 'radios',
         'values'     => 'yes\r\nno',
      ]);
      $section2 = $this->getSection([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
         'name' => \Toolbox::addslashes_deep("section 2"),
         'show_rule' => \PluginFormcreatorCondition::SHOW_RULE_HIDDEN,
         '_conditions' => [
            'plugin_formcreator_questions_id' => [$question1->getID()],
            'show_condition' => [\PluginFormcreatorCondition::SHOW_CONDITION_EQ],
            'show_value'     => ['yes'],
            'show_logic'     => [\PluginFormcreatorCondition::SHOW_LOGIC_AND],
         ]
      ]);
      $question3 = $this->getQuestion([
         \PluginFormcreatorSection::getForeignKeyField() => $section2->getID(),
         'name' => \Toolbox::addslashes_deep("text"),
         'fieldtype'  => 'text',
         'values'     => 'hello',
         'show_rule' => \PluginFormcreatorCondition::SHOW_RULE_HIDDEN,
         '_conditions' => [
            'plugin_formcreator_questions_id' => [$question2->getID()],
            'show_condition' => [\PluginFormcreatorCondition::SHOW_CONDITION_EQ],
            'show_value'     => ['yes'],
            'show_logic'     => [\PluginFormcreatorCondition::SHOW_LOGIC_AND],
         ]
      ]);

      return [
         // fullForm matches all question and section names
         [
            'answer' => [
               \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
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
            'answer' => [
               \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
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
            'answer' => [
               \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
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
      \PluginFormcreatorFields::resetVisibilityCache();
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
      $form = new \PluginFormcreatorForm();
      $form->getFromDBByQuestion($question);
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
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
      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswerId = $formAnswer->add($input);
      $this->boolean($formAnswer->isNewItem())->isFalse();

      // check existence of generated target
      // - ticket
      $item_ticket = new \Item_Ticket;
      $this->boolean($item_ticket->getFromDBByCrit([
         'itemtype' => \PluginFormcreatorFormAnswer::class,
         'items_id' => $formAnswerId,
      ]))->isTrue();
      $ticket = new \Ticket;
      $this->boolean($ticket->getFromDB($item_ticket->fields['tickets_id']))->isTrue();
      $this->string($ticket->fields['content'])->contains($answer);

      // - change
      $change_item = new \Change_Item;
      $this->boolean($change_item->getFromDBByCrit([
         'itemtype' => \PluginFormcreatorFormAnswer::class,
         'items_id' => $formAnswerId,
      ]))->isTrue();
      $change = new \Change;
      $this->boolean($change->getFromDB($change_item->fields['changes_id']))->isTrue();
      $this->string($change->fields['content'])->contains($answer);

      // - issue
      $issue = new \PluginFormcreatorIssue;
      $this->boolean($issue->getFromDBByCrit([
        'itemtype' => \Ticket::class,
        'items_id'  => $ticket->getID()
      ]))->isTrue();

      $CFG_GLPI['use_notifications'] = $use_notifications;
   }

   public function providerCanValidate() {
      $validatorUserId = 42;
      $group = new \Group();
      $group->add([
         'name' => $this->getUniqueString(),
      ]);
      $form1 = $this->getForm([
         'validation_required' => \PluginFormcreatorForm::VALIDATION_USER,
         '_validator_users' => $validatorUserId
      ]);

      $form2 = $this->getForm([
         'validation_required' => \PluginFormcreatorForm::VALIDATION_GROUP,
         '_validator_groups' => $group->getID()
      ]);
      $groupUser = new \Group_User();
      $groupUser->add([
         'users_id' => $validatorUserId,
         'groups_id' => $group->getID(),
      ]);

      return [
         [
            'right'          => \TicketValidation::VALIDATEINCIDENT,
            'loginUserId'    => $validatorUserId,
            'validatorId'    => $validatorUserId,
            'form'           => $form1,
            'expected'       => true,
         ],
         [
            'right'          => \TicketValidation::VALIDATEINCIDENT,
            'loginUserId'    => $validatorUserId,
            'validatorId'    => $group->getID(),
            'form'           => $form2,
            'expected'       => true,
         ],
         [
            'right'          => \TicketValidation::VALIDATEINCIDENT,
            'loginUserId'    => $validatorUserId + 1,
            'validatorId'    => $group->getID(),
            'form'           => $form2,
            'expected'       => false,
         ],
         [
            'right'          => \TicketValidation::VALIDATEREQUEST,
            'loginUserId'    => $validatorUserId,
            'validatorId'    => $group->getID(),
            'form'           => $form2,
            'expected'       => true,
         ],
         [
            'right'          => \TicketValidation::VALIDATEREQUEST | \TicketValidation::VALIDATEINCIDENT,
            'loginUserId'    => $validatorUserId,
            'validatorId'    => $group->getID(),
            'form'           => $form2,
            'expected'       => true,
         ],
         [
            'right'          => \TicketValidation::VALIDATEREQUEST | \TicketValidation::VALIDATEINCIDENT,
            'loginUserId'    => $validatorUserId + 1,
            'validatorId'    => $group->getID(),
            'form'           => $form2,
            'expected'       => false,
         ],         [
            'right'          => 0,
            'loginUserId'    => $validatorUserId,
            'validatorId'    => $group->getID(),
            'form'           => $form2,
            'expected'       => false,
         ],
      ];
   }

   /**
    * @dataProvider providerCanValidate
    */
   public function testCanValidate($right, $loginUserId, $validatorId, $form, $expected) {
      // Save answers for a form
      $instance = $this->newTestedInstance();
      $input = [
         'plugin_formcreator_forms_id' => $form->getID(),
         'formcreator_validator' => $validatorId,
      ];
      $fields = $form->getFields();
      foreach ($fields as $id => $question) {
         $fields[$id]->parseAnswerValues($input);
      }
      $formAnswerId = $instance->add($input);

      // test canValidate against user
      $_SESSION['glpiID'] = $loginUserId;
      $_SESSION['glpiactiveprofile']['ticketvalidation'] = $right;
      $instance = $this->newTestedInstance();
      $instance->getFromDB($formAnswerId);
      $this->boolean($instance->isNewItem())->isFalse();
      $output = $instance->canValidate();
      $this->boolean($output)->isEqualTo($expected);
   }

   public function testGetMyLastAnswersAsRequester() {
      // Create a form
      $this->login('glpi', 'glpi');
      $form = $this->getForm();

      // Add some form answers
      $userName = $this->getUniqueString();
      $this->getUser($userName);
      $this->login($userName, 'p@ssw0rd');

      $formAnswers = [];
      $formAnswer1 = $this->newTestedInstance();
      $formAnswers[] = $formAnswer1->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $formAnswer2 = $this->newTestedInstance();
      $formAnswers[] = $formAnswer2->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      // Check the count of result matches the expected count
      $output = \PluginFormcreatorFormAnswer::getMyLastAnswersAsRequester();
      foreach ($output as $row) {
         $this->boolean(in_array($row['id'], $formAnswers))->isTrue();
      }
      $this->integer(count($output))->isEqualTo(count($formAnswers));
   }

   public function testGetMyLastAnswersAsValidator() {
      // Create a form
      $this->login('glpi', 'glpi');
      $user = $this->getUser($this->getUniqueString(), 'p@ssw0rd', 'Technician');
      $validatorId = $user->getID();
      $form = $this->getForm([
         'validation_required' => \PluginFormcreatorForm_Validator::VALIDATION_USER,
         '_validator_users' => $validatorId,
      ]);

      // Add some form answers
      $this->login('normal', 'normal');
      $formAnswers = [];
      $formAnswer1 = $this->newTestedInstance();
      $formAnswers[] = $formAnswer1->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'formcreator_validator'       => $validatorId,
      ]);
      $formAnswer2 = $this->newTestedInstance();
      $formAnswers[] = $formAnswer2->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'formcreator_validator'       => $validatorId,
      ]);

      // Check the requester does not has his forms in list to validate
      $output = \PluginFormcreatorFormAnswer::getMyLastAnswersAsValidator();
      foreach ($output as $row) {
         $this->boolean(in_array($row['id'], $formAnswers))->isFalse();
      }

      $this->login($user->fields['name'], 'p@ssw0rd');
      // Check the validator does not has the forms in list to validate
      $output = \PluginFormcreatorFormAnswer::getMyLastAnswersAsValidator();
      foreach ($output as $row) {
         $this->boolean(in_array($row['id'], $formAnswers))->isTrue();
      }
   }

   public function testDeserialiseAnswers() {

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
      $form->getFromDBByQuestion($question);
      $formValidator = new \PluginFormcreatorForm_Validator();
      $formValidator->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'itemtype'                    => \User::class,
         'items_id'                    => \Session::getLoginUserID(),
      ]);
      $form->update([
         'id' => $form->getID(),
         'validation_required' => \PluginFormcreatorForm::VALIDATION_USER,
      ]);

      /**
       * Test updating a simple form answer
       */

      // Setup test
      $instance = $this->newTestedInstance();
      $formAnswerId = $instance->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'formcreator_validator'       => \Session::getLoginUserID(),
         'formcreator_field_' . $question->getID() => 'foo',
      ]);
      $this->integer((int) $formAnswerId);
      $answer = new \PluginFormcreatorAnswer();
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
      $input = [
         'plugin_formcreator_forms_id'             => $form->getID(),
         'accept_formanswer'                       => 'accept',
         'status'                                  => \PluginFormcreatorFormAnswer::STATUS_ACCEPTED,
      ];
      $input = $instance->prepareInputForUpdate($input);
      $this->array($input)->size->isGreaterThan(0);
      $instance->input = $input;
      $instance->post_updateItem();
      $answer = new \PluginFormcreatorAnswer();
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
         'status'                                  => \PluginFormcreatorFormAnswer::STATUS_ACCEPTED,
         'formcreator_field_' . $question->getID() => 'bar',
      ];
      $input = $instance->prepareInputForUpdate($input);
      $this->array($input)->size->isGreaterThan(0);
      $instance->input = $input;
      $instance->post_updateItem();
      $answer = new \PluginFormcreatorAnswer();
      $answer->getFromDBByCrit([
         'plugin_formcreator_formanswers_id' => $instance->getID(),
         'plugin_formcreator_questions_id'  => $question->getID(),
      ]);
      $this->boolean($answer->isNewItem())->isFalse();
      $this->string($answer->fields['answer'])->isEqualTo('bar');
   }
}
