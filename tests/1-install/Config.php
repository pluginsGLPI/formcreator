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

use Glpi\Dashboard\Dashboard;
use Glpi\Dashboard\Item;
use Glpi\Dashboard\Right;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;
use Profile;

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

      $this->given($this->setupGLPIFramework())
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

      $this->checkConfig();
      $this->checkRequestType();
      $this->checkPluginName();
      $this->checkAutomaticAction();
      $this->checkDashboard();
   }

   public function testUpgradedPlugin() {
      global $DB;

      $pluginName = TEST_PLUGIN_NAME;

      $fresh_tables = $DB->listTables("glpi_plugin_${pluginName}_%");
      foreach ($fresh_tables as $fresh_table) {
         $table = $fresh_table['TABLE_NAME'];
         $this->boolean($this->olddb->tableExists($table, false))
            ->isTrue("Table $table does not exist after an upgrade from an old version!");

         $tableStructure = $DB->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
         $create = $this->getTableSchema($table, $tableStructure);
         $fresh = $create['schema'];
         $fresh_idx = $create['index'];

         $tableStructure = $this->olddb->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
         $update = $this->getTableSchema($table, $tableStructure);
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

      $this->checkConfig();
      $this->checkRequestType();
      $this->checkPluginName();
      $this->checkAutomaticAction();
      $this->checkDashboard();
   }

   public function checkPluginName() {
      $plugin = new \Plugin();
      $plugin->getFromDBbyDir(TEST_PLUGIN_NAME);
      $this->string($plugin->fields['name'])->isEqualTo('Form Creator');
   }

   public function checkConfig() {
      $pluginName = TEST_PLUGIN_NAME;

      // Check the version saved in configuration
      $this->string(\Config::getConfigurationValue($pluginName, 'schema_version'))->isEqualTo(PLUGIN_FORMCREATOR_SCHEMA_VERSION);
   }

   public function checkRequestType() {
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

   /**
    * Undocumented function
    *
    * @param string $table
    * @param string|null $structure
    * @return array
    */
   public function getTableSchema($table, $structure = null) {
      global $DB;

      if ($structure === null) {
         $structure = $DB->query("SHOW CREATE TABLE `$table`")->fetch_row();
         $structure = $structure[1];
      }

      //get table index
      $index = preg_grep(
         "/^\s\s+?KEY/",
         array_map(
            function($idx) { return rtrim($idx, ','); },
            explode("\n", $structure)
         )
      );
      //get table schema, without index, without AUTO_INCREMENT
      $structure = preg_replace(
         [
            "/\s\s+KEY .*/",
            "/AUTO_INCREMENT=\d+ /"
         ],
         "",
         $structure
      );
      $structure = preg_replace('/,(\s)?$/m', '', $structure);
      $structure = preg_replace('/ COMMENT \'(.+)\'/', '', $structure);

      $structure = str_replace(
         [
            " COLLATE utf8mb4_unicode_ci",
            " CHARACTER SET utf8mb4",
            " COLLATE utf8_unicode_ci",
            " CHARACTER SET utf8",
            ', ',
         ], [
            '',
            '',
            '',
            '',
            ',',
         ],
         trim($structure)
      );

      //do not check engine nor collation
      $structure = preg_replace(
         '/\) ENGINE.*$/',
         '',
         $structure
      );

      //Mariadb 10.2 will return current_timestamp()
      //while older retuns CURRENT_TIMESTAMP...
      $structure = preg_replace(
         '/ CURRENT_TIMESTAMP\(\)/i',
         ' CURRENT_TIMESTAMP',
         $structure
      );

      //Mariadb 10.2 allow default values on longblob, text and longtext
      $defaults = [];
      preg_match_all(
         '/^.+ ((medium|long)?(blob|text)) .+$/m',
         $structure,
         $defaults
      );
      if (count($defaults[0])) {
         foreach ($defaults[0] as $line) {
               $structure = str_replace(
                  $line,
                  str_replace(' DEFAULT NULL', '', $line),
                  $structure
               );
         }
      }

      $structure = preg_replace("/(DEFAULT) ([-|+]?\d+)(\.\d+)?/", "$1 '$2$3'", $structure);
      //$structure = preg_replace("/(DEFAULT) (')?([-|+]?\d+)(\.\d+)(')?/", "$1 '$3'", $structure);

      // Remove integer display width
      $structure = preg_replace('/(INT)\(\d+\)/i', '$1', $structure);

      return [
         'schema' => strtolower($structure),
         'index'  => $index
      ];
   }

   public function checkDashboard() {
      // Check the dashboard exists
      $dashboard = new Dashboard();
      $dashboard->getFromDB('plugin_formcreator_issue_counters');
      $this->boolean($dashboard->isNewItem())->isFalse();

      // Check rights on the dashboard
      $right = new Right();
      $profile = new Profile();
      $helpdeskProfiles = $profile->find([
         'interface' => 'helpdesk',
      ]);
      foreach ($helpdeskProfiles as $helpdeskProfile) {
         $rows = $right->find([
            'dashboards_dashboards_id' => $dashboard->fields['id'],
            'itemtype'                 => Profile::getType(),
            'items_id'                 => $helpdeskProfile['id']
         ]);
         $this->array($rows)->hasSize(1);
      }

      // Check there is widgets in the dashboard
      $dashboardItem = new Item();
      $rows = $dashboardItem->find([
         'dashboards_dashboards_id' => $dashboard->fields['id'],
      ]);
      $this->array($rows)->hasSize(7);
   }
}
