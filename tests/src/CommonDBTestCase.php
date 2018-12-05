<?php
namespace GlpiPlugin\Formcreator\Tests;
use atoum;

class CommonDBTestCase extends atoum {

   protected function drop_database($dbuser = '', $dbhost = '', $dbdefault = '', $dbpassword = '') {

      $cmd = $this->construct_mysql_options($dbuser, $dbhost, $dbpassword, 'mysql');

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
         'returncode'   => $returncode,
         'output'       => $output
      ];
   }

   protected function load_mysql_file($dbuser = '', $dbhost = '', $dbdefault = '', $dbpassword = '', $file = null) {

      if (!file_exists($file)) {
         return [
            'returncode' => 1,
            'output' => ["ERROR: File '$file' does not exist !"]
         ];
      }

      $result = $this->construct_mysql_options($dbuser, $dbhost, $dbpassword, 'mysql');

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
      array_unshift($output, "Output of '$cmd'");
      return [
         'returncode'   => $returncode,
         'output'       => $output
      ];
   }

   protected function construct_mysql_options($dbuser = '', $dbhost = '', $dbpassword = '', $cmd_base = 'mysql') {
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

   protected function mysql_dump($dbuser = '', $dbhost = '', $dbpassword = '', $dbdefault = '', $file = null) {
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

   /**
    * compare a .sql schema against the database
    * @param string $pluginname
    * @param string Tables of the t ested DB having this string in their name are checked
    * @param string $when
    */
   public function checkInstall($filename = '', $filter = 'glpi_', $when = '') {
      global $DB;

      if ($filename == '') {
         return;
      }

      // See http://joefreeman.co.uk/blog/2009/07/php-script-to-compare-mysql-database-schemas/
      $file_content = file_get_contents($filename);
      $a_lines = explode("\n", $file_content);

      $a_tables_ref = [];
      $current_table = '';
      foreach ($a_lines as $line) {
         if (strstr($line, "CREATE TABLE ") || strstr($line, "CREATE VIEW ")) {
            $matches = [];
            preg_match("/`(.*)`/", $line, $matches);
            $current_table = $matches[1];
         } else {
            if (preg_match("/^`/", trim($line))) {
               $line = preg_replace('/\s+/', ' ', $line);
               $s_line = explode("`", $line);
               $s_type = explode("COMMENT", $s_line[2]);
               $s_type[0] = trim($s_type[0]);
               $s_type[0] = str_replace(" COLLATE utf8_unicode_ci", "", $s_type[0]);
               $s_type[0] = str_replace(" CHARACTER SET utf8", "", $s_type[0]);
               if (strpos(trim($s_type[0]), 'text') === 0
                   || strpos(trim($s_type[0]), 'longtext') === 0) {
                  $s_type[0] = str_replace(" DEFAULT NULL", "", $s_type[0]);
               }
               $s_type[0] = str_replace(", ", "", $s_type[0]);
               $a_tables_ref[$current_table][$s_line[1]] = str_replace(",", "", $s_type[0]);
            }
         }
      }

      // * Get tables from MySQL
      $a_tables_db = [];
      $a_tables = [];
      // SHOW TABLES;
      $query = "SHOW TABLES LIKE '$filter%'";
      $result = $DB->query($query);
      while ($data = $DB->fetch_array($result)) {
         $data[0] = str_replace(" COLLATE utf8_unicode_ci", "", $data[0]);
         $data[0] = str_replace("( ", "(", $data[0]);
         $data[0] = str_replace(" )", ")", $data[0]);
         $a_tables[] = $data[0];
      }

      foreach ($a_tables as $table) {
         $query = "SHOW CREATE TABLE ".$table;
         $result = $DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $a_lines = explode("\n", $data['Create Table']);

            foreach ($a_lines as $line) {
               if (strstr($line, "CREATE TABLE ")
                     OR strstr($line, "CREATE VIEW")) {
                        $matches = [];
                        preg_match("/`(.*)`/", $line, $matches);
                        $current_table = $matches[1];
               } else {
                  if (preg_match("/^`/", trim($line))) {
                     $line = preg_replace('/\s+/', ' ', $line);
                     $s_line = explode("`", $line);
                     $s_type = explode("COMMENT", $s_line[2]);
                     $s_type[0] = trim($s_type[0]);
                     $s_type[0] = str_replace(" COLLATE utf8_unicode_ci", "", $s_type[0]);
                     $s_type[0] = str_replace(" CHARACTER SET utf8", "", $s_type[0]);
                     $s_type[0] = str_replace(", ", "", $s_type[0]);
                     $a_tables_db[$current_table][$s_line[1]] = str_replace(",", "", $s_type[0]);
                  }
               }
            }
         }
      }

      $a_tables_ref_tableonly = [];
      foreach ($a_tables_ref as $table=>$data) {
         $a_tables_ref_tableonly[] = $table;
      }
      $a_tables_db_tableonly = [];
      foreach ($a_tables_db as $table=>$data) {
         $a_tables_db_tableonly[] = $table;
      }

      // Compare
      $tables_toremove = array_diff($a_tables_db_tableonly, $a_tables_ref_tableonly);
      $tables_toadd = array_diff($a_tables_ref_tableonly, $a_tables_db_tableonly);

      // See tables missing or to delete
      $this->integer(count($tables_toadd))->isEqualTo(0, "Tables missing $when " . print_r($tables_toadd, true));
      $this->integer(count($tables_toremove))->isEqualTo(0, "Tables to delete $when " . print_r($tables_toremove, true));

      // See if fields are same
      foreach ($a_tables_db as $table=>$data) {
         if (isset($a_tables_ref[$table])) {
            $fields_toremove = array_diff_assoc($data, $a_tables_ref[$table]);
            $fields_toadd = array_diff_assoc($a_tables_ref[$table], $data);
            $diff = "======= DB ============== Ref =======> ".$table."\n";
            $diff .= print_r($data, true);
            $diff .= print_r($a_tables_ref[$table], true);

            // See tables missing or to delete
            $this->integer(count($fields_toadd))->isEqualTo(0, "Fields missing/not good during $when $table " . print_r($fields_toadd, true)." into ".$diff);
            $this->integer(count($fields_toremove))->isEqualTo(0, "Fields to delete during  $when $table " . print_r($fields_toremove, true)." into ".$diff);
         }
      }
   }

}
