<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
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
               'show_rule'       =>\PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
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
               'show_rule'       =>\PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
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
               'show_rule'       =>\PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
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
               'show_rule'       =>\PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
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

   public function providerIsValid() {
      return $this->providerGetValue();
   }

   /**
    * @dataProvider providerIsValid
    */
   public function testIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $instance = $this->newTestedInstance($fields, $data);
      $instance->deserializeValue($fields['default_values']);

      $isValid = $instance->isValid();
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
   }

   public function testGetName() {
      $output = \PluginFormcreatorDatetimeField::getName();
      $this->string($output)->isEqualTo('Date & time');
   }

   public function providerparseAnswerValues() {
      return [
         [
            'id' => '1',
            'input' => [
               'formcreator_field_1' => ''
            ],
            'expected' => true,
            'expectedValue' => ' ',
         ],
         [
            'id' => '1',
            'input' => [
               'formcreator_field_1' => '2018-12-25 23:00',
            ],
            'expected' => true,
            'expectedValue' => '2018-12-25 23:00',
         ],
      ];
   }

   /**
    * Undocumented function
    *
    * @dataProvider providerparseAnswerValues
    *
    * @param [type] $id
    * @param [type] $input
    * @param [type] $expected
    * @param [type] $expectedValue
    * @return void
    */
   public function testParseAnswerValues($id, $input, $expected, $expectedValue) {
      $instance = $this->newTestedInstance(['id' => $id]);
      $output = $instance->parseAnswerValues($input);
      $this->boolean($output)->isEqualTo($expected);

      $outputValue = $instance->getValueForTargetText(false);
      if ($expected === false) {
         $this->variable($outputValue)->isNull();
      } else {
         $this->string($outputValue)
            ->isEqualTo($expectedValue);
      }
   }

   public function providerSerializeValue() {
      return [
         [
            'id' => '1',
            'input' => []
         ]
      ];
   }

   public function testSerializeValue() {
      $instance = $this->newTestedInstance([]);
   }


   public function testIsAnonymousFormCompatible() {
      $instance = new \PluginFormcreatorDatetimeField([]);
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isTrue();
   }

   public function testIsPrerequisites() {
      $instance = $this->newTestedInstance([]);
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(true);
   }
}
