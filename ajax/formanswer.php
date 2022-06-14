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

if (!isset($_REQUEST['id']) || !isset($_REQUEST['action'])) {
   http_response_code(400);
   die();
}

if ($_POST['action'] == 'submit_formcreator') {
   unset($_SESSION['MESSAGE_AFTER_REDIRECT']);
   // Save form
   $formAnswer = PluginFormcreatorCommon::getFormAnswer();
   $form = PluginFormcreatorCommon::getForm();
   $form->getFromDB($_POST['id']);
   $_POST['plugin_formcreator_forms_id'] = (int) $_POST['id'];
   unset($_POST['id']);
   if ($formAnswer->add($_POST) === false) {
      http_response_code(400);
      die();
   }
   $form->increaseUsageCount();

   if ($_SESSION['glpiname'] == 'formcreator_temp_user') {
      // Form was saved by an annymous user
      unset($_SESSION['glpiname']);
      // don't show notifications
      unset($_SESSION['MESSAGE_AFTER_REDIRECT']);
      echo json_encode([
         'redirect' => 'formdisplay.php?answer_saved',
      ], JSON_OBJECT_AS_ARRAY);
      http_response_code(200);
      die();
   }

   // redirect to created item
   if ($_SESSION['glpibackcreated']) {
      if (count($formAnswer->targetList) == 1) {
         $target = current($formAnswer->targetList);
         echo json_encode([
            'redirect' => $target->getFormURLWithID($target->getID()),
         ], JSON_OBJECT_AS_ARRAY);
         http_response_code(200);
         die();
      }
      echo json_encode([
         'redirect' => PluginFormcreatorFormAnswer::getFormURLWithID($formAnswer->getID()),
      ], JSON_OBJECT_AS_ARRAY);
      http_response_code(200);
      die();
   }

   if (plugin_formcreator_replaceHelpdesk()) {
      // Form was saved from the service catalog
      echo json_encode([
          'redirect' => PluginFormcreatorIssue::getSearchUrl(),
      ], JSON_OBJECT_AS_ARRAY);
      http_response_code(200);
      die();
   }
   if (strpos($_SERVER['HTTP_REFERER'], 'formdisplay.php') !== false) {
      // Form was saved from helpdesk (assistance > forms)
      http_response_code(200);
      echo json_encode([
          'redirect' => $_SERVER['HTTP_REFERER'],
      ], JSON_OBJECT_AS_ARRAY);
      die();
   }
   // Form was saved from preview tab, go back to the preview
   http_response_code(200);
   echo json_encode([
       'redirect' => $_SERVER['HTTP_REFERER'],
   ], JSON_OBJECT_AS_ARRAY);
}