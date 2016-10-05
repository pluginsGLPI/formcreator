<?php
class SelectFieldTest extends SuperAdminTestCase {

   public function setUp() {
      parent::setUp();

      // Force include of not autoloaded classes
      // TODO : enhance the plugin to use autoloading
      PluginFormcreatorFields::getTypes();

      $this->selectField = new selectField(
            array(
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '0',
                  'show_empty'      => '0',
                  'default_values'  => '',
                  'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
                  'order'           => '1',
                  'show_rule'       => 'always'
            ),
            null
      );

   }

   public function testFieldAvailableValue() {
      $availableValues = $this->selectField->getAvailableValues();
      $this->assertContains(1, $availableValues);
      $this->assertContains(2, $availableValues);
      $this->assertContains(3, $availableValues);
      $this->assertContains(4, $availableValues);
      $this->assertContains(5, $availableValues);
      $this->assertContains(6, $availableValues);
      $this->assertCount(6, $availableValues);

      return $availableValues;
   }

   /**
    * @depends testFieldAvailableValue
    */
   public function testFieldValue($availableValues) {
      $value = $this->selectField->getValue();
      $this->assertEquals(array_shift($availableValues), $value);
   }

}