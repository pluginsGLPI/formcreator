<?php
class PluginFormcreatorTargetChange_Actor extends PluginFormcreatorTarget_Actor
{

   protected function getTargetItem() {
      return new PluginFormcreatorTargetChange();
   }

}