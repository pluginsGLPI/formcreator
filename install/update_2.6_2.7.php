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
 *
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
function plugin_formcreator_update_2_7(Migration $migration) {
   global $DB;

   // Migrate regex question parameters
   $table = 'glpi_plugin_formcreator_questions';
   if ($DB->fieldExists($table, 'regex')) {
      $request = [
      'FROM' => $table,
      'WHERE' => ['fieldtype' => ['float', 'integer', 'text', 'textarea']]
      ];
      foreach ($DB->request($request) as $row) {
         $id = $row['id'];
         $regex = $DB->escape($row['regex']);
         $uuid = plugin_formcreator_getUuid();
         $DB->query("INSERT INTO `glpi_plugin_formcreator_questionregexes`
                             SET `plugin_formcreator_questions_id`='$id', `fieldname`='regex', `regex`='$regex', `uuid`='$uuid'"
         ) or plugin_formcreator_upgrade_error($migration);
      }
      $migration->dropField($table, 'regex');
   }

   // Migrate range question parameters
   $table = 'glpi_plugin_formcreator_questions';
   if ($DB->fieldExists($table, 'range_min')) {
      $request = [
      'FROM' => $table,
      'WHERE' => ['fieldtype' => ['float', 'integer', 'checkboxes', 'multiselect', 'text']]
      ];
      foreach ($DB->request($request) as $row) {
         $id = $row['id'];
         $rangeMin = $DB->escape($row['range_min']);
         $rangeMax = $DB->escape($row['range_max']);
         $uuid = plugin_formcreator_getUuid();
         $DB->query("INSERT INTO `glpi_plugin_formcreator_questionranges`
                             SET `plugin_formcreator_questions_id`='$id', `fieldname`='range', `range_min`='$rangeMin', `range_max`='$rangeMax', `uuid`='$uuid'"
         ) or plugin_formcreator_upgrade_error($migration);
      }
      $migration->dropField($table, 'range_min');
      $migration->dropField($table, 'range_max');

      // decode html entities in answers
      $request = [
         'SELECT' => [
            'glpi_plugin_formcreator_answers.*'
         ],
         'FROM' => 'glpi_plugin_formcreator_answers',
         'INNER JOIN' => [
            'glpi_plugin_formcreator_questions' => [
               'FKEY' => [
                  'glpi_plugin_formcreator_answers' => 'plugin_formcreator_questions_id',
                  'glpi_plugin_formcreator_questions' => 'id'
               ]
            ]
         ],
         'WHERE' => ['fieldtype' => 'textarea']
      ];
      foreach ($DB->request($request) as $row) {
         $answer = Toolbox::addslashes_deep(html_entity_decode($row['answer']));
         $id = $row['id'];
         $DB->query("UPDATE `glpi_plugin_formcreator_answers` SET `answer`='$answer' WHERE `id` = '$id'");
      }
   }

   // ------------------

   // Remove abusive encoding in questions
   $table = 'glpi_plugin_formcreator_questions';
   $request = [
      'FROM' => $table,
   ];
   foreach ($DB->request($request) as $row) {
      $description = Toolbox::addslashes_deep(html_entity_decode($row['description'], ENT_QUOTES|ENT_HTML5));
      $name = Toolbox::addslashes_deep(html_entity_decode($row['name'], ENT_QUOTES|ENT_HTML5));
      $id = $row['id'];
      $DB->query("UPDATE `$table` SET `description`='$description' `name`='$name' WHERE `id` = '$id'");
      switch ($row['fieldtype']) {
         case 'checkboxes':
         case 'multiselect':
         case 'radios':
         case 'select':
            $defaultValues = Toolbox::addslashes_deep(html_entity_decode($row['default_values'], ENT_QUOTES|ENT_HTML5));
            $values = Toolbox::addslashes_deep(html_entity_decode($row['values'], ENT_QUOTES|ENT_HTML5));
            $DB->query("UPDATE `$table` SET `default_values`='$defaultValues', `values`='$values' WHERE `id` = '$id'");
            break;

         case 'hidden':
         case 'text':
         case 'textarea':
         case 'urgency':
            $defaultValues = Toolbox::addslashes_deep(html_entity_decode($row['default_values'], ENT_QUOTES|ENT_HTML5));
            $DB->query("UPDATE `$table` SET `default_values`='$defaultValues' WHERE `id` = '$id'");
            break;

         case 'ldapselect':
            $values = Toolbox::addslashes_deep(html_entity_decode($row['values'], ENT_QUOTES|ENT_HTML5));
            $DB->query("UPDATE `$table` SET `values`='$values' WHERE `id` = '$id'");
            break;
      }
   }

   // Remove abusive encoding in target changes
   $table = 'glpi_plugin_formcreator_forms';
   $request = [
      'FROM' => $table,
   ];
   foreach ($DB->request($request) as $row) {
      $name = Toolbox::addslashes_deep(html_entity_decode($row['name'], ENT_QUOTES|ENT_HTML5));
      $description = Toolbox::addslashes_deep(html_entity_decode($row['description'], ENT_QUOTES|ENT_HTML5));
      $content = Toolbox::addslashes_deep(html_entity_decode($row['content'], ENT_QUOTES|ENT_HTML5));
      $id = $row['id'];
      $DB->query("UPDATE `$table` SET `name`='$name', `description`='$description', `content`='$content' WHERE `id` = '$id'");
   }

   // decode html entities in name of targets
   $request = [
      'FROM' => 'glpi_plugin_formcreator_forms_targets',
   ];
   foreach ($DB->request($request) as $row) {
      $id = $row['id'];
      $name = Toolbox::addslashes_deep(Html::entity_decode_deep($row['name']));
      $DB->query("UPDATE `glpi_plugin_formcreator_forms_targets`
                  SET `name`='$name',
                  WHERE `id` = '$id'");
   }

   // decode html entities in name, description and content of forms
   $request = [
      'FROM' => 'glpi_plugin_formcreator_forms',
   ];
   foreach ($DB->request($request) as $row) {
      $id = $row['id'];
      $name = addslashes(Html::entity_decode_deep($row['name']));
      $description = addslashes(Html::entity_decode_deep($row['description']));
      // have only HTML special chars encoded
      $content = addslashes(htmlspecialchars(Html::entity_decode_deep($row['description'])));
      $DB->query("UPDATE `glpi_plugin_formcreator_forms`
                  SET `name`='$name',
                  `description`='$description',
                  `content`='$content'
                  WHERE `id` = '$id'");
   }

   // decode html entities in name of form answers
   $request = [
      'FROM' => 'glpi_plugin_formcreator_forms_answers',
   ];
   foreach ($DB->request($request) as $row) {
      $id = $row['id'];
      $name = addslashes(Html::entity_decode_deep($row['name']));
      $DB->query("UPDATE `glpi_plugin_formcreator_forms_answers`
                  SET `name`='$name',
                  WHERE `id` = '$id'");
   }

   // Force update of issues to remove abusive encoding
   $query = "SELECT DISTINCT
               NULL                           AS `id`,
               CONCAT('f_',`fanswer`.`id`)    AS `display_id`,
               `fanswer`.`id`                 AS `original_id`,
               'PluginFormcreatorForm_Answer' AS `sub_itemtype`,
               `f`.`name`                     AS `name`,
               `fanswer`.`status`             AS `status`,
               `fanswer`.`request_date`       AS `date_creation`,
               `fanswer`.`request_date`       AS `date_mod`,
               `fanswer`.`entities_id`        AS `entities_id`,
               `fanswer`.`is_recursive`       AS `is_recursive`,
               `fanswer`.`requester_id`       AS `requester_id`,
               `fanswer`.`users_id_validator` AS `validator_id`,
               `fanswer`.`comment`            AS `comment`
            FROM `glpi_plugin_formcreator_forms_answers` AS `fanswer`
            LEFT JOIN `glpi_plugin_formcreator_forms` AS `f`
               ON`f`.`id` = `fanswer`.`plugin_formcreator_forms_id`
            LEFT JOIN `glpi_items_tickets` AS `itic`
               ON `itic`.`items_id` = `fanswer`.`id`
               AND `itic`.`itemtype` = 'PluginFormcreatorForm_Answer'
            WHERE `fanswer`.`is_deleted` = '0'
            GROUP BY `original_id`
            HAVING COUNT(`itic`.`tickets_id`) != 1

            UNION

            SELECT DISTINCT
               NULL                          AS `id`,
               CONCAT('t_',`tic`.`id`)       AS `display_id`,
               `tic`.`id`                    AS `original_id`,
               'Ticket'                      AS `sub_itemtype`,
               `tic`.`name`                  AS `name`,
               `tic`.`status`                AS `status`,
               `tic`.`date`                  AS `date_creation`,
               `tic`.`date_mod`              AS `date_mod`,
               `tic`.`entities_id`           AS `entities_id`,
               0                             AS `is_recursive`,
               `tic`.`users_id_recipient`    AS `requester_id`,
               0                             AS `validator_id`,
               `tic`.`content`               AS `comment`
            FROM `glpi_tickets` AS `tic`
            LEFT JOIN `glpi_items_tickets` AS `itic`
               ON `itic`.`tickets_id` = `tic`.`id`
               AND `itic`.`itemtype` = 'PluginFormcreatorForm_Answer'
            WHERE `tic`.`is_deleted` = 0
            GROUP BY `original_id`
            HAVING COUNT(`itic`.`items_id`) <= 1";

   $countQuery = "SELECT COUNT(*) AS `cpt` FROM ($query) AS `issues`";
   $result = $DB->query($countQuery);
   if ($result !== false) {
      $count = $DB->fetch_assoc($result);
      $table = PluginFormcreatorIssue::getTable();
      if (countElementsInTable($table) != $count['cpt']) {
         if ($DB->query("TRUNCATE `$table`")) {
            $DB->query("INSERT INTO `$table` SELECT * FROM ($query) as `dt`");
            $volume = 1;
         }
      }
   }

   // Add properties for dropdown of ticket categories
   $request = [
      'FROM'   => 'glpi_plugin_formcreator_questions',
      'WHERE'  => [
         'fieldtype' => 'dropdown'
      ],
   ];
   foreach ($DB->request($request) as $row) {
      $values = json_decode($row['values'], true);
      if ($values['itemtype'] === ITILCategory::class) {
         if (!isset($values['show_ticket_categories'])) {
            $values['show_ticket_categories'] = 'both';
         }
         if (!isset($values['show_ticket_categories_depth'])) {
            $values['show_ticket_categories_depth'] = '0';
         }
         $id = $row['id'];
         $values = json_encode($values);
         $DB->query("UPDATE `glpi_plugin_formcreator_questions` SET `values`='$values' WHERE `id` = '$id'");
      }
   }

   // multiple files upload per field
   $request = [
      'SELECT' => 'glpi_plugin_formcreator_answers.*',
      'FROM' => 'glpi_plugin_formcreator_answers',
      'LEFT JOIN' => [
         'glpi_plugin_formcreator_questions' => [
            'FKEY' => [
               'glpi_plugin_formcreator_questions' => 'id',
               'glpi_plugin_formcreator_answers'   => 'plugin_formcreator_questions_id'
            ]
         ]
      ],
      'WHERE' => [
         'fieldtype' => 'file',
      ]
   ];
   foreach ($DB->request($request) as $row) {
      if (!is_array(json_decode($row['answer']))) {
         $id = $row['id'];
         $answer = json_encode([$row['answer']]);
         $DB->query("UPDATE `glpi_plugin_formcreator_answers` SET `answer` = '$answer' WHERE `id` = '$id'");
      }
   }
}
