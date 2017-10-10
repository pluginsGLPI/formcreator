<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorSelectField extends CommonTestCase {

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
      $fieldInstance = new \PluginFormcreatorSelectField($fields, $data);

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
      $fieldInstance = new \PluginFormcreatorSelectField($fields, $data);

      $value = $fieldInstance->getValue();
      $this->string($value)->isEqualTo($expectedValue);
   }

   /**
    * @dataProvider provider
    */
   public function testFieldIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorSelectField($fields, $data);

      $isValid = $fieldInstance->isValid($fields['default_values']);
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
   }
}