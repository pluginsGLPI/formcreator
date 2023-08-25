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
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace GlpiPlugin\Formcreator\Field\tests\units;
use GlpiPlugin\Formcreator\Tests\CommonAbstractFieldTestCase;
use PluginFormcreatorFormAnswer;
use PluginFormcreatorCondition;

class RadiosField extends CommonAbstractFieldTestCase {

   public function providerPrepareQuestionInputForSave() {
      global $DB;

      $question = $this->getQuestion([
         'fieldtype'       => 'radios',
         'name'            => 'question',
         'required'        => '0',
         'default_values'  => '1',
         'values'          => '1',
         'order'           => '1',
         'show_rule'       => PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
         'range_min'       => 3,
         'range_max'       => 4,
      ]);

      yield [
         'field'    => $this->newTestedInstance($question),
         'input'    => [
            'values'   => "",
            'name'     => 'foo',
         ],
         'expected' => [],
         'message'  => 'The field value is required.',
      ];

      yield [
         'field'    => $this->newTestedInstance($question),
         'input'    => [
            'values'          => 'éè\r\nsomething else',
            'default_values'  => 'éè',
         ],
         'expected' => [
            'values' => '[\"éè\",\"something else\"]',
            'default_values' => 'éè',
         ],
         'message'  => '',
      ];

      yield [
         'field'    => $this->newTestedInstance($question),
         'input'    => [
            'values'          => ' something \r\n  something else  ',
            'default_values'  => ' something      ',
         ],
         'expected' => [
            'values' => '[\"something\",\"something else\"]',
            'default_values' => 'something',
         ],
         'message'  => '',
      ];

      yield 'no default value' => [
         'field'    => $this->newTestedInstance($question),
         'input'    => [
            'values'   => 'a\r\nb\r\nc',
            'name'     => 'foo',
            'default_values' => ''
         ],
         'expected' => [
            'values'   => $DB->escape('["a","b","c"]'),
            'name'     => 'foo',
            'default_values' => null,
         ],
         'message'  => '',
      ];

      yield 'several default values not allowed' => [
         'field'    => $this->newTestedInstance($question),
         'input'    => [
            'values'   => 'a\r\nb\r\nc',
            'name'     => 'foo',
            'default_values' => 'a\r\n\b'
         ],
         'expected' => [],
         'message'  => 'Only one default value is allowed.',
      ];

      yield 'one default value' => [
         'field'    => $this->newTestedInstance($question),
         'input'    => [
            'values'   => 'a\r\nb\r\nc',
            'name'     => 'foo',
            'default_values' => 'b'
         ],
         'expected' => [
            'values'   => $DB->escape('["a","b","c"]'),
            'name'     => 'foo',
            'default_values' => 'b'
         ],
         'message'  => '',
      ];

      yield 'invalid default value' => [
         'field'    => $this->newTestedInstance($question),
         'input'    => [
            'values'   => 'a\r\nb\r\nc',
            'name'     => 'foo',
            'default_values' => 'z'
         ],
         'expected' => [],
         'message'  => 'The default value is not in the list of available values.',
      ];
   }

   /**
    * @dataProvider providerPrepareQuestionInputForSave
    *
    * @return void
    */
   public function testPrepareQuestionInputForSave($field, $input, $expected, $message) {
      // Clean error messages
      $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];

      $output = $field->prepareQuestionInputForSave($input);
      if ($expected === false || is_array($expected) && count($expected) == 0) {
         $this->array($output)->hasSize(0);
         $this->sessionHasMessage($message, ERROR);
         //End of test on expected failure
         return;
      }

