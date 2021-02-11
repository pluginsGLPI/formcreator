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
 * @copyright Copyright © 2011 - 2020 Teclib'
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
$formLanguage = new PluginFormcreatorForm_Language();

if (isset($_POST['add'])) {
   $formLanguage->add($_POST);
   Html::back();
} else if (isset($_POST['update'])) {
   $formLanguage->update($_POST);
   Html::back();
} else if (isset($_POST['delete'])) {
   if ($formLanguage->getFromDB((int) $_POST['id'])) {
      $formLanguage->massDeleteTranslations($_POST);
   }
   Html::back();
} else {
   Html::header(
      PluginFormcreatorForm_Language::getTypeName(2),
      $_SERVER['PHP_SELF'],
      'admin',
      'PluginFormcreatorForm_Language',
      'option'
   );

   $_GET['id'] = (int) ($_GET['id'] ?? -1);
   if (!$formLanguage->getFromDB($_GET['id'])) {
      $_SESSION['glpilisturl'][$formLanguage::getType()] = Html::getBackUrl();
   } else {
      $_SESSION['glpilisturl'][$formLanguage::getType()] = PluginFormcreatorForm::getFormURLWithID($formLanguage->fields[PluginFormcreatorForm::getForeignKeyField()]);
   }
   $formLanguage->display([
      'ids' => $_GET['id']
   ]);

   Html::footer();
}
