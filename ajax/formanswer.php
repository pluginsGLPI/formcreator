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
if (!Plugin::isPluginActive('formcreator')) {
   http_response_code(404);
   die();
}

if (!isset($_POST['submit_formcreator']) || !isset($_POST['plugin_formcreator_forms_id'])) {
   http_response_code(500);
   die();
}

$form = PluginFormcreatorCommon::getForm();
if (!$form->getFromDB($_POST['plugin_formcreator_forms_id'])) {
   http_response_code(500);
   die();
}

// If user is not authenticated, create temporary user
if (!isset($_SESSION['glpiname'])) {
   $_SESSION['glpiname'] = 'formcreator_temp_user';
}

// Save form
$backup_debug = $_SESSION['glpi_use_mode'];
$_SESSION['glpi_use_mode'] = \Session::NORMAL_MODE;
$formAnswer = PluginFormcreatorCommon::getFormAnswer();
if ($formAnswer->add($_POST) === false) {
   http_response_code(400);
   if ($_SESSION['glpiname'] == 'formcreator_temp_user') {
      // Messages are for authenticated users. This is a workaround
      ob_start();
      Html::displayMessageAfterRedirect(filter_var(($_GET['display_container'] ?? true), FILTER_VALIDATE_BOOLEAN));
      $messages = ob_get_clean();
      echo json_encode([
         'message' => $messages
      ]);
   }
   $_SESSION['glpi_use_mode'] = $backup_debug;
   die();
}
$form->increaseUsageCount();
$_SESSION['glpi_use_mode'] = $backup_debug;

if ($_SESSION['glpiname'] == 'formcreator_temp_user') {
   // Form was saved by an annymous user
   unset($_SESSION['glpiname']);
   // don't show notifications
   unset($_SESSION['MESSAGE_AFTER_REDIRECT']);
   echo json_encode(
      [
         'redirect' => 'formdisplay.php?answer_saved',
      ], JSON_FORCE_OBJECT
   );
   die();
}

// redirect to created item
if ($_SESSION['glpibackcreated'] && Ticket::canView()) {
   if (strpos($_SERVER['HTTP_REFERER'], 'form.form.php') === false) {
      // User was not testing the form from preview
      if (count($formAnswer->targetList) == 1) {
         $target = current($formAnswer->targetList);
         echo json_encode(
            [
               'redirect' => $target->getFormURLWithID($target->getID()),
            ], JSON_FORCE_OBJECT
         );
         die();
      }
      echo json_encode(
         [
            'redirect' => $formAnswer->getFormURLWithID($formAnswer->getID()),
         ], JSON_FORCE_OBJECT
      );
      die();
   }
   echo json_encode(
      [
         'redirect' => (new PluginFormcreatorForm())->getFormURLWithID($formAnswer->fields['plugin_formcreator_forms_id']),
      ], JSON_FORCE_OBJECT
   );
   die();
}

if (plugin_formcreator_replaceHelpdesk()) {
   if (Ticket::canView()) {
      $redirect = PluginFormcreatorIssue::getSearchURL();
   } else {
      $redirect = 'wizard.php';
   }

   // Form was saved from the service catalog
   echo json_encode(
      [
         'redirect' => $redirect,
      ], JSON_FORCE_OBJECT
   );
   die();
}
if (strpos($_SERVER['HTTP_REFERER'], 'formdisplay.php') !== false) {
   // Form was saved from helpdesk (assistance > forms)
   echo json_encode(
      [
         'redirect' => 'formlist.php',
      ], JSON_FORCE_OBJECT
   );
   die();
}
