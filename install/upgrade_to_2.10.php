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
class PluginFormcreatorUpgradeTo2_10 {

   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      $this->migration = $migration;

      // Add conditions on the submit button for a form
      $table = 'glpi_plugin_formcreator_forms';
      $migration->addField(
         $table,
         'show_rule',
         'integer',
         [
            'value'   => '1',
            'after'   => 'is_default',
            'comment' => 'Conditions setting to show the submit button'
         ]
      );

      // Add request type specific question
      $table = 'glpi_plugin_formcreator_targettickets';
      $migration->changeField($table, 'type', 'type_question', 'integer', ['after' => 'target_name', 'value' => '0']);
      $migration->migrationOneTable($table);
      $migration->addField($table, 'type_rule', 'integer', ['after' => 'target_name', 'value' => '0']);

      // conditions on targets
      $table = 'glpi_plugin_formcreator_targetchanges';
      $migration->addField($table, 'show_rule', 'integer', ['value' => '1', 'after' => 'category_question']);
      $table = 'glpi_plugin_formcreator_targettickets';
      $migration->addField($table, 'show_rule', 'integer', ['value' => '1', 'after' => 'location_question']);

      // support for validator group in issues
      $table = 'glpi_plugin_formcreator_issues';
      $migration->changeField($table, 'validator_id', 'users_id_validator', 'integer');
      $migration->addField($table, 'groups_id_validator', 'integer', ['after' => 'users_id_validator']);
      $migration->migrationOneTable($table);
      $migration->dropKey($table, 'validator_id');
      $migration->addKey($table, 'users_id_validator');
      $migration->addKey($table, 'groups_id_validator');
   }
}
