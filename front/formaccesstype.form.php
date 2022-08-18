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

// Get target form
$form_id = $_POST[PluginFormcreatorForm::getForeignKeyField()] ?? null;
if (is_null($form_id)) {
   http_response_code(400);
   die;
}

// No update if `access_rights` is not modified, keeping the save behavior as
// the previous form_profile.form.php file
if (!isset($_POST['access_rights'])) {
   Html::back();
   die;
}

// Try to load form
$form = PluginFormcreatorForm::getById($form_id);
if (!$form) {
   Html::displayNotFoundError();
}

// Prepare input
$input = [
   'id'                 => (int) $form_id,
   'is_captcha_enabled' => $_POST['is_captcha_enabled'] ?? false,
   'access_rights'      => (int) $_POST['access_rights'],
   'users'              => [],
   'groups'             => [],
   'profiles'           => [],
   'entities'           => [],
];

$restrictions = $_POST['restrictions'] ?? null;
if (!is_null($restrictions)) {
   $input['users']    = AbstractRightsDropdown::getPostedIds($restrictions, User::class);
   $input['groups']   = AbstractRightsDropdown::getPostedIds($restrictions, Group::class);
   $input['profiles'] = AbstractRightsDropdown::getPostedIds($restrictions, Profile::class);
}

// Update form
$form->update($input);

Html::back();
