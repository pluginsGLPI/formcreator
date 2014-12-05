<?php
include ('../../../inc/includes.php');
Session::checkRight("entity", UPDATE);

echo '<form name="form_target" method="post" action="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/target.form.php">';
echo '<table class="tab_cadre_fixe">';

echo '<tr><th colspan="4">' . __('Add a destination', 'formcreator') . '</th></tr>';

echo '<tr class="line1">';
echo '<td width="15%"><strong>' . __('Name') . ' <span style="color:red;">*</span></strong></td>';
echo '<td width="40%"><input type="text" name="name" style="width:100%;" value="" /></td>';
echo '<td width="15%"><strong>' . _n('Type', 'Types', 1) . ' <span style="color:red;">*</span></strong></td>';
echo '<td width="30%">';
Dropdown::showFromArray('itemtype', array(
   ''                              => '-----',
   'PluginFormcreatorTargetTicket' => __('Ticket'),
));
echo '</td>';
echo '</tr>';

echo '<tr class="line0">';
echo '<td colspan="4" class="center">';
echo '<input type="hidden" name="plugin_formcreator_forms_id" value="' . (int) $_REQUEST['form_id'] . '" />';
echo '<input type="submit" name="add" class="submit_button" value="' . __('Add') . '" />';
echo '</td>';
echo '</tr>';

echo '</table>';
Html::closeForm();
