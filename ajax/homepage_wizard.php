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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
include ('../../../inc/includes.php');

if (!isset($_SESSION['glpiactiveprofile']['id'])) {
   // Session is not valid then exit
   exit;
}

if ($_REQUEST['wizard'] == 'categories') {
   plugin_formcreator_showWizardCategories();
} else if ($_REQUEST['wizard'] == 'forms') {
   if (isset($_REQUEST['categoriesId'])) {
      $categoriesId = (int) $_REQUEST['categoriesId'];
   } else {
      $categoriesId = 0;
   }
   $keywords = isset($_REQUEST['keywords']) ? $_REQUEST['keywords'] : '';
   $helpdeskHome = isset($_REQUEST['helpdeskHome']) ? $_REQUEST['helpdeskHome'] != '0' : false;
   plugin_formcreator_showWizardForms($categoriesId, $keywords, $helpdeskHome);
} else if ($_REQUEST['wizard'] == 'toggle_menu') {
   $_SESSION['plugin_formcreator_toggle_menu'] = isset($_SESSION['plugin_formcreator_toggle_menu'])
                                                   ? !$_SESSION['plugin_formcreator_toggle_menu']
                                                   : true;
}

function plugin_formcreator_showWizardCategories() {
   $tree = PluginFormcreatorCategory::getCategoryTree(0, false);
   echo json_encode($tree, JSON_UNESCAPED_SLASHES);
}

function plugin_formcreator_showWizardForms($rootCategory = 0, $keywords = '', $helpdeskHome = false) {
   $form = new PluginFormcreatorForm();
   $formList = $form->showFormList($rootCategory, $keywords, $helpdeskHome);
   echo json_encode($formList, JSON_UNESCAPED_SLASHES);
}
