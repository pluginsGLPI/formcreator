<?php
include ("../../../inc/includes.php");
      Toolbox::logDebug('coucou');
      Toolbox::logDebug($_POST);

Session::checkRight("config", "w");

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $section = new PluginFormcreatorSection();

   // Add a new Section
   if(isset($_POST["add"])) {
      $section->check(-1,'w',$_POST);
      $section->add($_POST);
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   // Edit an existinf Section
   } elseif(isset($_POST["update"])) {
      $section->check($_POST['id'],'w');
      $section->update($_POST);
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   // Delete a Section
   } elseif(isset($_POST["delete"])) {
      $section->check($_POST['id'], 'd');
      $section->delete($_POST);
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   // Move a Section
   } elseif(isset($_POST["move"])) {
      global $DB;

      $section->check($_POST['id'], 'd');

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
}else{
   Html::displayNotFoundError();
}
