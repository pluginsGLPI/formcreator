<?php
include ('../../../inc/includes.php');
Session::checkRight("entity", UPDATE);

$target = new PluginFormcreatorTarget();
$target->showSubForm(0);
