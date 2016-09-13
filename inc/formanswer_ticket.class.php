<?php
class PluginFormcreatorFormanswer_Ticket extends CommonDBRelation
{

   // From CommonDBRelation
   /**
    * @var string $itemtype_1 First itemtype of the relation
    */
   public static $itemtype_1 = 'PluginFormcreatorFormanswer';

   /**
    * @var string $items_id_1 DB's column name storing the ID of the first itemtype
    */
   public static $items_id_1 = 'plugin_formcreator_formanswers_id';

   /**
    * @var string $itemtype_2 Second itemtype of the relation
    */
   public static $itemtype_2 = 'Ticket';

   /**
    * @var string $items_id_2 DB's column name storing the ID of the second itemtype
    */
   public static $items_id_2 = 'id';

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

         $query = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `plugin_formcreator_formanswers_id` int(11) NOT NULL DEFAULT '0',
            `tickets_id` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE INDEX `unicity` (`plugin_formcreator_formanswers_id`, `tickets_id`),
         )
         ENGINE = MyISAM
         DEFAULT CHARACTER SET = utf8
         COLLATE = utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      } else {

      }

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