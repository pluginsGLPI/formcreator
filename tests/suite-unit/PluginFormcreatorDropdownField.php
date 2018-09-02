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

class PluginFormcreatorDropdownField extends CommonTestCase {
   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testPrepareQuestionInputForSave':
            $this->login('glpi', 'glpi');
      }
   }

   public function testGetName() {
      $instance = $this->newTestedInstance([]);
      $output = $instance->getName();
      $this->string($output)->isEqualTo('Dropdown');
   }

   public function providerGetValue() {
      $location = new \Location();
      $locationName = $this->getUniqueString();
      $locationId = $location->import([
         'completename' => $locationName,
         'entities_id'  => 0,
         'is_recursive' => 1,
      ]);
      $this->integer($locationId);
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'dropdown',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => json_encode(['itemtype' => \Location::class]),
               'order'           => '1',
               'show_rule'       => 'always',
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
            'expectedValue'   => 0,
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'dropdown',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $locationId,
               'values'          => json_encode(['itemtype' => \Location::class]),
               'order'           => '1',
               'show_rule'       => 'always',
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
            'expectedValue'   => $locationId,
            'expectedIsValid' => true
         ],
      ];
      return $dataset;
   }

   /**
    * @dataProvider providerGetValue
    */
   public function testGetValue($fields, $data, $expectedValue, $expectedIsValid) {
      $instance = new \PluginFormcreatorDropdownField($fields, $data);
      $output = $instance->getValue();
      $this->variable($output)->isIdenticalTo($expectedValue);

   }

   public function providerGetAnswer() {
      $location = new \Location();
      $locationName = $this->getUniqueString();
      $locationId = $location->import([
         'completename' => $locationName,
         'entities_id'  => 0,
         'is_recursive' => 1,
      ]);
      $this->integer($locationId);
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'dropdown',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => json_encode(['itemtype' => \Location::class]),
               'order'           => '1',
               'show_rule'       => 'always',
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
            'expectedValue'   => "&nbsp;",
         ],
         [
            'fields'          => [
               'fieldtype'       => 'dropdown',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $locationId,
               'values'          => json_encode(['itemtype' => \Location::class]),
               'order'           => '1',
               'show_rule'       => 'always',
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
            'expectedValue'   => $locationName,
         ],
      ];
      return $dataset;
   }

   /**
    * @dataProvider providerGetAnswer
    */
   public function testgetAnswer($fields, $data, $expectedValue) {
      $instance = new \PluginFormcreatorDropdownField($fields, $data);
      $output = $instance->getAnswer();
      $DbUtil = new \DbUtils();
      $itemtype = json_decode($fields['values'], JSON_OBJECT_AS_ARRAY);
      $itemtype = $itemtype['itemtype'];
      $this->variable($output)->isIdenticalTo($expectedValue);
   }

   public function providerPrepareQuestionInputForSave() {
      $name = $this->getUniqueString();
      return [
         [
            'input' => [
               'name' => $name,
               'dropdown_values' => \Location::class,
            ],
            'expected' => [
               'name' => $name,
               'values' => json_encode(['itemtype' => \Location::class]),
               'dropdown_values' => \Location::class,
               'default_values' => '',
            ]
         ],
         [
            'input' => [
               'name' => $name,
               'dropdown_values' => \ITILCategory::class,
               'show_ticket_categories' => '2',
               'show_ticket_categories_depth' => '3',
            ],
            'expected' => [
               'name' => $name,
               'values' => json_encode([
                  'itemtype' => \ITILCategory::class,
                  'show_ticket_categories' => '2',
                  'show_ticket_categories_depth' => '3',
               ]),
               'dropdown_values' => \ITILCategory::class,
               'default_values' => '',
            ]
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareQuestionInputForSave
    */
   public function testPrepareQuestionInputForSave($input, $expected) {
      $instance = new \PluginFormcreatorDropdownField([]);
      $output = $instance->prepareQuestionInputForSave($input);
      $this->array($output)->hasSize(count($expected));
      foreach ($expected as $key => $value) {
         $this->variable($output[$key])->isIdenticalTo($value);
      }
   }
}
