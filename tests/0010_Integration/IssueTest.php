<?php
class IssueTest extends SuperAdminTestCase {


   /**
    *
    */
   public function testAddTicket() {
      $this->assertTrue(true);
      $ticket = new Ticket();
      $ticket->add(array(
            'name'      => 'ticket without form_answer',
            'content'   => 'My computer is down !'
      ));
      $this->assertFalse($ticket->isNewItem());

      $ticketId = $ticket->getID();
      $issue = new PluginFormcreatorIssue();
      // one and only one issue must exist. If several created, getFromDB will fail
      $issue->getFromDBByCrit([
            'AND' => [
                  'sub_itemtype' => Ticket::class,
                  'original_id' => $ticketId
            ]
      ]);
      $this->assertFalse($issue->isNewItem());
   }

   public function testAddFormAnswerWithoutTargetTicket() {
      // create a form with a target ticket
      $form = new PluginFormcreatorForm();
      $form->add(array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'form with 1 target ticket',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => 0
      ));
      $this->assertFalse($form->isNewItem());

      // answer the form (no matter it is empty)
      $formId = $form->getID();

      // saveForm returns true if form data is valid
      $this->assertTrue($form->saveForm(['formcreator_form'   => $formId]));

      // find the generated form answer
      $form_answer = new PluginFormcreatorForm_Answer();
      $form_answer->getFromDBByCrit(['plugin_formcreator_forms_id' => $formId]);
      $this->assertFalse($form_answer->isNewItem());

