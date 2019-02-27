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
class PluginFormcreatorUpgradeTo2_8 {

   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      $this->migration = $migration;
      // add item association rule
      $table = 'glpi_plugin_formcreator_targettickets';
      $migration->addField($table, 'associate_rule', 'integer', ['after' => 'category_question']);
      $migration->addField($table, 'associate_question', 'integer', ['after' => 'associate_rule']);

      // Rename the plugin
      $plugin = new Plugin();
      $plugin->getFromDBbyDir('formcreator');
      $success = $plugin->update([
         'id' => $plugin->getID(),
         'name' => 'Form Creator',
      ]);

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
            'default_value' => '1'
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
            'default_value' => '1'
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
            'default_value' => '1'
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
            'default_value' => '1'
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
               'default_value' => '1'
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
               'default_value' => '1'
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
               'default_value' => '1'
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
               'default_value' => '1'
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
               'default_value' => '1'
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
               'default_value' => '1'
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
            'default_value' => '1'
         ]
      );
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
