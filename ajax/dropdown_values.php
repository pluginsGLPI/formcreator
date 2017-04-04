<?php
include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

// Verifier si on utilise un objet creer à partir de la référentiel
if ($_REQUEST['dropdown_itemtype'] == 'Applications') {
    $_REQUEST['dropdown_itemtype'] = 'PluginFormcreatorReferentielsApplications';
}
if (class_exists($_REQUEST['dropdown_itemtype'])) {
    Dropdown::show($_REQUEST['dropdown_itemtype'], array(
        'name' => 'dropdown_default_value',
        'rand' => mt_rand(),
    ));
} else {
    echo '<select name="dropdown_dropdown_default_value<?php echo $rand; ?>">
            <option value="">---</option>
         </select>';
}
