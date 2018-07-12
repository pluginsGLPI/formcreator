<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorQuestion extends CommonTestCase {

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testCreateQuestionText':
            $this->login('glpi', 'glpi');
            break;
      }
   }

   public function testDuplicate() {
      $section = $this->getSection();

      $question = new \PluginFormcreatorQuestion();
      $questions_id_1 = $question->add(['name'                           => "test clone question 1",
                                        'fieldtype'                      => 'text',
                                        'plugin_formcreator_sections_id' => $section->getID(),
                                        '_parameters' => [
                                           'text' => [
                                           'regex' => ['regex' => ''],
                                           'range' => ['min' => '', 'max' => ''],
                                           ]
                                         ],
                                        ]);

      //clone the question
      $this->integer($question->duplicate());

      //get cloned section
      $originalId = $question->getID();
      $new_question  = new \PluginFormcreatorQuestion();
      $new_question->getFromDBByCrit([
          'AND' => [
              'name'                           => 'test clone question 1',
              'NOT'                            => ['uuid' => $question->getField('uuid')],  // operator <> available in GLPI 9.3+ only
              'plugin_formcreator_sections_id' => $question->getField('plugin_formcreator_sections_id')
          ]
      ]);
      $this->boolean($new_question->isNewItem())->isFalse();
   }
}