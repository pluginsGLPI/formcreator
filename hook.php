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
				  `cat` INT( 3 ) NOT NULL,
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
                  `type` tinyint(1) NOT NULL default '2',
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
                  `position` int(11) NOT NULL default '0',
                  `plugin_formcreator_targets_id` tinyint(1) NOT NULL,
                  `plugin_formcreator_forms_id` int(11) NOT NULL,
                PRIMARY KEY (`id`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      $DB->query($query) or die("error creating glpi_plugin_formcreator_sections ". $DB->error());
   } else {
	  $query = "ALTER TABLE `glpi_plugin_formcreator_questions`
				CHANGE `plugin_formcreator_sections_id`
				`plugin_formcreator_sections_id` INT( 11 ) NOT NULL";
	  $DB->query($query) or die("error creating glpi_plugin_formcreator_sections ". $DB->error());
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
	  
	if (!TableExists("glpi_plugin_formcreator_cats")) {
      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_cats` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `position` int(3) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;";
      $DB->query($query) or die("error creating glpi_plugin_formcreator_cats ". $DB->error());
   }
   
   if (!TableExists("glpi_plugin_formcreator_titles")) {
      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_titles` (
			  `id` int(2) NOT NULL AUTO_INCREMENT,
			  `name` longtext NOT NULL,
			  `language` varchar(5) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;";
      $DB->query($query) or die("error creating glpi_plugin_formcreator_cats ". $DB->error());
   }
   
   $query = 'DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = "PluginFormcreatorForm"';
   $DB->query($query) or die("error deleting glpi_displaypreferences ". $DB->error());
   
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
   $query = "INSERT INTO `glpi_plugin_formcreator_cats` (`id`, `name`, `position`) VALUES (0, 'Default cat', 0);";
   
   foreach($query_displayprefs as $query) {
      $DB->query($query) or die("error insert glpi_displaypreferences datas from  
                                 glpi_plugin_formcreator_forms". $DB->error());
   }
   
   //ajout d'une catégorie sinon pas d'affichage s'il n'y en pas
   $query = "INSERT INTO `glpi_plugin_formcreator_cats` (`name`, `position`) VALUES ('Default cat', 0);";
   $DB->query($query) or die("error inserting cat ". $DB->error());
     
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
   $tables[] = "glpi_plugin_formcreator_cats";
   $tables[] = "glpi_plugin_formcreator_titles";
   
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