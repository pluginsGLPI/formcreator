<?php
class QuestionTest extends SuperAdminTestCase {

   public function inputProvider() {
      return [
         'single quote test' => [
            'input' => [
               'fieldtype' => 'text',
               'name' => "here is a single quote (')",
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
               'fieldtype' => 'text',
               'name' => "here is a single quote (\')",
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

   /**
    * @dataProvider inputProvider
    * @param array $input
    * @param array $expected
    */
   public function testPrepareInputForAdd($input, $expected) {
      $section = $this->getSection();
      $input[$section::getForeignKeyField()] = $section->getID();

      $question = new PluginFormcreatorQuestion();
      $output = $question->prepareInputForAdd($input);
      $this->assertEquals($expected['name'], $output['name'], json_encode($_SESSION['MESSAGE_AFTER_REDIRECT'], JSON_PRETTY_PRINT));
      $this->assertArrayHasKey('uuid', $output);
   }

   /**
    * @dataProvider inputProvider
    * @param array $input
    * @param array $expected
    */
   public function testPrepareInputForUpdate($input, $expected) {
      $section = $this->getSection();
      $input[$section::getForeignKeyField()] = $section->getID();

      $question = new PluginFormcreatorQuestion();
      $output = $question->prepareInputForAdd($input);
      $this->assertEquals($expected['name'], $output['name']);
   }

   private function getSection() {
      $form = new PluginFormcreatorForm();
      $form->add([
         'name' => 'form'
      ]);
      $section = new PluginFormcreatorSection();
      $section->add([
         $form::getForeignKeyField() => $form->getID(),
         'name' => 'section',
      ]);
      return $section;
   }
}