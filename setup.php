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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

global $CFG_GLPI;
// Version of the plugin
define('PLUGIN_FORMCREATOR_VERSION', '2.6.4');
// Schema version of this version
define('PLUGIN_FORMCREATOR_SCHEMA_VERSION', '2.6');
// is or is not an official release of the plugin
define('PLUGIN_FORMCREATOR_IS_OFFICIAL_RELEASE', true);


// Minimal GLPI version, inclusive
define ('PLUGIN_FORMCREATOR_GLPI_MIN_VERSION', '9.2.1');
// Maximum GLPI version, exclusive
define ('PLUGIN_FORMCREATOR_GLPI_MAX_VERSION', '9.4');

define('FORMCREATOR_ROOTDOC', $CFG_GLPI['root_doc'] . '/plugins/formcreator');

/**
 * Define the plugin's version and informations
 *
 * @return Array [name, version, author, homepage, license, minGlpiVersion]
 */
function plugin_version_formcreator() {
   $glpiVersion = rtrim(GLPI_VERSION, '-dev');
   if (!method_exists('Plugins', 'checkGlpiVersion') && version_compare($glpiVersion, PLUGIN_FORMCREATOR_GLPI_MIN_VERSION, 'lt')) {
      echo 'This plugin requires GLPI >= ' . PLUGIN_FORMCREATOR_GLPI_MIN_VERSION;
      return false;
   }
   $requirements = [
      'name'           => _n('Form', 'Forms', 2, 'formcreator'),
      'version'        => PLUGIN_FORMCREATOR_VERSION,
      'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
      'homepage'       => 'https://github.com/pluginsGLPI/formcreator',
      'license'        => '<a href="../plugins/formcreator/LICENSE" target="_blank">GPLv2</a>',
      'requirements'   => [
         'glpi'           => [
            'min'            => PLUGIN_FORMCREATOR_GLPI_MIN_VERSION,
         ]
      ]
   ];

   if (PLUGIN_FORMCREATOR_IS_OFFICIAL_RELEASE) {
      // This is not a development version
      $requirements['requirements']['glpi']['max'] = PLUGIN_FORMCREATOR_GLPI_MAX_VERSION;
   }
   return $requirements;
}

/**
 * Check plugin's prerequisites before installation
 *
 * @return boolean
 */
function plugin_formcreator_check_prerequisites() {
   return true;
}

/**
 * Check plugin's config before activation (if needed)
 *
 * @param string $verbose Set true to show all messages (false by default)
 * @return boolean
 */
function plugin_formcreator_check_config($verbose = false) {
   return true;
}

/**
 * Initialize all classes and generic variables of the plugin
 */
