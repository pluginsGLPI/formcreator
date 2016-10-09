<?php
class MultielectFieldTest extends SuperAdminTestCase {

   public function provider() {

      // Force include of not autoloaded classes
      // TODO : enhance the plugin to use autoloading
      PluginFormcreatorFields::getTypes();

      $dataset = array(
            array(
                  'fields'          => array(
                        'fieldtype'       => 'select',
                        'name'            => 'question',
                        'required'        => '0',
                        'show_empty'      => '0',
                        'default_values'  => '',
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array(''),
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'select',
                        'name'            => 'question',
                        'required'        => '0',
                        'show_empty'      => '0',
                        'default_values'  => '3',
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => array('3'),
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'select',
                        'name'            => 'question',
                        'required'        => '0',
                        'show_empty'      => '0',
                        'default_values'  => '3',
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'range_min'       => 2,
                        'range_max'       => 4,
                  ),
                  'data'            => null,
                  'expectedValue'   => array('3'),
                  'expectedIsValid' => false
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'select',
                        'name'            => 'question',
                        'required'        => '0',
                        'show_empty'      => '0',
                        'default_values'  => "3\r\n4",
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'range_min'       => 2,
                        'range_max'       => 4,
                  ),
                  'data'            => null,
                  'expectedValue'   => array('3', '4'),
                  'expectedIsValid' => true
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'select',
                        'name'            => 'question',
                        'required'        => '0',
                        'show_empty'      => '0',
                        'default_values'  => "3\r\n4\r\n2\r\n1\r\n6",
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always',
                        'range_min'       => 2,
                        'range_max'       => 4,
                  ),
                  'data'            => null,
                  'expectedValue'   => array('3', '4', '2', '1', '6'),
                  'expectedIsValid' => false
            ),
      );

      foreach($dataset as &$data) {
         $data['field'] = new multiSelectField($data['fields'], $data['data']);
      }

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testFieldAvailableValue($fields, $data, $expectedValue, $expectedValidity, $fieldInstance) {
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
   public function testFieldValue($fields, $data, $expectedValue, $expectedValidity, $fieldInstance) {
      $value = $fieldInstance->getValue();
      $this->assertEquals(count($expectedValue), count($value));
      foreach ($expectedValue as $expectedSubValue) {
         $this->assertTrue(in_array($expectedSubValue, $value));
      }
   }

   /**
    * @dataProvider provider
    */
   public function testFieldIsValid($fields, $data, $expectedValue, $expectedValidity, $fieldInstance) {
      $values = json_encode(explode("\r\n", $fields['default_values']), JSON_OBJECT_AS_ARRAY);
      $isValid = $fieldInstance->isValid($values);
      $this->assertEquals($expectedValidity, $isValid);
   }

}