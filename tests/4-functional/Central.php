<?php

namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonFunctionalTestCase;

class Central extends CommonFunctionalTestCase {
   private $selectors = [
      'formsTab' => '#page > div > div > ul > li > a[title="Forms"]',
   ];

   public function testCentralHasTab() {
      $this->takeScreenshot();

      // Test the central show the Forms tab
      $output = $this->crawler->filter($this->selectors['formsTab']);
      $this->dump($output->text());
      $this->string($output->text())->isEqualTo('Forms');
   }

   public function testFormsAreVisible() {
      // Use a clean entity for the tests
      $this->login('glpi', 'glpi');
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
         'name'          => 'Visible on helpdesk',
         'entities_id'   => $entity->getID(),
         'helpdesk_home' => '0',
      ]);

      // Select the entity for the test
      $this->browsing->changeActiveEntity($entity, true);

      // Open the forms tab
      $this->browsing->openTab('Forms');

      // Check the form is not displayed
      $this->takeScreenshot();
      $formSelector = '#plugin_formcreatorHomepageForms [data-itemtype="PluginFormcreatorForm"][data-id="' . $form->getID() . '"]';
      $output = $this->crawler->filter($formSelector)->count();
      $this->integer($output)->isEqualTo(0);

      // Check a message exists to say there is no form
      $this->client->waitForVisibility('#plugin_formcreatorHomepageForms');
      $output = $this->crawler->filter('#plugin_formcreatorHomepageForms')->text();
      $this->string($output)->isEqualTo('No form available');

      // make the form visible in the tab
      $form->update([
         'id'            => $form->getID(),
         'helpdesk_home' => '1',
      ]);
      $this->crawler = $this->client->reload();

      // Check the form is displayed in the expected category

      // Open the forms tab
      $this->browsing->openTab('Forms');
      $this->takeScreenshot();
      $formSelector = '#plugin_formcreatorHomepageForms [data-itemtype="PluginFormcreatorCategory"][data-id="0"] + [data-itemtype="PluginFormcreatorForm"][data-id="' . $form->getID() . '"]';
      $output = $this->crawler->filter($formSelector)->count();
      $this->integer($output)->isEqualTo(1);
      $formSelector .= ' a';
      $output = $this->crawler->filter($formSelector)->text();
      $this->string($output)->isEqualTo($form->fields['name']);

      // Move the form in a category
      $form->update([
         'id'            => $form->getID(),
         \PluginFormcreatorCategory::getForeignKeyField() => $categoryId,
      ]);
      $this->crawler = $this->client->reload();
      // Open the forms tab
      $this->browsing->openTab('Forms');

      // Check the form shows in the expected category
      $this->takeScreenshot();
      $formSelector = '#plugin_formcreatorHomepageForms [data-itemtype="PluginFormcreatorCategory"][data-id="' . $categoryId . '"] + [data-itemtype="PluginFormcreatorForm"][data-id="' . $form->getID() . '"]';
      $output = $this->crawler->filter($formSelector)->count();
      $this->integer($output)->isEqualTo(1);
      $formSelector .= ' a';
      $output = $this->crawler->filter($formSelector)->text();
      $this->string($output)->isEqualTo($form->fields['name']);
   }
}
