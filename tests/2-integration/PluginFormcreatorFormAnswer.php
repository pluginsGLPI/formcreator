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
use PluginFormcreatorForm_Validator;
use PluginFormcreatorIssue;
use Search;
use TicketValidation;
use Ticket;

/**
 * The methods conflict when running in parallel
 * @engine inline
 */
class PluginFormcreatorFormAnswer extends CommonTestCase {

   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testNotificationFormAnswerCreated':
         case 'testOtherUserValidates':
            $this->boolean($this->login('glpi', 'glpi', true))->isTrue();
            break;
      }
   }

   public function afterTestMethod($method) {
      parent::afterTestMethod($method);
      switch ($method) {
         case 'testNotificationFormAnswerCreated':
            $user = new \User();
            $user->getFromDBbyName('glpi');
            $userEmail = new \Useremail();
            $userEmail->deleteByCriteria([
               'users_id' => $user->getID(),
            ]);
            break;
      }
   }

   public function testNotificationFormAnswerCreated() {
      global $DB, $CFG_GLPI;

      $user = new \User();
      $user->getFromDBbyName('glpi');
      $user->update([
         'id' => $user->getID(),
         '_useremails' => [$this->getUniqueEmail()],
      ]);

      $CFG_GLPI['use_notifications'] = '1';
      $CFG_GLPI['notifications_mailing'] = '1';

      $form = $this->getForm();

      // Answer the form
      $formAnswer = $this->newTestedInstance();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID()
      ]);

      // Check a notification was created with the expected template
      $result = $DB->request([
         'SELECT' => \Notification_NotificationTemplate::getTable() . '.' . \NotificationTemplate::getForeignKeyField(),
         'FROM' => \Notification_NotificationTemplate::getTable(),
         'INNER JOIN' => [
            \Notification::getTable() => [
               'FKEY' => [
                  \Notification::getTable() => 'id',
                  \Notification_NotificationTemplate::getTable() => \Notification::getForeignKeyField()
               ]
            ]
         ],
         'WHERE' => [
            'itemtype'  => \PluginFormcreatorFormAnswer::class,
            'event'     => 'plugin_formcreator_form_created',
         ]
      ]);
      $this->integer($result->count())->isEqualTo(1);
      $row = $result->current();

      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswer->getFromDBByCrit([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($formAnswer->isNewItem())->isFalse();
      $queued = new \QueuedNotification();
      $queued->getFromDBByCrit([
         \NotificationTemplate::getForeignKeyField() => $row[\NotificationTemplate::getForeignKeyField()],
         'itemtype'  => \PluginFormcreatorFormAnswer::class,
         'items_id'  => $formAnswer->getID(),
      ]);

      // Check the notification is linked to the expected itemtype
      $this->boolean($queued->isNewItem())->isFalse();
   }

   public function testOtherUserValidates() {
      $form = $this->getForm([
         'entities_id'         => $_SESSION['glpiactive_entity'],
         'name'                => __METHOD__,
         'description'         => 'form description',
         'content'             => 'a content',
         'is_active'           => 1,
         'validation_required' => \PluginFormcreatorForm_Validator::VALIDATION_USER,
         '_validator_users'    => '2', // user is glpi
      ]);

      $section = $this->getSection([
         'name'                        => 'a section',
         'plugin_formcreator_forms_id' => $form->getID()
      ]);
      $this->boolean($section->isNewItem())->isFalse();

      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'status'                      => 'waiting',
         'formcreator_validator'       => $_SESSION['glpiID'],
      ]);
      $this->boolean($formAnswer->isNewItem())->isFalse();

      // Reload the item
      $formAnswer->getFromDB($formAnswer->getID());

      $login = $this->getUniqueString();
      $user = new \User();
      $user->add([
         'name'                  => $login,
         'password'              => 'superadmin',
         'password2'             => 'superadmin',
         '_profiles_id'          => '4', // super admin profile
         '_entities_id'          => 0,
         '_is_recursive'         => 1,
      ]);
      $this->boolean($user->isNewItem())
         ->isFalse(json_encode(
            $_SESSION['MESSAGE_AFTER_REDIRECT'],
            JSON_PRETTY_PRINT));

      // Login as other user
      $this->boolean($this->login($login, 'superadmin', true))->isTrue();
      $this->boolean($formAnswer->canValidate($form, $formAnswer))->isFalse();

      // Login as glpi
      $this->boolean($this->login('glpi', 'glpi', true))->istrue();
      $this->boolean($formAnswer->canValidate($form, $formAnswer))->isTrue();

      // Login as normal
      $this->boolean($this->login('normal', 'normal', true))->istrue();
      $this->boolean($formAnswer->canValidate($form, $formAnswer))->isFalse();
   }

   public function testSearchMyLastAnswersAsRequester() {
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

      // Search for answers
      $criteria = [
         'criteria' => [
            0 => [
               'field'      => 4,
               'searchtype' => 'equals',
               'value'      => 'myself',
            ],
         ],
         'sort' => [
            0 => 6
         ],
         'order' => [
            0 => 'DESC'
         ],
      ];
      $showColumns = [
         2, // id
         1, // name
         6, // request date
         8, // status
      ];
      $backupListLimit = $_SESSION['glpilist_limit'];
      $_SESSION['glpilist_limit'] = 5;
      $search = Search::getDatas($this->getTestedClassName(), $criteria, $showColumns);
      $_SESSION['glpilist_limit'] = $backupListLimit;

      // Check the count of result matches the expected count
      foreach ($search['data']['items'] as $id => $order) {
         $this->boolean(in_array($id, $formAnswers))->isTrue();
      }
      $this->integer(count($search['data']['items']))->isEqualTo(count($formAnswers));
   }

   public function testGetMyLastAnswersAsValidator() {
      // Create a form
      $this->login('glpi', 'glpi');
      $user = $this->getUser($this->getUniqueString(), 'p@ssw0rd', 'Technician');
      $validatorId = $user->getID();
      $form = $this->getForm([
         'validation_required' => PluginFormcreatorForm_Validator::VALIDATION_USER,
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

      // Give validate right for the test
      // $_SESSION["glpiactiveprofile"][TicketValidation::$rightname] = TicketValidation::VALIDATEINCIDENT;
      $criteria = [
         'criteria' => [
            0 => [
               'field'      => 5,
               'searchtype' => 'equals',
               'value'      => 'myself',
            ],
            1 => [
               'link'       => 'OR',
               'field'      => 7,
               'searchtype' => 'equals',
               'value'      => 'mygroups',
            ],
         ],
         'sort' => [
            0 => 6
         ],
         'order' => [
            0 => 'DESC'
         ],
      ];
      $showColumns = [
         2, // id
         1, // name
         6, // request date
         8, // status
      ];
      $backupListLimit = $_SESSION['glpilist_limit'];
      $_SESSION['glpilist_limit'] = 5;
      $search = Search::getDatas($this->getTestedClassName(), $criteria, $showColumns);
      $_SESSION['glpilist_limit'] = $backupListLimit;

      // Check the requester does not has his forms in list to validate
      foreach ($search['data']['items'] as $id => $order) {
         $this->boolean(in_array($id, $formAnswers))->isTrue();
      }

      $this->login($user->fields['name'], 'p@ssw0rd');
      $backupListLimit = $_SESSION['glpilist_limit'];
      $_SESSION['glpilist_limit'] = 5;
      $search = Search::getDatas($this->getTestedClassName(), $criteria, $showColumns);
      $_SESSION['glpilist_limit'] = $backupListLimit;

      // Check the validator does not has the forms in list to validate
      foreach ($search['data']['items'] as $id => $order) {
         $this->boolean(in_array($id, $formAnswers))->isTrue();
      }
   }

   /**
    * Undocumented function
    *
    * @return void
    */
   public function testDeleteTicket() {
      $form = $this->getForm();

      $targetTicket1 = $this->getTargetTicket([
         $form::getForeignKeyField() => $form->getID(),
      ]);
      $formAnswer = $this->newTestedInstance();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($formAnswer->isNewItem())->isFalse();
      $this->array($formAnswer->targetList)->hasSize(1);
      /** @var Ticket */
      $ticket = $formAnswer->targetList[0];
      $issue = new PluginFormcreatorIssue();
      $issue->getFromDbByCrit([
         'itemtype' => $ticket::getType(),
         'items_id' => $ticket->getID(),
      ]);
      $this->boolean($issue->isNewItem())->isFalse();

      $ticket->delete([
         'id' => $ticket->getID(),
      ]);

      // Test the issue has been deleted
      $issue = new PluginFormcreatorIssue();
      $issue->getFromDbByCrit([
         'itemtype' => $ticket::getType(),
         'items_id' => $ticket->getID(),
      ]);
      $this->boolean($issue->isNewItem())->isTrue();

      // Add a 2nd ttarget ticket to the form
      $targetTicket2 = $this->getTargetTicket([
         $form::getForeignKeyField() => $form->getID(),
      ]);

      $formAnswer = $this->newTestedInstance();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($formAnswer->isNewItem())->isFalse();
      $this->array($formAnswer->targetList)->hasSize(2);
      $ticket = $formAnswer->targetList[0];
      $issue = new PluginFormcreatorIssue();
      $issue->getFromDbByCrit([
         'itemtype' => $formAnswer::getType(),
         'items_id' => $formAnswer->getID(),
      ]);
      $this->boolean($issue->isNewItem())->isFalse();

      $ticket->delete([
         'id' => $ticket->getID(),
      ]);

      // Test the issue still exists
      $issue = new PluginFormcreatorIssue();
      $issue->getFromDbByCrit([
         'itemtype' => $formAnswer::getType(),
         'items_id' => $formAnswer->getID(),
      ]);
      $this->boolean($issue->isNewItem())->isFalse();
   }

   /**
    * Undocumented function
    *
    * @return void
    */
   public function testRestoreTicket() {
      $form = $this->getForm();

      $targetTicket = $this->getTargetTicket([
        $form::getForeignKeyField() => $form->getID(),
      ]);
      $formAnswer = $this->newTestedInstance();
      $formAnswer->add([
        'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($formAnswer->isNewItem())->isFalse();
      $this->array($formAnswer->targetList)->hasSize(1);
      /** @var Ticket */
      $ticket = $formAnswer->targetList[0];
      $issue = new PluginFormcreatorIssue();
      $issue->getFromDbByCrit([
        'itemtype' => $ticket::getType(),
        'items_id' => $ticket->getID(),
      ]);
      $this->boolean($issue->isNewItem())->isFalse();

      $ticket->delete([
        'id' => $ticket->getID(),
      ]);

      // Test the issue has been deleted
      $issue = new PluginFormcreatorIssue();
      $issue->getFromDbByCrit([
        'itemtype' => $ticket::getType(),
        'items_id' => $ticket->getID(),
      ]);
      $this->boolean($issue->isNewItem())->isTrue();

      // Restore the ticket (triggers plugin's hook)
      $ticket->restore([
        'id' => $ticket->getID(),
      ]);

      // Test the issue has been recreated
      $issue = new PluginFormcreatorIssue();
      $issue->getFromDbByCrit([
        'itemtype' => $ticket::getType(),
        'items_id' => $ticket->getID(),
      ]);
      $this->boolean($issue->isNewItem())->isFalse();

      // Add a 2nd ttarget ticket to the form
      $targetTicket2 = $this->getTargetTicket([
        $form::getForeignKeyField() => $form->getID(),
      ]);

      $formAnswer = $this->newTestedInstance();
      $formAnswer->add([
        'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($formAnswer->isNewItem())->isFalse();
      $this->array($formAnswer->targetList)->hasSize(2);
      $ticket = $formAnswer->targetList[0];
      $issue = new PluginFormcreatorIssue();
      $issue->getFromDbByCrit([
        'itemtype' => $formAnswer::getType(),
        'items_id' => $formAnswer->getID(),
      ]);
      $this->boolean($issue->isNewItem())->isFalse();

      $ticket->delete([
        'id' => $ticket->getID(),
      ]);

      // Test the issue still exists
      $issue = new PluginFormcreatorIssue();
      $issue->getFromDbByCrit([
        'itemtype' => $formAnswer::getType(),
        'items_id' => $formAnswer->getID(),
      ]);
      $this->boolean($issue->isNewItem())->isFalse();

      // Restore the ticket (triggers plugin's hook)
      $ticket->restore([
        'id' => $ticket->getID(),
      ]);

      // Test there is still only 1 issue
      $issue = new PluginFormcreatorIssue();
      $issue->getFromDbByCrit([
        'itemtype' => $formAnswer::getType(),
        'items_id' => $formAnswer->getID(),
      ]);
      // If no issue or several issues matches the previous request,
      // then the issue is not populated from DB
      $this->boolean($issue->isNewItem())->isFalse();
   }
}
