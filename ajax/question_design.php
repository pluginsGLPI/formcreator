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
Session::checkRight('entity', UPDATE);

if (!isset($_REQUEST['id'])) {
   http_response_code(400);
   exit();
}
if (!isset($_REQUEST['fieldtype'])) {
   http_response_code(400);
   exit();
}

$question = new PluginFormcreatorQuestion();
$question->getEmpty();
if (!$question->isNewID((int) $_REQUEST['id']) && !$question->getFromDB((int) $_REQUEST['id'])) {
   http_response_code(400);
   exit();
}

// Modify the question to reflect changes in the form
$question->fields['plugin_formcreator_sections_id'] = (int) $_REQUEST['plugin_formcreator_sections_id'];
$values = [];
//compute question->fields from $_REQUEST (by comparing key)
//add other keys to 'values' key
foreach ($_REQUEST as $request_key => $request_value) {
   if (isset($question->fields[$request_key])) {
      $question->fields[$request_key] = $_REQUEST[$request_key];
   } else {
      $values[$request_key] = $request_value;
   }
}

$question->fields['values'] = json_encode($values);
$field = PluginFormcreatorFields::getFieldInstance(
   $_REQUEST['fieldtype'],
   $question
);
$question->fields['fieldtype'] = '';
if ($field !== null) {
   $question->fields['fieldtype'] = $_REQUEST['fieldtype'];
}
$question->showForm($question->getID());
