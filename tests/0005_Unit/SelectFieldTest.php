<?php
class SelectFieldTest extends SuperAdminTestCase {

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
                  'expectedValue'   => '1'
            ),
            array(
                  'fields'          => array(
                        'fieldtype'       => 'select',
                        'name'            => 'question',
                        'required'        => '0',
                        'show_empty'      => '1',
                        'default_values'  => '',
                        'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                        'order'           => '1',
                        'show_rule'       => 'always'
                  ),
                  'data'            => null,
                  'expectedValue'   => ''
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
                  'expectedValue'   => '3'
            )
      );

      foreach($dataset as &$data) {
         $data['field'] = new selectField($data['fields'], $data['data']);
      }

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testFieldAvailableValue($fields, $data, $expectedValue, $fieldInstance) {
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
   public function testFieldValue($fields, $data, $expectedValue, $fieldInstance) {
      $value = $fieldInstance->getValue();
      $this->assertEquals($expectedValue, $value);
   }

}