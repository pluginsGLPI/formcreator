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

if (!isset($_REQUEST['items_id'])) {
   http_response_code(400);
   exit;
}
$itemId = (int) $_REQUEST['items_id'];

if (!isset($_REQUEST['itemtype'])) {
   http_response_code(400);
   exit;
}
$itemtype = $_REQUEST['itemtype'];

if (!class_exists($itemtype)) {
   http_response_code(400);
   exit;
}

if (!is_subclass_of($itemtype, CommonDBTM::class)) {
   http_response_code(400);
   exit;
}
$item = new $itemtype();
$parentType = $item::$itemtype;
$parent = new $parentType();
$parentFk = $parent::getForeignKeyField();
if (!$item->getFromDB($itemId)) {
   // check for existence of container object 
   if (!isset($_REQUEST[$parentFk])) {
      http_response_code(400);
      exit;   
   }
   $parentId = (int) $_REQUEST[$parentFk];
   if (!$parent->getFromDB($parentId)) {
      http_response_code(400);
      exit;   
   }
   // Set the relation of the empty item with the container
   $item->getEmpty();
   $item->fields[$parentFk] = $parentId;
} else {
   $parentId = $item->fields[$parentFk];
   if (!$parent->getFromDB($parentId)) {
      http_response_code(400);
      exit;   
   }
}
$form = new PluginFormcreatorForm();
switch ($itemtype) {
   case PluginFormcreatorQuestion::class:

      $form->getFromDBBySection($parent);
      break;
   case PluginFormcreatorSection::class:
   case PluginFormcreatorTargetTicket::class:
   case PluginFormcreatorTargetChange::class:
         $form->getFromDB($parentId);
      break;
   default:
      http_response_code(400);
      exit;
}
if ($form->isNewItem()) {
   http_response_code(400);
   exit;
}

// get an empty condition HTML table row
$condition = new PluginFormcreatorCondition();
echo $condition->getConditionHtml($item);
