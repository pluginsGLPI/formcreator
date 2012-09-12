<?php

function plugin_formcreator_install() {
   global $DB;

   if (!TableExists("glpi_plugin_formcreator_forms")) {
      $query = "CREATE TABLE `glpi_plugin_formcreator_forms` (
                  `id` int(11) NOT NULL auto_increment,
                  `name` varchar(255) NOT NULL collate utf8_unicode_ci,
                  `content` longtext collate utf8_unicode_ci,
                  `is_active` tinyint(1) NOT NULL default '0',
                  `is_recursive` tinyint(1) NOT NULL default '0',
                  `entities_id` int(11) NOT NULL default '0',
                  `language` varchar(5) NOT NULL collate utf8_unicode_ci,
                PRIMARY KEY (`id`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_formcreator_forms ". $DB->error());
   }

   if (!TableExists("glpi_plugin_formcreator_targets")) {
      $query = "CREATE TABLE `glpi_plugin_formcreator_targets` (
                  `id` int(11) NOT NULL auto_increment,
                  `name` varchar(255) NOT NULL collate utf8_unicode_ci,
                  `content` longtext collate utf8_unicode_ci,
                  `urgency` int(11) NOT NULL default '1',
                  `priority` int(11) NOT NULL default '1',
                  `itilcategories_id` int(11) NOT NULL default '0',
                  `plugin_formcreator_forms_id` int(11) NOT NULL,
                PRIMARY KEY (`id`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_formcreator_targets ". $DB->error());
   }

   if (!TableExists("glpi_plugin_formcreator_sections")) {
      $query = "CREATE TABLE `glpi_plugin_formcreator_sections` (
                  `id` int(11) NOT NULL auto_increment,
                  `name` varchar(255) NOT NULL collate utf8_unicode_ci,
                  `content` longtext collate utf8_unicode_ci,
                  `plugin_formcreator_targets_id` tinyint(1) NOT NULL,
                  `plugin_formcreator_forms_id` int(11) NOT NULL,
                  `position` int(11) NOT NULL,
                PRIMARY KEY (`id`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_formcreator_sections ". $DB->error());
   }
   if (!FieldExists("glpi_plugin_formcreator_sections", "position")) {
      $query = "ALTER TABLE `glpi_plugin_formcreator_sections` ADD `position` INT( 11 ) NOT NULL";
      $DB->query($query) or die("Can't add glpi_plugin_formcreator_sections.position ". $DB->error());
   }


   if (!TableExists("glpi_plugin_formcreator_questions")) {
      $query = "CREATE TABLE `glpi_plugin_formcreator_questions` (
                  `id` int(11) NOT NULL auto_increment,
                  `name` varchar(255) NOT NULL collate utf8_unicode_ci,
                  `type` int(11) NOT NULL default '0',
                  `data` longtext collate utf8_unicode_ci,
                  `content` longtext collate utf8_unicode_ci,
                  `option` longtext collate utf8_unicode_ci,
                  `position` int(11) NOT NULL default '0',
                  `plugin_formcreator_sections_id` tinyint(1) NOT NULL,
                  `plugin_formcreator_forms_id` int(11) NOT NULL,
                PRIMARY KEY (`id`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_formcreator_questions ". $DB->error());
   }

   $query="DELETE FROM `glpi_displaypreferences`
              WHERE `itemtype`='PluginFormcreatorForm'";
   $DB->query($query) or die($DB->error());

   $query_displayprefs = array();

   $query_displayprefs[] = "INSERT INTO `glpi_displaypreferences`
                            VALUES (NULL,'PluginFormcreatorForm','0','0','0')";
   $query_displayprefs[] = "INSERT INTO `glpi_displaypreferences`
                            VALUES (NULL,'PluginFormcreatorForm','1','1','0')";
   $query_displayprefs[] = "INSERT INTO `glpi_displaypreferences`
                            VALUES (NULL,'PluginFormcreatorForm','2','2','0')";
   $query_displayprefs[] = "INSERT INTO `glpi_displaypreferences`
                            VALUES (NULL,'PluginFormcreatorForm','3','3','0')";
   $query_displayprefs[] = "INSERT INTO `glpi_displaypreferences`
                            VALUES (NULL,'PluginFormcreatorForm','4','4','0')";

   foreach($query_displayprefs as $query) {
      $DB->query($query) or die("error insert glpi_displaypreferences datas from ".
                                 "glpi_plugin_formcreator_forms". $DB->error());
   }

   CronTask::Register('PluginFormcreator', 'Init', DAY_TIMESTAMP, array('param' => 50));
   return true;
}

function plugin_formcreator_uninstall() {
   global $DB;

   $tables = array();
   $tables[] = "glpi_plugin_formcreator_forms";
   $tables[] = "glpi_plugin_formcreator_questions";
   $tables[] = "glpi_plugin_formcreator_targets";
   $tables[] = "glpi_plugin_formcreator_sections";

   foreach($tables as $table) {
      if (TableExists($table)) {
         $query = "DROP TABLE `".$table."`";
         $DB->query($query) or die("error deleting ".$table);
      }
   }

   $querys_data = array();
   $querys_data[] = "DELETE FROM `glpi_displaypreferences`
                     WHERE `itemtype` = 'PluginFormcreatorForm'";

   foreach($querys_data as $query) {
      $DB->query($query) or die("error delete datas from
                                 glpi_plugin_formcreator_forms". $DB->error());
   }

   return true;
}
?>
