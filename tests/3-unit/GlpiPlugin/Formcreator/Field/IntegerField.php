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

class IntegerField extends CommonTestCase {

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Integer');
   }

   public function providerIsValid() {
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ],
            'expectedValue'   => '',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '2',
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ],
            'expectedValue'   => '2',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "2",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => 3,
                        'range_max' => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ],
            'expectedValue'   => '2',
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "5",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => 3,
                        'range_max' => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ],
            'expectedValue'   => '5',
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "3.4",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => 3,
                        'range_max' => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ],
            'expectedValue'   => '3.4',
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "4",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => 3,
                        'range_max' => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ],
            'expectedValue'   => '4',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => ['regex' => '/[0-9]{2}\\\\.[0-9]{3}\\\\.[0-9]{3}\\\\/[0-9]{4}-[0-9]{2}/'],
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => '4',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => ['regex' => '/[0-9]{2}\\\\.[0-9]{3}\\\\.[0-9]{3}\\\\/[0-9]{4}-[0-9]{2}/'],
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => '4',
            'expectedIsValid' => true
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider providerIsValid
    */
   public function testIsValid($fields, $expectedValue, $expectedValidity) {
      $section = $this->getSection();
      $fields[$section::getForeignKeyField()] = $section->getID();

      $question = $this->getQuestion($fields);

      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($fields['default_values']);

      $isValid = $instance->isValid();
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
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
