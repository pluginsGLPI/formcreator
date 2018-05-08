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

include (__DIR__ . "/../vendor/docopt/docopt/src/docopt.php");

$doc = <<<DOC
cliinstall.php

Usage:
   cliinstall.php [--as-user USER] [ --tests ]

Options:
   --as-user USER       Do install/upgrade as specified USER. If not provided, 'glpi' user will be used
   --tests              Use GLPi test database

DOC;

$docopt = new \Docopt\Handler();
$args = $docopt->handle($doc);
$args = $args->args;

$asUser = 'glpi';
if (isset($args)) {
   if (!is_null($args['--as-user'])) {
      $asUser = $args['--as-user'];
   }
   if (isset($args['--tests']) &&  $args['--tests'] !== false) {
      // Use test GLPi's database
      // Requires use of cliinstall of GLPI with --tests argument
      define('GLPI_ROOT', dirname(dirname(dirname(__DIR__))));
      define("GLPI_CONFIG_DIR", GLPI_ROOT . "/tests");
   }
}

include (__DIR__ . "/../../../inc/includes.php");

// Init debug variable
$_SESSION['glpi_use_mode'] = Session::DEBUG_MODE;
$_SESSION['glpilanguage']  = "en_GB";

Session::loadLanguage();

// Only show errors
$CFG_GLPI["debug_sql"]        = $CFG_GLPI["debug_vars"] = 0;
$CFG_GLPI["use_log_in_files"] = 1;
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
//set_error_handler('userErrorHandlerDebug');

// Prevent problem of execution time
ini_set("max_execution_time", "0");
ini_set("memory_limit", "-1");
ini_set("session.use_cookies", "0");

$DB = new DB();
if (!$DB->connected) {
   die("No DB connection\n");
}

$user = new User();
$user->getFromDBbyName($asUser);
$auth = new Auth();
$auth->auth_succeded = true;
$auth->user = $user;
Session::init($auth);

if (!$DB->tableExists("glpi_configs")) {
   die("GLPI not installed\n");
}

$plugin = new Plugin();

// Install the plugin
if (!$plugin->getFromDBbyDir("formcreator")) {
   print("Failed : GLPi does not find the plugin");
   exit(1);
}
print("Installing Plugin Id: " . $plugin->fields['id'] . " version " . $plugin->fields['version'] . "...\n");
ob_start(function($in) { return ''; });
$plugin->install($plugin->fields['id']);
ob_end_clean();
print("Done\n");

// Enable the plugin
print("Activating Plugin...\n");
if (!$plugin->activate($plugin->fields['id'])) {
   print("Activation failed\n");
   exit(1);
}
print("Activation Done\n");

//Load the plugin
print("Loading Plugin...\n");
$plugin->load("formcreator");
print("Load Done...\n");
