<?php
/**
 * LICENSE
 *
 * Copyright © 2016-2017 Teclib'
 * Copyright © 2010-2016 by the FusionInventory Development Team.
 *
 * This file is part of Flyve MDM Plugin for GLPI.
 *
 * Flyve MDM Plugin for GLPI is a subproject of Flyve MDM. Flyve MDM is a mobile
 * device management software.
 *
 * Flyve MDM Plugin for GLPI is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * Flyve MDM Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License
 * along with Flyve MDM Plugin for GLPI. If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @author    Thierry Bugier Pineau
 * @copyright Copyright © 2017 Teclib
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/flyve-mdm/flyve-mdm-glpi-plugin
 * @link      https://flyve-mdm.com/
 * ------------------------------------------------------------------------------
 */

namespace tests\units;

use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class Config extends CommonTestCase {

   public function testInstallPlugin() {
      global $DB;

      $pluginname = TEST_PLUGIN_NAME;

      $this->given(self::setupGLPIFramework())
           ->and($this->boolean($DB->connected)->isTrue())
           ->and($this->configureGLPI())
           ->and($this->installDependancies());

      //Drop plugin configuration if exists
      $config = $this->newTestedInstance();
      $config->deleteByCriteria(['context' => $pluginname]);

      // Drop tables of the plugin if they exist
      $query = "SHOW TABLES";
      $result = $DB->query($query);
      while ($data = $DB->fetch_array($result)) {
         if (strstr($data[0], "glpi_plugin_$pluginname") !== false) {
            $DB->query("DROP TABLE " . $data[0]);
         }
      }

      // Reset logs
      $this->resetGLPILogs();

      $plugin = new \Plugin();
      $plugin->getFromDBbyDir($pluginname);

      // Install the plugin
      ob_start(function($in) { return ''; });
      $plugin->install($plugin->fields['id']);
      ob_end_clean();

      // Assert the database matches the schema
      $filename = GLPI_ROOT."/plugins/$pluginname/install/mysql/plugin_" . $pluginname . "_empty.sql";
      $this->checkInstall($filename, 'glpi_plugin_' . $pluginname . '_', 'install');

      // Enable the plugin
      $plugin->activate($plugin->fields['id']);
      $this->boolean($plugin->isActivated($pluginname))->isTrue('Cannot enable the plugin');

      // Enable debug mode for enrollment messages
      \Config::setConfigurationValues($pluginname, ['debug_enrolment' => '1']);

      // Take a snapshot of the database before any test
      $this->mysql_dump($DB->dbuser, $DB->dbhost, $DB->dbpassword, $DB->dbdefault, './save.sql');

      $this->boolean(file_exists("./save.sql"))->isTrue();
      $filestats = stat("./save.sql");
      $length = $filestats[7];
      $this->integer($length)->isGreaterThan(0);
   }

   /**
    * Configure GLPI to isntall the plugin
    */
   private function configureGLPI() {
      global $CFG_GLPI;

      $settings = [
         'use_notifications' => '1',
         'notifications_mailing' => '1',
         'enable_api'  => '1',
         'enable_api_login_credentials'  => '1',
         'enable_api_login_external_token'  => '1',
      ];
      \Config::setConfigurationValues('core', $settings);

      $CFG_GLPI = $settings + $CFG_GLPI;

      $settings = [
         'mqtt_broker_port' => '1884',
      ];
      \Config::setConfigurationValues('flyvemdm', $settings);
   }

   /**
    * install requirements for the plugin
    */
   private function installDependancies() {
   }

   public function testUpgradePlugin() {
      global $DB;

      $pluginName = TEST_PLUGIN_NAME;

      $fresh_tables = $DB->listTables("glpi_plugin_${pluginName}_%");
      while ($fresh_table = $fresh_tables->next()) {
         $table = $fresh_table['TABLE_NAME'];
         $this->boolean($this->olddb->tableExists($table, false))
            ->isTrue("Table $table does not exists from migration!");

         // To be replaced by call to dbmysql::getTableSchema
         // when GLPI 9.2.x support drops
         $create = $this->getTableSchema($DB, $table);
         $fresh = $create['schema'];
         $fresh_idx = $create['index'];

         // To be replaced by call to dbmysql::getTableSchema
         // when GLPI 9.2.x support drops
         $update = $this->getTableSchema($this->olddb, $table);
         $updated = $update['schema'];
         $updated_idx = $update['index'];

         //compare table schema
         $this->string($updated)->isIdenticalTo($fresh);
         //check index
         $fresh_diff = array_diff($fresh_idx, $updated_idx);
         $this->array($fresh_diff)->isEmpty("Index missing in update for $table: " . implode(', ', $fresh_diff));
         $update_diff = array_diff($updated_idx, $fresh_idx);
         $this->array($update_diff)->isEmpty("Index missing in empty for $table: " . implode(', ', $update_diff));
      }
   }
}
