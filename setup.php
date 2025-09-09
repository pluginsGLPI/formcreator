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

use Glpi\Plugin\Hooks;

/** @var array $CFG_GLPI */
global $CFG_GLPI;
// Version of the plugin (major.minor.bugfix)
define('PLUGIN_FORMCREATOR_VERSION', '3.0.0');
// Schema version of this version (major.minor only)
define('PLUGIN_FORMCREATOR_SCHEMA_VERSION', '3.0');
// is or is not an official release of the plugin
define('PLUGIN_FORMCREATOR_IS_OFFICIAL_RELEASE', true);

// Minimal GLPI version, inclusive
define ('PLUGIN_FORMCREATOR_GLPI_MIN_VERSION', '11.0.0');
// Maximum GLPI version, exclusive (ignored if PLUGIN_FORMCREATOR_IS_OFFICIAL_RELEASE == false)
define ('PLUGIN_FORMCREATOR_GLPI_MAX_VERSION', '11.0.99');

// Plugin is now migration-only (End of Life for functionality)
define('PLUGIN_FORMCREATOR_IS_EOL', true);

// Use a constant for web directory to avoid deprecated method calls
define('FORMCREATOR_ROOTDOC', '/plugins/formcreator');

// Advanced features for Formcreator
define('PLUGIN_FORMCREATOR_ADVANCED_VALIDATION', 'advform');

/**
 * Define the plugin's version and informations
 *
 * @return array [name, version, author, homepage, license, minGlpiVersion]
 */
function plugin_version_formcreator() {
   plugin_formcreator_savePreviousVersion();

   // Use constant instead of deprecated method
   $webDir = FORMCREATOR_ROOTDOC;

   return [
      'name'           => 'Form Creator (Migration Only)',
      'version'        => PLUGIN_FORMCREATOR_VERSION,
      'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
      'homepage'       => 'https://github.com/pluginsGLPI/formcreator',
      'license'        => '<a href="' . $webDir . '/LICENSE.md" target="_blank">GPLv2</a>',
      'requirements'   => [
         'glpi'           => [
            'min'            => PLUGIN_FORMCREATOR_GLPI_MIN_VERSION,
            'max'            => PLUGIN_FORMCREATOR_GLPI_MAX_VERSION
         ]
      ]
   ];
}

/**
 * Initialize all classes and generic variables of the plugin
 * VERSION 3.0.0 - MIGRATION ONLY (END OF LIFE)
 */
function plugin_init_formcreator() {
   /** @var array $CFG_GLPI */
   global $CFG_GLPI;

   // Always set permanent hooks for migration and cleanup
   plugin_formcreator_permanent_hook();

   $plugin = new Plugin();
   if (!$plugin->isActivated('formcreator')) {
      return;
   }

   // This version is always EOL, so always load migration-only functionality
   plugin_formcreator_init_migration_only();
   
   // Register plugin classes
   plugin_formcreator_registerClasses();
   
   // Load plugin hooks for menu and interface elements
   plugin_formcreator_hook();
}

/**
 * Initialize migration-only functionality for EOL version
 */
function plugin_formcreator_init_migration_only() {
   // Load only essential classes for migration
   spl_autoload_register('plugin_formcreator_autoload');
   
   // Display EOL warning in admin interface
   if (Session::haveRight('config', UPDATE)) {
      plugin_formcreator_show_eol_warning();
   }

   // Register minimal classes needed for migration
   Plugin::registerClass(PluginFormcreatorInstall::class);
   
   // Add admin menu for migration status only
   if (Session::haveRight('config', UPDATE)) {
      /** @var array $PLUGIN_HOOKS */
      global $PLUGIN_HOOKS;
      $PLUGIN_HOOKS['menu_entry']['formcreator'] = 'front/migration_status.php';
   }
}

/**
 * Show End-of-Life warning message
 */
