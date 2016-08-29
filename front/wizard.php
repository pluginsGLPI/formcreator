<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();

if($plugin->isActivated("formcreator")) {

   PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));

   $form = new PluginFormcreatorForm();
   $form->showServiceCatalog();

   if (Session::haveRight("reminder_public", READ)) {
      //Reminder::showListForCentral(false);
   }

   if (Session::haveRight("rssfeed_public", READ)) {
      //RSSFeed::showListForCentral(false);
   }

   PluginFormcreatorWizard::footer();
} else {
   Html::displayNotFoundError();
}
