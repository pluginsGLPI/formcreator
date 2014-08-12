<?php
include ('../../../inc/includes.php');

$form_table  = getTableForItemType('PluginFormcreatorForm');
$where       = getEntitiesRestrictRequest( "", $form_table, "", "", true, false);
$table_fp    = getTableForItemType('PluginFormcreatorFormprofiles');
$query_forms = "SELECT $form_table.id, $form_table.name, $form_table.description
                FROM $form_table
                WHERE $form_table.`is_active` = 1
                AND ($form_table.`language` = '{$_SESSION['glpilanguage']}' OR $form_table.`language` = '')
                AND $where
                AND (`access_rights` != " . PluginFormcreatorForm::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
                   SELECT plugin_formcreator_forms_id
                   FROM $table_fp
                   WHERE plugin_formcreator_profiles_id = " . (int) $_SESSION['glpiactiveprofile']['id'] . "))
                AND helpdesk_home = 1
                ORDER BY $form_table.name ASC";
$result_forms = $GLOBALS['DB']->query($query_forms) or die($GLOBALS['DB']->error());

if (count($GLOBALS['DB']->numrows($result_forms)) > 0) {
   echo '<table class="tab_cadrehov">';

   echo '<tr>';
   echo '<th>' . _n('Form', 'Forms', 2, 'formcreator') . '</th>';
   echo '</tr>';

   $i = 0;
   while($form = $GLOBALS['DB']->fetch_array($result_forms)) {
      $i++;
      echo '<tr class="line' . ($i % 2) . '">';
      echo '<td><a href="../plugins/formcreator/front/showform.php?id=' . $form['id'] . '">' . $form['name'] . '</a></td>';
      echo '</tr>';
   }

   echo '</table>';

   echo '<br /><br />';
}
