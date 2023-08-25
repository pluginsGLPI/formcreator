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

class CheckboxesField extends CommonAbstractFieldTestCase {
   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Checkboxes');
   }

   public function providerGetAvailableValues() {
      return [
         [
            'instance'           => $this->newTestedInstance($this->getQuestion([
               'fieldtype'          => 'checkboxes',
               'values'             => implode('\r\n', ['2']),
               '_parameters'        => [
                  'checkboxes'         => [
                     'range'              => [
                        'range_min'          => '',
                        'range_max'          => '',
                     ]
                  ]
               ],
            ])),
            'expected'   => ['2' => '2'],
         ],
         [
            'instance'           => $this->newTestedInstance($this->getQuestion([
               'fieldtype'          => 'checkboxes',
               'values'             => implode('\r\n', ['3', '5']),
               '_parameters'        => [
                  'checkboxes'         => [
                     'range'              => [
                        'range_min'          => '',
                        'range_max'          => '',
                     ]
                  ]
               ],
            ])),
            'expected'   => ['3' => '3', '5' => '5'],
         ],
      ];
   }

   /**
    * @dataProvider providerGetAvailableValues
    */
   public function testGetAvailableValues($instance, $expected) {
      $output = $instance->getAvailableValues();
      $this->array($output)->isEqualTo($expected);
   }

   public function providerIsValidValue() {
      $instance = $this->newTestedInstance($this->getQuestion([
         'fieldtype' => 'checkboxes',
         'values'    => implode('\r\n', ['1', '2', '3', '4']),
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
         'value'    => '',
         'expected' => true,
      ];
      yield [
         'instance' => $instance,
         'value'    => [],
         'expected' => true,
      ];
      yield [
         'instance' => $instance,
         'value'    => ['1'],
         'expected' => true,
      ];
      yield [
         'instance' => $instance,
         'value'    => ['1', '4'],
         'expected' => true,
      ];
      yield [
         'instance' => $instance,
         'value'    => ['1', '9'],
         'expected' => false,
      ];
      yield [
         'instance' => $instance,
         'value'    => ['9'],
         'expected' => false,
      ];

      // values are escaped by GLPI, then backslashes are doubled
      $instance = $this->newTestedInstance($this->getQuestion([
         'fieldtype' => 'checkboxes',
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
         'value'    => ['X:\\path\\to\\file'],
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
      return [
         [
            'value'     => null,
            'expected'  => '[]',
         ],
         [
            'value'     => '',
            'expected'  => '[]',
         ],
         [
            'value'     => ['foo'],
            'expected'  => '["foo"]',
         ],
         [
            'value'     => ["test d'apostrophe"],
            'expected'  => '["test d\'apostrophe"]',
         ],
         [
            'value'     => ['foo', 'bar'],
            'expected'  => '["foo","bar"]',
         ],
      ];
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function testSerializeValue($value, $expected) {
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $value]);
      $form = $this->getForm();
      $formAnswer = new PluginFormcreatorFormAnswer();
      $formAnswer->add([
         $form::getForeignKeyField() => $form->getID(),
      ]);
      $output = $instance->serializeValue($formAnswer);
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
            'value'     => '["foo"]',
            'expected'  => ['foo'],
         ],
         [
            'value'     => '["test d\'apostrophe"]',
            'expected'  => ["test d'apostrophe"],
         ],
         [
            'value'     => '["foo", "bar"]',
            'expected'  => ['foo', 'bar'],
         ],
      ];
   }

   /**
    * @dataProvider providerDeserializeValue
    */
   public function testDeserializeValue($value, $expected) {
      $question = $this->getQuestion([
         'fieldtype' => 'checkboxes',
         'values' => implode('\r\n', ["foo", "bar","test d'apostrophe"]),
      ]);
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($value);
      $output = $instance->getValueForTargetText('', false);
      $this->string($output)->isEqualTo(implode(', ', $expected));
   }

   public function providerPrepareQuestionInputForSave() {
      global $DB;

      $question = $this->getQuestion([
         'fieldtype'       => 'checkboxes',
         'name'            => 'question',
         'required'        => '0',
         'values'          => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
         'default_values'  => '1\r\n2\r\n3\r\n5\r\n6',
         'order'           => '1',
         'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
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
            'default_values'  => '[\"éè\"]',
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
            'default_values' => '[\"something\"]',
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
            'default_values' => '',
         ],
         'message'  => '',
      ];

      yield 'several default values' => [
         'field'    => $this->newTestedInstance($question),
         'input'    => [
            'values'   => 'a\r\nb\r\nc',
            'name'     => 'foo',
            'default_values' => 'a\r\n\b'
         ],
         'expected' => [
            'values'   => $DB->escape('["a","b","c"]'),
            'name'     => 'foo',
            'default_values' => $DB->escape('["a","b"]'),
         ],
         'message'  => '',
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
            'default_values' => $DB->escape('["b"]'),
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
         'message'  => 'The default values are not in the list of available values.',
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

      return;

      $question = $this->getQuestion([
        'fieldtype'       => 'checkboxes',
        'name'            => 'question',
        'required'        => '0',
        'default_values'  => json_encode(['1', '2', '3', '5', '6']),
        'values'          => json_encode(['1', '2', '3', '4', '5', '6']),
        'order'           => '1',
        'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
        'range_min'       => 3,
        'range_max'       => 4,
      ]);
      $fieldInstance = $this->newTestedInstance($question);

      // Test a value is mandatory
      $input = [
        'values'          => "",
        'name'            => 'foo',
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->integer(count($out))->isEqualTo(0);

      // Test accented chars are kept
      $input = [
        'values'          => 'éè\r\nsomething else',
        'default_values'  => 'éè',
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->string($out['values'])->isEqualTo('[\"éè\",\"something else\"]');
      $this->string($out['default_values'])->isEqualTo('[\"éè\"]');

      // Test values are trimmed
      $input = [
        'values'          => ' something \r\n  something else  ',
        'default_values'  => ' something      ',
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->string($out['values'])->isEqualTo('[\"something\",\"something else\"]');
      $this->string($out['default_values'])->isEqualTo('[\"something\"]');
   }

   /**
    * @engine  inline
    */
   public function testGetEmptyParameters() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->getEmptyParameters();
      $this->array($output)
         ->hasKey('range')
         ->array($output)->size->isEqualTo(1);
      $this->object($output['range'])
         ->isInstanceOf(\PluginFormcreatorQuestionRange::class);
   }

   public function testisPublicFormCompatible() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isPublicFormCompatible();
      $this->boolean($output)->isTrue();
   }

   public function providerGetValueForTargetText() {
      $fieldtype = 'checkboxes';

      yield [
         'question' => $this->getQuestion([
            'fieldtype' => $fieldtype,
            'values' => "[]"
         ]),
         'value' => 'a',
         'expectedValue' => '',
      ];

      yield [
         'question' => $this->getQuestion([
            'fieldtype' => $fieldtype,
            'values' => 'a\r\nb\r\nc'
         ]),
         'value' => json_encode(['a']),
         'expectedValue' => 'a',
      ];

      yield [
         'question' => $this->getQuestion([
            'fieldtype' => $fieldtype,
            'values' => 'a\r\nb\r\nc'
         ]),
         'value' => json_encode(['a', 'c']),
         'expectedValue' => 'a, c',
         'expectedRichValue' => 'a<br />c'
      ];
   }

   public function providerGetValueForDesign() {
      $fieldtype = 'checkboxes';

      return [
         [
            'question' => $this->getQuestion([
               'fieldtype' => $fieldtype,
               'values' => 'a\r\nb\r\nc'
            ]),
            'value' => json_encode(['a']),
            'expected' => 'a'
         ],
         [
            'question' => $this->getQuestion([
               'fieldtype' => $fieldtype,
               'values' => 'a\r\nb\r\nc'
            ]),
            'value' => json_encode(['a', 'c']),
            'expected' => "a\r\nc"
         ],
      ];
   }

   /**
    * @dataprovider providerGetValueForDesign
    */
   public function testGetValueForDesign($question, $value, $expected) {
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($value);

      $output = $instance->getValueForDesign(true);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerParseAnswerValues() {
      return [
         [
            'input' => ['a', 'c'],
            'expected' => '["a","c"]',
         ],
         [
            'input' => ['a', "test d\'apostrophe"],
            'expected' => '["a","test d\'apostrophe"]',
         ],
      ];
   }

   /**
    * @dataprovider providerParseAnswerValues
    */
   public function testParseAnswerValues($input, $expected) {
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question);
      $instance->parseAnswerValues([
         'formcreator_field_' . $question->getID() => $input
      ]);

      $form = $this->getForm();
      $formAnswer = new PluginFormcreatorFormAnswer();
      $formAnswer->add([
         $form::getForeignKeyField() => $form->getID(),
      ]);
      $output = $instance->serializeValue($formAnswer);
      $this->string($output)->isEqualTo($expected);
   }

   public  function providerEquals() {
      return [
         [
            'question' => $this->getQuestion([
               'values' => ""
            ]),
            'value' => "",
            'compare' => '',
            'expected' => false
         ],
         [
            'question' => $this->getQuestion([
               'values' => json_encode(['a', 'b', 'c'])
            ]),
            'value' => json_encode(['a', 'c']),
            'compare' => 'b',
            'expected' => false
         ],
         [
            'question' => $this->getQuestion([
               'values' => json_encode(['a', 'b', 'c'])
            ]),
            'value' => json_encode(['a', 'c']),
            'compare' => 'a',
            'expected' => true
         ],
         [
            'question' => $this->getQuestion([
               'values' => json_encode(['a', 'b', 'c'])
            ]),
            'value' => json_encode(['a', 'c']),
            'compare' => 'c',
            'expected' => true
         ],
      ];
   }

   /**
    * @dataprovider providerEquals
    */
   public function testEquals($question, $value, $compare, $expected) {
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
   public function testNotEquals($question, $value, $compare, $expected) {
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($value);

      $output = $instance->notEquals($compare);
      $this->boolean($output)->isEqualTo(!$expected);
   }

   public function testIsPrerequisites() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(true);
   }

   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }

   public function testCanRequire() {
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question);
      $output = $instance->canRequire();
      $this->boolean($output)->isTrue();
   }

   public function providerGetValueForApi() {
      return [
         [
            'input'    => json_encode([
               "a (checkbox)",
               "c (checkbox)"
            ]),
            'expected' => [
               "a (checkbox)",
               "c (checkbox)"
            ]
         ]
      ];
   }

   /**
    * @dataProvider providerGetValueForApi
    *
    * @return void
    */
   public function testGetValueForApi($input, $expected) {
      $question = $this->getQuestion([
         'values'    => json_encode(["a (checkbox)","b (checkbox)","c (checkbox)"], JSON_OBJECT_AS_ARRAY)
      ]);

      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($input);
      $output = $instance->getValueForApi();
      $this->array($output)->isEqualTo($expected);
   }
}
