<?php
/**
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
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

// Check if plugin is activated...
if (!(new Plugin())->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

Session::checkRight('entity', UPDATE);

$form = new PluginFormcreatorForm;
$export_array = ['schema_version' => PLUGIN_FORMCREATOR_SCHEMA_VERSION, 'forms' => []];
foreach ($_GET['plugin_formcreator_forms_id'] as $id) {
   $form->getFromDB($id);
   try {
      $export_array['forms'][] = $form->export();
   } catch (\RuntimeException $e) {
      Session::addMessageAfterRedirect($e->getMessage(), false, ERROR, true);
      Html::back();
   }
}

$export_json = json_encode($export_array, JSON_UNESCAPED_UNICODE
                                        | JSON_UNESCAPED_SLASHES
                                        | JSON_NUMERIC_CHECK
                                        | ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE
                                             ? JSON_PRETTY_PRINT
                                             : 0));

header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
header('Pragma: private');
header('Cache-control: private, must-revalidate');
header("Content-disposition: attachment; filename=\"export_formcreator_".date("Ymd_Hi").".json\"");
header("Content-type: application/json");

echo $export_json;
