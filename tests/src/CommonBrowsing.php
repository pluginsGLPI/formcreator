<?php

namespace GlpiPlugin\Formcreator\Tests;

use GlpiPlugin\Formcreator\Tests\CommonFunctionalTestCase;
use \DBUtils;
use \Entity;

/**
 * Implements navigation tasks in GLPI like login or changing entity
 */
class CommonBrowsing {
   protected $test;

   /**@var array $selectors USefun selectors to navigate through GLPI */
   private $selectors = [
      // Selectors available in the header of GLPI (most pages)
      '_header' => [
         'user icon'                     => 'body > div.page > header > div > div.ms-md-4.d-none.d-lg-block',
         'user menu'                     => 'div.navbar-nav.flex-row.order-md-last.user-menu > div > a',
         'entity select dialog'          => '.dropdown-menu.dropdown-menu-end .dropstart + .dropstart a',
         'entity search input'           => 'input[name="entsearchtext"]',
         'entity search button'          => 'body > div.page > header > div > div.ms-md-4.d-none.d-lg-block > div > div.navbar-nav.flex-row.order-md-last.user-menu > div > div > div:nth-child(3) > div div > button',

         'globalEntitySelect'            => '#global_entity_select',
         'entityTreeView'                => 'ul.jstree-container-ul',
         'entityTreeView-rootEntity'     => 'ul.jstree-container-ul li[aria-labelledby="0r_anchor"]',
      ],
   ];

   public function __construct(CommonFunctionalTestCase $test) {
      $this->test = $test;
   }

   public function login($user, $password) {
      // Browse to login page
      $this->test->crawler = $this->test->client->request('GET', '/');

      // screenshot
      $this->test->client->waitForVisibility('.page-anonymous  form input#login_name');
      $this->test->takeScreenshot();
      $form = $this->test->crawler->filter('.page-anonymous  form')->form();

      // Login as glpi
      $login = $this->test->crawler->filter('input#login_name')->attr('name');
      $passwd = $this->test->crawler->filter('input[type="password"]')->attr('name');
      $form[$login] = $user;
      $form[$passwd] = $password;
      $this->test->crawler = $this->test->client->submit($form);

      $this->test->client->waitFor('#backtotop'); // back to top button in footer
   }

   public function logout() {
      $this->test->crawler = $this->test->client->request('GET', '/front/logout.php?noAUTO=1');
      $this->test->client->waitFor('.page-anonymous');
   }

   /**
    * Change the active entity
    *
    * @param Entity $entity
    * @param bool    $subtree if true, select the subtree of the entity
    */
   public function changeActiveEntity(Entity $entity, bool $subtree) {
      $this->test->crawler = $this->test->client->request('GET', '/front/central.php?active_entity=' . $entity->getID());
   }

   public function openTab($title) {
      // Get the anchor to click
      $tabNameSelector = '.nav.nav-tabs a[title="' . $title . '"]';
      $anchor = $this->test->crawler->filter($tabNameSelector);

      // Get the ID of the display area of the tab
      $tabId = $anchor->attr('data-bs-target');

      // Click the name of the tab to show it
      $this->test->client->executeScript("
         document.querySelector('" . $tabNameSelector . "').click();
      ");
      $this->test->client->waitFor($tabId . ' > *:not(i.fa-spinner)');

      // TODO : Check the tab area is now visible
   }

   /**
    * Select an item in a select2 input
    * @param string $value
    * @param string $name name of the option
    *
    * @return void
    */
   public function selectInDropdown($selector, $value, $name = '') {

      $slashSelector = addslashes($selector);
      $htmlValue = htmlentities($value);
      $slashValue = addslashes($value);
      $slashName = addslashes($name);
      $js = "
         var selector = '$slashSelector';
         var exists = $('$slashSelector option[value=\"$htmlValue\"]');
         if (exists.length < 1) {
            var newOption = new Option('$slashName', '$slashValue', true, true);
            $('$slashSelector').append(newOption)
         } else {
            $('$slashSelector').val('$slashValue');
         }
         $('$slashSelector').trigger('change');
      ";
      $this->test->client->executeScript($js);
   }
}