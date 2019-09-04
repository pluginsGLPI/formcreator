<?php
include ('../../../inc/includes.php');

// Check required parameters
if (!isset($_GET['itemtype']) || !isset($_GET['root']) || !isset($_GET['maxDepth'])) {
   http_response_code(400);
   die;
}

// Load parameters
$itemtype = $_GET['itemtype'];
$root     = $_GET['root'];
$depth    = $_GET['maxDepth'];

// This should only be used for dropdowns
if (!is_a($itemtype, CommonTreeDropdown::class, true)) {
   http_response_code(400);
   die;
}

// Build the row content
$rand = mt_rand();
$additions = '<td>';
$additions .= '<label for="dropdown_root_ticket_categories'.$rand.'" id="label_root_ticket_categories">';
$additions .= __('Subtree root', 'formcreator');
$additions .= '</label>';
$additions .= '</td>';
$additions .= '<td>';
$additions .= Dropdown::show($itemtype, [
   'name'  => 'show_ticket_categories_root',
   'value' => $root,
   'rand'  => $rand,
   'display' => false,
]);
$additions .= '</td>';
$additions .= '<td>';
$additions .= '<label for="dropdown_show_ticket_categories_depth'.$rand.'" id="label_show_ticket_categories_depth">';
$additions .= __('Limit subtree depth', 'formcreator');
$additions .= '</label>';
$additions .= '</td>';
$additions .= '<td>';
$additions .= dropdown::showNumber(
   'show_ticket_categories_depth', [
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
