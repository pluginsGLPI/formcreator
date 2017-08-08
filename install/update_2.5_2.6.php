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
   foreach ($rows as $row) {
      $row['values'] = json_encode([
         'itemtype'                       => $row['values'],
         'show_ticket_categories'         => 'both',
         'show_ticket_categories_depth'   => 0
      ]);
      $question->update($row);
   }

   $migration->executeMigration();
}
