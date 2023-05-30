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

use Glpi\RichText\RichText;
use Glpi\Toolbox\Sanitizer;

class PluginFormcreatorUpgradeTo2_13_6 {
   /** @var Migration */
   protected $migration;

   public function isResyncIssuesRequired() {
      return false;
   }

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      $this->migration = $migration;
      $this->migrateToRichText();
      $this->sanitizeConditions();
   }

   public function migrateToRichText() {
      global $DB;

      $tables = [
         'glpi_plugin_formcreator_targetchanges' => [
            'content',
            'impactcontent',
            'controlistcontent',
            'rolloutplancontent',
            'backoutplancontent',
            'checklistcontent',
         ],
         'glpi_plugin_formcreator_targetproblems' => [
            'content',
            'impactcontent',
            'causecontent',
            'symptomcontent',
         ],
      ];

      foreach ($tables as $table => $fields) {
         $request = [
            'SELECT' => ['id'] + $fields,
            'FROM'   => $table
         ];
         foreach ($DB->request($request) as $row) {
            foreach ($fields as $field) {
               $row[$field] = RichText::getSafeHtml($row[$field]);
               $row[$field] = Sanitizer::dbEscape($row[$field]);
            }
            $DB->update($table, $row, ['id' => $row['id']]);
         }
      }
   }

   /**
    * Conditions written in Formcreator < 2.13.0 are not sanitized.
    * With versions >= 2.13.0, comparisons require sanitization
    *
    * @return void
    */
   protected function sanitizeConditions() {
      global $DB;

      $table = 'glpi_plugin_formcreator_conditions';
      $request = $DB->request([
         'SELECT' => ['id', 'show_value'],
         'FROM' => $table,
      ]);
      foreach ($request as $row) {
         $row['show_value'] = Sanitizer::sanitize($row['show_value'], true);
         $DB->update($table, $row, ['id' => $row['id']]);
      }
   }
}
