<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();

if($plugin->isActivated("formcreator")) {

   if (isset($_SESSION['glpiactiveprofile']['interface'])
         && isset($_SESSION['glpiactive_entity'])) {
       // Interface and active entity are set in session
      if (PluginFormcreatorEntityconfig::getUsedConfig('replace_helpdesk', $_SESSION['glpiactive_entity']) == '0'
            || $_SESSION['glpiactiveprofile']['interface'] != 'helpdesk') {
         Html::redirect($CFG_GLPI["root_doc"]."/plugins/formcreator/front/formlist.php");
      }
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
