<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator v3.0.0 - Migration Only (End of Life)
 * ---------------------------------------------------------------------
 * This file redirects to the migration status page as all form 
 * functionality is now available in GLPI 11 core.
 * ---------------------------------------------------------------------
 */

include('../../../inc/includes.php');

/** @var array $CFG_GLPI */
global $CFG_GLPI;

// Check if user has admin rights
Session::checkRight('config', UPDATE);

// Show EOL message
$message = sprintf(
   __('Formcreator v%s is End-of-Life. This page has been disabled. Use GLPI 11 native forms instead.', 'formcreator'),
   PLUGIN_FORMCREATOR_VERSION
);
Session::addMessageAfterRedirect($message, true, WARNING);

// Redirect to migration status page
Html::redirect($CFG_GLPI['root_doc'] . '/plugins/formcreator/front/migration_status.php');
