<?php
include ("../../../inc/includes.php");

Session::checkLoginUser();

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

if (!isset($_GET["reservationitems_id"])) {
   $_GET["reservationitems_id"] = '';
}

PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));
Reservation::showCalendar($_GET["reservationitems_id"]);
PluginFormcreatorWizard::footer();
