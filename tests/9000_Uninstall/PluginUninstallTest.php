<?php
/**
 * LICENSE
 *
 * Copyright © 2011-2018 Teclib'
 *
 * This file is part of Formcreator Plugin for GLPI.
 *
 * Formcreator is a plugin that allow creation of custom, easy to access forms
 * for users when they want to create one or more GLPI tickets.
 *
 * Formcreator Plugin for GLPI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @author    Thierry Bugier Pineau
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2018 Teclib
 * @license   GPLv2 https://www.gnu.org/licenses/gpl2.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ------------------------------------------------------------------------------
 */

class PluginUninstallTest extends SuperAdminTestCase
{
   public function testUninstall() {
      global $DB;

      $plugin = new Plugin();
      $plugin->getFromDBbyDir("formcreator");

      // Uninstall the plugin
      ob_start(function($in) { return ''; });
      $plugin->uninstall($plugin->getID());
      ob_end_clean();

      // Check all  tables are dropped
      $tables = [];
      $result = $DB->query("SHOW TABLES LIKE 'glpi_plugin_formcreator_%'");
      while ($row = $DB->fetch_assoc($result)) {
         $tables[] = array_pop($row);
      }
      $this->assertCount(0, $tables, "not deleted tables \n" . json_encode($tables, JSON_PRETTY_PRINT));

      // Check the request type still exists
      $requestType = new RequestType();
      $rows = $requestType->find("`name` = 'Formcreator'");
      $this->assertCount(1, $rows);

      // Check the notifications of the plugin no longer exist
      $notification = new Notification();
      $rows = $notification->find("`itemtype` = 'PluginFormcreatorForm_Answer'");
      $this->assertCount(0, $rows);

      $template = new NotificationTemplate();
      $rows = $template->find("`itemtype` = 'PluginFormcreatorForm_Answer'");
      $this->assertCount(0, $rows);

      $config = Config::getConfigurationValues('formcreator');
      $this->assertArrayNotHasKey('schema_version', $config);

      // TODO: need to find a reliable way to detect not clenaed
      // - NotificationTemplateTranslation
      // - Notification_NotificationTemplate
   }
}