<?php

include ("../../../inc/includes.php");

Session::checkRightsOr('reservation', array(READ, ReservationItem::RESERVEANITEM));

PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));

$res = new ReservationItem();
$res->display($_GET);

if (isset($_POST['submit'])) {
   $_SESSION['glpi_saved']['ReservationItem'] = $_POST;
} else {
   unset($_SESSION['glpi_saved']['ReservationItem']);
}

PluginFormcreatorWizard::footer();
?>