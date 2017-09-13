<?php
/**
 *
 * @param Migration $migration
 *
 * @return void
 */
function plugin_formcreator_update_dev(Migration $migration) {
   $migration->displayMessage("Upgrade to schema version 2.6");

   plugin_formcreator_updateForm_Answer_2_6($migration);

   $migration->executeMigration();
}

function plugin_formcreator_updateForm_Answer_2_6(Migration $migration) {
   global $DB;

   $migration->displayMessage("Upgrade glpi_plugin_formcreator_forms_answers");

   $table = 'glpi_plugin_formcreator_forms_answers';
   $migration->displayMessage("Upgrade $table");

   $migration->addField($table, 'users_id_validator', 'integer', ['after' => 'requester_id']);
   $migration->addField($table, 'groups_id_validator', 'integer', ['after' => 'users_id_validator']);
   $migration->addKey($table, 'users_id_validator');
   $migration->addKey($table, 'groups_id_validator');
   $migration->migrationOneTable($table);

   $formTable = 'glpi_plugin_formcreator_forms';
   $query = "UPDATE `$table`
                INNER JOIN `$formTable` ON (`$table`.`plugin_formcreator_forms_id` = `$formTable`.`id`)
                SET `users_id_validator` = 'validator_id'
                WHERE `$formTable`.`validation_required` = '1'";
   $DB->query($query) or plugin_formcreator_upgrade_error($migration);
   $query = "UPDATE `$table`
                INNER JOIN `$formTable` ON (`$table`.`plugin_formcreator_forms_id` = `$formTable`.`id`)
                SET `groups_id_validator` = 'validator_id'
                WHERE `$formTable`.`validation_required` = '2'";
   $DB->query($query) or plugin_formcreator_upgrade_error($migration);

   $migration->dropKey($table, 'validator_id');
   $migration->dropField($table, 'validator_id');

   // update questions
   $table = 'glpi_plugin_formcreator_questions';
   $migration->displayMessage("Upgrade $table");

   $question = new PluginFormcreatorQuestion();
   $rows = $question->find("`fieldtype` = 'dropdown' AND `values` = 'ITILCategory'");
   foreach ($rows as $id => $row) {
      $updatedValue = json_encode([
         'itemtype'                       => $row['values'],
         'show_ticket_categories'         => 'both',
         'show_ticket_categories_depth'   => 0
      ]);
      // Don't use update() method because the json will be HTML-entities-ified (see prepareInputForUpdate() )
      $query = "UPDATE `$table` SET `values`='$updatedValue' WHERE `id`='$id'";
      $DB->query($query) or plugin_formcreator_upgrade_error($migration);
   }

   // add location rule
   $enum_location_rule = "'".implode("', '", array_keys(PluginFormcreatorTargetTicket::getEnumLocationRule()))."'";
   if (!FieldExists('glpi_plugin_formcreator_targettickets', 'location_rule', false)) {
      $migration->addField(
         'glpi_plugin_formcreator_targettickets',
         'location_rule',
         "ENUM($enum_location_rule) NOT NULL DEFAULT 'none'",
         ['after' => 'category_question']
      );
   } else {
      $current_enum_location_rule = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targettickets', 'location_rule');
      if (count($current_enum_location_rule) != count(PluginFormcreatorTargetTicket::getEnumLocationRule())) {
         $migration->changeField(
            'glpi_plugin_formcreator_targettickets',
            'location_rule',
            'location_rule',
            "ENUM($enum_location_rule) NOT NULL DEFAULT 'none'",
            ['after' => 'category_question']
         );
      }
   }
   $migration->addField('glpi_plugin_formcreator_targettickets', 'location_question', 'integer', ['after' => 'location_rule']);

   $migration->executeMigration();
}
