<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorCheckboxesField extends CommonTestCase {
   public function provider() {
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'checkboxes',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'checkboxes' => [
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
               'fieldtype'       => 'checkboxes',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '2',
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'checkboxes' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
         ],
            'data'            => null,
            'expectedValue'   => ['2'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'checkboxes',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "3\r\n5",
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'checkboxes' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3', '5'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'checkboxes',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "3\r\n5",
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'checkboxes' => [
                     'range' => [
                        'range_min' => '3',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3', '5'],
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'checkboxes',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "3\r\n5\r\n6",
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'checkboxes' => [
                     'range' => [
                        'range_min' => '3',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3', '5', '6'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'checkboxes',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "1\r\n2\r\n3\r\n5\r\n6",
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'checkboxes' => [
                     'range' => [
                        'range_min' => '3',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['1', '2', '3', '5', '6'],
            'expectedIsValid' => false
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testGetAvailableValues($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorCheckboxesField($fields, $data);

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
   public function testGetValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorCheckboxesField($fields, $data);

      $value = $fieldInstance->getValue();
      $this->integer(count($value))->isEqualTo(count($expectedValue));
      foreach ($expectedValue as $expectedSubValue) {
         $this->array($value)->contains($expectedSubValue);
      }
   }

   /**
    * @dataProvider provider
    */
   public function testIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $section = $this->getSection();
      $fields[$section::getForeignKeyField()] = $section->getID();

      $question = new \PluginFormcreatorQuestion();
      $question->add($fields);
      $question->updateParameters($fields);

      $fieldInstance = new \PluginFormcreatorCheckboxesField($question->fields, $data);

      $values = json_encode(explode("\r\n", $fields['default_values']), JSON_OBJECT_AS_ARRAY);
      $isValid = $fieldInstance->isValid($values);
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
   }

   public function testPrepareInputForSave() {
      $fields = array(
         'fieldtype'       => 'checkboxes',
         'name'            => 'question',
         'required'        => '0',
         'default_values'  => "1\r\n2\r\n3\r\n5\r\n6",
         'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
         'order'           => '1',
         'show_rule'       => 'always',
         'range_min'       => 3,
         'range_max'       => 4,
      );
      $fieldInstance = new \PluginFormcreatorCheckboxesField($fields);

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