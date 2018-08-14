<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorActorField extends CommonTestCase {

   public function testGetName() {
      $output = \PluginFormcreatorActorField::getName();
      $this->string($output)->isEqualTo('Actor');
   }

   public function providerGetValue() {
      $user = new \User();
      $user->getFromDBbyName('glpi');
      $userId = $user->getID();
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => [''],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => 'glpi',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => [''],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => 'nonexistent',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => [''],
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => 'email@incomplete',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => [''],
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => 'email@something.com',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => ['email@something.com'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $userId . ',email@something.com',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => ['glpi', 'email@something.com'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $userId . ',email@something.com,nonexistent',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => ['glpi', 'email@something.com'],
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $userId . ',email@something.com,email@incomplete',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => ['glpi', 'email@something.com'],
            'expectedIsValid' => false
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider providerGetValue
    */
   public function testGetValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorActorField($fields, $data);

      $value = $fieldInstance->getValue();
      $this->integer(count(explode(',', $value)))->isEqualTo(count($expectedValue));
      foreach ($expectedValue as $expectedSubValue) {
         if (!empty($expectedSubValue)) {
            $this->boolean(in_array($expectedSubValue, explode(',', $value)))->isTrue();
         }
      }
   }

   public function providerFieldIsValid() {
      return providerGetValue();
   }

   /**
    * @dataProvider providerFieldIsValid
    */
   public function testFieldIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorActorField($fields, $data);

      $values = $fields['default_values'];
      $isValid = $fieldInstance->isValid($values);
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
   }

   public function providerSerializeValue() {
      return [
         [
            'value'     => 'glpi',
            'expected'  => [2]
         ],
         [
            'value'     => "glpi\r\nnormal",
            'expected'  => [2 => 'glpi', 5 => 'normal']
         ],
         [
            'value'     => "glpi\r\nnormal\r\nuser@localhost.local",
            'expected'  => [
               2 => 'glpi',
               5 => 'normal',
               'user@localhost.local' => 'user@localhost.local'
            ]
         ],
         [
            'value'     => 'user@localhost.local',
            'expected'  => ['user@localhost.local' => 'user@localhost.local']
         ],
      ];
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function testSerializeValue($value, $expected) {
      $fieldInstance = new \PluginFormcreatorActorField($fields, $data);
      $output = $fieldInstance->serializeValue($value);
      $this->array($output)->hasKeys(array_keys($expected))
         ->containsValues($expected)
         ->size->isEqualTo(count($expected));
   }
}
