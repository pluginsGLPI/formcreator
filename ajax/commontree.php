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

// Check required parameters
if (ctype_digit($_GET['itemtype']) || !isset($_GET['itemtype']) || !isset($_GET['root']) || !isset($_GET['maxDepth'])) {
   http_response_code(400);
   die;
}

// Load parameters
$itemtype       = $_GET['itemtype'];
$root           = $_GET['root'];
$depth          = $_GET['maxDepth'];
$selectableRoot = $_GET['selectableRoot'];

// This should only be used for dropdowns
if (!is_a($itemtype, CommonTreeDropdown::class, true)) {
   http_response_code(400);
   die;
}

// Build the row content
$rand = mt_rand();
$additions = '<td>';
$additions .= '<label for="dropdown_show_tree_root'.$rand.'" id="label_show_tree_root">';
$additions .= __('Subtree root', 'formcreator');
$additions .= '</label>';
$additions .= '<br>';
$additions .= '<label for="dropdown_selectable_tree_root'.$rand.'" id="label_selectable_tree_root">';
$additions .= __('Selectable', 'formcreator');
$additions .= '</label>';
$additions .= '</td>';
$additions .= '<td>';
$additions .= Dropdown::show($itemtype, [
   'name'  => 'show_tree_root',
   'value' => $root,
   'rand'  => $rand,
   'display' => false,
]);
$additions .= '<br>';
$additions .= Dropdown::showYesNo('selectable_tree_root', $selectableRoot, -1, ['display' => false]);
$additions .= '</td>';
$additions .= '<td>';
$additions .= '<label for="dropdown_show_tree_depth'.$rand.'" id="label_show_tree_depth">';
$additions .= __('Limit subtree depth', 'formcreator');
$additions .= '</label>';
$additions .= '</td>';
$additions .= '<td>';
$additions .= dropdown::showNumber(
   'show_tree_depth', [
      'rand'  => $rand,
      'value' => $depth,
      'min' => 1,
      'max' => 16,
      'toadd' => [0 => __('No limit', 'formcreator')],
      'display' => false,
   ]
);
$additions .= '</td>';

echo $additions;
