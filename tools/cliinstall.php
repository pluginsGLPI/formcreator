<?php
include (__DIR__ . "/docopt.php");

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
   if (isset($args['--tests'])) {
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
ini_set("session.use_cookies","0");

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

if (!TableExists("glpi_configs")) {
   die("GLPI not installed\n");
}

$plugin = new Plugin();

// Install the plugin
print("Installing Plugin... Id: " . $plugin->fields['id'] . " " . PLUGIN_FORMCREATOR_VERSION . "... ");
if (!$plugin->getFromDBbyDir("formcreator")) {
   print("Failed : GLPi does not find the plugin");
   exit(1);
}
$plugin->install($plugin->fields['id']);
print("Done\n");

// Enable the plugin
print("Activating Plugin...\n");
$plugin->activate($plugin->fields['id']);
print("Activation Done\n");

//Load the plugin
print("Loading Plugin...\n");
$plugin->load("storkmdm");
print("Load Done...\n");
