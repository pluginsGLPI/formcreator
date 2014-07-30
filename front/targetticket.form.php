<?php
include ("../../../inc/includes.php");

Session::checkRight("config", "w");

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $targetticket = new PluginFormcreatorTargetTicket();

   // Edit an existing target ticket
   if(isset($_POST["update"])) {
      $targetticket->check($_POST['id'],'w');
      $targetticket->update($_POST);
      Html::back();

   // Show target ticket form
   } else {
      Html::header(
         __('Form Creator', 'formcreator'),
         $_SERVER['PHP_SELF'],
         'plugins',
         'formcreator',
         'options'
      );

      $targetticket->showForm($_REQUEST);

      Html::footer();
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
