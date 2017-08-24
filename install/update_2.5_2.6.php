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

   $migration->executeMigration();
}
