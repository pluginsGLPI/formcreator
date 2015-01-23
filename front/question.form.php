<?php
include ("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $question = new PluginFormcreatorQuestion();

   // Add a new Question
   if(isset($_POST["add"])) {
      Session::checkRight("entity", UPDATE);
      if ($newid = $question->add($_POST)) {
         Session::addMessageAfterRedirect(__('The question have been successfully saved!', 'formcreator'), true, INFO);
         $_POST['id'] = $newid;
         $question->updateConditions($_POST);
      }
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   // Edit an existing Question
   } elseif(isset($_POST["update"])) {
      Session::checkRight("entity", UPDATE);
      if ($question->update($_POST)) {
         Session::addMessageAfterRedirect(__('The question have been successfully updated!', 'formcreator'), true, INFO);
         $question->updateConditions($_POST);
      }
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   // Delete a Question
   } elseif(isset($_POST["delete_question"])) {
      Session::checkRight("entity", UPDATE);
      $question->delete($_POST);

   // Set a Question required
   } elseif(isset($_POST["set_required"])) {
      global $DB;
      Session::checkRight("entity", UPDATE);
      $table = getTableForItemtype('PluginFormcreatorQuestion');
      $DB->query("UPDATE $table SET `required` = " . $_POST['value'] . " WHERE id = " . $_POST['id']);

   // Move a Question
   } elseif(isset($_POST["move"])) {
      global $DB;

      Session::checkRight("entity", UPDATE);

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
