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

   // update questions
   $question = new PluginFormcreatorQuestion();
   $rows = $question->find("`fieldtype` = 'dropdown' AND `values` = 'ITILCategory'");
   $table = PluginFormcreatorQuestion::getTable();
   foreach ($rows as $id => $row) {
      $updatedValue = json_encode([
         'itemtype'                       => $row['values'],
         'show_ticket_categories'         => 'both',
         'show_ticket_categories_depth'   => 0
      ]);
      // Don't use update() method because the json will be HTML-entities-ified (see prepareInputForUpdate() )
      $query = "UPDATE `$table` SET `values`='$updatedValue' WHERE `id`='$id'";
      $DB->query($query) or plugin_formcreator_upgrade_error($migration);;
   }

   // add location rule
   $enum_location_rule = "'".implode("', '", array_keys(PluginFormcreatorTargetTicket::getEnumLocationRule()))."'";
   if (!FieldExists('glpi_plugin_formcreator_targettickets', 'location_rule', false)) {
      $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets`
                ADD `location_rule` ENUM($enum_location_rule) NOT NULL DEFAULT 'none' AFTER `category_question`;";
      $DB->query($query) or plugin_formcreator_upgrade_error($migration);
   } else {
      $current_enum_location_rule = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targettickets', 'location_rule');
      if (count($current_enum_location_rule) != count(PluginFormcreatorTargetTicket::getEnumLocationRule())) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targettickets`
                   CHANGE COLUMN `location_rule` `location_rule`
                   ENUM($enum_location_rule)
                   NOT NULL DEFAULT 'none'";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }
   }
   $migration->addField('glpi_plugin_formcreator_targettickets', 'location_question', 'integer', array('after' => 'location_rule'));

   $migration->executeMigration();
}
