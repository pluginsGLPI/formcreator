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
      $this->test->client->waitForVisibility('#boxlogin > form');
      $this->test->takeScreenshot();
      $form = $this->test->crawler->filter('#boxlogin > form')->form();

      // Login as glpi
      $login = $this->test->crawler->filter('input#login_name')->attr('name');
      $passwd = $this->test->crawler->filter('input#login_password')->attr('name');
      $form[$login] = $user;
      $form[$passwd] = $password;
      $this->test->crawler = $this->test->client->submit($form);

      $this->test->client->waitFor('#footer');
   }

   public function logout() {
      $this->test->crawler = $this->test->client->request('GET', '/front/logout.php?noAUTO=1');
      $this->test->client->waitFor('#display-login');
   }

   /**
    * Change the active entity
    *
    * @param Entity $entity
    * @param bool    $subtree if true, select the subtree of the entity
    */
   public function changeActiveEntity(Entity $entity, bool $subtree) {
      // Open the entity selection modal
      $this->test->crawler->filter($this->selectors['_header']['globalEntitySelect'])->link();
      $this->test->client->executeScript("
         $('" . $this->selectors['_header']['globalEntitySelect'] . "').click();
      ");
      $this->test->client->waitFor($this->selectors['_header']['entityTreeView-rootEntity']);

      // Develop all ancestors to the entity to select
      $dbUtils = new DbUtils();
      $ancestors = $dbUtils->getAncestorsOf($entity->getTable(), $entity->getID());
      foreach ($ancestors as $ancestor) {
         if ($ancestor == '0') {
            $ancestor = $ancestor . 'r';
         }
         $selector = $this->selectors['_header']['entityTreeView'] . ' li[aria-labelledby="' . $ancestor . '_anchor"] i.jstree-icon.jstree-ocl';
         $this->test->client->executeScript("
            $('" . $selector . "').click();
         ");
         $this->test->client->waitFor($this->selectors['_header']['entityTreeView'] . ' li[aria-labelledby="' . $ancestor . '_anchor"].jstree-open');
      }

      // click the entity to activate
      $id = $entity->getID();
      if ($id == '0') {
         $id = $id . 'r';
      }
      $selector = $this->selectors['_header']['entityTreeView'] . " a#${id}_anchor";
      if ($subtree && $entity->haveChildren()) {
         // select the subtree
         $selector = $selector . 'i:nth-child(2)';
      }
      $this->test->client->executeScript("
         $('" . $selector . "').click();
      ");
      $this->test->crawler = $this->test->client->waitFor($this->selectors['_header']['globalEntitySelect']);

      //Check the entity is the selected one
      $expectedNnewEntity = $entity->fields['name'];
      if ($subtree && $entity->haveChildren()) {
         $expectedNnewEntity .= ' (tree structure)';
      }
      $newEntity = $this->test->crawler->filter($this->selectors['_header']['globalEntitySelect'])->text();
      $this->test->string($newEntity)->isEqualTo($expectedNnewEntity);
   }

   public function openTab($title) {
      // Get the anchor to click
      $tabNameSelector = '.ui-tabs-tab > a[title="' . $title . '"]';
      $anchor = $this->test->crawler->filter($tabNameSelector);

      // Get the ID of the display area of the tab
      $anchorId = $anchor->attr('id');
      $tabDisplayId = $this->test->crawler->filter('.ui-tabs-tab[aria-labelledby="' . $anchorId . '"]')->attr('aria-controls');

      // Click the name of the tab to show it
      $this->test->client->executeScript("
         $('" . $tabNameSelector . "').click();
      ");
      $this->test->client->waitFor('#' . $tabDisplayId . ' > *:not(#loadingtabs)');

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
         } else {
            $('$slashSelector').val('$slashValue');
         }
         $('$slashSelector').append(newOption).trigger('change');
      ";
      $this->test->client->executeScript($js);
   }
}