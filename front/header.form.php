<?php
include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

Plugin::load('formcreator',true);

$dropdown = new PluginFormcreatorHeader();

if(isset($_POST['add'])) {
   $found = $dropdown->find('entities_id LIKE "' . $_SESSION['glpiactive_entity'] . '"');
   if(!empty($found)) {
      Session::addMessageAfterRedirect(__('An header already exists for this entity! You can have only one header per entity.', 'formcreator'), false, ERROR);
      Html::back();
   }
}

include (GLPI_ROOT . "/front/dropdown.common.form.php");
