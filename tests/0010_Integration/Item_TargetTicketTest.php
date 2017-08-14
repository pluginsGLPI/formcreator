<?php
class PluginFormcreatorItem_TargetTicketTest extends SuperAdminTestCase {

   /**
    *
    */
   public function testBuildCompositeRelations() {
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
         'name'                  => 'a form',
         'validation_required'   => '0'
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

      // Answer the form
      $_POST['formcreator_form'] = $form->getID();
      $form->saveForm();

      // Check the tickets are generated
      $generatedTicket_1 = new Ticket();
      $generatedTicket_1->getFromDB($ticket->getID() + 1);
      $this->assertFalse($generatedTicket_1->isNewItem());

      $generatedTicket_2 = new Ticket();
      $generatedTicket_2->getFromDB($ticket->getID() + 2);
      $this->assertFalse($generatedTicket_2->isNewItem());

      // Check the generated tickets have expected relations
      $ticket_ticket_1 = new Ticket_Ticket();
      $ticket_ticket_1->getFromDBByCrit([
         'tickets_id_1' => $generatedTicket_1->getID(),
         'tickets_id_2' => $ticket->getID(),
         'link'         => Ticket_Ticket::LINK_TO
      ]);
      $this->assertFalse($ticket_ticket_1->isNewItem());

      $ticket_ticket_2 = new Ticket_Ticket();
      $ticket_ticket_2->getFromDBByCrit([
         'tickets_id_1' => $generatedTicket_1->getID(),
         'tickets_id_2' => $generatedTicket_2->getID(),
         'link'         => Ticket_Ticket::LINK_TO
      ]);
      $this->assertFalse($ticket_ticket_2->isNewItem());
   }
}