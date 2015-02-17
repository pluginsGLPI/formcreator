<?php
include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

Plugin::load('formcreator',true);

$dropdown = new PluginFormcreatorCategory();

if(isset($_POST['add']) && isset($_POST['name'])) {
   $found = $dropdown->find('name LIKE "' . $_POST['name'] . '"');
   if(!empty($found)) {
      Session::addMessageAfterRedirect(__('A category already exists with the same name! Category creation failed.', 'formcreator'), false, ERROR);
      Html::back();
   }
}

include (GLPI_ROOT . "/front/dropdown.common.form.php");
