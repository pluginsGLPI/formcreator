<?php
include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

if (isset($_POST['update'])) {
   $entityConfig = new PluginFormcreatorEntityconfig();
   $entityConfig->update($_POST);
}

Html::back();