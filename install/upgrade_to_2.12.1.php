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
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
class PluginFormcreatorUpgradeTo2_12_1 {
   /** @var Migration */
   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      $this->migration = $migration;

      $table = 'glpi_plugin_formcreator_entityconfigs';

      // Change default value in DB
      $this->migration->changeField($table, 'replace_helpdesk', 'replace_helpdesk', 'integer', ['value' => '-2']);
      $this->migration->changeField($table, 'sort_order', 'sort_order', 'integer', ['value' => '-2']);
      $this->migration->changeField($table, 'is_kb_separated', 'is_kb_separated', 'integer', ['value' => '-2']);
      $this->migration->changeField($table, 'is_search_visible', 'is_search_visible', 'integer', ['value' => '-2']);
      $this->migration->changeField($table, 'is_header_visible', 'is_header_visible', 'integer', ['value' => '-2']);

      // Change all 0 to -2 (aka inherit from parent)
      $DB->update(
         $table,
         [
            'is_kb_separated' => -2,
         ],
         [
            'is_kb_separated' => 0,
            'id' => ['<>', 0],
         ]
      );

      // Decrement values from 1 to 0 and from 2 to 1
      $DB->update(
         $table,
         [
            'is_kb_separated' => new QueryExpression('`is_kb_separated` - 1'),
         ],
         [
            'is_kb_separated' => ['>', 0],
         ]
      );

      // Inscrease description size
      $table = 'glpi_plugin_formcreator_questions';
      $this->migration->changeField($table, 'description', 'description', 'MEDIUMTEXT');
   }
}
