<?php
include ('../../../inc/includes.php');

Session::checkRight("config", "w");

Plugin::load('formcreator',true);

$dropdown = new PluginFormcreatorHeader();

if(isset($_REQUEST['popup'])) $_SESSION['is_popup'] = true;

if((isset($_POST['add']) || isset($_POST['update'])) && !isset($_SESSION['is_popup'])) {
   $_SERVER['HTTP_REFERER'] = $CFG_GLPI['root_doc'].'/plugins/formcreator/front/config.form.php';
}
include (GLPI_ROOT . "/front/dropdown.common.form.php");
