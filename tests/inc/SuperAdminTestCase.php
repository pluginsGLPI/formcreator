<?php
class SuperAdminTestCase extends CommonTestCase
{

   public static function setupBeforeClass() {
      parent::setupBeforeClass();
      self::resetState();
   }

   public function setUp() {
      self::setupGLPIFramework();
      $this->assertTrue(self::login('glpi', 'glpi', true));
   }

}