<?php
include ("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $question = new PluginFormcreatorQuestion();

   // Add a new Question
   if (isset($_POST["add"])) {
      Session::checkRight("entity", UPDATE);
      if ($newid = $question->add($_POST)) {
         Session::addMessageAfterRedirect(__('The question has been successfully saved!', 'formcreator'), true, INFO);
         $_POST['id'] = $newid;
         $question->updateConditions($_POST);
      }
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

      // Edit an existing Question
   } else if (isset($_POST["update"])) {
      Session::checkRight("entity", UPDATE);
      if ($question->update($_POST)) {
         Session::addMessageAfterRedirect(__('The question has been successfully updated!', 'formcreator'), true, INFO);
         $question->updateConditions($_POST);
      }
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

      // Delete a Question
   } else if (isset($_POST["delete_question"])) {
      Session::checkRight("entity", UPDATE);
      $question->delete($_POST);

      // Duplicate a Question
   } else if (isset($_POST["duplicate_question"])) {
      Session::checkRight("entity", UPDATE);
      if ($question->getFromDB((int) $_POST['id'])) {
         $question->duplicate();
      }

      // Set a Question required
   } else if (isset($_POST["set_required"])) {
      global $DB;
      Session::checkRight("entity", UPDATE);
      $table = getTableForItemtype('PluginFormcreatorQuestion');
      $DB->query("UPDATE $table SET `required` = " . $_POST['value'] . " WHERE id = " . $_POST['id']);

      // Move a Question
   } else if (isset($_POST["move"])) {
      Session::checkRight("entity", UPDATE);

      if ($question->getFromDB((int) $_POST['id'])) {
         if ($_POST["way"] == 'up') {
            $question->moveUp();
         } else {
            $question->moveDown();
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
