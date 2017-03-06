<?php
abstract class CommonTestCase extends CommonDBTestCase
{

   protected static function resetState() {

      self::resetGLPILogs();

      $DBvars = get_class_vars('DB');
      $result = self::drop_database(
         $DBvars['dbuser'],
         $DBvars['dbhost'],
         $DBvars['dbdefault'],
         $DBvars['dbpassword']
      );

      $result = self::load_mysql_file($DBvars['dbuser'],
         $DBvars['dbhost'],
         $DBvars['dbdefault'],
         $DBvars['dbpassword'],
         './save.sql'
      );

   }

   protected static function resetGLPILogs() {
      // Reset error logs
      file_put_contents(GLPI_LOG_DIR."/sql-errors.log", '');
      file_put_contents(GLPI_LOG_DIR."/php-errors.log", '');
   }

   protected function tearDown() {
      // Check logs
      $fileSqlContent = file_get_contents(GLPI_LOG_DIR."/sql-errors.log");
      $filePhpContent = file_get_contents(GLPI_LOG_DIR."/php-errors.log");

      self::resetGLPILogs();

      $this->assertEquals('', $fileSqlContent, 'sql-errors.log not empty');
      $this->assertEquals('', $filePhpContent, 'php-errors.log not empty');
   }
}