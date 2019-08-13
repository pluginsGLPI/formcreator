<?php

use function GuzzleHttp\json_encode;

include ('../../../inc/includes.php');
header('Content-Type: application/json');

if (!isset($_GET['itemtype']) || !isset($_GET['values'])) {
    http_response_code(400);
    die;
}

$itemtype = $_GET['itemtype'];
$values   = json_decode(stripcslashes($_GET['values']), true);

if (!is_a($itemtype, "CommonTreeDropdown", true)) {
    die;
}

$rootValue = Dropdown::EMPTY_VALUE;
if (isset($values['show_ticket_categories_root'])) {
    $rootValue = $values['show_ticket_categories_root'];
}
$root = Dropdown::show($itemtype, [
    'name'    => 'show_ticket_categories_root',
    'value'   => $rootValue,
    'rand'    => mt_rand(),
    'display' => false,
 ]);

$deptValue = 0;
if (isset($values['show_ticket_categories_depth'])) {
    $deptValue = $values['show_ticket_categories_depth'];
}
$depth = dropdown::showNumber('show_ticket_categories_depth', [
    'rand'    => $rand,
    'value'   => $deptValue,
    'min'     => 1,
    'max'     => 16,
    'toadd'   => [0 => __('No limit', 'formcreator')],
    'display' => false,
]);

echo json_encode([
    'root'  => $root,
    'depth' => $depth,
]);