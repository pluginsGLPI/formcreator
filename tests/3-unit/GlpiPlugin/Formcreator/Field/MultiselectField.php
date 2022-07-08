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

class MultiSelectField extends CommonTestCase {

   public function provider() {
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'multiselect',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => '[]',
               'values'          => json_encode(['1', '2', '3', '4', '5', '6']),
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'expectedValue'   => [],
            'expectedValidity' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'multiselect',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => '["3"]',
               'values'          => json_encode(['1', '2', '3', '4', '5', '6']),
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ]
                  ]
               ],
            ],
            'expectedValue'   => ['3'],
            'expectedValidity' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'multiselect',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => '[3]',
               'values'          => json_encode(['1', '2', '3', '4', '5', '6']),
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '2',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'expectedValue'   => ['3'],
            'expectedValidity' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'multiselect',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => json_encode(['3', '4']),
               'values'          => json_encode(['1', '2', '3', '4', '5', '6']),
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '2',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'expectedValue'   => ['3', '4'],
            'expectedValidity' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'multiselect',
               'name'            => 'question',
               'required'        => '0',
               'show_empty'      => '0',
               'default_values'  => json_encode(['3', '4', '2', '1', '6']),
               'values'          => json_encode(['1', '2', '3', '4', '5', '6']),
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [
                  'multiselect' => [
                     'range' => [
                        'range_min' => '2',
                        'range_max' => '4',
                     ]
                  ]
               ],
            ],
            'expectedValue'   => ['3', '4', '2', '1', '6'],
            'expectedValidity' => false
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testGetAvailableValues($fields, $expectedValue, $expectedValidity) {
      $question = $this->getQuestion($fields);
      $fieldInstance = $this->newTestedInstance($question);

      $availableValues = $fieldInstance->getAvailableValues();
      $expectedAvaliableValues = explode("\r\n", $fields['values']);

      $this->integer(count($availableValues))->isEqualTo(count($expectedAvaliableValues));
      foreach ($expectedAvaliableValues as $expectedValue) {
         $this->array($availableValues)->contains($expectedValue);
      }
   }

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Multiselect');
   }

   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }

   public function providerGetValueForApi() {
      return [
         [
            'input'    => json_encode([
               'a (multiselect)',
               'b (multiselect)'
            ]),
            'expected' => [
               'a (multiselect)',
               'b (multiselect)'
            ],
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
         'values' => '["a (multiselect)","b (multiselect)","c (multiselect)"]'
      ]);

      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($input);
      $output = $instance->getValueForApi();
      $this->array($output)->isEqualTo($expected);
   }
}
