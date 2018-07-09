<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorQuestion extends CommonTestCase {

   private $form;

   private $section;

   public function setup() {
      // instanciate classes
      $form           = new \PluginFormcreatorForm;
      $form_section   = new \PluginFormcreatorSection;
      $form_question  = new \PluginFormcreatorQuestion;
      $form_condition = new \PluginFormcreatorQuestion_Condition;
      $form_validator = new \PluginFormcreatorForm_Validator;
      $form_target    = new \PluginFormcreatorTarget;
      $form_profile   = new \PluginFormcreatorForm_Profile;

      // create objects
      $forms_id = $form->add([
         'name'                => "test clone form",
         'is_active'           => true,
         'validation_required' => \PluginFormcreatorForm_Validator::VALIDATION_USER
      ]);

      $sections_id = $form_section->add([
         'name'                        => "test clone section",
         'plugin_formcreator_forms_id' => $forms_id
      ]);

      $questions_id_1 = $form_question->add([
         'name'                           => "test clone question 1",
         'fieldtype'                      => 'text',
         'plugin_formcreator_sections_id' => $sections_id
      ]);
      $questions_id_2 = $form_question->add([
         'name'                           => "test clone question 2",
         'fieldtype'                      => 'textarea',
         'plugin_formcreator_sections_id' => $sections_id
      ]);
   }

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testPrepareInputForAdd':
         case 'testPrepareInputForUpdate':
            $this->form = new \PluginFormcreatorForm;
            $this->section = new \PluginFormcreatorSection;
            $this->form->add([
               'name' => "$method"
            ]);
            $this->boolean($this->form->isNewItem())->isFalse();
            $this->section->add([
               'name' => 'section',
               'plugin_formcreator_forms_id' => $this->form->getID(),
            ]);
            $this->boolean($this->section->isNewItem())->isFalse();
      }
   }

   /**
    * @cover PluginFormcreatorQuestion::clone
    */
   public function testDuplicate() {
      $question      = $this->getQuestion();

      //clone it
      $newQuestion_id = $question->duplicate();
      $this->integer($newQuestion_id)->isGreaterThan(0);

      //get cloned section
      $new_question  = new \PluginFormcreatorQuestion;
      $new_question->getFromDB($newQuestion_id);

      // check uuid
      $this->string($new_question->getField('uuid'))->isNotEqualTo($question->getField('uuid'));
   }

   public function providerPrepareInputForAdd() {
      return [
         [
            'input' => [
               'plugin_formcreator_sections_id' => $this->section->getID(),
               'fieldtype'                      => 'radios',
               'name'                           => "it\'s nice",
               'values'                         => "it\'s nice\r\nit's good",
               'required'                       => '1',
               'show_empty'                     => '0',
               'default_values'                 => 'it\'s nice',
               'desription'                     => "it\'s excellent",
               'order'                          => '1',
               'show_rule'                      => 'always',
               '_parameters'     => [
                  'text' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => [
                        'regex' => ''
                     ]
                  ]
               ],
            ],
            'expected' => [
               'plugin_formcreator_sections_id' => $this->section->getID(),
               'fieldtype'                      => 'radios',
               'name'                           => "it\'s nice",
               'values'                         => "it\'s nice\r\nit's good",
               'required'                       => '1',
               'show_empty'                     => '0',
               'default_values'                 => 'it\'s nice',
               'desription'                     => "it\'s excellent",
               'order'                          => '1',
               'show_rule'                      => 'always',
               '_parameters'     => [
                  'text' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => [
                        'regex' => ''
                     ]
                  ]
               ],
            ]
         ],
      ];
   }

   public function providerPrepareInputForUpdate() {
      return providerPrepareInputForAdd();
   }

   /**
    * @dataProvider providerPrepareInputForAdd
    */
   public function testPrepareInputForAdd($input, $expected) {
      $section = $this->getSection();
      $input[$section::getForeignKeyField()] = $section->getID();

      $instance = new \PluginFormcreatorQuestion();
      $output = $instance->prepareInputForAdd($input);
      $this->array($output)->hasKeys(array_keys($expected));
      /*
      // Disabled for now
      $this->array($output)->containsValues($expected);
      */
      $this->array($output)->hasKey('uuid');
      // The method added a UUID key
      $this->array($output)->size->isEqualTo(count($expected) + 1);
   }

   /**
    * @dataProvider providerPrepareInputForUpdate
    */
   public function prepareInputForUpdate($input, $expected) {
      $section = $this->getSection();
      $input[$section::getForeignKeyField()] = $section->getID();

      $instance = new \PluginFormcreatorQuestion();
      $output = $instance->prepareInputForUpdate($input);
      $this->array($output)->hasKeys(array_keys($expected));
      $this->array($output)->containsValues($expected);
      $this->array($output)->hasKey('uuid');
      $this->array($output)->size->isEqualTo(count($expected));
   }
}
