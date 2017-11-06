<?php
include ("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $section = new PluginFormcreatorSection();

   if (isset($_POST["add"])) {
      // Add a new Section
      Session::checkRight("entity", UPDATE);
      $section->add($_POST);
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   } else if (isset($_POST["update"])) {
      // Edit an existing section
      Session::checkRight("entity", UPDATE);
      $section->update($_POST);
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   } else if (isset($_POST["delete_section"])) {
      // Delete a Section
      Session::checkRight("entity", UPDATE);
      $section->delete($_POST);
      // Page refresh handled by Javascript

   } else if (isset($_POST["duplicate_section"])) {
      // Duplicate a Section
      Session::checkRight("entity", UPDATE);
      if ($section->getFromDB((int) $_POST['id'])) {
         $section->duplicate();
      }
      // Page refresh handled by Javascript

   } else if (isset($_POST["move"])) {
      // Move a Section
      Session::checkRight("entity", UPDATE);

      if ($section->getFromDB((int) $_POST['id'])) {
         if ($_POST["way"] == 'up') {
            $section->moveUp();
         } else {
            $section->moveDown();
         }
      }
      // Page refresh handled by Javascript

   } else {
      // Return to form list
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.php');
   }

   // Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
