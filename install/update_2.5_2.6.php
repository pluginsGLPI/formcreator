<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

/**
 * Upgrade any version of Formcreator >= 2.5.0 to 2.6.0
 * @param Migration $migration
 */
function plugin_formcreator_update_2_6(Migration $migration) {
   global $DB;

   $migration->displayMessage("Upgrade to schema version 2.6");

   $migration->displayMessage("Upgrade glpi_plugin_formcreator_forms_answers");

   // update questions
   $table = 'glpi_plugin_formcreator_questions';
   $migration->displayMessage("Upgrade $table");

   $question = new PluginFormcreatorQuestion();
   $rows = $question->find("`fieldtype` = 'dropdown' AND `values` = 'ITILCategory'");
   foreach ($rows as $id => $row) {
      $updatedValue = json_encode([
         'itemtype'                       => $row['values'],
         'show_ticket_categories'         => 'both',
         'show_ticket_categories_depth'   => 0
      ]);
      // Don't use update() method because the json will be HTML-entities-ified (see prepareInputForUpdate() )
      $query = "UPDATE `$table` SET `values`='$updatedValue' WHERE `id`='$id'";
      $DB->query($query) or plugin_formcreator_upgrade_error($migration);
   }

   // Update Form Answers
   $table = 'glpi_plugin_formcreator_forms_answers';
   $migration->displayMessage("Upgrade $table");

   $migration->addField($table, 'users_id_validator', 'integer', ['after' => 'requester_id']);
   $migration->addField($table, 'groups_id_validator', 'integer', ['after' => 'users_id_validator']);
   $migration->addKey($table, 'users_id_validator');
   $migration->addKey($table, 'groups_id_validator');
   $migration->migrationOneTable($table);

   $formTable = 'glpi_plugin_formcreator_forms';
   $query = "UPDATE `$table`
             INNER JOIN `$formTable` ON (`$table`.`plugin_formcreator_forms_id` = `$formTable`.`id`)
             SET `users_id_validator` = 'validator_id'
             WHERE `$formTable`.`validation_required` = '1'";
   $DB->query($query) or plugin_formcreator_upgrade_error($migration);
   $query = "UPDATE `$table`
             INNER JOIN `$formTable` ON (`$table`.`plugin_formcreator_forms_id` = `$formTable`.`id`)
             SET `groups_id_validator` = 'validator_id'
             WHERE `$formTable`.`validation_required` = '2'";
   $DB->query($query) or plugin_formcreator_upgrade_error($migration);

   $migration->dropKey($table, 'validator_id');
   $migration->dropField($table, 'validator_id');

   // add location rule
   $enum_location_rule = "'".implode("', '", array_keys(PluginFormcreatorTargetTicket::getEnumLocationRule()))."'";
   if (!$DB->fieldExists('glpi_plugin_formcreator_targettickets', 'location_rule', false)) {
      $migration->addField(
         'glpi_plugin_formcreator_targettickets',
         'location_rule',
         "ENUM($enum_location_rule) NOT NULL DEFAULT 'none'",
         ['after' => 'category_question']
      );
   } else {
      $current_enum_location_rule = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targettickets', 'location_rule');
      if (count($current_enum_location_rule) != count(PluginFormcreatorTargetTicket::getEnumLocationRule())) {
         $migration->changeField(
            'glpi_plugin_formcreator_targettickets',
            'location_rule',
            'location_rule',
            "ENUM($enum_location_rule) NOT NULL DEFAULT 'none'",
            ['after' => 'category_question']
         );
      }
   }
   $migration->addField('glpi_plugin_formcreator_targettickets', 'location_question', 'integer', ['after' => 'location_rule']);

   // Fix bad foreign key
   $table = 'glpi_plugin_formcreator_answers';
   $migration->changeField($table, 'plugin_formcreator_question_id', 'plugin_formcreator_questions_id', 'integer');

   $table = 'glpi_plugin_formcreator_items_targettickets';
   if (!$DB->tableExists($table)) {
      $query = "CREATE TABLE `$table` (
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
   foreach ($all_targetTickets as $targetTicket) {
      $targetTicket['_skip_checks'] = true;
      $obj->update($targetTicket);
   }
   unset($obj);

   $enum_category_rule      = "'".implode("', '", array_keys(PluginFormcreatorTargetTicket::getEnumCategoryRule()))."'";
   $table = 'glpi_plugin_formcreator_targettickets';
   if (!$DB->fieldExists($table, 'category_rule', false)) {
      $query = "ALTER TABLE `$table`
                ADD `category_rule` ENUM($enum_category_rule) NOT NULL DEFAULT 'none' AFTER `tag_specifics`;";
      $DB->query($query) or plugin_formcreator_upgrade_error($migration);
   } else {
      $current_enum_category_rule = PluginFormcreatorCommon::getEnumValues($table, 'category_rule');
      if (count($current_enum_category_rule) != count(PluginFormcreatorTargetTicket::getEnumCategoryRule())) {
         $query = "ALTER TABLE `$table`
                   CHANGE COLUMN `category_rule` `category_rule`
                   ENUM($enum_category_rule)
                   NOT NULL DEFAULT 'none'";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }
   }

   $table = 'glpi_plugin_formcreator_items_targetchanges';
   $enum_urgency_rule       = "'".implode("', '", array_keys(PluginFormcreatorTargetChange::getEnumUrgencyRule()))."'";
   $enum_category_rule      = "'".implode("', '", array_keys(PluginFormcreatorTargetChange::getEnumCategoryRule()))."'";
   if (!$DB->fieldExists('glpi_plugin_formcreator_targetchanges', 'urgency_rule', false)) {
      $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges`
                ADD `urgency_rule` ENUM($enum_urgency_rule) NOT NULL DEFAULT 'none' AFTER `due_date_period`;";
      $DB->query($query) or plugin_formcreator_upgrade_error($migration);
   } else {
      $current_enum_urgency_rule = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targetchanges', 'urgency_rule');
      if (count($current_enum_urgency_rule) != count(PluginFormcreatorTargetChange::getEnumUrgencyRule())) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges`
                   CHANGE COLUMN `urgency_rule` `urgency_rule`
                   ENUM($enum_urgency_rule)
                   NOT NULL DEFAULT 'none'";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }
   }
   $migration->addField('glpi_plugin_formcreator_targetchanges', 'urgency_question', 'integer', ['after' => 'urgency_rule']);

   if (!$DB->fieldExists('glpi_plugin_formcreator_targetchanges', 'category_rule', false)) {
      $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges`
                ADD `category_rule` ENUM($enum_category_rule) NOT NULL DEFAULT 'none' AFTER `tag_specifics`;";
      $DB->query($query) or plugin_formcreator_upgrade_error($migration);
   } else {
      $current_enum_category_rule = PluginFormcreatorCommon::getEnumValues('glpi_plugin_formcreator_targetchanges', 'category_rule');
      if (count($current_enum_category_rule) != count(PluginFormcreatorTargetChange::getEnumCategoryRule())) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_targetchanges`
                   CHANGE COLUMN `category_rule` `category_rule`
                   ENUM($enum_category_rule)
                   NOT NULL DEFAULT 'none'";
         $DB->query($query) or plugin_formcreator_upgrade_error($migration);
      }
   }
   $migration->addField('glpi_plugin_formcreator_targetchanges', 'category_question', 'integer', ['after' => 'category_rule']);

   $migration->executeMigration();
}
