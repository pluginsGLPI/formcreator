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

   // Remove abusive encoding in target tickets
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

   // Remove abusive encoding in target changes
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

   // decode html entities in answers
   $request = [
      'SELECT' => ['glpi_plugin_formcreator_answers.*'],
      'FROM' => 'glpi_plugin_formcreator_answers',
      'INNER JOIN' => ['glpi_plugin_formcreator_questions' => [
         'FKEY' => [
            'glpi_plugin_formcreator_answers' => 'plugin_formcreator_questions_id',
            'glpi_plugin_formcreator_questions' => 'id'
         ]
      ]],
      'WHERE' => ['fieldtype' => 'textarea']
   ];
   foreach ($DB->request($request) as $row) {
      $answer = Toolbox::addslashes_deep(html_entity_decode($row['answer']));
      $id = $row['id'];
      $DB->query("UPDATE `glpi_plugin_formcreator_answers` SET `answer`='$answer' WHERE `id` = '$id'");
   }

   // decode html entities in question definitions
   $request = [
      'FROM'   => 'glpi_plugin_formcreator_questions',
      'WHERE'  => [
         'fieldtype' => ['select', 'multiselect', 'checkboxes', 'radios']
      ]
   ];
   foreach ($DB->request($request) as $row) {
      $values = html_entity_decode($row['values']);
      $defaultValues = html_entity_decode($row['default_values']);
      $DB->query("UDATE `glpi_plugin_formcreator_questions` SET `values` = '$values', `default_values` = '$defaultValues'");
   }

   // decode html entities in name of section
   foreach ($DB->request(['FROM' => 'glpi_plugin_formcreator_sections']) as $row) {
      $name = html_entity_decode($row['name']);
      $DB->query("UPDATE `glpi_plugin_formcreator_sections` SET `name`='$name'");
   }

   // decode html entities in name of questions
   foreach ($DB->request(['FROM' => 'glpi_plugin_formcreator_questions']) as $row) {
      $name = html_entity_decode($row['name']);
      $DB->query("UPDATE `glpi_plugin_formcreator_questions` SET `name`='$name'");
   }
}
