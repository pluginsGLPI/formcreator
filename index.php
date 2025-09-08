<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator v3.0.0 - Migration Only (End of Life)
 * ---------------------------------------------------------------------
 * This file redirects to the migration status page as all form 
 * functionality is now available in GLPI 11 core.
 * ---------------------------------------------------------------------
 */

include('../../inc/includes.php');

// Check if user has admin rights
Session::checkRight('config', UPDATE);

// Show EOL message
$message = sprintf(
   __('Formcreator v%s is End-of-Life. All form functionality is now available in GLPI 11 core. Check migration status or use native forms.', 'formcreator'),
   PLUGIN_FORMCREATOR_VERSION
);
Session::addMessageAfterRedirect($message, true, WARNING);

// Redirect to migration status page
Html::redirect($CFG_GLPI['root_doc'] . '/plugins/formcreator/front/migration_status.php');

header('Location: front/form.php');
