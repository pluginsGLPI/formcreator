<?php
include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

if($_REQUEST['dropdown_itemtype'] == '0' || !class_exists($_REQUEST['dropdown_itemtype'])) {
   $options = array(
         'display_emptychoice'   => true,
   );
   Dropdown::showFromArray("dropdown_default_value", array(), $options);
} else {
   Dropdown::show($_REQUEST['dropdown_itemtype'], array(
         'name' => 'dropdown_default_value',
         'rand' => mt_rand(),
   ));
}
