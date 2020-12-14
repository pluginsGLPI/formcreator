<?php

namespace GlpiPlugin\Formcreator\Tests;

trait CommonQuestionTest
{

   /**
    * Submit a questin form then check it is created and displayed
    *
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