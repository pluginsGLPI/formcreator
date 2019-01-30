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

class PluginFormcreatorDateField extends CommonTestCase {

   public function providerGetValue() {
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'date',
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
               'fieldtype'       => 'date',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '2018-08-16',
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
            'expectedValue'   => '2018-08-16',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'date',
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
               'fieldtype'       => 'date',
               'name'            => 'question',
               'required'        => '1',
               'default_values'  => '2018-08-16',
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
            'expectedValue'   => '2018-08-16',
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
      $output = \PluginFormcreatorDateField::getName();
      $this->string($output)->isEqualTo('Date');
   }

   public function testIsAnonymousFormCompatible() {
      $instance = $this->newTestedInstance([]);
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isTrue();
   }

   public function testIsPrerequisites() {
      $instance = $this->newTestedInstance([]);
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(true);
   }

   public function testSerializeValue() {
      $value = $expected = '2019-01-01';
      $instance = $this->newTestedInstance(['id' => 1]);
      $instance->parseAnswerValues(['formcreator_field_1' => $value]);
      $output = $instance->serializeValue();
      $this->string($output)->isEqualTo($expected);
   }

   public function testGetValueForDesign() {
      $value = $expected = '2019-01-01';
      $instance = new \PluginFormcreatorDateField([]);
      $instance->deserializeValue($value);
      $output = $instance->getValueForDesign();
      $this->string($output)->isEqualTo($expected);
   }

   public function providerEquals() {
      return [
         [
            'value'     => '2019-01-01',
            'answer'    => '',
            'expected'  => false,
         ],
         [
            'value'     => '2019-01-01',
            'answer'    => '2018-01-01',
            'expected'  => false,
         ],
         [
            'value'     => '2019-01-01',
            'answer'    => '2019-01-01',
            'expected'  => true,
         ],
      ];
   }

   /**
    * @dataProvider providerEquals
    */
   public function testEquals($value, $answer, $expected) {
      $instance = new \PluginFormcreatorDateField(['id' => '1']);
      $instance->parseAnswerValues(['formcreator_field_1' => $answer]);
      $this->boolean($instance->equals($value))->isEqualTo($expected);
   }

   public function providerNotEquals() {
      return [
         [
            'value'     => '2019-01-01',
            'answer'    => '',
            'expected'  => true,
         ],
         [
            'value'     => '2019-01-01',
            'answer'    => '2018-01-01',
            'expected'  => true,
         ],
         [
            'value'     => '2019-01-01',
            'answer'    => '2019-01-01',
            'expected'  => false,
         ],

      ];
   }

   /**
    * @dataProvider providerNotEquals
    */
   public function testNotEquals($value, $answer, $expected) {
      $instance = new \PluginFormcreatorDateField(['id' => '1'], $answer);
      $instance->parseAnswerValues(['formcreator_field_1' => $answer]);
      $this->boolean($instance->notEquals($value))->isEqualTo($expected);
   }

   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance([]);
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }


}