function plugin_formcreator_show_eol_warning() {
   if (isset($_SESSION['formcreator_eol_warning_shown'])) {
      return; // Show only once per session
   }

   $message = sprintf(
      __('Formcreator v%s is now End-of-Life (EOL). This version only provides migration to GLPI 11 native forms. After successful migration, consider uninstalling this plugin and use GLPI\'s native form system.', 'formcreator'),
      PLUGIN_FORMCREATOR_VERSION
   );
   
   Session::addMessageAfterRedirect($message, true, WARNING);
   $_SESSION['formcreator_eol_warning_shown'] = true;
}

/**
 * Legacy initialization (preserved for reference, should not be used in v3.0.0)
 */
function plugin_formcreator_init_legacy() {
   // This function is disabled in EOL version
   // All functional features have been removed
   return;
}

/**
 * Tells if helpdesk replacement is enabled for the current user
 * DISABLED in EOL version
 *
 * @return boolean
 */
function plugin_formcreator_replaceHelpdesk() {
   // Always return false in EOL version - no helpdesk replacement
   return false;
}


/**
 * Generate unique id for form based on server name, glpi directory and basetime
 **/
function plugin_formcreator_getUuid() {
   //encode uname -a, ex Linux localhost 2.4.21-0.13mdk #1 Fri Mar 14 15:08:06 EST 2003 i686
   $serverSubSha1 = substr(sha1(php_uname('a')), 0, 8);
   // encode script current dir, ex : /var/www/glpi_X
   $dirSubSha1    = substr(sha1(__FILE__), 0, 8);

   return uniqid("$serverSubSha1-$dirSubSha1-", true);
}

/**
 * Retrieve an item from the database
 *
 * @param CommonDBTM $item instance of CommonDBTM object
 * @param string $field field of object's table to search in
 * @param mixed $value value to search in provided field
 *
 * @return int|false ID of the item if found, false otherwise
 */
function plugin_formcreator_getFromDBByField(CommonDBTM $item, $field = '', $value = '') {
   /** @var \DBmysql $DB */
   global $DB;

   // != 0 because 0 is consider as empty
   if (!$item instanceof Entity
       && (strlen($value) == 0
           || $value === 0)) {
      return false;
   }

   $value = $DB->escape($value);
   $found = $item->getFromDBByRequest([
      'WHERE' => [$item::getTable() . '.' . $field => $value],
      'LIMIT' => 1
   ]);

   if ($found) {
      return $item->getID();
   } else {
      return false;
   }
}

/**
 * Autoloader
 * @param string $classname
 */
function plugin_formcreator_autoload($classname) {
   if (strpos($classname, 'PluginFormcreator') === 0) {
      // useful only for installer GLPi autoloader already handles inc/ folder
      $filename = __DIR__ . '/inc/' . strtolower(str_replace('PluginFormcreator', '', $classname)). '.class.php';
      if (is_readable($filename) && is_file($filename)) {
         include_once($filename);
         return true;
      }
   }
}

/**
 * Show the last SQL error, logs its backtrace and dies
 * @param Migration $migration
 */
function plugin_formcreator_upgrade_error(Migration $migration) {
   /** @var \DBmysql $DB */
   global $DB;

   $error = $DB->error() ?: '';
   $migration->log($error . "\n" . Toolbox::backtrace($error, '', ['Toolbox::backtrace()']), false);
   die($error . "<br><br> Please, check migration log");
}

/**
 * Permanent hooks, must be set even when the plugin is disabled
 * SIMPLIFIED for EOL version
 *
 * @return void
 */
function plugin_formcreator_permanent_hook(): void {
   /** @var array $PLUGIN_HOOKS */
   global $PLUGIN_HOOKS;

   // Minimal hooks for migration only
   $PLUGIN_HOOKS[Hooks::ITEM_ADD]['formcreator'] = [];
   $PLUGIN_HOOKS[Hooks::PRE_ITEM_UPDATE]['formcreator'] = [];
   $PLUGIN_HOOKS[Hooks::ITEM_UPDATE]['formcreator'] = [];
   $PLUGIN_HOOKS[Hooks::ITEM_DELETE]['formcreator'] = [];
   $PLUGIN_HOOKS[Hooks::ITEM_RESTORE]['formcreator'] = [];
   $PLUGIN_HOOKS[Hooks::ITEM_PURGE]['formcreator'] = [];
   $PLUGIN_HOOKS[Hooks::PRE_ITEM_PURGE]['formcreator'] = [];

   // No timeline actions in EOL version
   $PLUGIN_HOOKS[Hooks::TIMELINE_ACTIONS]['formcreator'] = [];

   // No transfer hooks in EOL version
   $PLUGIN_HOOKS[Hooks::ITEM_TRANSFER]['formcreator'] = [];
}

