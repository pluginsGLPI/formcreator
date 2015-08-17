<?php
include ('../../../inc/includes.php');

Session::checkRight("entity", "w");
if(class_exists($_REQUEST['dropdown_itemtype'])) {
   if ("User" == $_REQUEST['dropdown_itemtype']) {
      $current_checked = ($_REQUEST['value'] == -1) ? 'checked="checked"' : '';
      User::dropdown(array(
         'name'                => 'dropdown_default_value',
         'value'               => $_REQUEST['value'],
         'comments'            => false,
         'right'               => 'all',
         'display_emptychoice' => true,
         'rand'                => $_REQUEST['rand'],
      ));
      echo '<br /><b>' . __('or') . '</b><br />';
      echo '<label>';
      echo '<input type="checkbox" name="dropdown_default_value" value="-1" ' . $current_checked . ' /> ';
      echo __('Use current user', 'formcreator');
      echo '</label>';
   } else {
      Dropdown::show($_REQUEST['dropdown_itemtype'], array(
         'name'  => 'dropdown_default_value',
         'value' => $_REQUEST['value'],
         'rand'  => $_REQUEST['rand'],
      ));
   }
} else {
   echo '<select name="dropdown_dropdown_default_value<?php echo $rand; ?>">
            <option value="">---</option>
         </select>';
}
