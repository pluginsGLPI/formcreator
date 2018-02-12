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
include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isActivated("formcreator")) {
   Html::displayNotFoundError();
}

if (isset($_POST["profiles_id"]) && isset($_POST["form_id"])) {

   if (isset($_POST['access_rights'])) {
      $form = new PluginFormcreatorForm();
      $form->update([
         'id'            => (int) $_POST['form_id'],
         'access_rights' => (int) $_POST['access_rights']
      ]);
   }

   $form_profile = new PluginFormcreatorForm_Profile();
   $form_profile->deleteByCriteria([
         'plugin_formcreator_forms_id'    => (int) $_POST["form_id"],
   ]);

   foreach ($_POST["profiles_id"] as $profile_id) {
      if ($profile_id != 0) {
         $form_profile = new PluginFormcreatorForm_Profile();
         $form_profile->add([
               'plugin_formcreator_forms_id' => (int) $_POST["form_id"],
               'profiles_id'                 => (int) $profile_id,
         ]);
      }
   }
   Html::back();
} else {
   Html::back();
}
