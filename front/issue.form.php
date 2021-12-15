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

global $CFG_GLPI;
require_once ('../../../inc/includes.php');

// Check if plugin is activated...
if (!(new Plugin())->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

Session::checkValidSessionId();

// Accessing an issue from a tech profile, redirect to ticket page
if (isset($_REQUEST['id']) && Session::getCurrentInterface() == 'central') {
   /** @var PluginFormcreatorIssue $issue */
   $issue = PluginFormcreatorIssue::getById((int) $_REQUEST['id']);
   $id = $issue->fields['items_id'];
   $itemtype = $issue->fields['itemtype'];
   Html::redirect($itemtype::getFormURLWithID($id));
}

// Show issue only if service catalog is enabled
if (!plugin_formcreator_replaceHelpdesk()) {
   Html::redirect($CFG_GLPI['root_doc']."/front/helpdesk.public.php");
}

// force layout of glpi
PluginFormcreatorCommon::saveLayout();
$_SESSION['glpilayout'] = "lefttab";

/** @var PluginFormcreatorIssue $issue */
$issue = PluginFormcreatorIssue::getById((int) $_REQUEST['id']);

if ($issue) {
   $itemtype = $issue->fields['itemtype'];

   // Trick to change the displayed id as Html::includeHeader() rely on request data
   $old_id = $_GET['id'];
   $_GET['id'] = $issue->fields['display_id'];

   // Specific case, viewing a ticket from a formanswer result
   if ($itemtype == PluginFormcreatorFormAnswer::class && isset($_GET['tickets_id'])) {
      $itemtype = Ticket::class;
      $_GET['id'] = "f_$_GET[tickets_id]";
   }

   $header = $itemtype::getTypeName(1);
   if (Session::getCurrentInterface() == "helpdesk") {
      Html::helpHeader($header);
   } else {
      Html::header($header);
   }

   // Reset request param in case some other code depends on it
   $_GET['id'] = $old_id;

   $issue->display($_REQUEST);
} else {
   $header = __('Item not found');
   if (Session::getCurrentInterface() == "helpdesk") {
      Html::helpHeader($header);
   } else {
      Html::header($header);
   }
   PluginFormcreatorCommon::restoreLayout();
   Html::displayNotFoundError();
}

if (Session::getCurrentInterface() == "helpdesk") {
   Html::helpFooter();
} else {
   Html::footer();
}

PluginFormcreatorCommon::restoreLayout();
