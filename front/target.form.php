<?php
include ("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $target = new PluginFormcreatorTarget();

   // Add a new target
   if(isset($_POST["add"]) && !empty($_POST['plugin_formcreator_forms_id'])) {
      Session::checkRight("entity", UPDATE);
      $target->add($_POST);
      Html::back();

   // Delete a target
   } elseif(isset($_POST["delete_target"])) {
      Session::checkRight("entity", UPDATE);
      $target->delete($_POST);
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   } else {
      Html::back();
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
