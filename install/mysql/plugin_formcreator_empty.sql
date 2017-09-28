-- Database schema
-- Do NOT drop anything here

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_formcreator_forms_answers_id` int(11) NOT NULL,
  `plugin_formcreator_questions_id` int(11) NOT NULL,
  `answer` text,
  PRIMARY KEY (`id`),
  INDEX `plugin_formcreator_forms_answers_id` (`plugin_formcreator_forms_answers_id`),
  INDEX `plugin_formcreator_questions_id` (`plugin_formcreator_questions_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `comment` text,
  `completename` varchar(255) DEFAULT NULL,
  `plugin_formcreator_categories_id` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '1',
  `sons_cache` longtext,
  `ancestors_cache` longtext,
  `knowbaseitemcategories_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `name` (`name`),
  INDEX `knowbaseitemcategories_id` (`knowbaseitemcategories_id`),
  INDEX `plugin_formcreator_categories_id` (`plugin_formcreator_categories_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_entityconfigs` (
  `id` int(11) NOT NULL,
  `replace_helpdesk` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `access_rights` tinyint(1) NOT NULL DEFAULT '1',
  `requesttype` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `content` longtext,
  `plugin_formcreator_categories_id` int(11) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `language` varchar(5) NOT NULL,
  `helpdesk_home` tinyint(1) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `validation_required` tinyint(1) NOT NULL DEFAULT '0',
  `usage_count` int(11) NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `entities_id` (`entities_id`),
  INDEX `plugin_formcreator_categories_id` (`plugin_formcreator_categories_id`),
  FULLTEXT KEY `Search` (`name`,`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_forms_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `plugin_formcreator_forms_id` int(11) NOT NULL,
  `requester_id` int(11) DEFAULT NULL,
  `users_id_validator` int(11) DEFAULT NULL,
  `groups_id_validator` int(11) DEFAULT NULL,
  `request_date` datetime NOT NULL,
  `status` enum('waiting','refused','accepted') NOT NULL DEFAULT 'waiting',
  `comment` text,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `plugin_formcreator_forms_id` (`plugin_formcreator_forms_id`),
  INDEX `entities_id_is_recursive` (`entities_id`, `is_recursive`),
  INDEX `requester_id` (`requester_id`),
  INDEX `users_id_validator` (`users_id_validator`),
  INDEX `groups_id_validator` (`groups_id_validator`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_forms_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_formcreator_forms_id` int(11) NOT NULL,
  `profiles_id` int(11) NOT NULL,
  `uuid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_formcreator_forms_id`,`profiles_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_forms_validators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_formcreator_forms_id` int(11) NOT NULL,
  `itemtype` varchar(255) NOT NULL DEFAULT '',
  `items_id` int(11) NOT NULL,
  `uuid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_formcreator_forms_id`,`itemtype`,`items_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_formcreator_sections_id` int(11) NOT NULL,
  `fieldtype` varchar(30) NOT NULL DEFAULT 'text',
  `name` varchar(255) NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `show_empty` tinyint(1) NOT NULL DEFAULT '0',
  `default_values` text,
  `values` text,
  `range_min` varchar(10) DEFAULT NULL,
  `range_max` varchar(10) DEFAULT NULL,
  `description` text NOT NULL,
  `regex` varchar(255) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `show_rule` enum('always','hidden','shown') NOT NULL DEFAULT 'always',
  `uuid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `plugin_formcreator_sections_id` (`plugin_formcreator_sections_id`),
  FULLTEXT KEY `Search` (`name`,`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_questions_conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_formcreator_questions_id` int(11) NOT NULL,
  `show_field` int(11) DEFAULT NULL,
  `show_condition` enum('==','!=','<','>','<=','>=') DEFAULT NULL,
  `show_value` varchar(255) DEFAULT NULL,
  `show_logic` enum('AND','OR') DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '1',
  `uuid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `plugin_formcreator_questions_id` (`plugin_formcreator_questions_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_formcreator_forms_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `uuid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `plugin_formcreator_forms_id` (`plugin_formcreator_forms_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_targetchanges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `changetemplates_id` int(11) DEFAULT NULL,
  `comment` text,
  `impactcontent` text,
  `controlistcontent` text,
  `rolloutplancontent` text,
  `backoutplancontent` text,
  `checklistcontent` text,
  `due_date_rule` enum('answer','change','calcul') DEFAULT NULL,
  `due_date_question` int(11) DEFAULT NULL,
  `due_date_value` tinyint(4) DEFAULT NULL,
  `due_date_period` enum('minute','hour','day','month') DEFAULT NULL,
  `validation_followup` tinyint(1) NOT NULL DEFAULT '1',
  `destination_entity` enum('current','requester','requester_dynamic_first','requester_dynamic_last','form','validator','specific','user','entity') NOT NULL DEFAULT 'requester',
  `destination_entity_value` int(11) DEFAULT NULL,
  `tag_type` enum('none','questions','specifics','questions_and_specific','questions_or_specific') NOT NULL DEFAULT 'none',
  `tag_questions` varchar(255) NOT NULL,
  `tag_specifics` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `changetemplates_id` (`changetemplates_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_targetchanges_actors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_formcreator_targetchanges_id` int(11) NOT NULL,
  `actor_role` enum('requester','observer','assigned') NOT NULL,
  `actor_type` enum('creator','validator','person','question_person','group','question_group','supplier','question_supplier') NOT NULL,
  `actor_value` int(11) DEFAULT NULL,
  `use_notification` tinyint(1) NOT NULL DEFAULT '1',
  `uuid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `plugin_formcreator_targetchanges_id` (`plugin_formcreator_targetchanges_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_targets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_formcreator_forms_id` int(11) NOT NULL,
  `itemtype` varchar(100) NOT NULL DEFAULT 'PluginFormcreatorTargetTicket',
  `items_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `uuid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `plugin_formcreator_forms_id` (`plugin_formcreator_forms_id`),
  INDEX `itemtype_items_id` (`itemtype`, `items_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_targettickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `tickettemplates_id` int(11) DEFAULT NULL,
  `comment` text,
  `due_date_rule` enum('answer','ticket','calcul') DEFAULT NULL,
  `due_date_question` int(11) DEFAULT NULL,
  `due_date_value` tinyint(4) DEFAULT NULL,
  `due_date_period` enum('minute','hour','day','month') DEFAULT NULL,
  `urgency_rule` enum('none','specific','answer') NOT NULL DEFAULT 'none',
  `urgency_question` int(11) NOT NULL DEFAULT '0',
  `location_rule` enum('none','specific','answer') NOT NULL DEFAULT 'none',
  `location_question` int(11) NOT NULL DEFAULT '0',
  `validation_followup` tinyint(1) NOT NULL DEFAULT '1',
  `destination_entity` enum('current','requester','requester_dynamic_first','requester_dynamic_last','form','validator','specific','user','entity') NOT NULL DEFAULT 'current',
  `destination_entity_value` int(11) DEFAULT NULL,
  `tag_type` enum('none','questions','specifics','questions_and_specific','questions_or_specific') NOT NULL DEFAULT 'none',
  `tag_questions` varchar(255) NOT NULL,
  `tag_specifics` varchar(255) NOT NULL,
  `category_rule` enum('none','answer') NOT NULL DEFAULT 'none',
  `category_question` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `tickettemplates_id` (`tickettemplates_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_targettickets_actors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_formcreator_targettickets_id` int(11) NOT NULL,
  `actor_role` enum('requester','observer','assigned') NOT NULL,
  `actor_type` enum('creator','validator','person','question_person','group','question_group','supplier','question_supplier','question_actors') NOT NULL,
  `actor_value` int(11) DEFAULT NULL,
  `use_notification` tinyint(1) NOT NULL DEFAULT '1',
  `uuid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `plugin_formcreator_targettickets_id` (`plugin_formcreator_targettickets_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_issues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `display_id` varchar(255) NOT NULL,
  `original_id` int(11) NOT NULL DEFAULT '0',
  `sub_itemtype` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `status` varchar(255) NOT NULL DEFAULT '',
  `date_creation` datetime NOT NULL,
  `date_mod` datetime NOT NULL,
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `requester_id` int(11) NOT NULL DEFAULT '0',
  `validator_id` int(11) NOT NULL DEFAULT '0',
  `comment` text,
  PRIMARY KEY (`id`),
  INDEX `original_id_sub_itemtype` (`original_id`, `sub_itemtype`),
  INDEX `entities_id` (`entities_id`),
  INDEX `requester_id` (`requester_id`),
  INDEX `validator_id` (`validator_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_items_targettickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_formcreator_targettickets_id` int(11) NOT NULL DEFAULT '0',
  `link` int(11) NOT NULL DEFAULT '0',
  `itemtype` varchar(255) NOT NULL DEFAULT '',
  `items_id` int(11) NOT NULL DEFAULT '0',
  `uuid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `plugin_formcreator_targettickets_id` (`plugin_formcreator_targettickets_id`),
  INDEX `item` (`itemtype`,`items_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
