<?php
class PluginFormcreatorTargetTicket_Actor extends PluginFormcreatorTarget_Actor
{
   protected function getTargetItem() {
      return new PluginFormcreatorTargetTicket();
   }

}
