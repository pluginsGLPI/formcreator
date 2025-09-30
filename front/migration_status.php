<?php

/**
 *
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
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
