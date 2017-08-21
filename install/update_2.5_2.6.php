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
   $rows = $question->find("`regex` <> '' AND `regex` IS NOT NULL");
   $table = PluginFormcreatorQuestion::getTable();
   foreach ($rows as $id => $row) {
      // Avoid notice when validating the regex
      set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {});
      $isValid = !(preg_match($row['regex'], null) === false);
      restore_error_handler();

      if (!$isValid) {
         // The regex is invalid (likely not updated yet)
         $updatedRegex = addslashes('/^' . $row['regex'] . '$/');
         // Don't use update() method because the json will be HTML-entities-ified (see prepareInputForUpdate() )
         $query = "UPDATE `$table` SET `regex`='$updatedRegex' WHERE `id`='$id'";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }
   }

   $migration->executeMigration();
}
