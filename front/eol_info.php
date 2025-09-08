<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator v3.0.0 - End of Life Information Page
 * ---------------------------------------------------------------------
 * This page displays End of Life information and migration guidance.
 * ---------------------------------------------------------------------
 */

include('../../../inc/includes.php');

// Check if user has admin rights
Session::checkRight('config', READ);

/** @var array $CFG_GLPI */
global $CFG_GLPI;

Html::header(
   __('Formcreator End of Life Information', 'formcreator'), 
   $_SERVER['PHP_SELF'], 
   'tools', 
   'PluginFormcreatorEOLInfo'
);

$eolInfo = new PluginFormcreatorEOLInfo();
$eolInfo->showForm();

Html::footer();
