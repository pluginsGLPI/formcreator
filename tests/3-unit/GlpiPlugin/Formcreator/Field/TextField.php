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
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class TextField extends CommonTestCase {

   public function provider() {
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'text',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => '',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'text' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => [
                        'regex' => ''
                     ]
                  ]
               ],
            ],
            'expectedValue'   => '1',
            'expectedIsValid' => true,
            'expectedMessage' => '',
         ],
         [
            'fields'          => [
               'fieldtype'       => 'text',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => 'a',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'text' => [
                     'range' => [
                        'range_min' => '5',
                        'range_max' => '8',
                     ],
                     'regex' => [
                        'regex' => ''
                     ]
                  ]
               ],
            ],
            'expectedValue'   => '1',
            'expectedIsValid' => false,
            'expectedMessage' => 'The text is too short (minimum 5 characters): question',
         ],
         [
            'fields'          => [
               'fieldtype'       => 'text',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => 'short',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'text' => [
                     'range' => [
                        'range_min' => '6',
                        'range_max' => '8',
                     ],
                     'regex' => [
                        'regex' => ''
                     ]
                  ]
               ],
            ],
            'expectedValue'   => '1',
            'expectedIsValid' => false,
            'expectedMessage' => 'The text is too short (minimum 6 characters): question',
         ],
         [
            'fields'          => [
               'fieldtype'       => 'text',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => 'very very long',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'text' => [
                     'range' => [
                        'range_min' => '6',
                        'range_max' => '8',
                     ],
                     'regex' => [
                        'regex' => ''
                     ]
                  ]
               ],
            ],
            'expectedValue'   => '1',
            'expectedIsValid' => false,
            'expectedMessage' => 'The text is too long (maximum 8 characters): question',
         ],
         [
            'fields'          => [
               'fieldtype'       => 'text',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => 'very very long',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'text' => [
                     'range' => [
                        'range_min' => '3',
                        'range_max' => '8',
                     ],
                     'regex' => [
                        'regex' => ''
                     ]
                  ]
               ],
            ],
            'expectedValue'   => '1',
            'expectedIsValid' => false,
            'expectedMessage' => 'The text is too long (maximum 8 characters): question',
         ],
         'regex with escaped chars' => [
            'fields'          => [
               'fieldtype'       => 'text',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => '',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'text' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => [
                        'regex' => '/[0-9]{2}\\\\.[0-9]{3}\\\\.[0-9]{3}\\\\/[0-9]{4}-[0-9]{2}/'
                     ]
                  ]
               ],
            ],
            'expectedValue'   => '',
            'expectedIsValid' => true,
            'expectedMessage' => '',
         ],
      ];
      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testIsValid($fields, $expectedValue, $expectedValidity, $expectedMessage) {
      $section = $this->getSection();
      $fields[$section::getForeignKeyField()] = $section->getID();

      $question = $this->getQuestion($fields);

      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($fields['default_values']);
      $_SESSION["MESSAGE_AFTER_REDIRECT"] = [];

      $isValid = $instance->isValid();
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity, json_encode($_SESSION['MESSAGE_AFTER_REDIRECT'], JSON_PRETTY_PRINT));

      // Check error message
      if (!$isValid) {
         $this->sessionHasMessage($expectedMessage, ERROR);
      } else {
         $this->sessionHasNoMessage();
      }
   }

   public function testGetEmptyParameters() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->getEmptyParameters();
      $this->array($output)
         ->hasKey('range')
         ->hasKey('regex')
         ->array($output)->size->isEqualTo(2);
      $this->object($output['range'])
         ->isInstanceOf(\PluginFormcreatorQuestionRange::class);
      $this->object($output['regex'])
         ->isInstanceOf(\PluginFormcreatorQuestionRegex::class);
   }

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Text');
   }

   public function testIsAnonymousFormCompatible() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isTrue();
   }

   public function providerSerializeValue() {
      return [
         [
            'value' => '',
            'expected' => '',
         ],
         [
            'value' => "quote ' test",
            'expected' => "quote \' test",
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
      $output = $instance->serializeValue();
      $this->string($output)->isEqualTo($expected);
   }

   public function providerDeserializeValue() {
      return [
         [
            'value'     => '',
            'expected'  => '',
         ],
         [
            'value'     => 'foo',
            'expected'  => 'foo' ,
         ],
      ];
   }

   /**
    * @dataProvider providerDeserializeValue
    */
   public function testDeserializeValue($value, $expected) {
      $instance = $this->newTestedInstance($this->getQuestion());
      $instance->deserializeValue($value);
      $output = $instance->getValueForTargetText('', false);
      $this->string($output)->isEqualTo($expected);
   }

   public function testCanRequire() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->canRequire();
      $this->boolean($output)->isTrue();
   }

   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }
}
