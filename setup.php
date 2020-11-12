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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

global $CFG_GLPI;
// Version of the plugin
define('PLUGIN_FORMCREATOR_VERSION', '2.10.4');
// Schema version of this version
define('PLUGIN_FORMCREATOR_SCHEMA_VERSION', '2.10');
// is or is not an official release of the plugin
define('PLUGIN_FORMCREATOR_IS_OFFICIAL_RELEASE', true);

// Minimal GLPI version, inclusive
define ('PLUGIN_FORMCREATOR_GLPI_MIN_VERSION', '9.4');
// Maximum GLPI version, exclusive (ignored if PLUGIN_FORMCREATOR_IS_OFFICIAL_RELEASE == false)
define ('PLUGIN_FORMCREATOR_GLPI_MAX_VERSION', '9.6');

define ('PLUGIN_FORMCREATOR_TEXTAREA_FIX', true);

if (method_exists(Plugin::class, 'getWebDir')) {
   define('FORMCREATOR_ROOTDOC', Plugin::getWebDir('formcreator'));
} else {
   define('FORMCREATOR_ROOTDOC', $CFG_GLPI['root_doc'] . '/plugins/formcreator');
}

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
      'name'           => 'Form Creator',
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
   $prerequisitesSuccess = true;

   if (version_compare(GLPI_VERSION, PLUGIN_FORMCREATOR_GLPI_MIN_VERSION, 'lt')
       || PLUGIN_FORMCREATOR_IS_OFFICIAL_RELEASE && version_compare(GLPI_VERSION, PLUGIN_FORMCREATOR_GLPI_MAX_VERSION, 'ge')) {
      echo "This plugin requires GLPI >= " . PLUGIN_FORMCREATOR_GLPI_MIN_VERSION . " and GLPI < " . PLUGIN_FORMCREATOR_GLPI_MAX_VERSION . "<br>";
      $prerequisitesSuccess = false;
   }

   $fi = new FilesystemIterator(__DIR__ . '/data/', FilesystemIterator::SKIP_DOTS);
   if (iterator_count($fi) < 2) {
      // There is index.html at least in  the directory, then 2 files must estxist
      // to validate the existence of data files
      echo "You must run vendor/bin/robo build:fa-data in the directory of the plugin";
      $prerequisitesSuccess = false;
   }

   return $prerequisitesSuccess;
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
   array_push($CFG_GLPI["ticket_types"], PluginFormcreatorFormAnswer::class);
   array_push($CFG_GLPI["document_types"], PluginFormcreatorFormAnswer::class);

   // hook to update issues when an operation occurs on a ticket
   $PLUGIN_HOOKS['item_add']['formcreator'] = [
      Ticket::class => 'plugin_formcreator_hook_add_ticket'
   ];
   $PLUGIN_HOOKS['item_update']['formcreator'] = [
      Ticket::class => 'plugin_formcreator_hook_update_ticket',
      TicketValidation::class => 'plugin_formcreator_hook_update_ticketvalidation',
   ];
   $PLUGIN_HOOKS['item_delete']['formcreator'] = [
      Ticket::class => 'plugin_formcreator_hook_delete_ticket'
   ];
   $PLUGIN_HOOKS['item_restore']['formcreator'] = [
      Ticket::class => 'plugin_formcreator_hook_restore_ticket'
   ];
   $PLUGIN_HOOKS['item_purge']['formcreator'] = [
      Ticket::class => 'plugin_formcreator_hook_purge_ticket',
      TicketValidation::class => 'plugin_formcreator_hook_purge_ticketvalidation',
   ];
   $PLUGIN_HOOKS['pre_item_purge']['formcreator'] = [
      PluginFormcreatorTargetTicket::class => 'plugin_formcreator_hook_pre_purge_targetTicket',
      PluginFormcreatorTargetChange::class => 'plugin_formcreator_hook_pre_purge_targetChange'
   ];
   // hook to add custom actions on a ticket in service catalog
   $PLUGIN_HOOKS['timeline_actions']['formcreator'] = 'plugin_formcreator_timelineActions';

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
                     Html::redirect(FORMCREATOR_ROOTDOC."/front/wizard.php");
                  }
               }
            }
         }
         if (plugin_formcreator_replaceHelpdesk()) {
            if (strpos($_SERVER['REQUEST_URI'], "front/ticket.form.php") !== false) {
               if (!isset($_POST['update'])) {
                  $decodedUrl = [];
                  $openItilFollowup = '';
                  if (isset($_GET['_openfollowup'])) {
                     $openItilFollowup = '&_openfollowup=1';
                  }
                  parse_str($_SERVER['QUERY_STRING'], $decodedUrl);
                  if (isset($decodedUrl['forcetab'])) {
                     Session::setActiveTab(Ticket::class, $decodedUrl['forcetab']);
                  }
                  Html::redirect(FORMCREATOR_ROOTDOC . '/front/issue.form.php?id=' . $_GET['id'] . '&sub_itemtype=Ticket' . $openItilFollowup);
               }
            }

            $pages = [
               'front/reservationitem.php' => FORMCREATOR_ROOTDOC . '/front/reservationitem.php',
               'front/helpdesk.faq.php' => FORMCREATOR_ROOTDOC . '/front/wizard.php',
               'front/ticket.php' => FORMCREATOR_ROOTDOC . '/front/issue.php',
            ];
            foreach ($pages as $srcPage => $dstPage) {
               if (strpos($_SERVER['REQUEST_URI'], $srcPage) !== false && strpos($_SERVER['REQUEST_URI'], $dstPage) === false) {
                  if ($srcPage == 'front/reservationitem.php') {
                     $_SESSION['plugin_formcreator']['redirected']['POST'] = $_POST;
                  }
                  Html::redirect($dstPage);
                  break;
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
               $links['config'] = FORMCREATOR_ROOTDOC . '/front/form.php';
               $links['add']    = FORMCREATOR_ROOTDOC .  '/front/form.form.php';
            }
            $img = '<img  src="' . FORMCREATOR_ROOTDOC . '/pics/check.png"
                        title="' . __('Forms waiting for validation', 'formcreator') . '" alt="Waiting forms list" />';

            $links[$img] = FORMCREATOR_ROOTDOC . '/front/formanswer.php';

            // Set options for pages (title, links, buttons...)
            $links['search'] = FORMCREATOR_ROOTDOC . '/front/formlist.php';
            $PLUGIN_HOOKS['submenu_entry']['formcreator']['options'] = [
               'config'       => ['title'  => __('Setup'),
                                  'page'   => FORMCREATOR_ROOTDOC . '/front/form.php',
                                  'links'  => $links],
               'options'      => ['title'  => _n('Form', 'Forms', 2, 'formcreator'),
                                  'links'  => $links],
            ];
         }

         $pages = [
            FORMCREATOR_ROOTDOC . '/front/targetticket.form.php',
            FORMCREATOR_ROOTDOC . '/front/formdisplay.php',
            FORMCREATOR_ROOTDOC . '/front/form.form.php',
            FORMCREATOR_ROOTDOC . '/front/formanswer.form.php',
            FORMCREATOR_ROOTDOC . '/front/issue.form.php',
         ];
         foreach ($pages as $page) {
            if (strpos($_SERVER['REQUEST_URI'], $page) !== false) {
               Html::requireJs('photoswipe');
               echo Html::css('public/lib/photoswipe.css');
               Html::requireJs('tinymce');
               break;
            }
         }

         if (strpos($_SERVER['REQUEST_URI'], 'helpdesk') !== false
               || strpos($_SERVER['REQUEST_URI'], 'central.php') !== false
               || strpos($_SERVER['REQUEST_URI'], 'formcreator/front/formlist.php') !== false
               || strpos($_SERVER['REQUEST_URI'], 'formcreator/front/wizard.php') !== false) {
            $PLUGIN_HOOKS['add_javascript']['formcreator'][] = 'lib/slinky/assets/js/jquery.slinky.js';
            $PLUGIN_HOOKS['add_javascript']['formcreator'][] = 'lib/masonry.pkgd.min.js';
         }

         Plugin::registerClass(PluginFormcreatorForm::class, ['addtabon' => Central::class]);

         // Load field class and all its method to manage fields
         Plugin::registerClass(PluginFormcreatorFields::class);

         // Notification
         Plugin::registerClass(PluginFormcreatorFormAnswer::class, [
            'notificationtemplates_types' => true
         ]);

         Plugin::registerClass(PluginFormcreatorEntityconfig::class, ['addtabon' => Entity::class]);
      }

      // Load JS and CSS files if we are on a page which need them
      if (strpos($_SERVER['REQUEST_URI'], '/formcreator') !== false
         || strpos($_SERVER['REQUEST_URI'], 'central.php') !== false
         || isset($_SESSION['glpiactiveprofile']) &&
            $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk') {

         // Add specific JavaScript
         $PLUGIN_HOOKS['add_javascript']['formcreator'][] = 'js/scripts.js.php';
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
 *
 * @return boolean|integer
 */
function plugin_formcreator_replaceHelpdesk() {
   if (!isset($_SESSION['glpiactive_entity'])) {
      return false;
   }

   if (Session::getCurrentInterface() != 'helpdesk') {
      return false;
   }

   $helpdeskMode = PluginFormcreatorEntityconfig::getUsedConfig('replace_helpdesk', $_SESSION['glpiactive_entity']);
   if ($helpdeskMode != 0) {
      return $helpdeskMode;
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
