<?php
class PluginFormcreatorAnswer extends CommonDBChild
{
   static public $itemtype = "PluginFormcreatorForm_Answer";
   static public $items_id = "plugin_formcreator_forms_anwers_id";

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
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForAdd($input)
   {
      // Decode (if already encoded) and encode strings to avoid problems with quotes
      foreach ($input as $key => $value) {
         if (is_array($value)) {
            foreach($value as $key2 => $value2) {
               $input[$key][$key2] = plugin_formcreator_encode($value2, false);
            }
         } elseif(is_array(json_decode($value))) {
            $value = json_decode($value);
            foreach($value as $key2 => $value2) {
               $value[$key2] = plugin_formcreator_encode($value2, false);
            }
            // Verify the constant exits (included in PHP 5.4+)
            if (defined('JSON_UNESCAPED_UNICODE')) {
               $input[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            // If PHP 5.3, don't use the constant, but bug with UTF-8 languages like Russian...
            } else {
               $input[$key] = json_encode($value);
            }
         } else {
            $input[$key] = plugin_formcreator_encode($value, false);
         }
      }

      return $input;
   }

   /**
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForUpdate($input)
   {
      return $this->prepareInputForAdd($input);
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

      $obj   = new self();
      $table = $obj->getTable();

      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         // Create questions table
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `plugin_formcreator_forms_anwers_id` int(11) NOT NULL,
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

         //rename foreign key, to match table plugin_formcreator_forms_anwers name
         $migration->changeField($table,
                                 'plugin_formcreator_formanwers_id',
                                 'plugin_formcreator_forms_anwers_id',
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

      $obj = new self();
      $DB->query('DROP TABLE IF EXISTS `'.$obj->getTable().'`');

      // Delete logs of the plugin
      $DB->query("DELETE FROM `glpi_logs` WHERE itemtype = '".__CLASS__."'");

      return true;
   }
}
