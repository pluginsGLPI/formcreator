<?php
include ('../../../inc/includes.php');

$currentValues  = json_decode(stripslashes($_POST['values']), true);
$questionToShow = array();

foreach ($currentValues as $id => $value) {
   $questionToShow[$id] = PluginFormcreatorFields::isVisible($id, $currentValues);
}
echo json_encode($questionToShow);