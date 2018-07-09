<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorMultiSelectField extends CommonTestCase {

   public function provider() {

      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'select',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => '',
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => [''],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'select',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => '3',
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'select',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => '3',
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '2',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3'],
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'select',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => "3\r\n4",
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '2',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3', '4'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'select',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => "3\r\n4\r\n2\r\n1\r\n6",
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '2',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3', '4', '2', '1', '6'],
            'expectedIsValid' => false
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testFieldAvailableValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorMultiSelectField($fields, $data);

      $availableValues = $fieldInstance->getAvailableValues();
      $expectedAvaliableValues = explode("\r\n", $fields['values']);

      $this->integer(count($availableValues))->isEqualTo(count($expectedAvaliableValues));
      foreach ($expectedAvaliableValues as $expectedValue) {
         $this->array($availableValues)->contains($expectedValue);
      }
   }

   /**
    * @dataProvider provider
    */
   public function testFieldValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorMultiSelectField($fields, $data);

      $value = $fieldInstance->getValue();
      $this->integer(count($value))->isEqualTo(count($expectedValue));
      foreach ($expectedValue as $expectedSubValue) {
         $this->array($value)->contains($expectedSubValue);
      }
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

      $fieldInstance = new \PluginFormcreatorMultiSelectField($question->fields, $data);

      $values = json_encode(explode("\r\n", $fields['default_values']), JSON_OBJECT_AS_ARRAY);
      $isValid = $fieldInstance->isValid($values);
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
   }

   public function testPrepareInputForSave() {
      $fields = [
         'fieldtype'       => 'multiselect',
         'name'            => 'question',
         'required'        => '0',
         'default_values'  => "1\r\n2\r\n3\r\n5\r\n6",
         'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
         'order'           => '1',
         'show_rule'       => 'always',
         '_parameters'     => [
            'multiselect' => [
               'range' => [
                  'range_min' => '3',
                  'range_max' => '4',
               ]
            ]
         ],
      ];
      $section = $this->getSection();
      $fields[$section::getForeignKeyField()] = $section->getID();

      $question = new PluginFormcreatorQuestion();
      $question->add($fields);
      $question->updateParameters($fields);

      $fieldInstance = new \PluginFormcreatorMultiSelectField($question->fields, $data);

      // Test a value is mandatory
      $input = [
         'values'          => "",
         'name'            => 'foo',
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->integer(count($out))->isEqualTo(0);

      // Test accented chars are kept
      $input = [
         'values'          => "éè\r\nsomething else",
         'default_values'  => "éè",
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->string($out['values'])->isEqualTo("éè\r\nsomething else");
      $this->string($out['default_values'])->isEqualTo("éè");

      // Test values are trimmed
      $input = [
         'values'          => ' something \r\n  something else  ',
         'default_values'  => " something      ",
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->string($out['values'])->isEqualTo('something\r\nsomething else');
      $this->string($out['default_values'])->isEqualTo("something");
   }
}