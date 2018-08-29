<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorDatetimeField extends CommonTestCase {

   public function providerGetValue() {
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'datetime',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'date' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => null,
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'datetime',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '2018-08-16 08:12:34',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'date' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => '2018-08-16 08:12:34',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'datetime',
               'name'            => 'question',
               'required'        => '1',
               'default_values'  => '',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'date' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => null,
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'datetime',
               'name'            => 'question',
               'required'        => '1',
               'default_values'  => '2018-08-16 08:12:34',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'date' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => '2018-08-16 08:12:34',
            'expectedIsValid' => true
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider providerGetValue
    */
   public function testGetValue($fields, $data, $expectedValue, $expectedValidity) {
      $instance = $this->newTestedInstance($fields, $data);

      $value = $instance->getValue();
      $this->variable($value)->isEqualTo($expectedValue);
   }

   public function providerGetAnswer() {
      return $this->providerGetValue();
   }

   /**
    * @dataProvider providerGetAnswer
    * @engine inline
    */
   public function testGetAnswer($fields, $data, $expectedValue, $expectedValidity) {
      $instance = $this->newTestedInstance($fields, $data);

      $expected =  \Html::convDateTime($fields['default_values']);
      // FIXME: this shows an inconsistency with this field type
      if ($expected == ' ') {
         $expected = null;
      }
      $output = $instance->getAnswer();
      $this->variable($output)->isEqualTo($expected);
   }

   public function providerIsValid() {
      return $this->providerGetValue();
   }

   /**
    * @dataProvider providerIsValid
    */
   public function testIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $instance = $this->newTestedInstance($fields, $data);

      $isValid = $instance->isValid($fields['default_values']);
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
   }

   public function testGetName() {
      $output = \PluginFormcreatorDatetimeField::getName();
      $this->string($output)->isEqualTo('Date & time');
   }
}