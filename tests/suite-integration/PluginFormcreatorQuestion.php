<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorQuestion extends CommonTestCase {

   private $formData = null;

   private $sectionData = null;

   private $questionTextData = null;

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testCreateQuestionText':
            $this->login('glpi', 'glpi');
            break;
      }
   }

   public function testCreateQuestionText() {
      $this->formData = [
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => 'a form',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ];

      $this->sectionData = [
            'name'                  => 'a section',
      ];

      $this->questionTextData = [
            'name'                  => 'text question',
            'fieldtype'             => 'text'
      ];

      $form = new \PluginFormcreatorForm();
      $form->add($this->formData);
      $this->boolean($form->isNewItem())->isFalse();

      $section = new \PluginFormcreatorSection();
      $this->sectionData = $this->sectionData + [
            'plugin_formcreator_forms_id' => $form->getID()
      ];
      $section->add($this->sectionData);
      $this->boolean($section->isNewItem())->isFalse();

      $question = new \PluginFormcreatorQuestion();
      $this->questionTextData = $this->questionTextData  + ['plugin_formcreator_sections_id' => $section->getID()];
      $question->add($this->questionTextData);
      $this->boolean($question->isNewItem())->isFalse();

      return $question;
   }

   public function testCloneQuestion() {
      $form          = new \PluginFormcreatorForm;
      $section       = new \PluginFormcreatorSection;
      $question      = new \PluginFormcreatorQuestion;

      // create objects
      $forms_id = $form->add(['name'                => "test clone form",
                              'is_active'           => true,
                              'validation_required' => \PluginFormcreatorForm_Validator::VALIDATION_USER]);

      $sections_id = $section->add(['name'                        => "test clone section",
                                    'plugin_formcreator_forms_id' => $forms_id]);

      $questions_id_1 = $question->add(['name'                           => "test clone question 1",
                                        'fieldtype'                      => 'text',
                                        'plugin_formcreator_sections_id' => $sections_id,
                                        '_parameters' => [
                                           'text' => [
                                           'regex' => '',
                                           'range' => ['min' => '', 'max' => ''],
                                          ]
                                         ],
                                        ]);
      $new_question  = new PluginFormcreatorQuestion;

      //get question
      plugin_formcreator_getFromDBByField($question, 'name', "test clone question 1");

      //clone it
      $this->boolean($question->duplicate())->isTrue();

      //get cloned section
      $originalId = $question->getID();
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