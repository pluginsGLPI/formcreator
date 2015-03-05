<?php
include ('../../../inc/includes.php');

$currentValues  = json_decode(stripslashes($_POST['values']), true);
foreach ($currentValues as $id => &$value) {
   $value = str_replace("'", "&apos;", htmlentities(html_entity_decode($value)));
}
$questionToShow = array();

foreach ($currentValues as $id => $value) {
   $questionToShow[$id] = PluginFormcreatorFields::isVisible($id, $currentValues);
}
echo json_encode($questionToShow);