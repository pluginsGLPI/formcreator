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

         case 'testUpgradedPlugin':
            $this->olddb = new \DB();
            $this->string(getenv('OLD_DB_NAME'));
            $oldDbName = getenv('OLD_DB_NAME');
            $this->olddb->dbdefault = $oldDbName;
            $this->olddb->connect();
            $this->boolean($this->olddb->connected)->isTrue();
            break;
      }
   }

   public function afterTestMethod($method) {
      parent::afterTestMethod($method);
      switch ($method) {
         case 'testUpgradedPlugin':
            $this->olddb->close();
            break;
      }
   }

   public function testInstallPlugin() {
      global $DB;

      $pluginName = TEST_PLUGIN_NAME;

      $this->given(self::setupGLPIFramework())
           ->and($this->boolean($DB->connected)->isTrue());

      //Drop plugin configuration if exists
      $config = $this->newTestedInstance();
      $config->deleteByCriteria(['context' => $pluginName]);

      // Drop tables of the plugin if they exist
      $query = "SHOW TABLES";
      $result = $DB->query($query);
      if (version_compare(GLPI_VERSION, '9.5') >= 0) {
         $fa = 'fetchArray';
      } else {
         $fa = 'fetch_array';
      }
      while ($data = $DB->$fa($result)) {
         if (strstr($data[0], "glpi_plugin_$pluginName") !== false) {
            $DB->query("DROP TABLE " . $data[0]);
         }
      }

      // Reset logs
      $this->resetGLPILogs();

      $plugin = new \Plugin();
      // Since GLPI 9.4 plugins list is cached
      $plugin->checkStates(true);
      $plugin->getFromDBbyDir($pluginName);

      // Install the plugin
      ob_start(function($in) { return $in; });
      $plugin->install($plugin->fields['id']);
      $installOutput = ob_get_contents();
      ob_end_clean();
      $this->boolean($plugin->isInstalled($pluginName))->isTrue($installOutput);

      // Enable the plugin
      $plugin->activate($plugin->fields['id']);
      $this->boolean($plugin->isActivated($pluginName))->isTrue('Cannot enable the plugin');

      // Check the version saved in configuration
      $this->checkConfig();
      $this->testPluginName();
      $this->checkAutomaticAction();
   }

   public function testUpgradedPlugin() {
      global $DB;

      $pluginName = TEST_PLUGIN_NAME;

      // Check the version saved in configuration
      $this->checkConfig();
      $this->checkFontAwesomeData();
      $this->testPluginName();

      $fresh_tables = $DB->listTables("glpi_plugin_${pluginName}_%");
      while ($fresh_table = $fresh_tables->next()) {
         $table = $fresh_table['TABLE_NAME'];
         $this->boolean($this->olddb->tableExists($table, false))
            ->isTrue("Table $table does not exists in after an upgrade from an old version!");

         $create = $DB->getTableSchema($table);
         $fresh = $create['schema'];
         $fresh_idx = $create['index'];

         $update = $this->olddb->getTableSchema($table);
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

      $this->testRequestType();
      $this->checkAutomaticAction();
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
      $this->array($config)->isIdenticalTo([
         'schema_version' => PLUGIN_FORMCREATOR_SCHEMA_VERSION
      ]);
   }

   public function testRequestType() {
      $requestType = new \RequestType();
      $requestType->getFromDBByCrit(['name' => 'Formcreator']);
      $this->boolean($requestType->isNewItem())->isFalse();
   }

   public function checkAutomaticAction() {
      $cronTask = new \CronTask();
      $cronTask->getFromDBByCrit([
         'itemtype' => 'PluginFormcreatorISsue',
         'name'     => 'SyncIssues'
      ]);
      $this->boolean($cronTask->isNewItem())->isFalse();
      $this->integer((int) $cronTask->fields['state'])->isEqualTo(0);
   }

   public function checkFontAwesomeData() {
      $pluginName = TEST_PLUGIN_NAME;

      $file = GLPI_ROOT . '/files/_plugins/' . $pluginName . '/font-awesome.php';
      $this->boolean(is_readable($file))->isTrue();
   }
}
