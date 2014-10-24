<?php
include ("../../../inc/includes.php");

Session::checkRight("entity", "w");

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $question = new PluginFormcreatorQuestion();

   // Add a new Question
   if(isset($_POST["add"])) {
      $question->check(-1,'w',$_POST);
      if ($question->add($_POST)) {
         Session::addMessageAfterRedirect(__('The question have been successfully saved!', 'formcreator'), true, INFO);
      }
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   // Edit an existinf Question
   } elseif(isset($_POST["update"])) {
      $question->check($_POST['id'],'w');
      if ($question->update($_POST)) {
         Session::addMessageAfterRedirect(__('The question have been successfully updated!', 'formcreator'), true, INFO);
      }
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   // Delete a Question
   } elseif(isset($_POST["delete_question"])) {
      $question->check($_POST['id'], 'd');
      $question->delete($_POST);

   // Set a Question required
   } elseif(isset($_POST["set_required"])) {
      global $DB;
      $question->check($_POST['id'], 'w');
      $table = getTableForItemtype('PluginFormcreatorQuestion');
      $DB->query("UPDATE $table SET `required` = " . $_POST['value'] . " WHERE id = " . $_POST['id']);

   // Move a Question
   } elseif(isset($_POST["move"])) {
      global $DB;

      $question->check($_POST['id'], 'd');

      $table  = getTableForItemtype('PluginFormcreatorQuestion');
      $result = $DB->query("SELECT `order`, `plugin_formcreator_sections_id` FROM $table WHERE id = " . $_POST['id']);
      list($order, $section_id) = $DB->fetch_array($result);

      if($_POST["way"] == 'up') {
         $result = $DB->query("SELECT `id`, `order` FROM $table WHERE `order` < $order AND plugin_formcreator_sections_id = $section_id ORDER BY `order` DESC LIMIT 0, 1");
         if($DB->numrows($result) != 0) {
            list($id2, $order2) = $DB->fetch_array($result);
            $DB->query("UPDATE $table SET `order` = $order2 WHERE `id` = " . (int) $_POST['id']);
            $DB->query("UPDATE $table SET `order` = $order WHERE `id` = $id2");
         }
      } else {
         $result = $DB->query("SELECT `id`, `order` FROM $table WHERE `order` > $order AND plugin_formcreator_sections_id = $section_id ORDER BY `order` ASC LIMIT 0, 1");
         if($DB->numrows($result) != 0) {
            list($id2, $order2) = $DB->fetch_array($result);
            $DB->query("UPDATE $table SET `order` = $order2 WHERE `id` = " . (int) $_POST['id']);
            $DB->query("UPDATE $table SET `order` = $order WHERE `id` = $id2");
         }
      }

   // Return to form list
   } else {
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.php');
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
