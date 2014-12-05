<?php
include ("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $targetticket = new PluginFormcreatorTargetTicket();

   // Edit an existing target ticket
   if(isset($_POST["update"])) {
      Session::checkRight("config", UPDATE);
      $targetticket->update($_POST);
      Html::back();

   // Show target ticket form
   } else {
      Html::header(
         __('Form Creator', 'formcreator'),
         $_SERVER['PHP_SELF'],
         'admin',
         'PluginFormcreatorForm'
      );

      $targetticket->display($_REQUEST);

      Html::footer();
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
