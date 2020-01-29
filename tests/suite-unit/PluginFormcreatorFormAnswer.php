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

class PluginFormcreatorFormAnswer extends CommonTestCase {
   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testSaveForm':
            self::login('glpi', 'glpi');
      }
   }

   public function testGetFullForm() {
      $form = $this->getForm();
      $section = $this->getSection([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
         'name' => \Toolbox::addslashes_deep("section '1'"),
      ]);
      $question = $this->getQuestion([
         \PluginFormcreatorSection::getForeignKeyField() => $section->getID(),
         'name' => \Toolbox::addslashes_deep("question '1'"),
      ]);

      $instance = $this->newTestedInstance();
      $instance->add([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
         'formcreator_field_' . $question->getID() => ''
      ]);

      $questionId = $question->getID();

      $output = $instance->getFullForm(true);
      $this->string($output)->contains("section '1'");
      $this->string($output)->contains("##question_$questionId##");
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
        'sub_itemtype' => \Ticket::class,
        'original_id'  => $ticket->getID()
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
            'right'     => \TicketValidation::VALIDATEINCIDENT,
            'userId'    => $validatorUserId,
            'form'      => $form1,
            'expected'  => true,
         ],
         [
            'right'     => \TicketValidation::VALIDATEINCIDENT,
            'userId'    => $validatorUserId,
            'form'      => $form2,
            'expected'  => true,
         ],
         [
            'right'     => \TicketValidation::VALIDATEINCIDENT,
            'userId'    => $validatorUserId + 1,
            'form'      => $form2,
            'expected'  => false,
         ],
         [
            'right'     => \TicketValidation::VALIDATEREQUEST,
            'userId'    => $validatorUserId,
            'form'      => $form2,
            'expected'  => true,
         ],
         [
            'right'     => \TicketValidation::VALIDATEREQUEST | \TicketValidation::VALIDATEINCIDENT,
            'userId'    => $validatorUserId,
            'form'      => $form2,
            'expected'  => true,
         ],
         [
            'right'     => \TicketValidation::VALIDATEREQUEST | \TicketValidation::VALIDATEINCIDENT,
            'userId'    => $validatorUserId + 1,
            'form'      => $form2,
            'expected'  => false,
         ],         [
            'right'     => 0,
            'userId'    => $validatorUserId,
            'form'      => $form2,
            'expected'  => false,
         ],
      ];
   }

   /**
    * @dataProvider providerCanValidate
    */
   public function testCanValidate($right, $userId, $form, $expected) {
      // Save answers for a form
      $instance = $this->newTestedInstance();
      $input = [
         'plugin_formcreator_forms_id' => $form->getID(),
         'formcreator_validator' => $userId,
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
}
