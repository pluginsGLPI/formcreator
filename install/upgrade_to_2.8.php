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
class PluginFormcreatorUpgradeTo2_8 {
   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      // add item association rule
      $table = 'glpi_plugin_formcreator_targettickets';
      $enum_associate_rule = "'".implode("', '", array_keys(PluginFormcreatorTargetTicket::getEnumAssociateRule()))."'";
      if (!$DB->fieldExists($table, 'associate_rule', false)) {
         $migration->addField(
            $table,
            'associate_rule',
            "ENUM($enum_associate_rule) NOT NULL DEFAULT 'none'",
            ['after' => 'category_question']
         );
      } else {
         $current_enum_associate_rule = PluginFormcreatorCommon::getEnumValues($table, 'associate_rule');
         if (count($current_enum_associate_rule) != count($enum_associate_rule)) {
            $migration->changeField(
               $table,
               'location_rule',
               'location_rule',
               "ENUM($enum_associate_rule) NOT NULL DEFAULT 'none'",
               ['after' => 'category_question']
            );
         }
      }
      $migration->addField($table, 'associate_question', 'integer', ['after' => 'associate_rule']);

      // Rename the plugin
      $plugin = new Plugin();
      $plugin->getFromDBbyDir('formcreator');
      $success = $plugin->update([
         'id' => $plugin->getID(),
         'name' => 'Form Creator',
      ]);
   }
}
