<?php

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkLoginUser();

if (empty($_REQUEST["id"])) {
   $_REQUEST["id"] = "";
}

$form = new PluginFormcreatorQuestion;

if (isset($_POST["add"])) {
   
   $form->check(-1,'w',$_POST);
   
   $result = PluginFormcreatorQuestion::getQuestionArray($_REQUEST);
   
   $newID = $form->add($result);
   Html::back();

} else if (isset($_POST["delete"])) {
   $form->check($_POST["id"],'d');
   $form->delete($_POST);

   Html::back();

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

   $result = PluginFormcreatorQuestion::getQuestionArray($_REQUEST);

   $form->update($result);
   Html::back();

} else {
   Html::header($LANG['plugin_formcreator']['name'],
               $_SERVER['PHP_SELF'],
               "plugins",
               "formcreator",
               "form"
               );
               
   $form->showForm($_REQUEST);

   Html::footer();
}
?>