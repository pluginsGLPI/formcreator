<?php
class SelectFieldTest extends SuperAdminTestCase {

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
                        'show_rule'       => 'always'
                  ],
                  'data'            => null,
                  'expectedValue'   => '1',
                  'expectedIsValid' => true
            ],
            [
                  'fields'          => [
                        'fieldtype'       => 'select',
                        'name'            => 'question',
                        'required'        => '0',
                        'show_empty'      => '1',
                        'default_values'  => '',
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ],
                  'data'            => null,
                  'expectedValue'   => '',
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
                        'show_rule'       => 'always'
                  ],
                  'data'            => null,
                  'expectedValue'   => '3',
                  'expectedIsValid' => true
            ],
             [
                  'fields'          => [
                        'fieldtype'       => 'select',
                        'name'            => 'question',
                        'required'        => '1',
                        'show_empty'      => '0',
                        'default_values'  => '',
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ],
                  'data'            => null,
                  'expectedValue'   => '1',
                  'expectedIsValid' => false
             ],
             [
                  'fields'          => [
                        'fieldtype'       => 'select',
                        'name'            => 'question',
                        'required'        => '1',
                        'show_empty'      => '1',
                        'default_values'  => '',
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ],
                  'data'            => null,
                  'expectedValue'   => '',
                  'expectedIsValid' => false
             ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testFieldAvailableValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new PluginFormcreatorSelectField($fields, $data);

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
      $fieldInstance = new PluginFormcreatorSelectField($fields, $data);

      $value = $fieldInstance->getValue();
      $this->assertEquals($expectedValue, $value);
   }

   /**
    * @dataProvider provider
    */
   public function testFieldIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new PluginFormcreatorSelectField($fields, $data);

      $isValid = $fieldInstance->isValid($fields['default_values']);
      $this->assertEquals($expectedValidity, $isValid);
   }

   public function testPrepareInputForSave() {
      $fields = [
         'fieldtype'       => 'select',
         'name'            => 'question',
         'required'        => '0',
         'default_values'  => "1\r\n2\r\n3\r\n5\r\n6",
         'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
         'order'           => '1',
         'show_rule'       => 'always',
         'range_min'       => 3,
         'range_max'       => 4,
      ];
      $fieldInstance = new PluginFormcreatorSelectField($fields);

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
}