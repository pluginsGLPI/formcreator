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

class FloatField extends CommonTestCase {

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Float');
   }

   public function provider() {
      $dataset = [
         'empty value' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => '',
                        'range_max'       => '',
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ]
            ],
            'expectedValue'   => '',
            'expectedIsValid' => true,
            'expectedMessage' => '',
         ],
         'integer value' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '2',
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => '',
                        'range_max'       => '',
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ]
            ],
            'expectedValue'   => '2',
            'expectedIsValid' => true,
            'expectedMessage' => '',
         ],
         'too low value' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "2",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => 3,
                        'range_max'       => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ]
            ],
            'expectedValue'   => '2',
            'expectedIsValid' => false,
            'expectedMessage' => 'The following number must be greater than 3: question',
          ],
         'too high value' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "5",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => 3,
                        'range_max'       => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ]
            ],
            'expectedValue'   => '5',
            'expectedIsValid' => false,
            'expectedMessage' => 'The following number must be lower than 4: question',
         ],
         'float iin range' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "3.141592",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => 3,
                        'range_max'       => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ]
            ],
            'expectedValue'   => '3.141592',
            'expectedIsValid' => true,
            'expectedMessage' => '',
         ],
         'empty value and regex with backslash' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => '',
                        'range_max'       => '',
                     ],
                     'regex' => ['regex' => '/[0-9]{2}\\\\.[0-9]{3}/'],
                  ]
               ]
            ],
            'expectedValue'   => '',
            'expectedIsValid' => true,
            'expectedMessage' => '',
         ],
         'value not matching regex' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "1.234",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => '',
                        'range_max'       => '',
                     ],
                     'regex' => ['regex' => '/[0-9]{2}\\\\.[0-9]{3}/'],
                  ]
               ]
            ],
            'expectedValue'   => '',
            'expectedIsValid' => false,
            'expectedMessage' => 'Specific format does not match: question',
         ],
         'value matching regex' => [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "12.345",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => '',
                        'range_max'       => '',
                     ],
                     'regex' => ['regex' => '/[0-9]{2}\\\\.[0-9]{3}/'],
                  ]
               ]
            ],
            'expectedValue'   => '',
            'expectedIsValid' => true,
            'expectedMessage' => '',
         ],
         [
            'fields'          => [
               'fieldtype'       => 'float',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'float' => [
                     'range' => [
                        'range_min'       => '',
                        'range_max'       => '',
                     ],
                     'regex' => ['regex' => '/[0-9]{2}\\\\.[0-9]{3}\\\\.[0-9]{3}\\\\/[0-9]{4}-[0-9]{2}/'],
                  ]
               ]
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

      $question = new \PluginFormcreatorQuestion();
      $question->add($fields);
      $this->boolean($question->isNewItem())->isFalse(json_encode($_SESSION['MESSAGE_AFTER_REDIRECT'], JSON_PRETTY_PRINT));

      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($fields['default_values']);
      $_SESSION["MESSAGE_AFTER_REDIRECT"] = [];

      $isValid = $instance->isValid();
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);

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

   public function testIsAnonymousFormCompatible() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isAnonymousFormCompatible();
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


   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }
}
