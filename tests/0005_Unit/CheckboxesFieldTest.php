<?php
class CheckboxesFieldTest extends SuperAdminTestCase {

   public function provider() {

      $dataset = array(
            array(
               'fields'          => array(
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
               ),
               'data'            => null,
               'expectedValue'   => array(''),
               'expectedIsValid' => true
            ),
            array(
               'fields'          => array(
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
               ),
               'data'            => null,
               'expectedValue'   => array('2'),
               'expectedIsValid' => true
            ),
            array(
               'fields'          => array(
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
               ),
               'data'            => null,
               'expectedValue'   => array('3', '5'),
               'expectedIsValid' => true
            ),
            array(
               'fields'          => array(
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
               ),
               'data'            => null,
               'expectedValue'   => array('3', '5'),
               'expectedIsValid' => false
            ),
            array(
               'fields'          => array(
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
               ),
               'data'            => null,
               'expectedValue'   => array('3', '5', '6'),
               'expectedIsValid' => true
            ),
            array(
               'fields'          => array(
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
               ),
               'data'            => null,
               'expectedValue'   => array('1', '2', '3', '5', '6'),
               'expectedIsValid' => false
            ),
      );

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testFieldAvailableValue($fields, $data, $expectedValue, $expectedValidity) {
      $section = $this->getSection();
      $fields[$section::getForeignKeyField()] = $section->getID();
      $fieldInstance = new PluginFormcreatorCheckboxesField($fields, $data);

      $availableValues = $fieldInstance->getAvailableValues();
      $expectedAvaliableValues = explode("\r\n", $fields['values']);

      $this->assertCount(count($expectedAvaliableValues), $availableValues);

      foreach ($expectedAvaliableValues as $expectedValue) {
         $this->assertContains($expectedValue, $availableValues);
      }
   }

   /**
    * @dataProvider provider
    */
   public function testFieldValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new PluginFormcreatorCheckboxesField($fields, $data);

      $value = $fieldInstance->getValue();
      $this->assertEquals(count($expectedValue), count($value));
      foreach ($expectedValue as $expectedSubValue) {
         $this->assertTrue(in_array($expectedSubValue, $value));
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

      $fieldInstance = new PluginFormcreatorCheckboxesField($question->fields, $data);
      $values = json_encode(explode("\r\n", $fields['default_values']), JSON_OBJECT_AS_ARRAY);
      $isValid = $fieldInstance->isValid($values);
      $this->assertEquals($expectedValidity, $isValid);
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
      $fieldInstance = new PluginFormcreatorCheckboxesField($fields);

      // Test a value is mandatory
      $input = [
         'values'          => "",
         'name'            => 'foo',
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->assertEquals(0, count($out));

      // Test accented chars are kept
      $input = [
         'values'          => "éè\r\nsomething else",
         'default_values'  => "éè",
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->assertEquals("éè\r\nsomething else", $out['values']);
      $this->assertEquals("éè", $out['default_values']);

      // Test values are trimmed
      $input = [
         'values'          => ' something \r\n  something else  ',
         'default_values'  => " something      ",
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->assertEquals('something\r\nsomething else', $out['values']);
      $this->assertEquals("something", $out['default_values']);
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