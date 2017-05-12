<?php
include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

Plugin::load('formcreator', true);

$dropdown = new PluginFormcreatorCategory();

include (GLPI_ROOT . "/front/dropdown.common.form.php");
