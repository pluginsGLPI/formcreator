<?php

$AJAX_INCLUDE = 1;
define('GLPI_ROOT','../../../');
include (GLPI_ROOT."/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

$valueId = $_REQUEST['valueId'];
$formID = $_REQUEST['formID'];
$valueIdSection = $_REQUEST['valueIdSection']+1;

PluginFormcreatorQuestion::getNextValueDynamicSection($formID, $valueId, $valueIdSection);

Html::ajaxFooter();
?>