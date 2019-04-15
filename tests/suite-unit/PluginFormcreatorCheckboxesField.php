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

class PluginFormcreatorCheckboxesField extends CommonTestCase {
   public function testGetName() {
      $output = \PluginFormcreatorCheckboxesField::getName();
      $this->string($output)->isEqualTo('Checkboxes');
   }

   public function providerGetAvailableValues() {
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'checkboxes',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       =>\PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'checkboxes' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => [''],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'checkboxes',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '2',
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       =>\PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'checkboxes' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['2'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'checkboxes',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "3\r\n5",
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       =>\PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'checkboxes' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3', '5'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'checkboxes',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "3\r\n5",
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       =>\PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'checkboxes' => [
                     'range' => [
                        'range_min' => '3',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3', '5'],
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'checkboxes',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "3\r\n5\r\n6",
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       =>\PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'checkboxes' => [
                     'range' => [
                        'range_min' => '3',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3', '5', '6'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'checkboxes',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "1\r\n2\r\n3\r\n5\r\n6",
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       =>\PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'checkboxes' => [
                     'range' => [
                        'range_min' => '3',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['1', '2', '3', '5', '6'],
            'expectedIsValid' => false
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider providerGetAvailableValues
    */
   public function testGetAvailableValues($fields, $data, $expectedValue, $expectedValidity) {
      $instance = new \PluginFormcreatorCheckboxesField($fields, $data);
      $instance->deserializeValue($fields['default_values']);

      $availableValues = $instance->getAvailableValues();
      $expectedAvaliableValues = explode("\r\n", $fields['values']);

      $this->array($availableValues)->hasSize(count($expectedAvaliableValues));

      foreach ($expectedAvaliableValues as $expectedValue) {
         $this->array($availableValues)->contains($expectedValue);
      }
   }

   public function providerIsValid() {
      return $this->providerGetAvailableValues();
   }

   /**
    * @dataProvider providerIsValid
    */
   public function testIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $section = $this->getSection();
      $fields[$section::getForeignKeyField()] = $section->getID();

      $question = new \PluginFormcreatorQuestion();
      $question->add($fields);
      $question->updateParameters($fields);

      $instance = new \PluginFormcreatorCheckboxesField($question->fields, $data);
      $instance->deserializeValue($fields['default_values']);

      $isValid = $instance->isValid();
      $this->boolean($isValid)->isEqualTo($expectedValidity);
   }

   public function providerSerializeValue() {
      return [
         [
            'value'     => null,
            'expected'  => '',
         ],
         [
            'value'     => '',
            'expected'  => '',
         ],
         [
            'value'     => ['foo'],
            'expected'  => 'foo',
         ],
         [
            'value'     => ["test d'apostrophe"],
            'expected'  => "test d\'apostrophe",
         ],
         [
            'value'     => ['foo', 'bar'],
            'expected'  => "foo\r\nbar",
         ],
      ];
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function testSerializeValue($value, $expected) {
      $instance = new \PluginFormcreatorCheckboxesField(['id' => 1]);
      $instance->parseAnswerValues(['formcreator_field_1' => $value]);
      $output = $instance->serializeValue();
      $this->string($output)->isEqualTo($expected);
   }

   public function providerDeserializeValue() {
      return [
         [
            'value'     => null,
            'expected'  => [],
         ],
         [
            'value'     => '',
            'expected'  => [],
         ],
         [
            'value'     => "foo",
            'expected'  => ['foo'],
         ],
         [
            'value'     => "test d'apostrophe",
            'expected'  => ["test d'apostrophe"],
         ],
         [
            'value'     => "foo\r\nbar",
            'expected'  => ['foo', 'bar'],
         ],
      ];
   }

   /**
    * @dataProvider providerDeserializeValue
    */
   public function testDeserializeValue($value, $expected) {
      $instance = new \PluginFormcreatorCheckboxesField(['values' => "foo\r\nbar\r\ntest d'apostrophe"]);
      $instance->deserializeValue($value);
      $output = $instance->getValueForTargetText(false);
      $this->string($output)->isEqualTo(implode(', ', $expected));
   }

   public function testPrepareQuestionInputForSave() {
      $fields = [
         'fieldtype'       => 'checkboxes',
         'name'            => 'question',
         'required'        => '0',
         'default_values'  => "1\r\n2\r\n3\r\n5\r\n6",
         'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
         'order'           => '1',
         'show_rule'       =>\PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
         'range_min'       => 3,
         'range_max'       => 4,
      ];
      $fieldInstance = $this->newTestedInstance($fields);

      // Test a value is mandatory
      $input = [
         'values'          => "",
         'name'            => 'foo',
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->integer(count($out))->isEqualTo(0);

      // Test accented chars are kept
      $input = [
         'values'          => "éè\r\nsomething else",
         'default_values'  => "éè",
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->string($out['values'])->isEqualTo("éè\r\nsomething else");
      $this->string($out['default_values'])->isEqualTo("éè");

      // Test values are trimmed
      $input = [
         'values'          => ' something \r\n  something else  ',
         'default_values'  => " something      ",
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->string($out['values'])->isEqualTo('something\r\nsomething else');
      $this->string($out['default_values'])->isEqualTo("something");
   }

   /**
    * @engine  inline
    */
   public function testGetEmptyParameters() {
      $instance = $this->newTestedInstance([]);
      $output = $instance->getEmptyParameters();
      $this->array($output)
         ->hasKey('range')
         ->array($output)->size->isEqualTo(1);
      $this->object($output['range'])
         ->isInstanceOf(\PluginFormcreatorQuestionRange::class);
   }

   public function testIsAnonymousFormCompatible() {
      $instance = new \PluginFormcreatorCheckboxesField([]);
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isTrue();
   }

   public function providerGetValueForTargetText() {
      return [
         [
            'fields' => [
               'values' => ""
            ],
            'value' => "a",
            'expected' => '<br />'
         ],
         [
            'fields' => [
               'values' => "a\r\nb\r\nc"
            ],
            'value' => "a",
            'expected' => '<br />a'
         ],
         [
            'fields' => [
               'values' => "a\r\nb\r\nc"
            ],
            'value' => "a\r\nc",
            'expected' => '<br />a<br />c'
         ],
      ];
   }

   /**
    * @dataprovider providerGetValueForTargetText
    */
   public function testGetValueForTargetText($fields, $value, $expected) {
      $instance = $this->newTestedInstance($fields);
      $instance->deserializeValue($value);

      $output = $instance->getValueForTargetText(true);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerGetValueForDesign() {
      return [
         [
            'fields' => [
               'values' => ""
            ],
            'value' => "",
            'expected' => ''
         ],
         [
            'fields' => [
               'values' => "a\r\nb\r\nc"
            ],
            'value' => "a",
            'expected' => 'a'
         ],
         [
            'fields' => [
               'values' => "a\r\nb\r\nc"
            ],
            'value' => "a\r\nc",
            'expected' => "a\r\nc"
         ],
      ];
   }

   /**
    * @dataprovider providerGetValueForDesign
    */
   public function testGetValueForDesign($fields, $value, $expected) {
      $instance = $this->newTestedInstance($fields);
      $instance->deserializeValue($value);

      $output = $instance->getValueForDesign(true);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerParseAnswerValues() {
      return [
         [
            'input' => ['a', 'c'],
            'expected' => "a\r\nc",
         ],
         [
            'input' => ['a', "test d\'apostrophe"],
            'expected' => "a\r\ntest d\'apostrophe",
         ],
      ];
   }

   /**
    * @dataprovider providerParseAnswerValues
    */
   public function testParseAnswerValues($input, $expected) {
      $instance = $this->newTestedInstance([
         'id' => 1
      ]);
      $instance->parseAnswerValues([
         'formcreator_field_1' => $input
      ]);

      $output = $instance->serializeValue();
      $this->string($output)->isEqualTo($expected);
   }

   public  function providerEquals() {
      return [
         [
            'fields' => [
               'values' => ""
            ],
            'value' => "",
            'compare' => '',
            'expected' => false
         ],
         [
            'fields' => [
               'values' => "a\r\nb\r\nc"
            ],
            'value' => "a\r\nc",
            'compare' => 'b',
            'expected' => false
         ],
         [
            'fields' => [
               'values' => "a\r\nb\r\nc"
            ],
            'value' => "a\r\nc",
            'compare' => 'a',
            'expected' => true
         ],
         [
            'fields' => [
               'values' => "a\r\nb\r\nc"
            ],
            'value' => "a\r\nc",
            'compare' => 'c',
            'expected' => true
         ],
      ];
   }

   /**
    * @dataprovider providerEquals
    */
   public function testEquals($fields, $value, $compare, $expected) {
      $instance = $this->newTestedInstance($fields);
      $instance->deserializeValue($value);

      $output = $instance->equals($compare);
      $this->boolean($output)->isEqualTo($expected);
   }

   public function providerNotEquals() {
      return $this->providerEquals();
   }

   /**
    * @dataprovider providerNotEquals
    */
   public function testNotEquals($fields, $value, $compare, $expected) {
      $instance = $this->newTestedInstance($fields);
      $instance->deserializeValue($value);

      $output = $instance->notEquals($compare);
      $this->boolean($output)->isEqualTo(!$expected);
   }

   public function testIsPrerequisites() {
      $instance = $this->newTestedInstance([]);
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(true);
   }

   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance([]);
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }

   public function testCanRequire() {
      $instance = new \PluginFormcreatorCheckboxesField([
         'id' => '1',
      ]);
      $output = $instance->canRequire();
      $this->boolean($output)->isTrue();
   }
}
