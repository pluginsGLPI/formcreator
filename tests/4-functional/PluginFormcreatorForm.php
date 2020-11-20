<?php

namespace tests\units;

use GlpiPlugin\Formcreator\Tests\CommonFunctionalTestCase;
use Facebook\WebDriver\Remote\DriverCommand;

class PluginFormcreatorForm extends CommonFunctionalTestCase
{
   public function testFormIsVisibleInAssistanceForms()
   {
      // Use a clean entity for the tests
      $this->login('glpi', 'glpi');

      // have e fresh entity
      $entity = new \Entity();
      $entityId = $entity->import([
         'name' => $this->getUniqueString(),
         'entities_id' => '0' // Root entity
      ]);
      $entity->getFromDB($entityId);
      $this->boolean($entity->isNewItem())->isFalse();

      // Logout / login to refresh righs on entities
      $this->login('glpi', 'glpi');

      // create a category
      \Session::changeActiveEntities($entity->getID(), true);
      $category = new \PluginFormcreatorCategory();
      $categoryId = $category->import([
         'completename' => $this->getUniqueString(),
         'entities_id'  => $entity->getID(),
      ]);
      $this->boolean($category->isNewID($categoryId))->isFalse();

      // Create a form
      $form = $this->getForm([
         'name'          => 'Visible on Assistance > Forms',
         'entities_id'   => $entity->getID(),
         'helpdesk_home' => '0',
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      // Select the entity for the test
      $this->browsing->changeActiveEntity($entity, true);

      $this->crawler = $this->client->request('GET', '/plugins/formcreator/front/formlist.php');
      $this->client->waitFor('footer');
      // Forms are loaded with AJAX
      $formTileSelector = 'div[data-itemtype="PluginFormcreatorForm"][data-id="' . $form->getID() . '"]';
      $this->client->waitForVisibility($formTileSelector);

      $output = $this->crawler->filter($formTileSelector)->count();
      $this->integer($output)->isEqualTo(1);
   }

   public function testBackgroundColor()
   {
      // Use a clean entity for the tests
      $this->login('glpi', 'glpi');

      // have e fresh entity
      $entity = new \Entity();
      $entityId = $entity->import([
         'name' => $this->getUniqueString(),
         'entities_id' => '0' // Root entity
      ]);
      $entity->getFromDB($entityId);
      $this->boolean($entity->isNewItem())->isFalse();

      // Logout / login to refresh righs on entities
      $this->login('glpi', 'glpi');

      // Create a form with black background
      $form = $this->getForm([
         'name'             => 'background color',
         'is_active'        => '1',
         'background_color' => '#000000',
         'entities_id'      => $entity->getID(),
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      // Select the entity for the test
      $this->browsing->changeActiveEntity($entity, true);

      // View the form's tile
      $formTileSelector = 'div[data-itemtype="PluginFormcreatorForm"][data-id="' . $form->getID() . '"]';
      $this->crawler = $this->client->request('GET', '/plugins/formcreator/front/formlist.php');
      $this->client->waitForVisibility($formTileSelector);
      $this->takeScreenshot();

      // Check the tile is black
      $output = $this->client->executeScript("
         return $('" . $formTileSelector . "').css('background-color');
      ");
      $this->string($output)->isEqualTo("rgb(0, 0, 0)");

      // CHange the background color
      $success = $form->update([
         'id' => $form->getID(),
         'background_color' => '#009000'
      ]);
      $this->boolean($success)->isTrue();

      // View the form's tile
      $this->client->reload();
      $this->client->waitForVisibility($formTileSelector);
      $this->takeScreenshot();
      // Check the tile is green
      $output = $this->client->executeScript("
         return $('" . $formTileSelector . "').css('background-color');
      ");
      $this->string($output)->isEqualTo("rgb(0, 144, 0)");
   }

   public function testIconColor()
   {
      // Use a clean entity for the tests
      $this->login('glpi', 'glpi');

      // have e fresh entity
      $entity = new \Entity();
      $entityId = $entity->import([
         'name' => $this->getUniqueString(),
         'entities_id' => '0' // Root entity
      ]);
      $entity->getFromDB($entityId);
      $this->boolean($entity->isNewItem())->isFalse();

      // Logout / login to refresh righs on entities
      $this->login('glpi', 'glpi');

      // Create a form with black background
      $form = $this->getForm([
         'name'        => 'icon color',
         'is_active'   => '1',
         'icon_color'  => '#000000',
         'entities_id' => $entity->getID(),
         'icon'        => 'fas fa-bug',
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      // Select the entity for the test
      $this->browsing->changeActiveEntity($entity, true);

      // View the form's tile
      $formIconSelector = 'div[data-itemtype="PluginFormcreatorForm"][data-id="' . $form->getID() . '"] i';
      $this->crawler = $this->client->request('GET', '/plugins/formcreator/front/formlist.php');
      $this->client->waitForVisibility($formIconSelector);
      $this->takeScreenshot();

      // Check the tile is black
      $output = $this->client->executeScript("
         return $('" . $formIconSelector . "').css('color');
      ");
      $this->string($output)->isEqualTo("rgb(0, 0, 0)");

      // CHange the background color
      $success = $form->update([
         'id' => $form->getID(),
         'icon_color' => '#009000'
      ]);
      $this->boolean($success)->isTrue();

      // View the form's tile
      $this->client->reload();
      $this->client->waitForVisibility($formIconSelector);
      $this->takeScreenshot();
      // Check the tile is green
      $output = $this->client->executeScript("
         return $('" . $formIconSelector . "').css('color');
      ");
      $this->string($output)->isEqualTo("rgb(0, 144, 0)");
   }

   public function testVisibilityByLanguage()
   {
      // Use a clean entity for the tests
      $this->login('glpi', 'glpi');

      // have e fresh entity
      $entity = new \Entity();
      $entityId = $entity->import([
         'name' => $this->getUniqueString(),
         'entities_id' => '0', // Root entity
      ]);
      $entity->getFromDB($entityId);
      $this->boolean($entity->isNewItem())->isFalse();

      // change curent language
      $user = new \User();
      $user->getFromDB(2); // glpi
      $language = $user->fields['language'];
      $user->update([
         'id' => $user->getID(),
         'language' => 'en_US',
      ]);
      // Logout / login to refresh righs on entities
      $this->login('glpi', 'glpi');
      // Restore default language bfore possible failures
      $user->update([
         'id' => $user->getID(),
         'language' => $language,
      ]);
      // Create a form with black background
      $form = $this->getForm([
         'name'        => 'language visibility',
         'is_active'   => '1',
         'entities_id' => $entity->getID(),
         'language' => 'en_US',
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $this->browsing->logout();
      $this->browsing->login('glpi', 'glpi');

      // Select the entity for the test
      $this->browsing->changeActiveEntity($entity, true);

      $this->crawler = $this->client->request('GET', '/plugins/formcreator/front/formlist.php');
      $formTileSelector = 'div[data-itemtype="PluginFormcreatorForm"][data-id="' . $form->getID() . '"]';
      $this->client->waitForVisibility($formTileSelector);
      $this->takeScreenshot();

      $success = $form->update([
         'id' => $form->getID(),
         'language' => 'fr_FR',
      ]);
      $this->boolean($success)->isTrue();

      $this->crawler = $this->client->reload();
      $this->client->waitForVisibility('#plugin_formcreator_formlist');
      $output = $this->crawler->filter($formTileSelector);
      $this->integer(count($output))->isEqualTo(0);
   }
}
