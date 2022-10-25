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

require_once ('../../../inc/includes.php');

// Check if plugin is activated...
if (!(new Plugin())->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

if (!PluginFormcreatorIssue::canView()) {
   Html::displayRightError();
}
if (Session::getCurrentInterface() == "helpdesk") {
   Html::helpHeader(
      __('Service catalog', 'formcreator'),
      'my_assistance_requests',
      PluginFormcreatorIssue::class
   );
} else {
   Html::header(
      __('Service catalog', 'formcreator'),
      '',
      'admin',
      PluginFormcreatorForm::class
   );
}

if (Session::getCurrentInterface() == 'helpdesk') {
   PluginFormcreatorCommon::showMiniDashboard();
}

//backup session value
$save_session_fold_search = $_SESSION['glpifold_search'];
//hide search if need
if (PluginFormcreatorEntityconfig::getUsedConfig('is_search_issue_visible', Session::getActiveEntity()) == PluginFormcreatorEntityconfig::CONFIG_SEARCH_ISSUE_HIDDEN) {
   $_SESSION['glpifold_search'] = true;
}

Search::show('PluginFormcreatorIssue');

//restore session value
$_SESSION['glpifold_search'] = $save_session_fold_search;

if (Session::getCurrentInterface() == "helpdesk") {
   Html::helpFooter();
} else {
   Html::footer();
}
