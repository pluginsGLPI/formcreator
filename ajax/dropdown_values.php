<?php
include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

// Verifier si on utilise un objet creer à partir de la référentiel
if ($_REQUEST['dropdown_itemtype'] == 'Applications') {
    $_REQUEST['dropdown_itemtype'] = 'PluginFormcreatorReferentielsApplications';
}
if($_REQUEST['dropdown_itemtype'] == '0' || !class_exists($_REQUEST['dropdown_itemtype'])) {
   Dropdown::showFromArray("dropdown_default_value", array(), array('display_emptychoice'   => true));
} else {
   Dropdown::show($_REQUEST['dropdown_itemtype'], array(
         'name' => 'dropdown_default_value',
         'rand' => mt_rand(),
   ));
}
