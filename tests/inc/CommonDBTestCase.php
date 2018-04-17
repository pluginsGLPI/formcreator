<?php
class CommonDBTestCase extends PHPUnit\Framework\TestCase {

   protected static function drop_database($dbuser = '', $dbhost = '', $dbdefault = '', $dbpassword = '') {

      $cmd = self::construct_mysql_options($dbuser, $dbhost, $dbpassword, 'mysql');

      if (is_array($cmd)) {
         return $cmd;
      }

      $cmd = 'echo "DROP DATABASE IF EXISTS \`'.$dbdefault .'\`; CREATE DATABASE \`'.$dbdefault.'\`" | ' . $cmd ." 2>&1";

      $returncode = 0;
      $output = [];
      exec(
            $cmd,
            $output,
            $returncode
            );
      array_unshift($output, "Output of '{$cmd}'");
      return [
            'returncode'=>$returncode,
            'output' => $output
      ];
   }

   protected static function load_mysql_file($dbuser = '', $dbhost = '', $dbdefault = '', $dbpassword = '', $file = null) {

      if (!file_exists($file)) {
         return [
               'returncode' => 1,
               'output' => ["ERROR: File '{$file}' does not exist !"]
         ];
      }

      $result = self::construct_mysql_options($dbuser, $dbhost, $dbpassword, 'mysql');

      if (is_array($result)) {
         return $result;
      }

      $cmd = $result . " " . $dbdefault . " < ". $file ." 2>&1";

      $returncode = 0;
      $output = [];
      exec(
            $cmd,
            $output,
            $returncode
            );
      array_unshift($output, "Output of '{$cmd}'");
      return [
            'returncode'=>$returncode,
            'output' => $output
      ];
   }

   protected static function construct_mysql_options($dbuser = '', $dbhost = '', $dbpassword = '', $cmd_base = 'mysql') {
      $cmd = [];

      if (empty($dbuser) || empty($dbhost)) {
         return [
               'returncode' => 2,
               'output' => ["ERROR: missing mysql parameters (user='{$dbuser}', host='{$dbhost}')"]
         ];
      }
      $cmd = [$cmd_base];

      if (strpos($dbhost, ':') !== false) {
         $dbhost = explode( ':', $dbhost);
         if (!empty($dbhost[0])) {
            $cmd[] = "--host ".$dbhost[0];
         }
         if (is_numeric($dbhost[1])) {
            $cmd[] = "--port ".$dbhost[1];
         } else {
            // The dbhost's second part is assumed to be a socket file if it is not numeric.
            $cmd[] = "--socket ".$dbhost[1];
         }
      } else {
         $cmd[] = "--host ".$dbhost;
      }

      $cmd[] = "--user ".$dbuser;

      if (!empty($dbpassword)) {
         $cmd[] = "-p'".urldecode($dbpassword)."'";
      }

      return implode(' ', $cmd);
   }

   protected static function mysql_dump($dbuser = '', $dbhost = '', $dbpassword = '', $dbdefault = '', $file = null) {
      if (is_null($file) or empty($file)) {
         return [
               'returncode' => 1,
               'output' => ["ERROR: mysql_dump()'s file argument must neither be null nor empty"]
         ];
      }

      if (empty($dbdefault)) {
         return [
               'returncode' => 2,
               'output' => ["ERROR: mysql_dump() is missing dbdefault argument."]
         ];
      }

      $result = self::construct_mysql_options($dbuser, $dbhost, $dbpassword, 'mysqldump');
      if (is_array($result)) {
         return $result;
      }

      $cmd = $result . ' --opt '. $dbdefault.' > ' . $file;
      $returncode = 0;
      $output = [];
      exec(
            $cmd,
            $output,
            $returncode
            );
      array_unshift($output, "Output of '{$cmd}'");
      return [
            'returncode'=>$returncode,
            'output' => $output
      ];
   }

   protected static function setupGLPIFramework() {
      global $CFG_GLPI, $DB, $LOADED_PLUGINS, $PLUGIN_HOOKS, $AJAX_INCLUDE, $PLUGINS_INCLUDED;

      if (session_status() == PHP_SESSION_ACTIVE) {
         session_write_close();
      }
      $LOADED_PLUGINS = null;
      $PLUGINS_INCLUDED = null;
      $AJAX_INCLUDE = null;
      $_SESSION = [];
      $_SESSION['glpi_use_mode'] = Session::NORMAL_MODE;       // Prevents notice in execution of GLPI_ROOT . /inc/includes.php
      if (is_readable(GLPI_ROOT . "/config/config.php")) {
         $configFile = "/config/config.php";
      } else {
         $configFile = "/inc/config.php";
      }
      include (GLPI_ROOT . $configFile);
      require (GLPI_ROOT . "/inc/includes.php");
      $_SESSION['glpi_use_mode'] = Session::DEBUG_MODE;
      \Toolbox::setDebugMode();

      include_once (GLPI_ROOT . "/inc/timer.class.php");

      // Security of PHP_SELF
      $_SERVER['PHP_SELF']=Html::cleanParametersURL($_SERVER['PHP_SELF']);

      ini_set("memory_limit", "-1");
      ini_set("max_execution_time", "0");

      if (session_status() == PHP_SESSION_ACTIVE) {
         session_write_close();
      }
      session_start();
      $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];
   }

   protected static function login($name, $password, $noauto = false) {
      Session::start();
      $_SESSION['glpi_use_mode'] = Session::NORMAL_MODE;
      $auth = new Auth();
      $result = $auth->Login($name, $password, $noauto);
      $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];

      return $result;
   }
}
