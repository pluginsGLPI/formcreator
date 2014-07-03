<?php
$query_string = (!empty($_SERVER['QUERY_STRING'])) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: config.form.php' . $query_string);

// include ('../../../inc/includes.php');

// Plugin::load('formcreator',true);

// $dropdown = new PluginFormcreatorCategory();
// include (GLPI_ROOT . "/front/dropdown.common.php");
