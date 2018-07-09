<?php

namespace GlpiPlugin\Formcreator\Tests;
use Session;
use Html;
use DB;
use Auth;

abstract class CommonTestCase extends CommonDBTestCase
{
   protected $str = null;

   public function beforeTestMethod($method) {
      self::resetGLPILogs();
   }

   protected function resetState() {
      self::resetGLPILogs();

      $DBvars = get_class_vars('DB');
      $result = $this->drop_database(
         $DBvars['dbuser'],
         $DBvars['dbhost'],
         $DBvars['dbdefault'],
         $DBvars['dbpassword']
      );

      $result = $this->load_mysql_file($DBvars['dbuser'],
         $DBvars['dbhost'],
         $DBvars['dbdefault'],
         $DBvars['dbpassword'],
         './save.sql'
      );
   }

   protected function resetGLPILogs() {
      // Reset error logs
      file_put_contents(GLPI_LOG_DIR."/sql-errors.log", '');
      file_put_contents(GLPI_LOG_DIR."/php-errors.log", '');
   }

   protected function setupGLPIFramework() {
      global $CFG_GLPI, $DB, $LOADED_PLUGINS, $PLUGIN_HOOKS, $AJAX_INCLUDE, $PLUGINS_INCLUDED;

      if (session_status() == PHP_SESSION_ACTIVE) {
         session_write_close();
      }
      $LOADED_PLUGINS = null;
      $PLUGINS_INCLUDED = null;
      $AJAX_INCLUDE = null;
      $_SESSION = array();
      if (is_readable(GLPI_ROOT . "/config/config.php")) {
         $configFile = "/config/config.php";
      } else {
         $configFile = "/inc/config.php";
      }
      include (GLPI_ROOT . $configFile);
      require (GLPI_ROOT . "/inc/includes.php");
      //\Toolbox::setDebugMode(Session::DEBUG_MODE);

      $DB = new DB();

      include_once (GLPI_ROOT . "/inc/timer.class.php");

      // Security of PHP_SELF
      $_SERVER['PHP_SELF'] = Html::cleanParametersURL($_SERVER['PHP_SELF']);

      if (session_status() == PHP_SESSION_ACTIVE) {
         session_write_close();
      }
      ini_set('session.use_cookies', 0); //disable session cookies
      session_start();
      $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];
   }

   protected function login($name, $password, $noauto = false) {
      Session::start();
      $auth = new Auth();
      $result = $auth->login($name, $password, $noauto);
      $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];
      $this->setupGLPIFramework();

      return $result;
   }

   public function afterTestMethod($method) {
      // Check logs
      $fileSqlContent = file_get_contents(GLPI_LOG_DIR."/sql-errors.log");
      $filePhpContent = file_get_contents(GLPI_LOG_DIR."/php-errors.log");

      $class = static::class;
      $class = str_replace('\\', '_', $class);
      if ($fileSqlContent != '') {
         rename(GLPI_LOG_DIR."/sql-errors.log", GLPI_LOG_DIR."/sql-errors__${class}__$method.log");
      }
      if ($fileSqlContent != '') {
         rename(GLPI_LOG_DIR."/php-errors.log", GLPI_LOG_DIR."/php-errors__${class}__$method.log");
      }

      // Reset log files
      self::resetGLPILogs();

      // Test content
      $this->variable($fileSqlContent)->isEqualTo('', 'sql-errors.log not empty');
      $this->variable($filePhpContent)->isEqualTo('', 'php-errors.log not empty');
   }

   protected function loginWithUserToken($userToken) {
      // Login as guest user
      $_REQUEST['user_token'] = $userToken;
      Session::destroy();
      self::login('', '', false);
      unset($_REQUEST['user_token']);
   }

   /**
    * Get a unique random string
    */
   protected function getUniqueString() {
      if (is_null($this->str)) {
         return $this->str = uniqid('str');
      }
      return $this->str .= 'x';
   }

   protected function getUniqueEmail() {
      return $this->getUniqueString() . "@example.com";
   }

   public function getMockForItemtype($classname, $methods = []) {
      // create mock
      $mock = $this->getMockBuilder($classname)
                   ->setMethods($methods)
                   ->getMock();

      //Override computation of table to match the original class name
      // see CommonDBTM::getTable()
      $_SESSION['glpi_table_of'][get_class($mock)] = getTableForItemType($classname);

      return $mock;
   }

   protected function terminateSession() {
      if (session_status() == PHP_SESSION_ACTIVE) {
         session_write_close();
      }
   }

   protected function restartSession() {
      if (session_status() != PHP_SESSION_ACTIVE) {
         session_start();
         session_regenerate_id();
         session_id();
         //$_SESSION["MESSAGE_AFTER_REDIRECT"] = [];
      }
   }

   protected function getSection() {
      $form = new \PluginFormcreatorForm();
      $form->add([
         'name' => 'form'
      ]);
      $section = new \PluginFormcreatorSection();
      $section->add([
         $form::getForeignKeyField() => $form->getID(),
         'name' => 'section',
      ]);
      return $section;
   }

}
