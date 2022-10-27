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
class PluginFormcreatorUpgradeTo2_13_3 {
   /** @var Migration */
   protected $migration;

   public function isResyncIssuesRequired() {
      return true;
   }

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      $this->migration = $migration;
      $this->dropIssuesValidators();
   }

   public function dropIssuesValidators() {
      $table = 'glpi_plugin_formcreator_issues';
      $this->migration->dropKey($table, 'users_id_validator');
      $this->migration->dropKey($table, 'groups_id_validator');
      $this->migration->dropField($table, 'users_id_validator');
      $this->migration->dropField($table, 'groups_id_validator');
   }
}
