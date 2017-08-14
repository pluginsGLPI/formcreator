<?php
class PluginFormcreatorTargetTicketTest extends SuperAdminTestCase {

   /**
    * Tests that deleting a target ticket of a form also deletes relations between tickets and generated tickets
    *
    * @covers PluginFormcreatorTargetTicket::pre_deleteItem
    */
   public function testDeleteLinkedTickets() {
      // setup the test
      $ticket = new Ticket();
      $ticket->add([
         'name'      => 'ticket',
         'content'   => 'help !'
      ]);
      $this->assertFalse($ticket->isNewItem());

      $form = new PluginFormcreatorForm();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $form->add([
         'name' => 'a form'
      ]);
      $this->assertFalse($form->isNewItem());

      $target_1 = new PluginFormcreatorTarget();
      $target_1->add([
         'name'      => 'target 1',
         $formFk     => $form->getID(),
         'itemtype'  => PluginFormcreatorTargetTicket::class,
      ]);
      $this->assertFalse($target_1->isNewItem());

      $target_2 = new PluginFormcreatorTarget();
      $target_2->add([
         'name'      => 'target 2',
         $formFk     => $form->getID(),
         'itemtype'  => PluginFormcreatorTargetTicket::class,
      ]);
      $this->assertFalse($target_2->isNewItem());

      $targetTicket_1 = new PluginFormcreatorTargetTicket();
      $targetTicket_1->getFromDB($target_1->getField('items_id'));
      $this->assertFalse($targetTicket_1->isNewItem());

      $targetTicket_2 = new PluginFormcreatorTargetTicket();
      $targetTicket_2->getFromDB($target_2->getField('items_id'));
      $this->assertFalse($targetTicket_2->isNewItem());

      $targetTicketFk = PluginFormcreatorTargetTicket::getForeignKeyField();
      $item_targetticket_1 = new PluginFormcreatorItem_TargetTicket();
      $item_targetticket_1->add([
         $targetTicketFk   => $targetTicket_1->getID(),
         'link'            => Ticket_Ticket::LINK_TO,
         'itemtype'        => Ticket::class,
         'items_id'        => $ticket->getID(),
      ]);
      $this->assertFalse($item_targetticket_1->isNewItem());

      $item_targetticket_2 = new PluginFormcreatorItem_TargetTicket();
      $item_targetticket_2->add([
         $targetTicketFk   => $targetTicket_1->getID(),
         'link'            => Ticket_Ticket::LINK_TO,
         'itemtype'        => PluginFormcreatorTargetTicket::class,
         'items_id'        => $targetTicket_2->getID(),
      ]);
      $this->assertFalse($item_targetticket_2->isNewItem());

      // delete the target ticket
      $target_1->delete(['id' => $target_1->getID()]);

      // Check the linked ticket or target ticket are deleted
      $this->assertFalse($item_targetticket_1->getFromDB($item_targetticket_1->getID()));
      $this->assertFalse($item_targetticket_2->getFromDB($item_targetticket_2->getID()));
   }
}