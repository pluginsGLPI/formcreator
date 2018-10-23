<?php

namespace tests\units;
use atoum;

class CommonFunctionalTtestCase extends atoum {
   protected $crawler;
   protected $client;

   protected $screenshotPath;

   public function setUp() {
      global $CHROME_CLIENT;

   }

   public function beforeTestMethod($method) {
      global $CHROME_CLIENT;

      $CHROME_CLIENT = \Symfony\Component\Panther\Client::createChromeClient();
      $classname = explode('\\', static::class);
      $classname = array_pop($classname);
      $this->screenshotPath = SCREENSHOTS_DIR . '/' . $classname . '/' .  $method;
      @mkdir($this->screenshotPath, 0777, true);
      $this->client = \Symfony\Component\Panther\Client::createChromeClient();
      $this->crawler = $this->client->request('GET', 'http://localhost:8000');

      $this->takeScreenshot();
      $form = $this->crawler->filter('#boxlogin > form')->form();

      $login = $this->crawler->filter('input#login_name')->attr('name');
      $passwd = $this->crawler->filter('input#login_password')->attr('name');
      $form[$login] = 'glpi';
      $form[$passwd] = 'glpi';
      $this->crawler = $this->client->submit($form);

      $this->client->waitFor('#footer');
   }

   protected function takeScreenshot() {
      static $counter = 0;

      $counter++;
      $this->client->takeScreenshot($this->screenshotPath . '/' . sprintf("%'.04d", $counter) . '.png');
   }
}
