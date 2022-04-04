<?php

namespace GlpiPlugin\Formcreator\Tests;

use Plugin;
trait CommonQuestionTest
{

   public function showCreateQuestionForm() {
      // Use a clean entity for the tests
      $this->login('glpi', 'glpi');

      // Create a form and a section
      $section = $this->getSection([
         'name'          => __METHOD__ . ' ' . $this->getUniqueString(),
         'helpdesk_home' => '0',
      ]);
      $this->boolean($section->isNewItem())->isFalse();
      $form = \PluginFormcreatorForm::getByItem($section);
      $this->boolean($form->isNewItem())->isFalse();

      // navigate to the form designer
      $this->crawler = $this->client->request('GET', '/' . Plugin::getWebDir('formcreator', false) . '/front/form.form.php?id=' . $form->getID());
      $this->client->waitFor('[role="tablist"]');
      $this->browsing->openTab('Questions');
      $this->client->waitFor('#plugin_formcreator_form.plugin_formcreator_form_design');

      // show create question form
      $link = $this->crawler->filter('.plugin_formcreator_section .plugin_formcreator_question:not([data-id]) a');
      $this->crawler = $this->client->click($link->link());
      $this->client->waitForVisibility('form[data-itemtype="PluginFormcreatorQuestion"]');

      return $form;
   }

   /**
    * Undocumented function
    *
    * @param array  $input  input data to create the question
    * @return void
    */
   public function _testRenderQuestion(array $input) {
      // Use a clean entity for the tests
      $this->login('glpi', 'glpi');

      $question = $this->getQuestion($input);
      $this->boolean($question->isNewItem())->isFalse();

      // navigate to the form designer
      $form = \PluginFormcreatorForm::getByItem($question);
      $this->crawler = $this->client->request('GET', '/' . Plugin::getWebDir('formcreator', false) . '/front/form.form.php?id=' . $form->getID());
      $this->browsing->openTab('Preview');

      $this->client->waitForVisibility('form[name="plugin_formcreator_form"] [gs-x][gs-w][data-itemtype][data-id="' . $question->getID() . '"');
   }

   /**
    * Submit a questin form then check it is created and displayed
    *
    * @param PluginFormcreatorForm $form
    * @param string $nameField
    * @return void
    */
   public function _testQuestionCreated($form, $questionName) {
      // get existing items count
      $questionsCount = count($this->crawler->filter("[data-itemtype='PluginFormcreatorQuestion'][data-id]"));

      // Submit new question
      $browserForm = $this->crawler->filter('form[data-itemtype=PluginFormcreatorQuestion]')->form();
      $browserForm['name'] = $questionName;
      $this->crawler = $this->client->submit($browserForm);

      for ($wait = 10; $wait > 0; $wait--) {
         usleep(50000);
         if (count($this->crawler->filter("[data-itemtype='PluginFormcreatorQuestion'][data-id]")) > $questionsCount) {
            break;
         }
      }

      // test the question is created in DB
      $questions = (new \PluginFormcreatorQuestion())->getQuestionsFromForm($form->getID());
      $question = array_pop($questions);
      $this->variable($question)->isNotNull();

      // test the question is displayed
      $id = $question->getID();
      $this->client->waitForVisibility("div[data-itemtype='PluginFormcreatorQuestion'][data-id='$id']");
   }
}