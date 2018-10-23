<?php

global $CHROME_CLIENT;
require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/src/CommonFunctionalTestCase.php');

define('SCREENSHOTS_DIR', __DIR__ . '/logs/screenshots');

$home = getenv('HOME');
if (is_readable($home . '/.composer/vendor/autoload.php')) {
   require_once($home . '/.composer/vendor/autoload.php');
} else {
   require_once($home . '/.config/composer/vendor/autoload.php');
}
// Terminate the webdriver on fatal error or it will continue to run and prevent
// subsequent execution because the listening port is still in use
register_shutdown_function(function() {
   global $CHROME_CLIENT;

   if ($CHROME_CLIENT) {
      $CHROME_CLIENT->quit();
   }
});
