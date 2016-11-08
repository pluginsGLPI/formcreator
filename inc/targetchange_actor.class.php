<?php
class PluginFormcreatorTargetChange_Actor extends CommonDBTM
{

   public static function install(Migration $migration)
   {
      global $DB;

      $table = self::getTable();
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `plugin_formcreator_targetchanges_id` int(11) NOT NULL,
                    `actor_role` enum('requester','observer','assigned') NOT NULL,
                    `actor_type` enum('creator','validator','person','question_person','group','question_group','supplier','question_supplier') NOT NULL,
                    `actor_value` int(11) DEFAULT NULL,
                    `use_notification` BOOLEAN NOT NULL DEFAULT TRUE,
                    KEY `plugin_formcreator_targetchanges_id` (`plugin_formcreator_targetchanges_id`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci";
         $DB->query($query) or die($DB->error());
      }
   }

   public static function uninstall()
   {
      global $DB;

      $table = self::getTable();
      $query = "DROP TABLE IF EXISTS `$table`";
      return $DB->query($query) or die($DB->error());
   }

}