<?php
include ('../../../inc/includes.php');

Session::checkRight('entity', UPDATE);

$plugin = new Plugin();
if ($plugin->isActivated('formcreator')) {
   if (isset($_POST['purge'])) {
      $item_targetTicket = new PluginFormcreatorItem_TargetTicket();
      $item_targetTicket->delete($_POST, 1);
      Html::back();
   }
   Html::displayErrorAndDie("lost");
} else {
   // Or display a "Not found" error
   Html::displayNotFoundError();
}