function plugin_init_formcreator() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   // Add specific CSS
   $PLUGIN_HOOKS['add_css']['formcreator'][] = "css/styles.css";

   // Hack for vertical display
   if (isset($CFG_GLPI['layout_excluded_pages'])) {
      array_push($CFG_GLPI['layout_excluded_pages'], "targetticket.form.php");
   }

   // Set the plugin CSRF compliance (required since GLPI 0.84)
   $PLUGIN_HOOKS['csrf_compliant']['formcreator'] = true;

   // Can assign FormAnswer to tickets
   $PLUGIN_HOOKS['assign_to_ticket']['formcreator'] = true;
   array_push($CFG_GLPI["ticket_types"], 'PluginFormcreatorForm_Answer');
   array_push($CFG_GLPI["document_types"], 'PluginFormcreatorForm_Answer');

   // hook to update issues when an operation occurs on a ticket
   $PLUGIN_HOOKS['item_add']['formcreator'] = [
      'Ticket' => 'plugin_formcreator_hook_add_ticket'
   ];
   $PLUGIN_HOOKS['item_update']['formcreator'] = [
      'Ticket' => 'plugin_formcreator_hook_update_ticket'
   ];
   $PLUGIN_HOOKS['item_delete']['formcreator'] = [
      'Ticket' => 'plugin_formcreator_hook_delete_ticket'
   ];
   $PLUGIN_HOOKS['item_restore']['formcreator'] = [
      'Ticket' => 'plugin_formcreator_hook_restore_ticket'
   ];
   $PLUGIN_HOOKS['item_purge']['formcreator'] = [
      'Ticket' => 'plugin_formcreator_hook_purge_ticket'
   ];

   $plugin = new Plugin();
   if ($plugin->isInstalled('formcreator') && $plugin->isActivated('formcreator')) {
      spl_autoload_register('plugin_formcreator_autoload');

      if (isset($_SESSION['glpiactiveentities_string'])) {
         // Redirect to helpdesk replacement
         if (strpos($_SERVER['REQUEST_URI'], "front/helpdesk.public.php") !== false) {
            if (!isset($_POST['newprofile']) && !isset($_GET['active_entity'])) {
               // Not changing profile or active entity
               if (isset($_SESSION['glpiactiveprofile']['interface'])
                     && isset($_SESSION['glpiactive_entity'])) {
                  // Interface and active entity are set in session
                  if (plugin_formcreator_replaceHelpdesk()) {
                     Html::redirect($CFG_GLPI["root_doc"]."/plugins/formcreator/front/wizard.php");
                  }
               }
            }
         }

         // Massive Action definition
         $PLUGIN_HOOKS['use_massive_action']['formcreator'] = 1;

         // Load menu entries if user is logged in and if he has access to at least one form
         if (isset($_SESSION['glpiID'])) {
            // If user have acces to one form or more, add link
            if (PluginFormcreatorForm::countAvailableForm() > 0) {
               $PLUGIN_HOOKS['menu_toadd']['formcreator']['helpdesk'] = 'PluginFormcreatorFormlist';
            }

            // Add a link in the main menu plugins for technician and admin panel
            $PLUGIN_HOOKS['menu_entry']['formcreator'] = 'front/formlist.php';

            // Config page
            $links  = [];
            if (Session::haveRight('entity', UPDATE)) {
               $PLUGIN_HOOKS['config_page']['formcreator']         = 'front/form.php';
               $PLUGIN_HOOKS['menu_toadd']['formcreator']['admin'] = 'PluginFormcreatorForm';
               $links['config'] = '/plugins/formcreator/front/form.php';
               $links['add']    = '/plugins/formcreator/front/form.form.php';
            }
            $img = '<img  src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/check.png"
                        title="' . __('Forms waiting for validation', 'formcreator') . '" alt="Waiting forms list" />';

            $links[$img] = '/plugins/formcreator/front/formanswer.php';

            // Set options for pages (title, links, buttons...)
            $links['search'] = '/plugins/formcreator/front/formlist.php';
            $PLUGIN_HOOKS['submenu_entry']['formcreator']['options'] = [
               'config'       => ['title'  => __('Setup'),
                                  'page'   => '/plugins/formcreator/front/form.php',
                                  'links'  => $links],
               'options'      => ['title'  => _n('Form', 'Forms', 2, 'formcreator'),
                                  'links'  => $links],
            ];
         }

         // Load JS and CSS files if we are on a page which need them
         if (strpos($_SERVER['REQUEST_URI'], "plugins/formcreator") !== false
             || strpos($_SERVER['REQUEST_URI'], "central.php") !== false
             || isset($_SESSION['glpiactiveprofile']) &&
                $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk') {

            $PLUGIN_HOOKS['add_css']['formcreator'][]        = 'lib/pqselect/pqselect.min.css';
            $PLUGIN_HOOKS['add_javascript']['formcreator'][] = 'lib/pqselect/pqselect.min.js';

            // Add specific JavaScript
            $PLUGIN_HOOKS['add_javascript']['formcreator'][] = 'js/scripts.js.php';
         }

         if (strpos($_SERVER['REQUEST_URI'], "plugins/formcreator/front/targetticket.form.php") !== false) {
            if ($CFG_GLPI['use_rich_text']) {
               Html::requireJs('tinymce');
            }
         }

         if (strpos($_SERVER['REQUEST_URI'], "helpdesk") !== false
               || strpos($_SERVER['REQUEST_URI'], "central.php") !== false
               || strpos($_SERVER['REQUEST_URI'], "formcreator/front/formlist.php") !== false
               || strpos($_SERVER['REQUEST_URI'], "formcreator/front/wizard.php") !== false) {
            $PLUGIN_HOOKS['add_javascript']['formcreator'][] = 'lib/slinky/assets/js/jquery.slinky.js';

            $PLUGIN_HOOKS['add_javascript']['formcreator'][] = 'lib/masonry.pkgd.min.js';
         }

         Plugin::registerClass('PluginFormcreatorForm', ['addtabon' => 'Central']);

         // Load field class and all its method to manage fields
         Plugin::registerClass('PluginFormcreatorFields');

         // Notification
         Plugin::registerClass('PluginFormcreatorForm_Answer', [
            'notificationtemplates_types' => true
         ]);

         Plugin::registerClass('PluginFormcreatorEntityconfig', ['addtabon' => 'Entity']);
      }
   }
}

