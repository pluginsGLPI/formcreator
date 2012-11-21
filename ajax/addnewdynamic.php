<?php

$AJAX_INCLUDE = 1;
define('GLPI_ROOT','../../../');
include (GLPI_ROOT."/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

$id = $_REQUEST['id']+1;
$formID = $_REQUEST['formID'];

PluginFormcreatorQuestion::getValueDynamic($formID,$id);

Html::ajaxFooter();
?>