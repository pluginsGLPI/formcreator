<?php

namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonFunctionalTestCase;

class PluginFormcreatorQuestion extends CommonFunctionalTestCase {
   public function testCreateQuestion() {
      $form = $this->getForm();
      $this->getSection([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
      ]);
      $this->crawler = $this->client->request('GET', '/plugins/formcreator/front/form.form.php?id=' . $form->getID());
      $this->client->waitFor('footer');

      // Open the questions tab
      $this->browsing->openTab('Questions');
      $this->takeScreenshot();

      // Add a question in the 1st and only section
      $anchorSelector = ".plugin_formcreator_section .plugin_formcreator_question a";
      $this->client->executeScript("
         $('" . $anchorSelector . "').click();
      ");
      $this->client->waitFor('form[data-itemtype="PluginFormcreatorQuestion"]');
      $this->takeScreenshot();

   }
}