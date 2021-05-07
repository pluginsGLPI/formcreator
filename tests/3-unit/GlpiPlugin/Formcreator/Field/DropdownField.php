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

class DropdownField extends CommonTestCase {
   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testPrepareQuestionInputForSave':
         case 'testGetDesignSpecializationField':
         case 'testIsValid':
            $this->login('glpi', 'glpi');
      }
   }

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Dropdown');
   }

   public function providerPrepareQuestionInputForSave() {
      $name = $this->getUniqueString();
      return [
         [
            'input' => [
               'name' => $name,
               'dropdown_values' => \Location::class,
               'show_tree_depth' => '5',
               'show_tree_root' => '0',
               'selectable_tree_root' => '0',
            ],
            'expected' => [
               'name' => $name,
               'values' => json_encode([
                  'itemtype' => \Location::class,
                  'show_tree_depth' => '5',
                  'show_tree_root' => '0',
                  'selectable_tree_root' => '0',
                  'entity_restrict' => \GlpiPlugin\Formcreator\Field\DropdownField::ENTITY_RESTRICT_FORM,
               ]),
               'default_values'  => '',
            ]
         ],
         [
            'input' => [
               'name' => $name,
               'dropdown_values' => \ITILCategory::class,
               'show_ticket_categories' => '2',
               'show_tree_depth' => '3',
               'default_values'  => '',
            ],
            'expected' => [
               'name' => $name,
               'values' => json_encode([
                  'itemtype' => \ITILCategory::class,
                  'show_ticket_categories' => '2',
                  'show_tree_depth' => '3',
                  'show_tree_root'  => '',
                  'selectable_tree_root' => '0',
                  'entity_restrict' => \GlpiPlugin\Formcreator\Field\DropdownField::ENTITY_RESTRICT_FORM,
               ]),
               'default_values'  => '',
            ]
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareQuestionInputForSave
    */
   public function testPrepareQuestionInputForSave($input, $expected) {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->prepareQuestionInputForSave($input);
      $this->array($output)->hasSize(count($expected));
      foreach ($expected as $key => $value) {
         $this->variable($output[$key])->isIdenticalTo($value);
      }
   }

   public function testIsAnonymousFormCompatible() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isFalse();
   }

   public function testIsPrerequisites() {
      $instance = $this->newTestedInstance($this->getQuestion([
         'values' => \Computer::class
      ]));
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(true);

      $instance = $this->newTestedInstance($this->getQuestion([
         'values' => \UndefinedItemtype::class
      ]));
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(false);
   }

   public function testGetValueForDesign() {
      $value = $expected = 'foo';
      $instance = $this->newTestedInstance($this->getQuestion());
      $instance->deserializeValue($value);
      $output = $instance->getValueForDesign();
      $this->string($output)->isEqualTo($expected);
   }

   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance(new \PluginFormcreatorQuestion());
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }

   public function providerIsValid() {
      $location = new \Location();
      $locationId = $location->import([
         'completename' => 'foo',
         'entities_id'  => $_SESSION['glpiactive_entity']
      ]);

      return [
         [
            'question' => $this->getQuestion([
               'name' =>  'fieldname',
               'values' => json_encode([
                  'itemtype' => \Location::class,
               ]),
               'required' => '0',
               'default_values' => '0',
            ]),
            'input' => [
               'dropdown_values' => \Location::class,
               'dropdown_default_value' => '0',
               'show_ticket_categories_depth' => '5',
               'show_ticket_categories_root' => '0',
            ],
            'expected' => true,
         ],
         [
            'question' => $this->getQuestion([
               'name' =>  'fieldname',
               'values' => json_encode([
                  'itemtype' => \Location::class,
               ]),
               'required' => '1',
            ]),
            'input' => [
               'dropdown_values' => \Location::class,
               'dropdown_default_value' => '0',
               'show_ticket_categories_depth' => '5',
               'show_ticket_categories_root' => '0',
            ],
            'expected' => false,
         ],
         [
            'question' => $this->getQuestion([
               'name' =>  'fieldname',
               'values' => json_encode([
                  'itemtype' => \Location::class,
               ]),
               'required' => '1',
               'default_values' => '',
            ]),
            'input' => [
               'dropdown_values' => \Location::class,
               'dropdown_default_value' => '42',
               'show_ticket_categories_depth' => '5',
               'show_ticket_categories_root' => '0',
            ],
            'expected' => false,
         ],
         [
            'question' => $this->getQuestion([
               'name' =>  'fieldname',
               'values' => json_encode([
                  'itemtype' => \Location::class,
               ]),
               'required' => '1',
               'default_values' => $locationId,
            ]),
            'input' => [
               'dropdown_values' => \Location::class,
               'dropdown_default_value' => '42',
               'show_ticket_categories_depth' => '5',
               'show_ticket_categories_root' => '0',
            ],
            'expected' => true,
         ],
      ];
   }

   /**
    * @dataProvider providerIsValid
    */
   public function testIsValid($question, $input, $expected) {
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($question->fields['default_values']);
      $output = $instance->isValid();
      $this->boolean($output)->isEqualTo($expected);
   }

   public function providerGetValueForTargetText() {
      $location = new \Location();
      $location->add([
         'name' => $this->getUniqueString(),
      ]);
      return [
         [
            'fields' => $this->getQuestion([
               'name' =>  'fieldname',
               'values' => json_encode([
                  'itemtype' => \Location::class,
               ]),
               'required' => '1',
               'dropdown_values' => \Location::class,
               'dropdown_default_value' => '42',
            ]),
            'value' => "",
            'expected' => '&nbsp;'
         ],
         [
            'fields' => $this->getQuestion([
               'name' =>  'fieldname',
               'values' => json_encode([
                  'itemtype' => \Location::class,
               ]),
               'required' => '1',
               'dropdown_values' => \Location::class,
               'dropdown_default_value' => '',
            ]),
            'value' => $location->getID(),
            'expected' => $location->fields['completename']
         ],
      ];
   }

   /**
    * @dataprovider providerGetValueForTargetText
    */
   public function testGetValueForTargetText($fields, $value, $expected) {
      $instance = $this->newTestedInstance($fields);
      $instance->deserializeValue($value);

      $output = $instance->getValueForTargetText('', true);
      $this->string($output)->isEqualTo($expected);
   }

   public function testGetDesignSpecializationField() {
      $question = $this->getQuestion([
         'values' => json_encode([
            'itemtype' => \User::class,
         ]),
      ]);

      $instance = $this->newTestedInstance($question);
      $output = $instance->getDesignSpecializationField();
      $this->boolean($output['may_be_empty'])->isEqualTo(true);
      $this->boolean($output['may_be_required'])->isEqualTo(true);
   }


   public function providerEquals() {
      $location1 = new \Location();
      $location2 = new \Location();
      $location1Id = $location1->add([
         'name' => $this->getUniqueString()
      ]);
      $location2->add([
         'name' => $this->getUniqueString()
      ]);

      return [
         [
            'fields'    => $this->getQuestion([
               'values'    => json_encode(['itemtype' => \Location::class])
            ]),
            'value'     => $location1->fields['completename'],
            'answer'    => (string) $location1Id,
            'expected'  => true,
         ],
         [
            'fields'    => $this->getQuestion([
               'values'    => json_encode(['itemtype' => \Location::class])
            ]),
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
      $instance = $this->newTestedInstance($fields);
      $instance->parseAnswerValues(['formcreator_field_' . $fields->getID() => $answer]);
      $this->boolean($instance->equals($value))->isEqualTo($expected);
   }

   public function providerNotEquals() {
      $location1 = new \Location();
      $location2 = new \Location();
      $location1Id = $location1->add([
         'name' => $this->getUniqueString()
      ]);
      $location2->add([
         'name' => $this->getUniqueString()
      ]);

      return [
         [
            'fields'    => $this->getQuestion([
               'values'    => json_encode(['itemtype' => \Location::class])
            ]),
            'value'     => $location1->fields['completename'],
            'answer'    => (string) $location1Id,
            'expected'  => false,
         ],
         [
            'fields'    => $this->getQuestion([
               'values'    => json_encode(['itemtype' => \Location::class])
            ]),
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
      $instance = $this->newTestedInstance($fields);
      $instance->parseAnswerValues(['formcreator_field_' . $fields->getID() => $answer]);
      $this->boolean($instance->notEquals($value))->isEqualTo($expected);
   }

   public function testCanRequire() {
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question);
      $output = $instance->canRequire();
      $this->boolean($output)->isTrue();
   }
}
