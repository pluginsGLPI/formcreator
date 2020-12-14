<?php

namespace GlpiPlugin\Formcreator\Tests;
use GlpiPlugin\Formcreator\Tests\CommonBrowsing;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

/**
 * @engine inline
 */
class CommonFunctionalTestCase extends CommonTestCase {
   /** @var \Symfony\Component\DomCrawler\Crawler */
   public $crawler;

   /** @var \Symfony\Component\Panther\Client */
   public $client;

   /** @var CommonBrowsing */
   protected $browsing;
   protected $screenshotPath;
   private $currentTestMethod;

   public function setUp() {
   }

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);

      // Check the plugin is active
      $this->boolean(\Plugin::isPluginActive('formcreator'))->isTrue();

      // set path for screenshots
      $classname = explode('\\', static::class);
      $classname = array_pop($classname);
      $this->screenshotPath = TEST_SCREENSHOTS_DIR . '/' . $classname . '/' .  $method;
      @mkdir($this->screenshotPath, 0777, true);

      // create client
      $this->client = \Symfony\Component\Panther\Client::createChromeClient(null, null, [], 'http://localhost:8000');
      //$this->client = \Symfony\Component\Panther\Client::createFirefoxClient(null, null, [], 'http://localhost:8000');

      $this->currentTestMethod = $method;
      $this->browsing = new CommonBrowsing($this);

      $this->browsing->login('glpi', 'glpi');
   }

   public function takeScreenshot() {
      static $counter = 0;

      $counter++;
      $number = sprintf("%'.04d", $counter);
      $name = $this->currentTestMethod;
      $this->client->takeScreenshot($this->screenshotPath . "/$name-$number.png");
   }

   public function tearDown() {
      if ($this->client === null) {
         return;
      }
      $this->client->quit();
   }
}
