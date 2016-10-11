<?php
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * @since 0.90-1.5
 */
class PluginFormcreatorForm_Validator extends CommonDBRelation {

      // From CommonDBRelation
   static public $itemtype_1          = 'PluginFormcreatorForm';
   static public $items_id_1          = 'plugin_formcreator_forms_id';

   static public $itemtype_2          = 'itemtype';
   static public $items_id_2          = 'items_id';
   static public $checkItem_2_Rights  = self::HAVE_VIEW_RIGHT_ON_ITEM;

   public static function install(Migration $migration)
   {
      global $DB;

      $table = self::getTable();
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id`                          int(11) NOT NULL AUTO_INCREMENT,
                     `plugin_formcreator_forms_id` int(11) NOT NULL,
                     `itemtype`                    varchar(255) NOT NULL DEFAULT '',
                     `items_id`                    int(11) NOT NULL,
                     PRIMARY KEY (`id`),
                     UNIQUE KEY `unicity` (`plugin_formcreator_forms_id`, `itemtype`, `items_id`)
                  )
                  ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }

      // Convert the old relation in glpi_plugin_formcreator_formvalidators table
      if (TableExists('glpi_plugin_formcreator_formvalidators')) {
         $table_form = PluginFormcreatorForm::getTable();
         $old_table = 'glpi_plugin_formcreator_formvalidators';
         $query = "INSERT INTO `$table` (`plugin_formcreator_forms_id`, `itemtype`, `items_id`)
               SELECT
                  `$old_table`.`forms_id`,
                  IF(`validation_required` = '1', 'User', 'Group'),
                  `$old_table`.`users_id`
               FROM `$old_table`
               LEFT JOIN `$table_form` ON (`$table_form`.`id` = `$old_table`.`forms_id`)
               WHERE `validation_required` = '1' OR `validation_required` = '2'";
         $DB->query($query) or die ($DB->error());
         $migration->displayMessage('Backing up table glpi_plugin_formcreator_formvalidators');
         $migration->renameTable('glpi_plugin_formcreator_formvalidators', 'glpi_plugin_formcreator_formvalidators_backup');
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
