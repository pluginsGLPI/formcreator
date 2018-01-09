<?php

function plugin_formcreator_update_dev(Migration $migration) {
   global $DB;

   // Change id of search option for status of form_answer
   $table = 'glpi_displaypreferences';
   $query = "UPDATE `$table` SET `num`='8' WHERE `itemtype`='PluginFormcreatorForm_Answer' AND `num`='1'";
   $DB->query($query);

   // Remove abusive encoding in targets
   $table = 'glpi_plugin_formcreator_targets';
   $request = [
      'FROM' => $table,
   ];
   foreach ($DB->request($request) as $row) {
      $id = $row['id'];
      $name = Toolbox::addslashes_deep(html_entity_decode($row['name'], ENT_QUOTES|ENT_HTML5));
      $id = $row['id'];
      $DB->query("UPDATE `$table` SET `name`='$name' WHERE `id` = '$id'");
   }

   // Remove abusive encding in  target tickets
   $table = 'glpi_plugin_formcreator_targettickets';
   $request = [
      'FROM' => $table,
   ];
   foreach ($DB->request($request) as $row) {
      $id = $row['id'];
      $name = Toolbox::addslashes_deep(html_entity_decode($row['name'], ENT_QUOTES|ENT_HTML5));
      $id = $row['id'];
      $DB->query("UPDATE `$table` SET `name`='$name' WHERE `id` = '$id'");
   }

   // Remove abusive encding in  target tickets
   $table = 'glpi_plugin_formcreator_targetchanges';
   $request = [
      'FROM' => $table,
   ];
   foreach ($DB->request($request) as $row) {
      $id = $row['id'];
      $name = Toolbox::addslashes_deep(html_entity_decode($row['name'], ENT_QUOTES|ENT_HTML5));
      $id = $row['id'];
      $DB->query("UPDATE `$table` SET `name`='$name' WHERE `id` = '$id'");
   }
}
