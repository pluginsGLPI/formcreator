<?php
/**
 *
 * @param Migration $migration
 *
 * @return void
 */
function plugin_formcreator_update_2_6(Migration $migration) {
   global $DB;

   $migration->displayMessage("Upgrade to schema version 2.6");

   $migration->displayMessage("Upgrade glpi_plugin_formcreator_forms_answers");

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

   // Update Form Answers
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

   // Fix bad foreign key
   $table = 'glpi_plugin_formcreator_answers';
   $migration->changeField($table, 'plugin_formcreator_question_id', 'plugin_formcreator_questions_id', 'integer');

   $table = 'glpi_plugin_formcreator_items_targettickets';
   if (!$DB->tableExists($table)) {
      $query = "CREATE TABLE `$table` (
                 `id` int(11) NOT NULL AUTO_INCREMENT,
                 `plugin_formcreator_targettickets_id` int(11) NOT NULL DEFAULT '0',
                 `link` int(11) NOT NULL DEFAULT '0',
                 `itemtype` varchar(255) NOT NULL DEFAULT '',
                 `items_id` int(11) NOT NULL DEFAULT '0',
                 `uuid` varchar(255) DEFAULT NULL,
                 PRIMARY KEY (`id`),
                 INDEX `plugin_formcreator_targettickets_id` (`plugin_formcreator_targettickets_id`),
                 INDEX `item` (`itemtype`,`items_id`)
               ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->query($query) or plugin_formcreator_upgrade_error($migration);
   }

   // add uuid and generate for existing rows
   $table = PluginFormcreatorTargetTicket::getTable();
   $migration->addField($table, 'uuid', 'string', ['after' => 'category_question']);
   $migration->migrationOneTable($table);
   $obj = new PluginFormcreatorTargetTicket();
   $all_targetTickets = $obj->find("`uuid` IS NULL");
   foreach ($all_targetTickets as $targetTicket) {
      $targetTicket['_skip_checks'] = true;
      $obj->update($targetTicket);
   }
   unset($obj);

   $migration->executeMigration();
}
