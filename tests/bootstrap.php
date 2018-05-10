<?php
// fix empty CFG_GLPI on boostrap; see https://github.com/sebastianbergmann/phpunit/issues/325
global $CFG_GLPI;

class UnitTestAutoload
{

   public static function register() {
      spl_autoload_register(['UnitTestAutoload', 'autoload']);
   }

   public static function autoload($className) {
      $file = __DIR__ . "/inc/$className.php";
      if (is_readable($file) && is_file($file)) {
         include_once(__DIR__ . "/inc/$className.php");
         return true;
      }
      return false;
   }

}

ini_set('session.use_cookies', 0); //disable session cookies

UnitTestAutoload::register();

define('GLPI_ROOT', dirname(dirname(dirname(__DIR__))));
define("GLPI_CONFIG_DIR", GLPI_ROOT . "/tests");
if (!file_exists(GLPI_CONFIG_DIR . '/config_db.php')) {
   echo "config_db.php missing. Did GLPI successfully initialized ?\n";
   exit(1);
}
include (GLPI_ROOT . "/inc/includes.php");
