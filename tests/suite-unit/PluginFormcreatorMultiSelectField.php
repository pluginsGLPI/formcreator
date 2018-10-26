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
 *
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorMultiSelectField extends CommonTestCase {

   public function provider() {
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'multiselect',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => '',
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => [],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'multiselect',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => '3',
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'multiselect',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => '3',
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '2',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3'],
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'multiselect',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => "3\r\n4",
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '2',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3', '4'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'multiselect',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => "3\r\n4\r\n2\r\n1\r\n6",
               'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
               'order'           => '1',
               'show_rule'       => 'always',
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '2',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'data'            => null,
            'expectedValue'   => ['3', '4', '2', '1', '6'],
            'expectedIsValid' => false
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testGetAvailableValues($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorMultiSelectField($fields, $data);

      $availableValues = $fieldInstance->getAvailableValues();
      $expectedAvaliableValues = explode("\r\n", $fields['values']);

      $this->integer(count($availableValues))->isEqualTo(count($expectedAvaliableValues));
      foreach ($expectedAvaliableValues as $expectedValue) {
         $this->array($availableValues)->contains($expectedValue);
      }
   }

   /**
    * @dataProvider provider
    */
   public function testIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $section = $this->getSection();
      $fields[$section::getForeignKeyField()] = $section->getID();

      $question = new \PluginFormcreatorQuestion();
      $question->add($fields);
      // Re-load the question from the DB
      $question->getFromDB($question->getID());
      $question->updateParameters($fields);

      $instance = new \PluginFormcreatorMultiSelectField($question->fields, $data);
      $instance->deserializeValue($fields['default_values']);
      $isValid = $instance->isValid();
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
   }

   public function testPrepareQuestionInputForSave() {
      $fields = [
         'fieldtype'       => 'multiselect',
         'name'            => 'question',
         'required'        => '0',
         'default_values'  => "1\r\n2\r\n3\r\n5\r\n6",
         'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
         'order'           => '1',
         'show_rule'       => 'always',
         '_parameters'     => [
            'multiselect' => [
               'range' => [
                  'range_min' => '3',
                  'range_max' => '4',
               ]
            ]
         ],
      ];
      $section = $this->getSection();
      $fields[$section::getForeignKeyField()] = $section->getID();

      $question = new \PluginFormcreatorQuestion();
      $question->add($fields);
      $question->updateParameters($fields);

      $fieldInstance = new \PluginFormcreatorMultiSelectField($question->fields);

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

   public function testGetName() {
      $instance = new \PluginFormcreatorMultiSelectField([]);
      $output = $instance->getName();
      $this->string($output)->isEqualTo('Multiselect');
   }

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
      $instance = new \PluginFormcreatorMultiSelectField([]);
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isTrue();
   }
}
