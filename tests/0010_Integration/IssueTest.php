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
      $issue->getFromDBByQuery("WHERE `sub_itemtype` = 'Ticket' AND `original_id` = '$ticketId'");
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
      $_POST = array(
            'formcreator_form'   => $formId,
      );

      // saveForm returns true if form data is valid
      $this->assertTrue($form->saveForm());
      unset($_POST); // Don't disturb next tests

      // find the generated form answer
      $form_answer = new PluginFormcreatorForm_Answer();
      $form_answer->getFromDBByQuery("WHERE `plugin_formcreator_forms_id` = '$formId'");
      $this->assertFalse($form_answer->isNewItem());

      // check an issue was created for the form answer
      $formanswerId = $form_answer->getID();
      $form_answerIssue = new PluginFormcreatorIssue();
      $rows = $form_answerIssue->find("`sub_itemtype` = 'PluginFormcreatorForm_Answer' AND `original_id` = '$formanswerId'");
      $this->assertCount(1, $rows);
   }

   /**
    *
    */
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
      $_POST = array(
            'formcreator_form'   => $formId,
      );

      // saveForm returns true if form data is valid
      $this->assertTrue($form->saveForm());
      unset($_POST); // Don't disturb next tests

      // find the generated form answer
      $form_answer = new PluginFormcreatorForm_Answer();
      $form_answer->getFromDBByQuery("WHERE `plugin_formcreator_forms_id` = '$formId'");
      $this->assertFalse($form_answer->isNewItem());

      // find the generated ticket
      $formanswerId = $form_answer->getID();
      $item_ticket = new Item_Ticket();
      $item_ticket->getFromDBByQuery("WHERE `itemtype` = 'PluginFormcreatorForm_Answer' AND `items_id` = '$formanswerId'");
      $this->assertFalse($item_ticket->isNewItem());
      $ticket = new Ticket();
      $ticket->getFromDB($item_ticket->getField('tickets_id'));
      $this->assertFalse($ticket->isNewItem());

      // check an issue was created for the ticket
      $ticketId = $ticket->getID();
      $ticketIssue = new PluginFormcreatorIssue();
      $ticketIssue->getFromDBByQuery("WHERE `sub_itemtype` = 'Ticket' AND `original_id` = '$ticketId'");
      $this->assertFalse($ticketIssue->isNewItem());

      // check no issue was created for the form answer
      $form_answerIssue = new PluginFormcreatorIssue();
      $rows = $form_answerIssue->find("`sub_itemtype` = 'PluginFormcreatorForm_Answer' AND `original_id` = '$formanswerId'");
      $this->assertCount(0, $rows);
   }

   /**
    *
    */
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
      $_POST = array(
            'formcreator_form'   => $formId,
      );

      // saveForm returns true if form data is valid
      $this->assertTrue($form->saveForm());
      unset($_POST); // Don't disturb next tests

      // find the generated form answer
      $form_answer = new PluginFormcreatorForm_Answer();
      $form_answer->getFromDBByQuery("WHERE `plugin_formcreator_forms_id` = '$formId'");
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
}