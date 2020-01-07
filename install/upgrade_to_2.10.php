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
      global $DB;

      $this->migration = $migration;

      // rows / columns for sections
      $table = 'glpi_plugin_formcreator_questions';
      $migration->changeField($table, 'order', 'row', 'integer');
      $migration->addField($table, 'col', 'integer', ['after' => 'row']);
      $migration->addField($table, 'width', 'integer', ['after' => 'col']);
      $migration->addPostQuery("UPDATE `$table` SET `width`='4' WHERE `width` < '1'");
      // Reorder questions from 0 instead of 1
      $migration->migrationOneTable($table);
      $result = $DB->query("SELECT glpi_plugin_formcreator_sections.id FROM glpi_plugin_formcreator_sections
         INNER JOIN glpi_plugin_formcreator_questions ON (glpi_plugin_formcreator_sections.id = glpi_plugin_formcreator_questions.plugin_formcreator_sections_id)
         GROUP BY glpi_plugin_formcreator_sections.id
         HAVING MIN(glpi_plugin_formcreator_questions.`row`) > 0");
      foreach($result as $row) {
         $DB->update($table, [
            'row' => new QueryExpression("`row` - 1")
         ],
         [
            'plugin_formcreator_sections_id' => $row['id']
         ]);
      }
   }
}
