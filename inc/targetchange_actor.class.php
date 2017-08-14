<?php
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorTargetChange_Actor extends PluginFormcreatorTarget_Actor
{

   protected function getTargetItem() {
      return new PluginFormcreatorTargetChange();
   }

}