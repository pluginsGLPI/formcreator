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

   // Move a Section
   } elseif(isset($_POST["move"])) {
      global $DB;

      Session::checkRight("entity", UPDATE);

      $table  = getTableForItemtype('PluginFormcreatorSection');
      $result = $DB->query("SELECT `order`, `plugin_formcreator_forms_id` FROM $table WHERE id = " . $_POST['id']);
      list($order, $form_id) = $DB->fetch_array($result);

      if($_POST["way"] == 'up') {
         $result = $DB->query("SELECT `id`, `order` FROM $table WHERE `order` < $order AND plugin_formcreator_forms_id = $form_id ORDER BY `order` DESC LIMIT 0, 1");
         if($DB->numrows($result) != 0) {
            list($id2, $order2) = $DB->fetch_array($result);
            $DB->query("UPDATE $table SET `order` = $order2 WHERE `id` = " . (int) $_POST['id']);
            $DB->query("UPDATE $table SET `order` = $order WHERE `id` = $id2");
         }
      } else {
         $result = $DB->query("SELECT `id`, `order` FROM $table WHERE `order` > $order AND plugin_formcreator_forms_id = $form_id ORDER BY `order` ASC LIMIT 0, 1");
         if($DB->numrows($result) != 0) {
            list($id2, $order2) = $DB->fetch_array($result);
            $DB->query("UPDATE $table SET `order` = $order2 WHERE `id` = " . (int) $_POST['id']);
            $DB->query("UPDATE $table SET `order` = $order WHERE `id` = $id2");
         }
      }
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   // Return to form list
   } else {
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.php');
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
