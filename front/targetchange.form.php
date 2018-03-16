<?php
/**
 * LICENSE
 *
 * Copyright © 2011-2018 Teclib'
 *
 * This file is part of Formcreator Plugin for GLPI.
 *
 * Formcreator is a plugin that allow creation of custom, easy to access forms
 * for users when they want to create one or more GLPI tickets.
 *
 * Formcreator Plugin for GLPI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2018 Teclib
 * @license   GPLv2 https://www.gnu.org/licenses/gpl2.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ------------------------------------------------------------------------------
 */
include ("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isActivated('formcreator')) {
   Html::displayNotFoundError();
}
$targetticket = new PluginFormcreatorTargetChange();

// Edit an existing target change
if (isset($_POST["update"])) {
   $target = new PluginFormcreatorTarget();
   $found  = $target->find('items_id = ' . (int) $_POST['id']);
   $found  = array_shift($found);
   $target->update(['id' => $found['id'], 'name' => $_POST['name']]);
   $targetticket->update($_POST);
   Html::back();

} else if (isset($_POST['actor_role'])) {
   $id          = (int) $_POST['id'];
   $actor_value = isset($_POST['actor_value_' . $_POST['actor_type']])
                  ? $_POST['actor_value_' . $_POST['actor_type']]
                  : '';
   $use_notification = ($_POST['use_notification'] == 0) ? 0 : 1;
   $targetChange_actor = new PluginFormcreatorTargetChange_Actor();
   $targetChange_actor->add([
         'plugin_formcreator_targetchanges_id'  => $id,
         'actor_role'                           => $_POST['actor_role'],
         'actor_type'                           => $_POST['actor_type'],
         'actor_value'                          => $actor_value,
         'use_notification'                     => $use_notification
   ]);
   Html::back();

} else if (isset($_GET['delete_actor'])) {
   $targetChange_actor = new PluginFormcreatorTargetChange_Actor();
   $targetChange_actor->delete([
         'id'                                   => (int) $_GET['delete_actor']
   ]);
   Html::back();

   // Show target ticket form
} else {
   Html::header(
         __('Form Creator', 'formcreator'),
         $_SERVER['PHP_SELF'],
         'admin',
         'PluginFormcreatorForm'
         );

   $itemtype = "PluginFormcreatorTargetChange";
   $target   = new PluginFormcreatorTarget;
   $found    = $target->find("itemtype = '$itemtype' AND items_id = " . (int) $_REQUEST['id']);
   $first    = array_shift($found);
   $form     = new PluginFormcreatorForm;
   $form->getFromDB($first['plugin_formcreator_forms_id']);

   $_SESSION['glpilisttitle'][$itemtype] = sprintf(__('%1$s = %2$s'),
         $form->getTypeName(1), $form->getName());
   $_SESSION['glpilisturl'][$itemtype]   = $form->getFormURL()."?id=".$form->getID();

   $targetticket->display($_REQUEST);

   Html::footer();
}
