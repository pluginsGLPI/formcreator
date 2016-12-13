<?php
class FloatFieldTest extends SuperAdminTestCase {

   public function provider() {
      // Force include of not autoloaded classes
      // TODO : enhance the plugin to use autoloading
      PluginFormcreatorFields::getTypes();

      $dataset = array(
            array(
                  'fields'          => array(
                        'fieldtype'       => 'float',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => '',
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'show_empty'      => '0',
                        'values'          => ''
                  ),
                  'data'            => null,
                  'expectedValue'   => '',
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'float',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => '2',
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'show_empty'      => '0',
                        'values'          => ''
                  ),
                  'data'            => null,
                  'expectedValue'   => '2',
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'float',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => "2",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'show_empty'      => '0',
                        'range_min'       => 3,
                        'range_max'       => 4,
                  ),
                  'data'            => null,
                  'expectedValue'   => '2',
                  'expectedIsValid' => false
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'float',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => "5",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'show_empty'      => '0',
                        'values'          => '',
                        'range_min'       => 3,
                        'range_max'       => 4,
                  ),
                  'data'            => null,
                  'expectedValue'   => '5',
                  'expectedIsValid' => false
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'float',
                        'name'            => 'question',
                        'required'        => '0',
                        'default_values'  => "3.141592",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'show_empty'      => '0',
                        'values'          => '',
                        'range_min'       => 3,
                        'range_max'       => 4,
                  ),
                  'data'            => null,
                  'expectedValue'   => '3.141592',
                  'expectedIsValid' => true
            ),
      );

      foreach($dataset as &$data) {
         $data['field'] = new PluginFormcreatorFloatField($data['fields'], $data['data']);
      }

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testFieldValue($fields, $data, $expectedValue, $expectedValidity, $fieldInstance) {
      $value = $fieldInstance->getValue();
      $this->assertEquals($expectedValue, $value);
   }

   /**
    * @dataProvider provider
    */
   public function testFieldIsValid($fields, $data, $expectedValue, $expectedValidity, $fieldInstance) {
      $isValid = $fieldInstance->isValid($fields['default_values']);
      $this->assertEquals($expectedValidity, $isValid);
   }

}