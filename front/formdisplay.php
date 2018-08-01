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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();

if (!$plugin->isActivated("formcreator")) {
   Html::displayNotFoundError();
}

$form = new PluginFormcreatorForm();
PluginFormcreatorForm::header();

if (isset($_REQUEST['id'])
   && is_numeric($_REQUEST['id'])) {

   if ($form->getFromDB((int) $_REQUEST['id'])) {
      if ($form->fields['access_rights'] != PluginFormcreatorForm::ACCESS_PUBLIC) {
         Session::checkLoginUser();
         if (!$form->checkEntity(true)) {
            Html::displayRightError();
            exit();
         }
      }

      if ($form->fields['access_rights'] == PluginFormcreatorForm::ACCESS_RESTRICTED) {
         $form_profile = new PluginFormcreatorForm_Profile();
         $formId = $form->getID();
         $activeProfileId = $_SESSION['glpiactiveprofile']['id'];
         $rows = $form_profile->find("profiles_id = '$activeProfileId'
                                      AND plugin_formcreator_forms_id = '$formId'", "", "1");
         if (count($rows) == 0) {
            Html::displayRightError();
            exit();
         }
      }
      if (($form->fields['access_rights'] == PluginFormcreatorForm::ACCESS_PUBLIC) && (!isset($_SESSION['glpiID']))) {
         // If user is not authenticated, create temporary user
         if (!isset($_SESSION['glpiname'])) {
            $_SESSION['formcreator_forms_id'] = $form->fields['id'];
            $_SESSION['glpiname'] = 'formcreator_temp_user';
            $_SESSION['valid_id'] = session_id();
            $_SESSION['glpiactiveentities'] = $form->fields['entities_id'];
            $subentities = getSonsOf('glpi_entities', $form->fields['entities_id']);
            $_SESSION['glpiactiveentities_string'] = (!empty($subentities))
                                                   ? "'" . implode("', '", $subentities) . "'"
                                                   : "'" . $form->fields['entities_id'] . "'";
         }
      }

      $form->displayUserForm($form);

   } else {
      Html::displayNotFoundError();
   }

   // If user was not authenticated, remove temporary user
   if ($_SESSION['glpiname'] == 'formcreator_temp_user') {
      unset($_SESSION['glpiname']);
   }

   // Or display a "Not found" error
} else if (isset($_GET['answer_saved'])) {
   $message = __("The form has been successfully saved!");
   Html::displayTitle($CFG_GLPI['root_doc']."/pics/ok.png", $message, $message);
}

PluginFormcreatorForm::footer();
