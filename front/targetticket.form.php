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

// Check if plugin is activated...
if (!(new Plugin())->isActivated('formcreator')) {
   Html::displayNotFoundError();
}
$targetTicket = new PluginFormcreatorTargetTicket();

// Edit an existing target ticket
if (isset($_POST['update'])) {
   $targetTicket->getFromDB((int) $_POST['id']);
   if (!$targetTicket->canUpdateItem()) {
      Session::addMessageAfterRedirect(__('No right to update this item.', 'formcreator'), false, ERROR);
   } else {
      $targetTicket->update($_POST);
   }
   Html::back();

} else if (isset($_POST['actor_role'])) {
   $id = (int) $_POST['id'];
   $targetTicket->getFromDB($id);
   if (!$targetTicket->canUpdateItem()) {
      Session::addMessageAfterRedirect(__('No right to update this item.', 'formcreator'), false, ERROR);
   } else {
      $actor_value = $_POST['actor_value_' . $_POST['actor_type']] ?? 0;
      $use_notification = ($_POST['use_notification'] == 0) ? 0 : 1;
      $targetTicket_actor = new PluginFormcreatorTarget_Actor();
      $targetTicket_actor->add([
         'itemtype'         => $targetTicket->getType(),
         'items_id'         => $id,
         'actor_role'       => $_POST['actor_role'],
         'actor_type'       => $_POST['actor_type'],
         'actor_value'      => $actor_value,
         'use_notification' => $use_notification,
      ]);
   }
   Html::back();

} else if (isset($_GET['delete_actor'])) {
   $requiredKeys = ['id'];
   if (count(array_intersect(array_keys($_GET), $requiredKeys)) < count($requiredKeys)) {
      Session::addMessageAfterRedirect(__('Bad request while deleting an actor.', 'formcreator'), false, ERROR);
      Html::back();
   }
   $id = (int) $_GET['id'];
   $targetTicket->getFromDB($id);
   if (!$targetTicket->canUpdateItem()) {
      Session::addMessageAfterRedirect(__('No right to update this item.', 'formcreator'), false, ERROR);
   } else {
      $targetTicket_actor = new PluginFormcreatorTarget_Actor();
      $targetTicket_actor->delete([
         'itemtype' => $targetTicket->getType(),
         'items_id' => $id,
         'id'       => (int) $_GET['delete_actor']
      ]);
   }
   Html::back();

   // Show target ticket form
} else {
   Html::header(
      __('Form Creator', 'formcreator'),
      $_SERVER['PHP_SELF'],
      'admin',
      'PluginFormcreatorForm'
   );


   $targetTicket->getFromDB((int) $_REQUEST['id']);
   $form = PluginFormcreatorForm::getByItem($targetTicket);
   $_SESSION['glpilisttitle'][$targetTicket::getType()] = sprintf(
      __('%1$s = %2$s'),
      $form->getTypeName(1), $form->getName()
   );
   $_SESSION['glpilisturl'][$targetTicket::getType()]   = $form->getFormURL()."?id=".$form->getID();

   $targetTicket->display([
      'id' => $_GET['id'],
   ]);

   Html::footer();
}
