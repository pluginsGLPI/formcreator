<?php
/**
 *
 * @param Migration $migration
 *
 * @return void
 */
function plugin_formcreator_update_dev(Migration $migration) {
   global $DB;

   if (!$DB->tableExists('glpi_plugin_formcreator_items_targettickets')) {
      $query = "CREATE TABLE `glpi_plugin_formcreator_items_targettickets` (
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
   foreach ($all_targetTickets as $targetTicket_id => $targetTicket) {
      $targetTicket['_skip_checks'] = true;
      $obj->update($targetTicket);
   }
   unset($obj);

   $migration->executeMigration();
}
