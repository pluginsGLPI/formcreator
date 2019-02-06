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

namespace tests\units;

use GlpiPlugin\Formcreator\Tests\CommonTestCase;

/**
 * @engine inline
 */
class Config extends CommonTestCase {
   private $olddb;

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testInstallPlugin':
            $this->login('glpi', 'glpi');
            break;

         case 'testUpgradePlugin':
            $this->olddb = new \DB();
            $this->string(getenv('OLDDBNAME'));
            $this->olddb->dbdefault = getenv('OLDDBNAME');
            $this->olddb->connect();
            $this->boolean($this->olddb->connected)->isTrue();
            break;
      }
   }

   public function afterTestMethod($method) {
      parent::afterTestMethod($method);
      switch ($method) {
         case 'testUpgradePlugin':
            $this->olddb->close();
            break;
      }
   }

   public function testInstallPlugin() {
      global $DB;

      $pluginname = TEST_PLUGIN_NAME;

      $this->given(self::setupGLPIFramework())
           ->and($this->boolean($DB->connected)->isTrue());

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
      // Since GLPI 9.4 plugins list is cached
      $plugin->checkStates(true);
      $plugin->getFromDBbyDir($pluginname);

      // Install the plugin
      ob_start(function($in) { return ''; });
      $plugin->install($plugin->fields['id']);
      ob_end_clean();

      // Enable the plugin
      $plugin->activate($plugin->fields['id']);
      $this->boolean($plugin->isActivated($pluginname))->isTrue('Cannot enable the plugin');

      // Check the version saved in configuration
      $this->checkConfig();
      $this->testPluginName();

      // Take a snapshot of the database before any test
      $this->mysql_dump($DB->dbuser, $DB->dbhost, $DB->dbpassword, $DB->dbdefault, './save.sql');

      $this->boolean(file_exists("./save.sql"))->isTrue();
      $filestats = stat("./save.sql");
      $length = $filestats[7];
      $this->integer($length)->isGreaterThan(0);
   }

   public function testUpgradePlugin() {
      global $DB;

      $pluginName = TEST_PLUGIN_NAME;

      // Check the version saved in configuration
      $this->checkConfig();
      $this->testPluginName();

      $fresh_tables = $DB->listTables("glpi_plugin_${pluginName}_%");
      while ($fresh_table = $fresh_tables->next()) {
         $table = $fresh_table['TABLE_NAME'];
         $this->boolean($this->olddb->tableExists($table, false))
            ->isTrue("Table $table does not exists from migration!");

         $create = $DB->getTableSchema($DB, $table);
         $fresh = $create['schema'];
         $fresh_idx = $create['index'];

         $update = $DB->getTableSchema($this->olddb, $table);
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


   public function testPluginName() {
      $plugin = new \Plugin();
      $plugin->getFromDBbyDir(TEST_PLUGIN_NAME);
      $this->string($plugin->fields['name'])->isEqualTo('Form Creator');
   }

   public function checkConfig() {
      $pluginName = TEST_PLUGIN_NAME;

      // Check the version saved in configuration
      $config = \Config::getConfigurationValues($pluginName);
      $this->array($config)
         ->hasKeys(['schema_version'])
         ->hasSize(1);
      $this->string($config['schema_version'])->isEqualTo(PLUGIN_FORMCREATOR_SCHEMA_VERSION);
   }

   public function testPluginName() {
      $plugin = new \Plugin();
      $plugin->getFromDBbyDir(TEST_PLUGIN_NAME);
      $this->string($plugin->fields['name'])->isEqualTo('Form Creator');
   }
}
