<?php
function plugin_formcreator_update_dev(Migration $migration) {
   global $DB;

   // Change id of search option for status of form_answer
   $table = 'glpi_displaypreferences';
   $query = "UPDATE `$table` SET `num`='8' WHERE `itemtype`='PluginFormcreatorForm_Answer' AND `num`='1'";
   $DB->query($query);
}