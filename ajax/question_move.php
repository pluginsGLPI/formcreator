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

if (!isset($_REQUEST['move']) || !is_array($_REQUEST['move'])) {
   http_response_code(400);
   exit();
}

$questions = [];
foreach ($_REQUEST['move'] as $id => $item) {
   $question = new PluginFormcreatorQuestion();
   if (!$question->getFromDB((int) $id)) {
      http_response_code(404);
      echo __('Question not found', 'formcreator');
      exit;
   }
   if (!$question->canUpdate()) {
      http_response_code(403);
      echo __('You don\'t have right for this action', 'formcreator');
      exit;
   }
   $questions[$id] = $question;
}

$error = false;
foreach ($questions as $id => $item) {
   $question = $questions[$id];
   $question->fields['row'] = (int) $_REQUEST['move'][$id]['y'];
   $question->fields['col'] = (int) $_REQUEST['move'][$id]['x'];
   $question->fields['width'] = (int) $_REQUEST['move'][$id]['width'];
   if (isset($_REQUEST['move'][$id]['plugin_formcreator_sections_id'])) {
      $question->fields['plugin_formcreator_sections_id'] = (int) $_REQUEST['move'][$id]['plugin_formcreator_sections_id'];
   }
   $success = $question->change($question->fields);
   if (!$success) {
      $error = true;
   }
}

if ($error) {
   http_response_code(500);
   echo __('Could not move some questions', 'formcreator');
}
