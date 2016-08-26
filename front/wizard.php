<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();

if($plugin->isActivated("formcreator")) {

   // TODO : remove the standard GLPi header
   //Html::helpHeader(__('Service catalog', 'formcreator'));
   PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));

   $form = new PluginFormcreatorForm();
   $form->showServiceCatalog();

   if (Session::haveRight("reminder_public", READ)) {
      //echo "<tr class='noHover'><td class='top'>";
      Reminder::showListForCentral(false);
      //echo "</td></tr>";
   }

   if (Session::haveRight("rssfeed_public", READ)) {
      //echo "<tr class='noHover'><td class='top'>";
      RSSFeed::showListForCentral(false);
      //echo "</td></tr>";
   }


   Html::helpFooter();
} else {
   Html::displayNotFoundError();
}
