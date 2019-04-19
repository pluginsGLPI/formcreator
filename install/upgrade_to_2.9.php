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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
class PluginFormcreatorUpgradeTo2_9 {
   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      // Upgrade plugin configuration table
      $table = 'glpi_plugin_formcreator_entityconfigs';
      $migration->displayMessage("Upgrade $table");
      $migration->addField($table, 'external_links_prefix', 'string', ['after' => 'replace_helpdesk']);
      $migration->addField($table, 'external_links_icon', 'string', ['after' => 'external_links_prefix']);
      $migration->addField($table, 'external_links_title', 'string', ['after' => 'external_links_icon']);
      $migration->addField($table, 'tickets_summary', 'integer', ['after' => 'external_links_title', 'value' => '1']);
      $migration->addField($table, 'user_preferences', 'integer', ['after' => 'tickets_summary', 'value' => '1']);
      $migration->addField($table, 'avatar', 'integer', ['after' => 'user_preferences', 'value' => '1']);
      $migration->addField($table, 'user_name', 'integer', ['after' => 'avatar', 'value' => '0']);
      $migration->addField($table, 'profile_selector', 'integer', ['after' => 'user_name', 'value' => '1']);
   }
}
