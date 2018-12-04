<?php
/**
 *
 * @param Migration $migration
 *
 * @return void
 */
function plugin_formcreator_update_2_7($migration) {
   global $DB;

   // Upgrade plugin configuration table
   $table = 'glpi_plugin_formcreator_entityconfigs';
   $migration->displayMessage("Upgrade $table");
   $migration->addField($table, 'external_links_prefix', 'string', ['after' => 'replace_helpdesk']);
   $migration->addField($table, 'external_links_icon', 'string', ['after' => 'external_links_prefix']);
   $migration->addField($table, 'external_links_title', 'string', ['after' => 'external_links_icon']);
   $migration->addField($table, 'tickets_summary', 'integer', ['after' => 'external_links_title', 'value' => '1']);
   $migration->addField($table, 'user_preferences', 'integer', ['after' => 'tickets_summary', 'value' => '1']);
   $migration->addField($table, 'avatar', 'integer', ['after' => 'user_preferences', 'value' => '1']);
   $migration->addField($table, 'user_name', 'integer', ['after' => 'avatar', 'value' => '0']);
   $migration->addField($table, 'profile_selector', 'integer', ['after' => 'user_name', 'value' => '1']);
   // Migrate regex question parameters
   $table = 'glpi_plugin_formcreator_questions';
   if ($DB->fieldExists($table, 'regex')) {
      $request = [
      'FROM' => $table,
      'WHERE' => ['fieldtype' => ['float', 'integer', 'text', 'textarea']]
      ];
      foreach ($DB->request($request) as $row) {
         $id = $row['id'];
         $regex = $DB->escape($row['regex']);
         $uuid = plugin_formcreator_getUuid();
         $DB->query("INSERT INTO `glpi_plugin_formcreator_questionregexes`
                             SET `plugin_formcreator_questions_id`='$id', `fieldname`='regex', `regex`='$regex', `uuid`='$uuid'"
         ) or plugin_formcreator_upgrade_error($migration);
      }
      $migration->dropField($table, 'regex');
   }

      // Migrate range question parameters
      $table = 'glpi_plugin_formcreator_questions';
   if ($DB->fieldExists($table, 'range_min')) {
      $request = [
      'FROM' => $table,
      'WHERE' => ['fieldtype' => ['float', 'integer', 'checkboxes', 'multiselect', 'text']]
      ];
      foreach ($DB->request($request) as $row) {
         $id = $row['id'];
         $rangeMin = $DB->escape($row['range_min']);
         $rangeMax = $DB->escape($row['range_max']);
         $uuid = plugin_formcreator_getUuid();
         $DB->query("INSERT INTO `glpi_plugin_formcreator_questionranges`
                             SET `plugin_formcreator_questions_id`='$id', `fieldname`='range', `range_min`='$rangeMin', `range_max`='$rangeMax', `uuid`='$uuid'"
         ) or plugin_formcreator_upgrade_error($migration);
      }
      $migration->dropField($table, 'range_min');
      $migration->dropField($table, 'range_max');

      // decode html entities in answers
      $request = [
         'SELECT' => [
            'glpi_plugin_formcreator_answers.*'
         ],
         'FROM' => 'glpi_plugin_formcreator_answers',
         'INNER JOIN' => [
            'glpi_plugin_formcreator_questions' => [
               'FKEY' => [
                  'glpi_plugin_formcreator_answers' => 'plugin_formcreator_questions_id',
                  'glpi_plugin_formcreator_questions' => 'id'
               ]
            ]
         ],
         'WHERE' => ['fieldtype' => 'textarea']
      ];
      foreach ($DB->request($request) as $row) {
         $answer = Toolbox::addslashes_deep(html_entity_decode($row['answer']));
         $id = $row['id'];
         $DB->query("UPDATE `glpi_plugin_formcreator_answers` SET `answer`='$answer' WHERE `id` = '$id'");
      }
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
      $id = $row['id'];
      $DB->query("UPDATE `glpi_plugin_formcreator_questions` SET `values` = '$values', `default_values` = '$defaultValues' WHERE `id` = '$id'");
   }

   // decode html entities in name of questions
   foreach ($DB->request(['FROM' => 'glpi_plugin_formcreator_questions']) as $row) {
      $name = html_entity_decode($row['name']);
      $id = $row['id'];
      $DB->query("UPDATE `glpi_plugin_formcreator_questions` SET `name`='$name' WHERE `id` = '$id'");
   }

}
