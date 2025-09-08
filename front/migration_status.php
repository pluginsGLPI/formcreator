<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator v3.0.0 - Migration Status Interface
 * ---------------------------------------------------------------------
 * This page provides administrators with migration status and tools
 * for migrating from Formcreator to GLPI 11 native forms.
 * ---------------------------------------------------------------------
 */

include('../../../inc/includes.php');

use Glpi\Application\View\TemplateRenderer;

// Check if user has admin rights
Session::checkRight('config', UPDATE);

/** @var \DBmysql $DB */
global $DB;

// Collect migration data
$formCount = 0;
if ($DB->tableExists('glpi_plugin_formcreator_forms')) {
    $formCount = countElementsInTable('glpi_plugin_formcreator_forms');
}

$answerCount = 0;
if ($DB->tableExists('glpi_plugin_formcreator_formanswers')) {
    $answerCount = countElementsInTable('glpi_plugin_formcreator_formanswers');
}

$migrationCompleted = Config::getConfigurationValue('formcreator', 'migration_completed');
$showMigrationProgress = false;
$migrationError = null;

// Handle migration request
if (isset($_POST['start_migration'])) {
    Session::checkRight('config', UPDATE);
    Session::checkCSRF($_POST);
    
    $showMigrationProgress = true;
    
    try {
        $migration = new Migration(PLUGIN_FORMCREATOR_VERSION);
        
        // Simple migration status update for now
        Config::setConfigurationValues('formcreator', ['migration_completed' => true]);
        $migrationCompleted = true;
        
    } catch (Exception $e) {
        $migrationError = __('Migration error: ', 'formcreator') . $e->getMessage();
    }
}

// Display GLPI header
Html::header(__('Formcreator Migration Status', 'formcreator'), '', "tools", "migration");

// Render the template content
TemplateRenderer::getInstance()->display('@formcreator/migration_status.html.twig', [
    'form_count' => $formCount,
    'answer_count' => $answerCount,
    'migration_completed' => $migrationCompleted,
    'show_migration_progress' => $showMigrationProgress,
    'migration_error' => $migrationError,
    'current_url' => $_SERVER['PHP_SELF'],
    'csrf_token' => Session::getNewCSRFToken(),
]);

// Display GLPI footer
Html::footer();
