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

class Config extends CommonTestCase
{

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      $this->setupGLPIFramework();
   }

   public function testUninstallPlugin() {
      global $DB;

      $pluginName = TEST_PLUGIN_NAME;

      $plugin = new \Plugin();
      $plugin->getFromDBbyDir($pluginName);

      // Uninstall the plugin
      $log = '';
      ob_start(function($in) use (&$log) {
         $log .= $in;
         return '';
      });
      $plugin->uninstall($plugin->getID());
      ob_end_clean();

      // Check the plugin is not installed
      $plugin->getFromDBbyDir(strtolower($pluginName));
      $this->integer((int) $plugin->fields['state'])->isEqualTo(\Plugin::NOTINSTALLED);

      // Check all plugin's tables are dropped
      $tables = [];
      $result = $DB->query("SHOW TABLES LIKE 'glpi_plugin_" . $pluginName . "_%'");
      while ($row = $DB->fetchAssoc($result)) {
         $tables[] = array_pop($row);
      }
      $this->integer(count($tables))->isEqualTo(0, "not deleted tables \n" . json_encode($tables, JSON_PRETTY_PRINT));

      // Check the notifications of the plugin no longer exist
      $rows = $DB->request([
         'COUNT' => 'cpt',
         'FROM'  => \Notification::getTable(),
         'WHERE' => [
            'itemtype' => 'PluginFormcreatorFormAnswer',
         ]
      ])->next();
      $this->integer((int)$rows['cpt'])->isEqualTo(0);

      $rows = $DB->request([
         'COUNT' => 'cpt',
         'FROM'  => \NotificationTemplate::getTable(),
         'WHERE' => [
            'itemtype' => 'PluginFormcreatorFormAnswer',
         ]
      ])->next();
      $this->integer((int)$rows['cpt'])->isEqualTo(0);

      // Check that the requesttype is NOT deleted
      $requestType = new \RequestType();
      $requestType->getFromDBByCrit(['name' => 'Formcreator']);
      $this->boolean($requestType->isNewItem())->isFalse();

      // TODO: need to find a reliable way to detect not clenaed
      // - NotificationTemplateTranslation
      // - Notification_NotificationTemplate
   }
}
