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
 * @author    the Formcreator plugin team
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2018 Teclib
 * @license   GPLv2 https://www.gnu.org/licenses/gpl2.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ------------------------------------------------------------------------------
 */

class SaveInstallTest extends CommonDBTestCase
{

   public function should_restore_install() {
      return false;
   }

   public function testPluginIsActive() {
      $plugin = new Plugin();
      $this->assertTrue($plugin->isActivated('formcreator'), "The plugin is not activated");
   }

   public function testSaveInstallation() {
      global $DB;
      $DB = new DB();

      $this->mysql_dump($DB->dbuser, $DB->dbhost, $DB->dbpassword, $DB->dbdefault, './save.sql');

      $this->assertFileExists("./save.sql");
      $filestats = stat("./save.sql");
      $length = $filestats[7];
      $this->assertGreaterThan(0, $length);
   }
}