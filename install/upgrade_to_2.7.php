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
class PluginFormcreatorUpgradeTo2_7 {
   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      // Rename PluginFormcreatorForm_Answer into PluginFormcreatorFormAnswer
      $displayPreference = new DisplayPreference();
      $DB->update(
         'glpi_displaypreferences', [
            'itemtype'      => 'PluginFormcreatorFormAnswer',
         ], [
            'itemtype' => 'PluginFormcreatorForm_Answer'
         ]
      );
      $DB->update(
         'glpi_items_tickets', [
            'itemtype'      => 'PluginFormcreatorFormAnswer',
         ], [
            'itemtype' => 'PluginFormcreatorForm_Answer'
         ]
      );
      $DB->update(
         'glpi_queuednotifications', [
            'itemtype'      => 'PluginFormcreatorFormAnswer',
         ], [
            'itemtype' => 'PluginFormcreatorForm_Answer'
         ]
      );
      $table = 'glpi_plugin_formcreator_formanswers';
      $migration->renameTable('glpi_plugin_formcreator_forms_answers', $table);
      $table = 'glpi_plugin_formcreator_answers';
      $migration->changeField(
         $table,
         'plugin_formcreator_forms_answers_id',
         'plugin_formcreator_formanswers_id',
         'integer'
      );
      $migration->migrationOneTable($table);
      $migration->dropKey($table, 'plugin_formcreator_forms_answers_id');
      $migration->addKey($table, ['plugin_formcreator_formanswers_id'], 'plugin_formcreator_formanswers_id');

      // Changes don't support templates, remove the relation
      $table = 'glpi_plugin_formcreator_targetchanges';
      $migration->dropField($table, 'changetemplates_id');

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

      // decode html entities in question definitions
      $request = [
         'FROM'   => 'glpi_plugin_formcreator_questions',
         'WHERE'  => [
            'fieldtype' => ['select', 'multiselect', 'checkboxes', 'radios']
         ]
      ];
      foreach ($DB->request($request) as $row) {
         $values = Toolbox::addslashes_deep(html_entity_decode($row['values']));
         $defaultValues = Toolbox::addslashes_deep(html_entity_decode($row['default_values']));
         $id = $row['id'];
         $DB->query("UPDATE `glpi_plugin_formcreator_questions` SET `values` = '$values', `default_values` = '$defaultValues' WHERE `id` = '$id'");
      }

      // decode html entities in name of questions
      foreach ($DB->request(['FROM' => 'glpi_plugin_formcreator_questions']) as $row) {
         $name = Toolbox::addslashes_deep(html_entity_decode($row['name']));
         $id = $row['id'];
         $DB->query("UPDATE `glpi_plugin_formcreator_questions` SET `name`='$name' WHERE `id` = '$id'");
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
         if (!is_array(json_decode($row['answer'], true))) {
            $id = $row['id'];
            $answer = json_encode([$row['answer']]);
            $DB->query("UPDATE `glpi_plugin_formcreator_answers` SET `answer` = '$answer' WHERE `id` = '$id'");
         }
      }

      // Update target change columns
      $table = 'glpi_plugin_formcreator_targetchanges';
      $migration->changeField($table, 'comment', 'content', 'longtext');
      $migration->changeField($table, 'impactcontent', 'impactcontent', 'longtext');
      $migration->changeField($table, 'controlistcontent', 'controlistcontent', 'longtext');
      $migration->changeField($table, 'rolloutplancontent', 'rolloutplancontent', 'longtext');
      $migration->changeField($table, 'backoutplancontent', 'backoutplancontent', 'longtext');
      $migration->changeField($table, 'checklistcontent', 'checklistcontent', 'longtext');

      // Update target target columns
      $table = 'glpi_plugin_formcreator_targettickets';
      $migration->changeField($table, 'comment', 'content', 'longtext');

      // Reorder columns on some tables
      $tables = [
         'glpi_plugin_formcreator_forms',
         'glpi_plugin_formcreator_questions',
         'glpi_plugin_formcreator_sections',
         'glpi_plugin_formcreator_issues',
      ];
      foreach ($tables as $table) {
         $migration->changeField($table, 'name', 'name', 'string', ['after' => 'id']);
      }

      //remove html entities in forms
      $request = [
         'FROM'   => 'glpi_plugin_formcreator_forms',
      ];
      foreach ($DB->request($request) as $row) {
         $name = Toolbox::addslashes_deep(html_entity_decode($row['name']));
         $description = Toolbox::addslashes_deep(html_entity_decode($row['description']));
         $content = Toolbox::addslashes_deep(html_entity_decode($row['content']));
         $id = $row['id'];
         $DB->query("UPDATE `glpi_plugin_formcreator_forms` SET `name` = '$name', `description` = '$description', `content` = '$content' WHERE `id` = '$id'");
      }
   }
}
