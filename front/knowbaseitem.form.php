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

include ("../../../inc/includes.php");

// Check if plugin is activated...
if (!(new Plugin())->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

$kb = new KnowbaseItem();

if (!isset($_GET["id"])) {
   Html::displayNotFoundError();
}

$kb->check($_GET["id"], READ);

if (Session::getCurrentInterface() == "helpdesk") {
   Html::helpHeader(__('Service catalog', 'formcreator'));
} else {
   Html::header(__('Service catalog', 'formcreator'));
}

$available_options = ['item_itemtype', 'item_items_id', 'id'];
$options           = [];
foreach ($available_options as $key) {
   if (isset($_GET[$key])) {
      $options[$key] = $_GET[$key];
   }
}
$_SESSION['glpilisturl']['KnowbaseItem'] = Plugin::getWebDir('formcreator') . "/front/wizard.php";
$kb->showFull($options);

if (Session::getCurrentInterface() == "helpdesk") {
   Html::helpFooter();
} else {
   Html::footer();
}

