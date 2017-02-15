<?php
/**
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

class PluginInstallTest extends CommonTestCase
{

   public function setUp() {
      parent::setUp();
      self::setupGLPIFramework();
      self::login('glpi', 'glpi', true);
   }

   protected function setupGLPI() {
      global $CFG_GLPI;

      $settings = [
            'use_mailing' => '1',
      ];
      config::setConfigurationValues('core', $settings);

      $CFG_GLPI = $settings + $CFG_GLPI;
   }

   public function testInstallPlugin() {
      global $DB;

      $this->setupGLPI();

      $this->assertTrue($DB->connected, "Problem connecting to the Database");

      $this->login('glpi', 'glpi');

      //Drop plugin configuration if exists
      $config = new Config();
      $config->deleteByCriteria(array('context' => 'formcreator'));

      // Drop tables of the plugin if they exist
      $query = "SHOW TABLES";
      $result = $DB->query($query);
      while ($data = $DB->fetch_array($result)) {

         if (strstr($data[0], "glpi_plugin_formcreator") !== false) {
            $DB->query("DROP TABLE ".$data[0]);
         }
      }

      self::resetGLPILogs();

      $plugin = new Plugin();
      $plugin->getFromDBbyDir("formcreator");

      ob_start(function($in) { return ''; });
      $plugin->install($plugin->fields['id']);
      ob_end_clean();

      $PluginDBTest = new PluginDB();
      $PluginDBTest->checkInstall("formcreator", "install");

      // Enable the plugin
      $plugin->activate($plugin->fields['id']);
      $this->assertTrue($plugin->isActivated("flyvemdm"), "Cannot enable the plugin");

      // Force the MQTT backend's credentials
      // Useful to force the credientials to be the same as a development database
      // and not force broker's reconfiguration when launching tests on the test-dedicates DB
      $mqttUser = new PluginFlyvemdmMqttuser();
      if (!empty(PHPUNIT_FLYVEMDM_MQTT_PASSWD)) {
         $mqttUser->getByUser('flyvemdm-backend');
         $mqttUser->update([
               'id'        => $mqttUser->getID(),
               'password'  => PHPUNIT_FLYVEMDM_MQTT_PASSWD
         ]);
         Config::setConfigurationValues('flyvemdm', ['mqtt_passwd' => PHPUNIT_FLYVEMDM_MQTT_PASSWD]);
      }
   }

   public function testConfigurationExists() {
      $config = Config::getConfigurationValues('flyvemdm');
      $expected = [
            'mqtt_broker_address',
            'mqtt_broker_internal_address',
            'mqtt_broker_port',
            'mqtt_broker_tls',
            'mqtt_use_client_cert',
            'mqtt_broker_tls_ciphers',
            'mqtt_user',
            'mqtt_passwd',
            'instance_id',
            'registered_profiles_id',
            'guest_profiles_id',
            'service_profiles_id',
            'debug_enrolment',
            'debug_noexpire',
            'ssl_cert_url',
            'version',
            'default_device_limit',
            'default_agent_url',
      ];
      $diff = array_diff_key(array_flip($expected), $config);
      $this->assertEquals(0, count($diff));

      return $config;
   }

   /**
    * @depends testConfigurationExists
    */
   public function testGuestProfileExists($config) {
      $guestProfileId = $config['guest_profiles_id'];
      $profile = new Profile();
      $this->assertTrue($profile->getFromDB($guestProfileId));
   }

}
