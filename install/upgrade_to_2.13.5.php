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

use Glpi\Toolbox\Sanitizer;

class PluginFormcreatorUpgradeTo2_13_5 {
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
      $this->fixOldRadiosEncoding();
   }

   public function fixOldRadiosEncoding() {
      global $DB;

      $table = 'glpi_plugin_formcreator_questions';
      $questions = $DB->request([
         'SELECT' => ['id', 'values'],
         'FROM'  => $table,
         'WHERE' => ['fieldtype' => 'radios']
      ]);

      foreach ($questions as $row) {
         $values = Sanitizer::unsanitize($row['values']);
         $values = Sanitizer::sanitize($values);
         $DB->update(
            $table,
            [
               'values' => $values
            ],
            [
               'id' => $row['id']
            ]
         );
      }
   }
}
