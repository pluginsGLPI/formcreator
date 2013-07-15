<?php

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkLoginUser();

if (empty($_REQUEST["id"])) {
   $_REQUEST["id"] = "";
}

$form = new PluginFormcreatorCat;

if (isset($_POST["add"])) {
   $form->check(-1,'w',$_POST);
   
   $newID = $form->add($_POST);
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