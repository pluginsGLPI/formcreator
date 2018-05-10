<?php
class IntegerFieldTest extends SuperAdminTestCase {

   public function provider() {
      $dataset = [
            [
                  'fields'          => [
                        'fieldtype'       => 'integer',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => '',
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'show_empty'      => '0',
                        'values'          => ''
                  ],
                  'data'            => null,
                  'expectedValue'   => '',
                  'expectedIsValid' => true
            ],
            [
                  'fields'          => [
                        'fieldtype'       => 'integer',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => '2',
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'show_empty'      => '0',
                        'values'          => ''
                  ],
                  'data'            => null,
                  'expectedValue'   => '2',
                  'expectedIsValid' => true
            ],
            [
                  'fields'          => [
                        'fieldtype'       => 'integer',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => "2",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'show_empty'      => '0',
                        'range_min'       => 3,
                        'range_max'       => 4,
                  ],
                  'data'            => null,
                  'expectedValue'   => '2',
                  'expectedIsValid' => false
            ],
            [
                  'fields'          => [
                        'fieldtype'       => 'integer',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => "5",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'show_empty'      => '0',
                        'values'          => '',
                        'range_min'       => 3,
                        'range_max'       => 4,
                  ],
                  'data'            => null,
                  'expectedValue'   => '5',
                  'expectedIsValid' => false
            ],
            [
                  'fields'          => [
                        'fieldtype'       => 'integer',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => "3.4",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'show_empty'      => '0',
                        'values'          => '',
                        'range_min'       => 3,
                        'range_max'       => 4,
                  ],
                  'data'            => null,
                  'expectedValue'   => '3.4',
                  'expectedIsValid' => false
            ],
            [
                  'fields'          => [
                        'fieldtype'       => 'integer',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => "4",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'show_empty'      => '0',
                        'values'          => '',
                        'range_min'       => 3,
                        'range_max'       => 4,
                  ],
                  'data'            => null,
                  'expectedValue'   => '4',
                  'expectedIsValid' => true
            ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testFieldValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new PluginFormcreatorIntegerField($fields, $data);

      $value = $fieldInstance->getValue();
      $this->assertEquals($expectedValue, $value);
   }

   /**
    * @dataProvider provider
    */
   public function testFieldIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new PluginFormcreatorIntegerField($fields, $data);

      $isValid = $fieldInstance->isValid($fields['default_values']);
      $this->assertEquals($expectedValidity, $isValid);
   }

}