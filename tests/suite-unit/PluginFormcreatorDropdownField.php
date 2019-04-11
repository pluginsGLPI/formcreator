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

class PluginFormcreatorDropdownField extends CommonTestCase {
   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testPrepareQuestionInputForSave':
         case 'testIsValid':
            $this->login('glpi', 'glpi');
      }
   }

   public function testGetName() {
      $instance = $this->newTestedInstance([]);
      $output = $instance->getName();
      $this->string($output)->isEqualTo('Dropdown');
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
                  'show_ticket_categories_root'  => '',
               ]),
               'dropdown_values' => \ITILCategory::class,
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

   public function testIsAnonymousFormCompatible() {
      $instance = new \PluginFormcreatorDropdownField([]);
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isFalse();
   }

   public function testIsPrerequisites() {
      $instance = $this->newTestedInstance([]);
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(true);
   }

   public function testGetValueForDesign() {
      $value = $expected = 'foo';
      $instance = new \PluginFormcreatorDropdownField([]);
      $instance->deserializeValue($value);
      $output = $instance->getValueForDesign();
      $this->string($output)->isEqualTo($expected);
   }

   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance([]);
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }

   public function providerTestIsValid() {
      $name = $this->getUniqueString();
      return [
         [
            'input' => [
               'name' => $name,
               'dropdown_values' => \Location::class,
            ],
            'expected' => true
         ],
         [
            'input' => [
               'name' => $name,
               'dropdown_values' => \ITILCategory::class,
               'show_ticket_categories' => '2',
               'show_ticket_categories_depth' => '3',
            ],
            'expected' => true
         ],
      ];
   }

   /**
    * @dataProvider providerTestIsValid
    */
   public function testIsValid($input, $expected) {
      $instance = new \PluginFormcreatorDropdownField([
         'values' => json_encode([
            'itemtype' => $input['dropdown_values']
         ]),
         'required' => '0',
      ]);
      $instance->prepareQuestionInputForSave($input);
      $output = $instance->isValid();
      $this->boolean($output)->isEqualTo($expected);
   }

   public function providerEquals() {
      $location1 = new \Location();
      $location2 = new \Location();
      $location1Id = $location1->add([
         'name' => $this->getUniqueString()
      ]);
      $location2Id = $location2->add([
         'name' => $this->getUniqueString()
      ]);

      return [
         [
            'fields'    => [
               'id'        => '1',
               'values'    => json_encode(['itemtype' => \Location::class])
            ],
            'value'     => $location1->fields['completename'],
            'answer'    => (string) $location1Id,
            'expected'  => true,
         ],
         [
            'fields'    => [
               'id'        => '1',
               'values'    => json_encode(['itemtype' => \Location::class])
            ],
            'value'     => $location2->fields['completename'],
            'answer'    => (string) $location1Id,
            'expected'  => false,
         ],
      ];
   }

   /**
    * @dataProvider providerEquals
    */
   public function testEquals($fields, $value, $answer, $expected) {
      $instance = new \PluginFormcreatorDropdownField([
         'id' => '1',
         'values' => $fields['values'],
      ]);
      $instance->parseAnswerValues(['formcreator_field_1' => $answer]);
      $this->boolean($instance->equals($value))->isEqualTo($expected);
   }

   public function providerNotEquals() {
      $location1 = new \Location();
      $location2 = new \Location();
      $location1Id = $location1->add([
         'name' => $this->getUniqueString()
      ]);
      $location2Id = $location2->add([
         'name' => $this->getUniqueString()
      ]);

      return [
         [
            'fields'    => [
               'id'        => '1',
               'values'    => json_encode(['itemtype' => \Location::class])
            ],
            'value'     => $location1->fields['completename'],
            'answer'    => (string) $location1Id,
            'expected'  => false,
         ],
         [
            'fields'    => [
               'id'        => '1',
               'values'    => json_encode(['itemtype' => \Location::class])
            ],
            'value'     => $location2->fields['completename'],
            'answer'    => (string) $location1Id,
            'expected'  => true,
         ],
      ];
   }

   /**
    * @dataProvider providerNotEquals
    */
   public function testNotEquals($fields, $value, $answer, $expected) {
      $instance = new \PluginFormcreatorDropdownField([
         'id' => '1',
         'values' => $fields['values'],
      ]);
      $instance->parseAnswerValues(['formcreator_field_1' => $answer]);
      $this->boolean($instance->notEquals($value))->isEqualTo($expected);
   }

   public function testCanRequire() {
      $instance = new \PluginFormcreatorDropdownField([
         'id' => '1',
      ]);
      $output = $instance->canRequire();
      $this->boolean($output)->isTrue();
   }
}
