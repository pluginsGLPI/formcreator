<?php
include ("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $section = new PluginFormcreatorSection();

   // Add a new Section
   if(isset($_POST["add"])) {
      Session::checkRight("entity", UPDATE);
      $section->add($_POST);
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   // Edit an existing Section
   } elseif(isset($_POST["update"])) {
      Session::checkRight("entity", UPDATE);
      $section->update($_POST);
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   // Delete a Section
   } elseif(isset($_POST["delete_section"])) {
      Session::checkRight("entity", UPDATE);
      $section->delete($_POST);
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   // Duplicate a Section
   } elseif(isset($_POST["duplicate_section"])) {
      Session::checkRight("entity", UPDATE);
      if ($section->getFromDB((int) $_POST['id'])) {
         $section->duplicate();
      }

   // Move a Section
   } elseif(isset($_POST["move"])) {
      Session::checkRight("entity", UPDATE);

      if ($section->getFromDB((int) $_POST['id'])) {
         if($_POST["way"] == 'up') {
            $section->moveUp();
         } else {
            $section->moveDown();
         }
      }

      // Page refresh handled by Javascript

   // Return to form list
   } else {
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.php');
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
