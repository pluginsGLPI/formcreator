<?php
include ("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $targetticket = new PluginFormcreatorTargetTicket();

   // Edit an existing target ticket
   if(isset($_POST["update"])) {
      Session::checkRight("entity", UPDATE);
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

      $itemtype = "PluginFormcreatorTargetTicket";
      $target   = new PluginFormcreatorTarget;
      $found    = $target->find("itemtype = '$itemtype' AND items_id = '" . $_REQUEST['id'] . "'");
      $first    = array_shift($found);
      $form     = new PluginFormcreatorForm;
      $form->getFromDB($first['plugin_formcreator_forms_id']);

      $_SESSION['glpilisttitle'][$itemtype] = sprintf(__('%1$s = %2$s'),
                                                      $form->getTypeName(1), $form->getName());
      $_SESSION['glpilisturl'][$itemtype]   = $form->getFormURL()."?id=".$form->getID();

      $targetticket->display($_REQUEST);

      Html::footer();
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
