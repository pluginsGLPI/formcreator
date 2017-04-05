<?php
include ('../../../inc/includes.php');

$currentValues  = json_decode(stripslashes($_POST['values']), true);
$visibility = PluginFormcreatorFields::updateVisibility($currentValues);
echo json_encode($visibility);
exit();

