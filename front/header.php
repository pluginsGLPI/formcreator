<?php
include ('../../../inc/includes.php');

Plugin::load('formcreator',true);

$dropdown = new PluginFormcreatorHeader();
include (GLPI_ROOT . "/front/dropdown.common.php");
