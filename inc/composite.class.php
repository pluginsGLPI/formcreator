<?php
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorComposite
{
   private $item_targetTicket;

   private $targets = [];

   private $ticket_ticket;

   public function __construct(PluginFormcreatorItem_TargetTicket $item_targetTicket, Ticket_Ticket $ticket_ticket) {
      $this->item_targetTicket = $item_targetTicket;
      $this->ticket_ticket = $ticket_ticket;
   }

   /**
    * Add a target and generated target
    *
    * @param PluginFormcreatorTargetBase $target
    * @param CommonDBTM $generatedTarget
    */
   public function addTarget(PluginFormcreatorTargetBase $target, CommonDBTM $generatedTarget) {
      $itemtype = get_class($target);
      $this->targets[$itemtype][$target->getID()] = $generatedTarget;
   }

   public function buildCompositeRelations() {
      if (isset($this->targets['PluginFormcreatorTargetTicket'])) {
         foreach ($this->targets['PluginFormcreatorTargetTicket'] as $targetId => $generatedObject) {
            $rows = $this->item_targetTicket->find("`plugin_formcreator_targettickets_id` = '$targetId'");
            foreach ($rows as $row) {
               switch ($row['itemtype']) {
                  case 'Ticket':
                     $this->ticket_ticket->add([
                        'link' => $row['link'],
                        'tickets_id_1' => $generatedObject->getID(),
                        'tickets_id_2' => $row['items_id'],
                     ]);
                     break;

                  case 'PluginFormcreatorTargetTicket':
                     $ticket = $this->targets['PluginFormcreatorTargetTicket'][$row['items_id']];
                     $this->ticket_ticket->add([
                        'link' => $row['link'],
                        'tickets_id_1' => $generatedObject->getID(),
                        'tickets_id_2' => $ticket->getID(),
                     ]);
                     break;
               }
            }
         }
      }
   }
}
