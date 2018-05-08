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

if (isset($_POST["add"])) {
   // Add a new Form
   Session::checkRight("entity", UPDATE);
   $newID = $form->add($_POST);

   Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $newID);

} else if (isset($_POST["update"])) {
   // Edit an existing form
   Session::checkRight("entity", UPDATE);
   $form->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   // Delete a form (is_deleted = true)
   Session::checkRight("entity", UPDATE);
   $form->delete($_POST);
   $form->redirectToList();

} else if (isset($_POST["restore"])) {
   // Restore a deleteted form (is_deleted = false)
   Session::checkRight("entity", UPDATE);
   $form->restore($_POST);
   $form->redirectToList();

} else if (isset($_POST["purge"])) {
   // Delete defenitively a form from DB and all its datas
   Session::checkRight("entity", UPDATE);
   $form->delete($_POST, 1);
   $form->redirectToList();

} else if (isset($_POST['filetype_create'])) {
   $documentType = new DocumentType();
   $canAddType = $documentType->canCreate();
   if ($canAddType) {
      $form->createDocumentType();
   }
   Html::back();
} else if (isset($_POST['filetype_enable'])) {

   $documentType = new DocumentType();
   $canUpdateType = $documentType->canUpdate();
   if ($canUpdateType) {
      $form->enableDocumentType();
   }
   Html::back();

} else if (isset($_GET["import_form"])) {
   // Import form
   Session::checkRight("entity", UPDATE);
   Html::header(
      PluginFormcreatorForm::getTypeName(2),
      $_SERVER['PHP_SELF'],
      'admin',
      'PluginFormcreatorForm',
      'option'
   );

   Html::requireJs('fileupload');

   $form->showImportForm();
   Html::footer();

} else if (isset($_POST["import_send"])) {
   // Import form
   Session::checkRight("entity", UPDATE);
   $form->importJson($_REQUEST);
   Html::back();

} else if (isset($_POST['submit_formcreator'])) {
   // Save form to target
   if ($form->getFromDB($_POST['formcreator_form'])) {

      // If user is not authenticated, create temporary user
      if (!isset($_SESSION['glpiname'])) {
         $_SESSION['glpiname'] = 'formcreator_temp_user';
      }

      // Save form
      if (!$form->saveForm($_POST)) {
         Html::back();
      }
      $form->increaseUsageCount();

      // If user was not authenticated, remove temporary user
      if ($_SESSION['glpiname'] == 'formcreator_temp_user') {
         unset($_SESSION['glpiname']);
         Html::redirect('formdisplay.php?answer_saved');
      } else if (plugin_formcreator_replaceHelpdesk()) {
         Html::redirect('issue.php');
      } else {
         Html::redirect('formlist.php');
      }
   }

} else {
   // Show forms form
   Session::checkRight("entity", UPDATE);

   Html::header(
      PluginFormcreatorForm::getTypeName(2),
      $_SERVER['PHP_SELF'],
      'admin',
      'PluginFormcreatorForm',
      'option'
   );

   Html::requireJs('tinymce');

   $_GET['id'] = isset($_GET['id']) ? intval($_GET['id']) : -1;
   $form->display($_GET);

   Html::footer();
}
