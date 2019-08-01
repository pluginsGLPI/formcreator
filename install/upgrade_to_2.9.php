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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
class PluginFormcreatorUpgradeTo2_9 {

   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      $this->migration = $migration;

      // Remove targets table
      $tables = [
         'glpi_plugin_formcreator_targettickets',
         'glpi_plugin_formcreator_targetchanges'
      ];
      $formFk = 'plugin_formcreator_forms_id';
      foreach ($tables as $table) {
         $migration->addField($table, $formFk, 'integer', ['after' => 'id']);
         $migration->changeField($table, 'name', 'target_name', 'string', ['after' => $formFk]);
         $migration->migrationOneTable($table); // immediately rename the column
         $migration->addField($table, 'name', 'string', ['after' => 'id']);
         $migration->addField($table, 'uuid', 'string');
         $migration->migrationOneTable($table);
      }
      $request = [
         'FROM' => 'glpi_plugin_formcreator_targets'
      ];
      foreach ($DB->request($request) as $target) {
         $table = '';
         switch ($target['itemtype']) {
            case 'PluginFormcreatorTargetTicket':
               $table = 'glpi_plugin_formcreator_targettickets';
               break;

            case 'PluginFormcreatorTargetChange':
               $table = 'glpi_plugin_formcreator_targetchanges';
               break;
         }
         if ($table === '') {
            continue;
         }
         $DB->update(
            $table,
            [
               $formFk => $target[$formFk],
               'uuid'  => $target['uuid'],
               'name'  => $target['name'],
            ],
            [
               'id' => $target['items_id'],
            ]
         );
      }
      $migration->backupTables(['glpi_plugin_formcreator_targets']);

      // Remove enum for formanswer
      $this->enumToInt(
         'glpi_plugin_formcreator_formanswers',
         'status',
         [
            'waiting'  => 101,
            'refused'  => 102,
            'accepted' => 103,
         ],
         [
            'value' => '101'
         ]
      );

      // Remove enum for question
      $this->enumToInt(
         'glpi_plugin_formcreator_questions',
         'show_rule',
         [
            'always'  => 1,
            'hidden'  => 2,
            'shown' => 3,
         ],
         [
            'value' => '1'
         ]
      );

      // Remove show_logic enum for question conditions
      $this->enumToInt(
         'glpi_plugin_formcreator_questions_conditions',
         'show_logic',
         [
            'AND'  => 1,
            'OR'  => 2,
         ],
         [
            'value' => '1'
         ]
      );

      // Remove show_condition  enum for question conditions
      $this->enumToInt(
         'glpi_plugin_formcreator_questions_conditions',
         'show_condition',
         [
            '=='  => 1,
            '!='  => 2,
            '<'   => 3,
            '>'   => 4,
            '<='  => 5,
            '>='  => 6,
         ],
         [
            'value' => '1'
         ]
      );

      $tables = [
         'glpi_plugin_formcreator_targetchanges',
         'glpi_plugin_formcreator_targettickets'
      ];
      foreach ($tables as $table) {
         $this->enumToInt(
            $table,
            'due_date_rule',
            [
               'none'     => 1,
               'answer'   => 2,
               'ticket'   => 3,
               'calc'     => 4,
            ],
            [
               'value' => '1'
            ]
         );

         $this->enumToInt(
            $table,
            'due_date_period',
            [
               'minute' => 1,
               'hour'   => 2,
               'day'    => 3,
               'month'  => 4,
            ],
            [
               'value' => '1'
            ]
         );

         $this->enumToInt(
            $table,
            'urgency_rule',
            [
               'none'      => 1,
               'specific'  => 2,
               'answer'    => 3,
            ],
            [
               'value' => '1'
            ]
         );

         // Remove enum for destination_entity
         $this->enumToInt(
            $table,
            'destination_entity',
            [
               'current'                  => 1,
               'requester'                => 2,
               'requester_dynamic_first'  => 3,
               'requester_dynamic_last'   => 4,
               'form'                     => 5,
               'validator'                => 6,
               'specific'                 => 7,
               'user'                     => 8,
               'entity'                   => 9,
            ],
            [
               'value' => '1'
            ]
         );

         // Remove enum for urgency_rule
         $this->enumToInt(
            $table,
            'tag_type',
            [
               'none'                   => 1,
               'questions'              => 2,
               'specifics'              => 3,
               'questions_and_specific' => 4,
               'questions_or_specific'  => 5,
            ],
            [
               'value' => '1'
            ]
         );

         // Remove enum for category_rule
         $this->enumToInt(
            $table,
            'category_rule',
            [
               'none'      => 1,
               'specific'  => 2,
               'answer'    => 3,
            ],
            [
               'value' => '1'
            ]
         );
      }

      $this->enumToInt(
         'glpi_plugin_formcreator_targettickets',
         'location_rule',
         [
            'none'      => 1,
            'specific'  => 2,
            'answer'    => 3,
         ],
         [
            'value' => '1'
         ]
      );

      $tables = [
         'glpi_plugin_formcreator_targetchanges_actors',
         'glpi_plugin_formcreator_targettickets_actors'
      ];
      foreach ($tables as $table) {
         $this->enumToInt(
            $table,
            'actor_role',
            [
               'requester' => 1,
               'observer'  => 2,
               'assigned'  => 3,
            ],
            [
               'value' => '1'
            ]
         );

         $this->enumToInt(
            $table,
            'actor_type',
            [
               'creator'           => 1,
               'validator'         => 2,
               'person'            => 3,
               'question_person'   => 4,
               'group'             => 5,
               'question_group'    => 6,
               'supplier'          => 7,
               'question_supplier' => 8,
               'question_actors'   => 9,
            ],
            [
               'value' => '1'
            ]
         );
      }

      $table = 'glpi_plugin_formcreator_targettickets';
      $migration->addField($table, 'associate_rule', 'integer', ['after' => 'category_question', 'value' => '1']);
      $migration->addField($table, 'associate_question', 'integer', ['after' => 'associate_rule']);
      $migration->addField($table, 'type', 'integer', ['after' => 'target_name', 'value' => '1']);

      $table = 'glpi_plugin_formcreator_forms';
      $migration->addField($table, 'icon', 'string', ['after' => 'is_recursive']);
      $migration->addField($table, 'icon_color', 'string', ['after' => 'icon']);
      $migration->addField($table, 'background_color', 'string', ['after' => 'icon']);

      $table = 'glpi_plugin_formcreator_answers';
      $migration->changeField($table, 'answer', 'answer', 'longtext');
   }

   /**
    * convert an enum column into an int
    *
    * @param string $table
    * @param string $field
    * @param array $map map of enum value => equivalent integer
    * @param array $options options to give to Migration::addField and Migration::changeField
    * @return void
    */
   protected function enumToInt($table, $field, array $map, $options = []) {
      global $DB;
      $isEnum = PluginFormcreatorCommon::getEnumValues($table, $field);
      if (count($isEnum) > 0) {
         $this->migration->addField(
            $table,
            "new_$field",
            'integer',
            ['after' => $field] + $options
         );
         $this->migration->migrationOneTable($table);
         foreach ($map as $enumValue => $integerValue) {
            $DB->update(
               $table,
               ["new_$field" => $integerValue],
               [$field => $enumValue]
            );
         }
         $this->migration->changeField(
            $table,
            "new_$field",
            $field, 'integer'
         );
      }
   }
}
