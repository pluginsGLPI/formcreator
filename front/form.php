<?php

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkLoginUser();

if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
   Html::header($LANG['plugin_formcreator']['name'],
               $_SERVER['PHP_SELF'],
               "plugins",
               "formcreator",
               "form"
               );
} else {
   Html::helpHeader($LANG['plugin_formcreator']['name'], $_SERVER['PHP_SELF']);
}

Search::show('PluginFormcreatorForm');

Html::footer();
?>