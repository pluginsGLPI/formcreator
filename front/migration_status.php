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

// Collect basic statistics - simple and reliable
$formCount = 0;
if ($DB->tableExists('glpi_plugin_formcreator_forms')) {
    $formCount = countElementsInTable('glpi_plugin_formcreator_forms');
}

$answerCount = 0;
if ($DB->tableExists('glpi_plugin_formcreator_formanswers')) {
    $answerCount = countElementsInTable('glpi_plugin_formcreator_formanswers');
}

$nativeFormCount = 0;
if ($DB->tableExists('glpi_forms_forms')) {
    $nativeFormCount = countElementsInTable('glpi_forms_forms');
}

// Display GLPI header
Html::header(__('Formcreator Migration Status', 'formcreator'), '', "tools", "migration");

// Render the template content
TemplateRenderer::getInstance()->display('@formcreator/migration_status.html.twig', [
    'form_count' => $formCount,
    'answer_count' => $answerCount,
    'native_form_count' => $nativeFormCount,
]);

// Display GLPI footer
Html::footer();
