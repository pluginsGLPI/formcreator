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
if (!Session::haveRight('entity', UPDATE)) {
   http_response_code(403);
   die();
}

$form = PluginFormcreatorCommon::getForm();

if (!isset($_REQUEST['id']) || !isset($_REQUEST['action'])) {
   http_response_code(400);
   die();
}

$success = false;
switch ($_REQUEST['action']) {
   case 'toggle_active':
      $success = $form->update([
         'id' => $_POST['id'],
         'toggle' => 'active',
      ]);
      break;

   case 'toggle_default':
      $success = $form->update([
         'id' => $_POST['id'],
         'toggle' => 'default',
      ]);
      break;

   default:
      http_response_code(400);
      die();
}

if (!$success) {
   http_response_code(500);
}