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

include ("../../../inc/includes.php");

// Check if plugin is activated...
if (!(new Plugin())->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

$form = PluginFormcreatorCommon::getForm();

if (isset($_POST['add'])) {
   // Add a new Form
   Session::checkRight('entity', UPDATE);
   $_POST['_create_empty_section'] = true;
   if ($newID = $form->add($_POST)) {
      if ($_SESSION['glpibackcreated']) {
         Html::redirect($form->getLinkURL());
      }
   }
   Html::back();

} else if (isset($_POST['update'])) {
   // Edit an existing form
   Session::checkRight('entity', UPDATE);
   $form->update($_POST);
   Html::back();

} else if (isset($_POST['delete'])) {
   // Delete a form (is_deleted = true)
   Session::checkRight('entity', UPDATE);
   $form->delete($_POST);
   $form->redirectToList();

} else if (isset($_POST['restore'])) {
   // Restore a deleteted form (is_deleted = false)
   Session::checkRight('entity', UPDATE);
   $form->restore($_POST);
   $form->redirectToList();

} else if (isset($_POST['purge'])) {
   // Delete defenitively a form from DB and all its datas
   Session::checkRight('entity', UPDATE);
   $form->delete($_POST, 1);
   $form->redirectToList();

} else if (isset($_POST['add_target'])) {
   Session::checkRight('entity', UPDATE);
   $form->addTarget($_POST);
   Html::back();

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

} else if (isset($_GET['import_form'])) {
   // Import form
   Session::checkRight('entity', UPDATE);
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

} else if (isset($_POST['import_send'])) {
   Html::header(
      PluginFormcreatorForm::getTypeName(2),
      $_SERVER['PHP_SELF'],
      'admin',
      'PluginFormcreatorForm',
      'option'
   );

   // Import form
   Session::checkRight('entity', UPDATE);
   $form->importJson($_REQUEST);
   Html::back();

} else {
   // Show forms form
   Session::checkRight('entity', UPDATE);

   Html::header(
      PluginFormcreatorForm::getTypeName(Session::getPluralNumber()),
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
