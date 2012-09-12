<?php

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkLoginUser();

if (empty($_REQUEST["id"])) {
   $_REQUEST["id"] = "";
}

$form = new PluginFormcreatorForm;

if (isset($_POST["add"])) {
   $form->check(-1,'w',$_POST);

   $newID = $form->add($_POST);

   $newTarget = $form->createDefaultTarget($newID);
   $newSection = $form->createDefaultSection($newID,$newTarget);

   Html::redirect($CFG_GLPI["root_doc"]."/plugins/formcreator/front/form.form.php?id=".$newID);

} else if (isset($_POST["delete"])) {
   $form->check($_POST["id"],'d');
   $form->delete($_POST);

   $formID = $_POST["id"];
   //suppresion question
   $question = new PluginFormcreatorQuestion;
   $listQuestion = $question->find("plugin_formcreator_forms_id = '$formID'");
   foreach($listQuestion as $question_id => $values) {
       $question->delete($values);
   }
   //suppresion section
   $section = new PluginFormcreatorSection;
   $listSection = $section->find("plugin_formcreator_forms_id = '$formID'");
   foreach($listSection as $section_id => $values) {
       $section->delete($values);
   }
   //suppression target
   $target = new PluginFormcreatorTarget;
   $listTarget = $target->find("plugin_formcreator_forms_id = '$formID'");
   foreach($listTarget as $target_id => $values) {
       $target->delete($values);
   }

   $form->redirectToList();

} else if (isset($_POST["restore"])) {
   $form->check($_POST["id"],'d');

   $form->restore($_POST);
   $form->redirectToList();

} else if (isset($_REQUEST["purge"])) {
   $form->check($_REQUEST["id"],'d');

   $form->delete($_REQUEST,1);
   $form->redirectToList();

} else if (isset($_POST["update"])) {
   $form->check($_POST["id"],'w');

   $form->update($_POST);
   Html::back();

} else {
   Html::header($LANG['plugin_formcreator']['name'],
               $_SERVER['PHP_SELF'],
               "plugins",
               "formcreator",
               "form"
               );

   $form->showForm($_REQUEST["id"]);

   Html::footer();
}
?>