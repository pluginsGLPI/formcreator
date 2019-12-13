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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

include ('../../../inc/includes.php');
Session::checkRight('entity', UPDATE);

if (!isset($_REQUEST['questionId'])) {
   http_response_code(400);
   exit();
}
if (!isset($_REQUEST['questionType'])) {
   http_response_code(400);
   exit();
}

$question = new PluginFormcreatorQuestion();
$question->getEmpty();
if (!$question->isNewID((int) $_REQUEST['questionId']) && !$question->getFromDB((int) $_REQUEST['questionId'])) {
   http_response_code(400);
   exit();
}

$question->fields['fieldtype'] = $_REQUEST['questionType'];
$field = PluginFormcreatorFields::getFieldInstance(
   $question->fields['fieldtype'],
   $question
);
$json = [
   'label' => '',
   'field' => '',
   'additions' => '',
   'may_be_empty' => false,
];
if ($field !== null) {
   $field->deserializeValue($question->fields['default_values']);
   $json = $field->getDesignSpecializationField();
}
echo json_encode($json);
