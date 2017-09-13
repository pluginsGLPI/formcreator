<?php
/*
 LICENSE

 Copyright (C) 2016 Teclib'
 Copyright (C) 2010-2016 by the FusionInventory Development Team.

 This file is part of Flyve MDM Plugin for GLPI.

 Flyve MDM Plugin for GLPi is a subproject of Flyve MDM. Flyve MDM is a mobile
 device management software.

 Flyve MDM Plugin for GLPI is free software: you can redistribute it and/or
 modify it under the terms of the GNU Affero General Public License as published
 by the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 Flyve MDM Plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.
 You should have received a copy of the GNU Affero General Public License
 along with Flyve MDM Plugin for GLPI. If not, see http://www.gnu.org/licenses/.
 ------------------------------------------------------------------------------
 @author    Thierry Bugier Pineau
 @copyright Copyright (c) 2016 Flyve MDM plugin team
 @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 @link      https://github.com/flyve-mdm/flyve-mdm-glpi
 @link      http://www.glpi-project.org/
 ------------------------------------------------------------------------------
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

      // TODO: need to find a r eliable way to detect not clenaed
      // - NotificationTemplateTranslation
      // - Notification_NotificationTemplate
   }
}