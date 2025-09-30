<?php

/**
 *
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
 * @copyright Copyright © 2011 - 2018-2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class PluginFormcreatorUpgradeTo2_13_4 {
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
      $this->updateConditions();
      $this->addServiceCatalogHopepage();
   }

   public function addServiceCatalogHopepage() {
      $table = 'glpi_plugin_formcreator_entityconfigs';
      $this->migration->addField($table, 'service_catalog_home', 'integer', [
         'value' => -2,
         'after' => 'header',
         'update' => '0',
         'condition' => 'WHERE `entities_id` = 0'
      ]);
   }

   public function updateConditions() {
      $this->migration->changeField('glpi_plugin_formcreator_conditions', 'show_value', 'show_value', 'mediumtext');
   }
}
