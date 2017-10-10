<?php
class TextFieldTest extends SuperAdminTestCase {

   public function provider() {
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'text',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => '',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => 'always',
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
            'data'            => null,
            'expectedValue'   => '1',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'text',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => 'a',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'text' => [
                     'range' => [
                        'range_min' => '5',
                        'range_max' => '8',
                     ],
                     'regex' => [
                        'regex' => ''
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => '1',
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'text',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => 'short',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'text' => [
                     'range' => [
                        'range_min' => '6',
                        'range_max' => '8',
                     ],
                     'regex' => [
                        'regex' => ''
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => '1',
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'text',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => 'very long',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'text' => [
                     'range' => [
                        'range_min' => '6',
                        'range_max' => '8',
                     ],
                     'regex' => [
                        'regex' => ''
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => '1',
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'text',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => 'very long',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => 'good',
               '_parameters'     => [
                  'text' => [
                     'range' => [
                        'range_min' => '3',
                        'range_max' => '8',
                     ],
                     'regex' => [
                        'regex' => ''
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => '1',
            'expectedIsValid' => false
         ],
      ];
      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testFieldIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $section = $this->getSection();
      $fields[$section::getForeignKeyField()] = $section->getID();

      $question = new PluginFormcreatorQuestion();
      $question->add($fields);
      $question->updateParameters($fields);

      $fieldInstance = new PluginFormcreatorTextField($question->fields, $data);

      $isValid = $fieldInstance->isValid($fields['default_values']);
      $this->assertEquals($expectedValidity, $isValid, $_SESSION['MESSAGE_AFTER_REDIRECT']);
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