      // check an issue was created for the form answer
      $formanswerId = $form_answer->getID();
      $form_answerIssue = new PluginFormcreatorIssue();
      $rows = $form_answerIssue->find("`sub_itemtype` = 'PluginFormcreatorForm_Answer' AND `original_id` = '$formanswerId'");
      $this->assertCount(1, $rows);
   }

   public function testAddFormAnswerWithOneTargetTicket() {
      // create a form with a target ticket
      $form = new PluginFormcreatorForm();
      $form->add(array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'form with 1 target ticket',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => 0
      ));
      $this->assertFalse($form->isNewItem());

      $target = new PluginFormcreatorTarget();
      $target->add(array(
            'name'                        => 'target',
            'itemtype'                    => 'PluginFormcreatorTargetTicket',
            'plugin_formcreator_forms_id' => $form->getID(),
      ));
      $this->assertFalse($target->isNewItem());

      // answer the form (no matter it is empty)
      $formId = $form->getID();

      // saveForm returns true if form data is valid
      $this->assertTrue($form->saveForm(['formcreator_form'   => $formId]));

      // find the generated form answer
      $form_answer = new PluginFormcreatorForm_Answer();
      $form_answer->getFromDBByCrit(['plugin_formcreator_forms_id' => $formId]);
      $this->assertFalse($form_answer->isNewItem());

      // find the generated ticket
      $formanswerId = $form_answer->getID();
      $item_ticket = new Item_Ticket();
      $item_ticket->getFromDBByCrit([
         'AND' => [
            'itemtype' => PluginFormcreatorForm_Answer::class,
            'items_id' => $formanswerId
         ]
      ]);
      $this->assertFalse($item_ticket->isNewItem());
      $ticket = new Ticket();
      $ticket->getFromDB($item_ticket->getField('tickets_id'));
      $this->assertFalse($ticket->isNewItem());

      // check an issue was created for the ticket
      $ticketId = $ticket->getID();
      $ticketIssue = new PluginFormcreatorIssue();
      $ticketIssue->getFromDBByCrit([
         'AND' => [
            'sub_itemtype' => Ticket::class,
            'original_id'  => $ticketId
         ]
      ]);
      $this->assertFalse($ticketIssue->isNewItem());

      // check no issue was created for the form answer
      $form_answerIssue = new PluginFormcreatorIssue();
      $rows = $form_answerIssue->find("`sub_itemtype` = 'PluginFormcreatorForm_Answer' AND `original_id` = '$formanswerId'");
      $this->assertCount(0, $rows);
   }

   public function testAddFormAnswerWithSeveralTargetTickets() {
      // create form
      $form = new PluginFormcreatorForm();
      $form->add(array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'form with 2 target tickets',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => 0
      ));

      // create first target ticket
      $target = new PluginFormcreatorTarget();
      $target->add(array(
            'name'                        => 'target 1',
            'itemtype'                    => 'PluginFormcreatorTargetTicket',
            'plugin_formcreator_forms_id' => $form->getID(),
      ));
      $this->assertFalse($target->isNewItem());

      // create second target ticket
      $target = new PluginFormcreatorTarget();
      $target->add(array(
            'name'                        => 'target 2',
            'itemtype'                    => 'PluginFormcreatorTargetTicket',
            'plugin_formcreator_forms_id' => $form->getID(),
      ));
      $this->assertFalse($target->isNewItem());

      // answer the form (no matter it is empty)
      $formId = $form->getID();

      // saveForm returns true if form data is valid
      $this->assertTrue($form->saveForm(['formcreator_form'   => $formId]));

      // find the generated form answer
      $form_answer = new PluginFormcreatorForm_Answer();
      $form_answer->getFromDBByCrit(['plugin_formcreator_forms_id' => $formId]);
      $this->assertFalse($form_answer->isNewItem());

      // find the generated tickets
      $formanswerId = $form_answer->getID();
      $item_ticket = new Item_Ticket();
      $item_ticketRows = $item_ticket->find("`itemtype` = 'PluginFormcreatorForm_Answer' AND `items_id` = '$formanswerId'");
      $this->assertCount(2, $item_ticketRows);

      // check an issue was created for the form answer
      $form_answerIssue = new PluginFormcreatorIssue();
      $rows = $form_answerIssue->find("`sub_itemtype` = 'PluginFormcreatorForm_Answer' AND `original_id` = '$formanswerId'");
      $this->assertCount(1, $rows);

      // check no issue was created for each generatred ticket
      foreach ($item_ticketRows as $id => $row) {
         $ticketId = $row['tickets_id'];
         $rows = $form_answerIssue->find("`sub_itemtype` = 'Ticket' AND `original_id` = '$ticketId'");
         $this->assertCount(0, $rows);
      }
   }

   /**
    *
    */
   public function testDeleteTicket() {
      $this->assertTrue(true);
      $ticket = new Ticket();
      $ticket->add([
         'name'      => 'ticket to delete',
         'content'   => 'My computer is down (again) !'
      ]);
      $this->assertFalse($ticket->isNewItem());

      $ticketId = $ticket->getID();
      $issue = new PluginFormcreatorIssue();
      // one and only one issue must exist. If several created, getFromDB will fail
      $issue->getFromDBByCrit([
         'AND' => [
             'sub_itemtype' => Ticket::class,
            'original_id'   => $ticketId
         ]
      ]);
      $this->assertFalse($issue->isNewItem());

      $ticket->delete([
         'id' => $ticketId
      ]);

      $rows = $issue->find("`sub_itemtype` = 'Ticket' AND `original_id` = '$ticketId'");
      $this->assertCount(0, $rows);
   }

   public function testDeleteFormAnswer() {
      // create a form with a target ticket
      $form = new PluginFormcreatorForm();
      $form->add([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => 'form with 1 target ticket',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ]);
      $this->assertFalse($form->isNewItem());

      // answer the form (no matter it is empty)
      $formId = $form->getID();

      // saveForm returns true if form data is valid
      $this->assertTrue($form->saveForm(['formcreator_form'   => $formId]));

      // find the generated form answer
      $form_answer = new PluginFormcreatorForm_Answer();
      $form_answer->getFromDBByCrit(['plugin_formcreator_forms_id' => $formId]);
      $this->assertFalse($form_answer->isNewItem());

      // check an issue was created for the form answer
      $formanswerId = $form_answer->getID();
      $form_answerIssue = new PluginFormcreatorIssue();
      $rows = $form_answerIssue->find("`sub_itemtype` = 'PluginFormcreatorForm_Answer' AND `original_id` = '$formanswerId'");
      $this->assertCount(1, $rows);

      $form_answer->delete(array(
            'id'  => $formanswerId
      ));
   }

   public function testValidateFormAnswerSingleTargetTicket() {
      // create a form with a target ticket
      $userId = $_SESSION['glpiID'];
      $form = new PluginFormcreatorForm();
      $form->add(array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'form to validate with 1 target ticket',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => 1,
            '_validator_users'      => array($userId)
      ));
      $this->assertFalse($form->isNewItem());

      $target = new PluginFormcreatorTarget();
      $target->add(array(
            'name'                        => 'target',
            'itemtype'                    => 'PluginFormcreatorTargetTicket',
            'plugin_formcreator_forms_id' => $form->getID(),
      ));
      $this->assertFalse($target->isNewItem());

      // answer the form (no matter it is empty)
      $formId = $form->getID();
      $saveFormData = array(
         'formcreator_form'   => $formId,
         'formcreator_validator' => $_SESSION['glpiID']
      );

      // saveForm returns true if form data is valid
      $this->assertTrue($form->saveForm($saveFormData), json_encode($_SESSION['MESSAGE_AFTER_REDIRECT'], JSON_PRETTY_PRINT));

      // find the generated form answer
      $form_answer = new PluginFormcreatorForm_Answer();
      $form_answer->getFromDBByCrit(['plugin_formcreator_forms_id' => $formId]);
      $this->assertFalse($form_answer->isNewItem());

      // check no tickets are linked to the form answer
      $formanswerId = $form_answer->getID();
      $item_ticket = new Item_Ticket();
      $item_ticketRows = $item_ticket->find("`itemtype` = 'PluginFormcreatorForm_Answer' AND `items_id` = '$formanswerId'");
      $this->assertCount(0, $item_ticketRows);

      // check an issue was created for the form answer
      $form_answerIssue = new PluginFormcreatorIssue();
      $rows = $form_answerIssue->find("`sub_itemtype` = 'PluginFormcreatorForm_Answer' AND `original_id` = '$formanswerId'");
      $this->assertCount(1, $rows);

      // accept answers
      $input = array(
            'formcreator_form' => $formId
      );
      $form_answer->acceptAnswers($input);

      // find the generated ticket
      $item_ticket = new Item_Ticket();
      $item_ticket->getFromDBByCrit([
         'AND' => [
            'itemtype' => PluginFormcreatorForm_Answer::class,
            'items_id' => $formanswerId
         ]
      ]);
      $this->assertFalse($item_ticket->isNewItem());
      $ticket = new Ticket();
      $ticket->getFromDB($item_ticket->getField('tickets_id'));
      $this->assertFalse($ticket->isNewItem());

      // check an issue was created for the ticket
      $ticketId = $ticket->getID();
      $ticketIssue = new PluginFormcreatorIssue();
      $rows = $ticketIssue->find("`sub_itemtype` = 'Ticket' AND `original_id` = '$ticketId'");
      $this->assertCount(1, $rows);

      // check no issue was created for the form answer
      $form_answerIssue = new PluginFormcreatorIssue();
      $rows = $form_answerIssue->find("`sub_itemtype` = 'PluginFormcreatorForm_Answer' AND `original_id` = '$formanswerId'");
      $this->assertCount(0, $rows);
   }

   public function testValidateFormAnswerMultipleTargetTicket() {
      // create a form with a target ticket
      $userId = $_SESSION['glpiID'];
      $form = new PluginFormcreatorForm();
      $form->add(array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'form to validate with 2 target tickets',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => 1,
            '_validator_users'      => array($userId)
      ));
      $this->assertFalse($form->isNewItem());

      // create first target ticket
      $target = new PluginFormcreatorTarget();
      $target->add(array(
            'name'                        => 'target 1',
            'itemtype'                    => 'PluginFormcreatorTargetTicket',
            'plugin_formcreator_forms_id' => $form->getID(),
      ));
      $this->assertFalse($target->isNewItem());

      // create second target ticket
      $target = new PluginFormcreatorTarget();
      $target->add(array(
            'name'                        => 'target 2',
            'itemtype'                    => 'PluginFormcreatorTargetTicket',
            'plugin_formcreator_forms_id' => $form->getID(),
      ));
      $this->assertFalse($target->isNewItem());

      // answer the form (no matter it is empty)
      $formId = $form->getID();
      $saveFormData = [
         'formcreator_form'   => $formId,
         'formcreator_validator' => $_SESSION['glpiID']
      ];

      // saveForm returns true if form data is valid
      $this->assertTrue($form->saveForm($saveFormData), json_encode($_SESSION['MESSAGE_AFTER_REDIRECT'], JSON_PRETTY_PRINT));

      // find the generated form answer
      $form_answer = new PluginFormcreatorForm_Answer();
      $form_answer->getFromDBByCrit(['plugin_formcreator_forms_id' => $formId]);
      $this->assertFalse($form_answer->isNewItem());
      $formanswerId = $form_answer->getID();

      // check an issue was created for the form answer
      $form_answerIssue = new PluginFormcreatorIssue();
      $rows = $form_answerIssue->find("`sub_itemtype` = 'PluginFormcreatorForm_Answer' AND `original_id` = '$formanswerId'");
      $this->assertCount(1, $rows);

      // accept answers
      $input = array(
            'formcreator_form' => $formId
      );
      $form_answer->acceptAnswers($input);

      // check there is still an issue for the form answer
      $form_answerIssue = new PluginFormcreatorIssue();
      $rows = $form_answerIssue->find("`sub_itemtype` = 'PluginFormcreatorForm_Answer' AND `original_id` = '$formanswerId'");
      $this->assertCount(1, $rows);

      // find the generated tickets
      $formanswerId = $form_answer->getID();
      $item_ticket = new Item_Ticket();
      $item_ticketRows = $item_ticket->find("`itemtype` = 'PluginFormcreatorForm_Answer' AND `items_id` = '$formanswerId'");
      $this->assertCount(2, $item_ticketRows);

      // check no issue was created for each generatred ticket
      foreach ($item_ticketRows as $id => $row) {
         $ticketId = $row['tickets_id'];
         $rows = $form_answerIssue->find("`sub_itemtype` = 'Ticket' AND `original_id` = '$ticketId'");
         $this->assertCount(0, $rows);
      }

   }
}