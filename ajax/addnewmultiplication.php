<?php

$AJAX_INCLUDE = 1;
define('GLPI_ROOT','../../../');
include (GLPI_ROOT."/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

$id = $_REQUEST['id']+1;

PluginFormcreatorQuestion::getMultiplication($id);

Html::ajaxFooter();
?>