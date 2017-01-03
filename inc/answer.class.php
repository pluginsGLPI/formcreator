<?php
class PluginFormcreatorAnswer extends CommonDBChild
{
   static public $itemtype = "PluginFormcreatorForm_Answer";
   static public $items_id = "plugin_formcreator_forms_answers_id";

   /**
    * Check if current user have the right to create and modify requests
    *
    * @return boolean True if he can create and modify requests
    */
   public static function canCreate()
   {
      return true;
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView()
   {
      return true;
   }

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0)
   {
      return _n('Answer', 'Answers', $nb, 'formcreator');
   }

   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean True on success
    */
   public static function install(Migration $migration)
   {
      global $DB;

      $table = self::getTable();

      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         // Create questions table
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `plugin_formcreator_forms_answers_id` int(11) NOT NULL,
                     `plugin_formcreator_question_id` int(11) NOT NULL,
                     `answer` text NOT NULL
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci";
         $DB->query($query) or die ($DB->error());
      } else {
         // Update field type from previous version (Need answer to be text since text can be WYSIWING).
         $query = "ALTER TABLE  `$table` CHANGE  `answer`  `answer` text;";
         $DB->query($query) or die ($DB->error());

         /**
          * Migration of special chars from previous versions
          *
          * @since 0.85-1.2.3
          */
         $query  = "SELECT `id`, `answer`
                    FROM `$table`";
         $result = $DB->query($query);
         while ($line = $DB->fetch_array($result)) {
            $query_update = "UPDATE `$table` SET
                               `answer` = '".plugin_formcreator_encode($line['answer'])."'
                             WHERE `id` = ".$line['id'];
            $DB->query($query_update) or die ($DB->error());
         }

         //rename foreign key, to match table plugin_formcreator_forms_answers name
         $migration->changeField($table,
                                 'plugin_formcreator_formanwers_id',
                                 'plugin_formcreator_forms_answers_id',
                                 'integer');
         $migration->migrationOneTable($table);
      }

      return true;
   }

   /**
    * Database table uninstallation for the item type
    *
    * @return boolean True on success
    */
   public static function uninstall()
   {
      global $DB;

      $table = self::getTable();
      $DB->query("DROP TABLE IF EXISTS `$table`");

      // Delete logs of the plugin
      $DB->query("DELETE FROM `glpi_logs` WHERE itemtype = '".__CLASS__."'");

      return true;
   }
}
