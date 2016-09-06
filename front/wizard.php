<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();

if($plugin->isActivated("formcreator")) {

   if (! plugin_formcreator_replaceHelpdesk()) {
      Html::redirect($CFG_GLPI["root_doc"]."/plugins/formcreator/front/formlist.php");
   }

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
