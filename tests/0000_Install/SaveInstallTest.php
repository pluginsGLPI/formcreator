<?php
/*
 LICENSE

 This file is part of the Formcreator plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   Formcreator
 @author    the Formcreator plugin team
 @copyright Copyright (c) 2015 Formcreator plugin team
 @license   GPLv2+ http://www.gnu.org/licenses/gpl.txt
 @link      https://github.com/PluginsGLPI/Formcreator
 @link      http://www.glpi-project.org/
 @since     0.1.33
 ----------------------------------------------------------------------
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