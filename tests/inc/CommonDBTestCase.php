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

class CommonDBTestCase extends PHPUnit\Framework\TestCase {

   protected static function drop_database($dbuser='', $dbhost='', $dbdefault='', $dbpassword='') {

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
      return array(
            'returncode'=>$returncode,
            'output' => $output
      );
   }

   protected static function load_mysql_file($dbuser='', $dbhost='', $dbdefault='', $dbpassword='', $file = NULL) {

      if (!file_exists($file)) {
         return array(
               'returncode' => 1,
               'output' => array("ERROR: File '{$file}' does not exist !")
         );
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
      return array(
            'returncode'=>$returncode,
            'output' => $output
      );
   }

   protected static function construct_mysql_options($dbuser='', $dbhost='', $dbpassword='', $cmd_base='mysql') {
      $cmd = [];

      if (empty($dbuser) || empty($dbhost)) {
         return array(
               'returncode' => 2,
               'output' => array("ERROR: missing mysql parameters (user='{$dbuser}', host='{$dbhost}')")
         );
      }
      $cmd = array($cmd_base);

      if (strpos($dbhost, ':') !== FALSE) {
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

   protected static function mysql_dump($dbuser = '', $dbhost = '', $dbpassword = '', $dbdefault = '', $file = NULL) {
      if (is_null($file) or empty($file)) {
         return array(
               'returncode' => 1,
               'output' => array("ERROR: mysql_dump()'s file argument must neither be null nor empty")
         );
      }

      if (empty($dbdefault)) {
         return array(
               'returncode' => 2,
               'output' => array("ERROR: mysql_dump() is missing dbdefault argument.")
         );
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
      return array(
            'returncode'=>$returncode,
            'output' => $output
      );
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
