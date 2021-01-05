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

class PluginFormcreatorUpgradeTo2_6_3 {
   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      // Change id of search option for status of form_answer
      $table = 'glpi_displaypreferences';
      $query = "UPDATE `$table` SET `num`='8' WHERE `itemtype`='PluginFormcreatorForm_Answer' AND `num`='1'";
      $DB->query($query);

      // Remove abusive encding in sections
      $table = 'glpi_plugin_formcreator_sections';
      $request = [
         'FROM' => $table,
      ];
      foreach ($DB->request($request) as $row) {
         $name = Toolbox::addslashes_deep(html_entity_decode($row['name'], ENT_QUOTES|ENT_HTML5));
         $id = $row['id'];
         $DB->query("UPDATE `$table` SET `name`='$name' WHERE `id` = '$id'");
      }

      // Remove abusive encoding in targets
      $table = 'glpi_plugin_formcreator_targets';
      $request = [
         'FROM' => $table,
      ];
      foreach ($DB->request($request) as $row) {
         $name = Toolbox::addslashes_deep(html_entity_decode($row['name'], ENT_QUOTES|ENT_HTML5));
         $id = $row['id'];
         $DB->query("UPDATE `$table` SET `name`='$name' WHERE `id` = '$id'");
      }

      // Remove abusive encoding in target tickets
      $table = 'glpi_plugin_formcreator_targettickets';
      $request = [
         'FROM' => $table,
      ];
      foreach ($DB->request($request) as $row) {
         $name = Toolbox::addslashes_deep(html_entity_decode($row['name'], ENT_QUOTES|ENT_HTML5));
         $id = $row['id'];
         $DB->query("UPDATE `$table` SET `name`='$name' WHERE `id` = '$id'");
      }

      // Remove abusive encoding in target changes
      $table = 'glpi_plugin_formcreator_targetchanges';
      $request = [
         'FROM' => $table,
      ];
      foreach ($DB->request($request) as $row) {
         $name = Toolbox::addslashes_deep(html_entity_decode($row['name'], ENT_QUOTES|ENT_HTML5));
         $id = $row['id'];
         $DB->query("UPDATE `$table` SET `name`='$name' WHERE `id` = '$id'");
      }
   }
}