      $this->array($output)->isEqualTo($expected);
   }

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Radios');
   }


   public function testisPublicFormCompatible() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isPublicFormCompatible();
      $this->boolean($output)->isTrue();
   }

   public function testIsPrerequisites() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(true);
   }

   public function testCanRequire() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->canRequire();
      $this->boolean($output)->isTrue();
   }

   public function providerIsValidValue() {
      $instance = $this->newTestedInstance($this->getQuestion([
         'fieldtype' => 'radios',
         'values'    => implode('\r\n', ['1', '2', '3', '4']),
      ]));
      yield [
         'instance' => $instance,
         'value'    => '',
         'expected' => true,
      ];
      yield [
         'instance' => $instance,
         'value'    => '1',
         'expected' => true,
      ];
      yield [
         'instance' => $instance,
         'value'    => '9',
         'expected' => false,
      ];

      // values are escaped by GLPI, then backslashes are doubled
      $instance = $this->newTestedInstance($this->getQuestion([
         'fieldtype' => 'radios',
         'values'    => implode('\r\n', ['X:\\\\path\\\\to\\\\file', 'nothing']),
         '_parameters'        => [
            'checkboxes'         => [
               'range'              => [
                  'range_min'          => '',
                  'range_max'          => '',
               ]
            ]
         ],
      ]));
      yield [
         'instance' => $instance,
         'value'    => 'X:\\path\\to\\file',
         'expected' => true,
      ];
   }

   /**
    * @dataProvider providerIsValidValue
    */
   public function testIsValidValue($instance, $value, $expected) {
      $output = $instance->isValidValue($value);
      $this->boolean($output)->isEqualTo($expected);
   }

   public function providerSerializeValue() {
      $question = $this->getQuestion([
         'fieldtype' => 'radios',
         'values' => json_encode(['foo', 'bar', 'test d\'apostrophe'])
      ]);
      $instance = $this->newTestedInstance($question);
      return [
         [
            'instance'  => $instance,
            'value'     => null,
            'expected'  => '',
         ],
         [
            'instance'  => $instance,
            'value'     => '',
            'expected'  => '',
         ],
         [
            'instance'  => $instance,
            'value'     => 'foo',
            'expected'  => 'foo',
         ],
         [
            'instance'  => $instance,
            'value'     => "test d'apostrophe",
            'expected'  => 'test d\'apostrophe',
         ],
      ];
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function testSerializeValue($instance, $value, $expected) {
      $instance->parseAnswerValues(['formcreator_field_' . $instance->getQuestion()->getID() => $value]);
      $form = $this->getForm();
      $formAnswer = new PluginFormcreatorFormAnswer();
      $formAnswer->add([
         $form::getForeignKeyField() => $form->getID(),
      ]);
      $output = $instance->serializeValue($formAnswer);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerDeserializeValue() {
      $question = $this->getQuestion([
         'fieldtype' => 'radios',
         'values' => json_encode(['foo', 'bar', 'test d\'apostrophe'])
      ]);
      $instance = $this->newTestedInstance($question);
      return [
         [
            'instance'  => $instance,
            'value'     => null,
            'expected'  => '',
         ],
         [
            'instance'  => $instance,
            'value'     => '',
            'expected'  => '',
         ],
         [
            'instance'  => $instance,
            'value'     => "foo",
            'expected'  => 'foo',
         ],
         [
            'instance'  => $instance,
            'value'     => "test d'apostrophe",
            'expected'  => "test d'apostrophe",
         ],
      ];
   }

   /**
    * @dataProvider providerDeserializeValue
    */
   public function testDeserializeValue($instance, $value, $expected) {
      $instance->parseAnswerValues(['formcreator_field_' . $instance->getQuestion()->getID() => $value]);
      $instance->deserializeValue($value);
      $output = $instance->getValueForTargetText('', false);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerparseAnswerValues() {
      return [
         [
            'question' => $this->getQuestion(),
            'value' => '',
            'expected' => true,
            'expectedValue' => '',
         ],
         [
            'question' => $this->getQuestion(),
            'value' => 'test d\'apostrophe',
            'expected' => true,
            'expectedValue' => "test d'apostrophe",
         ],
      ];
   }

   /**
    * @dataProvider providerparseAnswerValues
    */
   public function testParseAnswerValues($question, $value, $expected, $expectedValue) {
      $instance = $this->newTestedInstance($question);
      $output = $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $value]);
      $this->boolean($output)->isEqualTo($expected);

      $outputValue = $instance->getValueForTargetText('', false);
      if ($expected === false) {
         $this->variable($outputValue)->isNull();
      } else {
         $this->string($outputValue)
            ->isEqualTo($expectedValue);
      }
   }

   public function providerGetValueForDesign() {
      return [
         [
            'value' => null,
            'expected' => '',
         ],
         [
            'value' => 'foo',
            'expected' => 'foo',
         ],
      ];
   }

   /**
    * @dataProvider providerGetValueForDesign
    */
   public function testGetValueForDesign($value, $expected) {
      $instance = $this->newTestedInstance($this->getQuestion());
      $instance->deserializeValue($value);
      $output = $instance->getValueForDesign();
      $this->string($output)->isEqualTo($expected);
   }

   public function providerIsValid() {
      return [
         [
            'fields' => [
               'fieldtype' => 'radios',
               'values' => 'a\r\nb',
               'required' => false,
            ],
            'value' => '',
            'expected' => true,
         ],
         [
            'fields' => [
               'fieldtype' => 'radios',
               'values' => 'a\r\nb',
               'required' => true,
            ],
            'value' => '',
            'expected' => false,
         ],
      ];
   }

   /**
    * @dataProvider providerIsValid
    */
   public function testIsValid($fields, $value, $expected) {
      $question = $this->getQuestion($fields);
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($value);

      $output = $instance->isValid();
      $this->boolean($output)->isEqualTo($expected);
   }

   public  function providerEquals() {
      return [
         [
            'fields' => [
               'values' => ""
            ],
            'value' => "",
            'compare' => '',
            'expected' => true
         ],
         [
            'fields' => [
               'values' => json_encode(['a', 'b', 'c'])
            ],
            'value' => "a",
            'compare' => 'b',
            'expected' => false
         ],
         [
            'fields' => [
               'values' => json_encode(['a', 'b', 'c'])
            ],
            'value' => "a",
            'compare' => 'a',
            'expected' => true
         ],
      ];
   }

   /**
    * @dataprovider providerEquals
    */
   public function testEquals($fields, $value, $compare, $expected) {
      $question = $this->getQuestion($fields);
      $instance = $this->newTestedInstance($question);
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
      $question = $this->getQuestion($fields);
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($value);

      $output = $instance->notEquals($compare);
      $this->boolean($output)->isEqualTo(!$expected);
   }

   public function providerGetValueForApi() {
      return [
         [
            'input'    => 'b (radio)',
            'expected' => 'b (radio)',
         ],
      ];
   }

   /**
    * @dataProvider providerGetValueForApi
    *
    * @return void
    */
   public function testGetValueForApi($input, $expected) {
      $question = $this->getQuestion([
      ]);

      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($input);
      $output = $instance->getValueForApi();
      $this->string($output)->isEqualTo($expected);
   }

   public function providerGetValueForTargetText() {
      $fieldtype = 'select';
      yield [
         'question' => $this->getQuestion([
            'fieldtype' => $fieldtype,
            'values'    => 'foo\r\nbar',
         ]),
         'value' => '',
         'expectedValue' => '',
      ];

      yield [
         'question' => $this->getQuestion([
            'fieldtype' => $fieldtype,
            'values'    => 'foo\r\nbar',
         ]),
         'value' => 'foo',
         'expectedValue' => 'foo',
      ];

      yield [
         'question' => $this->getQuestion([
            'fieldtype' => $fieldtype,
            'values'    => 'foo &#62; baz\r\nbar', // Saved sanitized in DB
         ]),
         'value' => 'foo &#62; baz', // Sanitized when used in a form
         'expectedValue' => 'foo > baz',
      ];
   }
}
