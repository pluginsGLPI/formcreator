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

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

$question = new PluginFormcreatorQuestion();

if (isset($_POST["add"])) {
   // Add a new Question
   Session::checkRight("entity", UPDATE);
   if ($newid = $question->add($_POST)) {
      Session::addMessageAfterRedirect(__('The question has been successfully saved!', 'formcreator'), true, INFO);
      $_POST['id'] = $newid;
      $question->updateConditions($_POST);
   }
   Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

} else if (isset($_POST["update"])) {
   // Edit an existing Question
   Session::checkRight("entity", UPDATE);
   if ($question->update($_POST)) {
      Session::addMessageAfterRedirect(__('The question has been successfully updated!', 'formcreator'), true, INFO);
      $question->updateConditions($_POST);
   }
   Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

} else if (isset($_POST["delete_question"])) {
   // Delete a Question
   Session::checkRight("entity", UPDATE);
   $question->delete($_POST);

} else if (isset($_POST["duplicate_question"])) {
   // Duplicate a Question
   Session::checkRight("entity", UPDATE);
   if ($question->getFromDB((int) $_POST['id'])) {
      $question->duplicate();
   }

} else if (isset($_POST["set_required"])) {
   // Set a Question required
   $question = new PluginFormcreatorQuestion();
   $question->getFromDB((int) $_POST['id']);
   $question->update(['required' => $_POST['value']] + $question->fields);

} else if (isset($_POST["move"])) {
   // Move a Question
   Session::checkRight("entity", UPDATE);

   if ($question->getFromDB((int) $_POST['id'])) {
      if ($_POST["way"] == 'up') {
         $question->moveUp();
      } else {
         $question->moveDown();
      }
   }

} else {
   // Return to form list
   Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.php');
}