/**
 * Encode special chars
 *
 * @param  String    $string  The string to encode
 * @return String             The encoded string
 */
function plugin_formcreator_encode($string, $mode_legacy = true) {
   if (!is_string($string)) {
      return $string;
   }
   if (!$mode_legacy) {
      $string = Html::clean(Html::entity_decode_deep($string));
      $string = preg_replace('/\\r\\n/', ' ', $string);
      $string = preg_replace('/\\n/', ' ', $string);
      $string = preg_replace('/\\\\r\\\\n/', ' ', $string);
      $string = preg_replace('/\\\\n/', ' ', $string);
      $string = Toolbox::stripslashes_deep($string);
      $string = Toolbox::addslashes_deep($string);
   } else {
      $string = stripcslashes($string);
      $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
      $string = str_replace('&apos;', "'", $string);
      $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
   }
   return $string;
}

/**
 * Encode special chars
 *
 * @param  String    $string  The string to encode
 * @return String             The encoded string
 */
function plugin_formcreator_decode($string) {
   $string = stripcslashes($string);
   $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
   $string = str_replace('&apos;', "'", $string);
   return $string;
}

/**
 * Tells if helpdesk replacement is enabled for the current user
 */
function plugin_formcreator_replaceHelpdesk() {
   if (isset($_SESSION['glpiactiveprofile']['interface'])
         && isset($_SESSION['glpiactive_entity'])) {
      // Interface and active entity are set in session
      $helpdeskMode = PluginFormcreatorEntityconfig::getUsedConfig('replace_helpdesk', $_SESSION['glpiactive_entity']);
      if ($helpdeskMode != '0'
            && $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk') {
         return $helpdeskMode;
      }
   }
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
 * @param $item instance of CommonDBTM object
 * @param $field field of object's table to search in
 * @param $value value to search in provided field
 *
 * @return true if succeed else false
 */
function plugin_formcreator_getFromDBByField(CommonDBTM $item, $field = '', $value = '') {
   global $DB;

   // != 0 because 0 is consider as empty
   if (!$item instanceof Entity
       && (strlen($value) == 0
           || $value === 0)) {
      return false;
   }

   $field = $DB->escape($field);
   $value = $DB->escape($value);
   if (!method_exists(PluginFormcreatorForm::class, 'getFromDBByRequest')) {
      $found = $item->getFromDBByQuery("WHERE `".$item::getTable()."`.`$field` = '"
                                    .$value."' LIMIT 1");
   } else {
      $found = $item->getFromDBByRequest([
         'WHERE' => [$item::getTable() . '.' . $field => $value],
         'LIMIT' => 1
      ]);
   }

   if ($found) {
      return $item->getID();
   } else {
      return false;
   }
}

/**
 * Autoloader
 * @param unknown $classname
 */
function plugin_formcreator_autoload($classname) {
   if (strpos($classname, 'PluginFormcreator') === 0) {
      // Search first for field clases
      $filename = __DIR__ . '/inc/fields/' . strtolower(str_replace('PluginFormcreator', '', $classname)) . '.class.php';
      if (is_readable($filename) && is_file($filename)) {
         include_once($filename);
         return true;
      }

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
   global $DB;

   $error = $DB->error();
   $migration->log($error . "\n" . Toolbox::backtrace(false, '', ['Toolbox::backtrace()']), false);
   die($error . "<br><br> Please, check migration log");
}

function plugin_formcreator_ldap_warning_handler($errno, $errstr, $errfile, $errline, array $errcontext) {
   if (0 === error_reporting()) {
      return false;
   }
   throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
