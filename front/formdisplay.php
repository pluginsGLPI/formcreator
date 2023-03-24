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

global $CFG_GLPI, $DB;
include ('../../../inc/includes.php');

// Check if plugin is activated...
if (!(new Plugin())->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

PluginFormcreatorCommon::header();

if (isset($_REQUEST['id'])
   && is_numeric($_REQUEST['id'])) {

   $criteria = [
      'id'        => (int) $_REQUEST['id'],
      'is_active' => '1',
      'is_deleted'=> '0',
   ];
   $form = PluginFormcreatorCommon::getForm();
   if (!$form->getFromDBByCrit($criteria)) {
      Html::displayNotFoundError();
   }

   // If the form has restriced access and user is not logged in, send to login form
   if ($form->fields['access_rights'] != PluginFormcreatorForm::ACCESS_PUBLIC && Session::getLoginUserID() === false) {
      Session::redirectIfNotLoggedIn();
      exit();
   }

   if (!$form->canViewForRequest()) {
      Html::displayRightError();
      exit();
   }
   if (($form->fields['access_rights'] == PluginFormcreatorForm::ACCESS_PUBLIC) && (!isset($_SESSION['glpiID']))) {
      // If user is not authenticated, create temporary user
      if (!isset($_SESSION['glpiname'])) {
         $_SESSION['formcreator_forms_id'] = $form->getID();
         $_SESSION['formcreator_public'] = true;
         $_SESSION['glpiname'] = 'formcreator_temp_user';
         $_SESSION['valid_id'] = session_id();
         $_SESSION['glpiactiveentities'] = [$form->fields['entities_id']];
         $subentities = getSonsOf('glpi_entities', $form->fields['entities_id']);
         $_SESSION['glpiactiveentities_string'] = (!empty($subentities))
                                                ? "'" . implode("', '", $subentities) . "'"
                                                : "'" . $form->fields['entities_id'] . "'";
         $_SESSION['glpilanguage'] = $form->getBestLanguage();
      }
   }

   $form->displayUserForm();

   // If user was not authenticated, remove temporary user
   if (isset($_SESSION['formcreator_public'])) {
      unset($_SESSION['formcreator_public']);
      session_write_close();
      unset($_SESSION['glpiname']);
   }
} else if (isset($_GET['answer_saved'])) {
   $message = __("The form has been successfully saved!", "formcreator");
   Html::displayTitle($CFG_GLPI['root_doc']."/pics/ok.png", $message, $message);
}

PluginFormcreatorCommon::footer();
