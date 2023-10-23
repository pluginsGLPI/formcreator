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

use Glpi\Dashboard\Dashboard;
use Glpi\Dashboard\Item;
use Glpi\Toolbox\Sanitizer;

class PluginFormcreatorUpgradeTo2_13_7 {
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
      $this->fixEncodingInQuestions();
      $this->resizeWidgets();
   }

   /**
    * Select and multiseiect questions pay contain RAW ampersand (&)
    * it must be encoded, or select / multiselect fields will not validate answers
    * containing this character
    *
    * @return void
    */
   public function fixEncodingInQuestions() {
      global $DB;

      $table = 'glpi_plugin_formcreator_questions';
      $result = $DB->request([
         'SELECT' => ['id', 'values'],
         'FROM' => $table,
         'WHERE' => [
            'fieldtype' => ['select', 'multiselect'],
            'values' => ['REGEXP', $DB->escape('&(?!#38;)')],
         ],
      ]);

      foreach ($result as $row) {
         $values = json_decode($row['values']);
         if (!is_array($values) || $values === null) {
            continue;
         }
         foreach ($values as &$value) {
            $value = Sanitizer::encodeHtmlSpecialChars($value);
         }
         $values = json_encode($values, JSON_UNESCAPED_UNICODE, JSON_UNESCAPED_SLASHES);
         $values = $DB->escape($values);
         $DB->update(
            $table,
            ['values' => $values],
            ['id' => $row['id']]
         );
      }
   }

   /**
    * Resize widgets of the `plugin_formcreator_issue_counters` dashboard to match
    * the mini_tickets core dashboard style
    *
    * @return void
    */
   public function resizeWidgets() {
      // Get container
      $dashboard = new Dashboard();
      $found = $dashboard->getFromDB("plugin_formcreator_issue_counters");

      if (!$found) {
         // Unable to fetch dashboard
         return;
      };

      $di = new Item();
      $cards = $di->find(['dashboards_dashboards_id' => $dashboard->fields['id']]);
      $x = 0;

      foreach ($cards as $card) {
         $di = new Item();
         $di->update([
            'id'     => $card['id'],
            'width'  => 4,
            'height' => 2,
            'x'      => $x,
            'y'      => 0,
         ]);

         $x +=4;
      }
   }
}