/**
 * Hooks to run when the plugin is active
 * SIMPLIFIED for EOL version
 *
 * @return void
 */
function plugin_formcreator_hook(): void {
   /**
    * @var array $PLUGIN_HOOKS
    */
   global $PLUGIN_HOOKS;

   // No CSS or JS in EOL version
   // No dashboard cards in EOL version
   // No menu entries in EOL version - only migration interface in admin

   if (Session::getLoginUserID() === false) {
      return;
   }

   // No helpdesk menu in EOL version
   // No assistance requests menu in EOL version
   
   // Minimal hooks for EOL version
   $PLUGIN_HOOKS['use_massive_action']['formcreator'] = 0;
   
   // Basic menu entry for migration status only
   if (Session::haveRight('config', UPDATE)) {
      $PLUGIN_HOOKS['menu_entry']['formcreator'] = 'front/migration_status.php';
      
      // Add EOL information button to plugin tile
      $PLUGIN_HOOKS['menu_toadd']['formcreator']['tools'] = 'PluginFormcreatorEOLInfo';
      
      // Alternative: Add a direct link to EOL documentation
      $PLUGIN_HOOKS['plugin_info_display']['formcreator'] = 'front/eol_info.php';
      
      // Display EOL warning on central dashboard
      $PLUGIN_HOOKS['display_central']['formcreator'] = ['PluginFormcreatorEOLInfo', 'displayCentralEOLWarning'];
   }
}

function plugin_formcreator_registerClasses() {
   // EOL version - minimal class registration for migration only
   
   // Only register core classes needed for migration
   Plugin::registerClass(PluginFormcreatorInstall::class);
   
   // Register EOL information class for admin menu
   Plugin::registerClass(PluginFormcreatorEOLInfo::class);
   
   // No entity configuration or form classes in EOL version
   // No field classes in EOL version
   // No notification classes in EOL version
}

function plugin_formcreator_redirect() {
   // EOL version - no redirections or helpdesk replacement
   // This functionality has been removed in the migration-only version
   return;
}

function plugin_formcreator_options() {
   return [
      Plugin::OPTION_AUTOINSTALL_DISABLED => true,
   ];
}

/**
 * Get the path to the empty SQL schema file
 *
 * @return string
 */
function plugin_formcreator_getSchemaPath(string $version = ''): string {
   if (empty($version)) {
      $version = PLUGIN_FORMCREATOR_VERSION;
   }

   // Drop suffixes for alpha, beta, rc versions
   $matches = [];
   preg_match('/^(\d+\.\d+\.\d+)/', $version, $matches);
   $version = $matches[1];

   return Plugin::getPhpDir('formcreator') . "/install/mysql/plugin_formcreator_{$version}_empty.sql";
}

/**
 * Detect a versin change and save the previous version in the DB
 *
 * Used to proceed a DB sanity check before an upgrade
 * @see PluginFormcreatorInstall::upgrade
 * @see PluginFormcreatorInstall::checkSchema
 *
 * @return void
 */
function plugin_formcreator_savePreviousVersion(): void {
   $plugin = new Plugin();
   $plugin->getFromDBbyDir('formcreator');
   $oldVersion = $plugin->fields['version'] ?? null;
   if ($oldVersion !== null && $oldVersion != PLUGIN_FORMCREATOR_VERSION) {
      Config::setConfigurationValues('formcreator', [
         'previous_version' => $oldVersion,
      ]);
   }
}
