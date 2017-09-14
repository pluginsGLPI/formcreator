<?php
include ("../../../inc/includes.php");

Session::checkRightsOr('reservation', array(READ, ReservationItem::RESERVEANITEM));

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));

$res = new ReservationItem();
$res->display($_GET);

if (isset($_POST['submit'])) {
   $_SESSION['glpi_saved']['ReservationItem'] = $_POST;
} else {
   unset($_SESSION['glpi_saved']['ReservationItem']);
}

PluginFormcreatorWizard::footer();
