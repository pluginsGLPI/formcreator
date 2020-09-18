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

$formFk = PluginFormcreatorForm::getForeignKeyField();
if (isset($_POST['profiles_id']) && isset($_POST[$formFk])) {
   if (isset($_POST['access_rights'])) {
      $form = new PluginFormcreatorForm();
      $form->update([
         'id'            => (int) $_POST[$formFk],
         'access_rights' => (int) $_POST['access_rights'],
         'is_captcha_enabled' => $_POST['is_captcha_enabled'],
      ]);
   }

   $form_profile = new PluginFormcreatorForm_Profile();
   $form_profile->deleteByCriteria([
         $formFk    => (int) $_POST[$formFk],
   ]);

   foreach ($_POST['profiles_id'] as $profile_id) {
      if ($profile_id != 0) {
         $form_profile = new PluginFormcreatorForm_Profile();
         $form_profile->add([
               'plugin_formcreator_forms_id' => (int) $_POST[$formFk],
               'profiles_id'                 => (int) $profile_id,
         ]);
      }
   }
}
Html::back();
