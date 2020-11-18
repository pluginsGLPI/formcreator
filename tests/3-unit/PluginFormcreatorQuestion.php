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
 * @copyright Copyright © 2011 - 2020 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorQuestion extends CommonTestCase {

   private $form;

   private $section;

   public function setup() {
      // instanciate classes
      $form           = new \PluginFormcreatorForm;
      $form_section   = new \PluginFormcreatorSection;
      $form_question  = new \PluginFormcreatorQuestion;

      // create objects
      $forms_id = $form->add([
         'name'                => "test clone form",
         'is_active'           => true,
         'validation_required' => \PluginFormcreatorForm_Validator::VALIDATION_USER
      ]);

      $sections_id = $form_section->add([
         'name'                        => "test clone section",
         'plugin_formcreator_forms_id' => $forms_id
      ]);

      $form_question->add([
         'name'                           => "test clone question 1",
         'fieldtype'                      => 'text',
         'plugin_formcreator_sections_id' => $sections_id
      ]);
      $form_question->add([
         'name'                           => "test clone question 2",
         'fieldtype'                      => 'textarea',
         'plugin_formcreator_sections_id' => $sections_id
      ]);
   }

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testImport':
            self::login('glpi', 'glpi');
            break;

         case 'testPrepareInputForAdd':
         case 'testPrepareInputForUpdate':
            $this->form = new \PluginFormcreatorForm;
            $this->section = new \PluginFormcreatorSection;
            $this->form->add([
               'name' => "$method"
            ]);
            $this->boolean($this->form->isNewItem())->isFalse();
            $this->section->add([
               'name' => 'section',
               'plugin_formcreator_forms_id' => $this->form->getID(),
            ]);
            $this->boolean($this->section->isNewItem())->isFalse();
      }
   }

   public function providerGetTypeName() {
      return [
         [
            'input' => 0,
            'expected' => 'Questions',
         ],
         [
            'input' => 1,
            'expected' => 'Question',
         ],
         [
            'input' => 2,
            'expected' => 'Questions',
         ],
      ];
   }

   /**
    * @dataProvider providerGetTypeName
    * @param integer $number
    * @param string $expected
    */
   public function testGetTypeName($number, $expected) {
      $output = \PluginFormcreatorQuestion::getTypeName($number);
      $this->string($output)->isEqualTo($expected);
   }

   /**
    *
    */
   public function testDuplicate() {
      $question = $this->getQuestion();

      //clone it
      $newQuestion_id = $question->duplicate();
      $this->integer($newQuestion_id)->isGreaterThan(0);

      //get cloned section
      $new_question  = new \PluginFormcreatorQuestion;
      $new_question->getFromDB($newQuestion_id);

      // check uuid
      $this->string($new_question->getField('uuid'))->isNotEqualTo($question->getField('uuid'));
   }

   public function providerPrepareInputForAdd() {
      $section1 = $this->getSection(
         [],
         []
      );
      $section2 = $this->getSection(
         [],
         [
            'access_rights' => \PluginFormcreatorForm::ACCESS_PUBLIC,
         ]
      );
      $dataset = [
         [
            'input' => [
               'plugin_formcreator_sections_id' => $section1->getID(),
               'fieldtype'                      => 'radios',
               'name'                           => "it\'s nice",
               'values'                         => "it\'s nice\r\nit's good",
               'required'                       => '1',
               'show_empty'                     => '0',
               'default_values'                 => 'it\'s nice',
               'desription'                     => "it\'s excellent",
               'row'                            => '1',
               'col'                            => '0',
               'width'                          => '4',
               'height'                         => '1',
               'show_rule'                      => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
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
            'expected' => [
               'plugin_formcreator_sections_id' => $section1->getID(),
               'fieldtype'                      => 'radios',
               'name'                           => "it\'s nice",
               'values'                         => "it\'s nice\r\nit's good",
               'required'                       => '1',
               'show_empty'                     => '0',
               'default_values'                 => 'it\'s nice',
               'desription'                     => "it\'s excellent",
               'row'                            => '1',
               'col'                            => '0',
               'width'                          => '4',
               'height'                         => '1',
               'show_rule'                      => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
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
            'expectedError' => null,
         ],
         'field type incompatible' => [
            'input' => [
               'plugin_formcreator_sections_id' => $section2->getID(),
               'fieldtype'                      => 'actor',
               'name'                           => "a question",
               'values'                         => "",
               'required'                       => '1',
               'show_empty'                     => '0',
               'default_values'                 => 'it\'s nice',
               'desription'                     => "it\'s excellent",
               'row'                            => '1',
               'col'                            => '0',
               'width'                          => '4',
               'height'                         => '1',
               'show_rule'                      => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
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
            'expected' => [],
            'expectedError' => 'This type of question is not compatible with public forms.',
         ],
         'non existent field type' => [
            'input' => [
               'plugin_formcreator_sections_id' => $section2->getID(),
               'fieldtype'                      => 'nonexistent',
               'name'                           => "question-name",
               'values'                         => "",
               'required'                       => '1',
               'show_empty'                     => '0',
               'default_values'                 => 'it\'s nice',
               'desription'                     => "it\'s excellent",
               'row'                            => '1',
               'col'                            => '0',
               'width'                          => '4',
               'height'                         => '1',
               'show_rule'                      => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
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
            'expected' => [],
            'expectedError' => 'Field type nonexistent is not available for question question-name.',
         ],
      ];

      return $dataset;
   }

   public function testImport() {
      $section = $this->getSection();
      $uuid = plugin_formcreator_getUuid();
      $input = [
         'name' => $this->getUniqueString(),
         'fieldtype' => 'text',
         'required' => '0',
         'show_empty' => '1',
         'default_values' => '',
         'values' => '',
         'description' => '',
         'order' => '1',
         'show_rule' => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
         'uuid' => $uuid,
      ];

      $linker = new \PluginFormcreatorLinker();
      $questionId = \PluginFormcreatorQuestion::import($linker, $input, $section->getID());
      $this->integer($questionId)->isGreaterThan(0);

      unset($input['uuid']);

      $this->exception(
         function() use($linker, $input, $section) {
            \PluginFormcreatorQuestion::import($linker, $input, $section->getID());
         }
      )->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ImportFailureException::class)
      ->hasMessage('UUID or ID is mandatory for Question'); // passes

      $input['id'] = $questionId;
      $questionId2 = \PluginFormcreatorQuestion::import($linker, $input, $section->getID());
      $this->integer((int) $questionId)->isNotEqualTo($questionId2);
   }

   /**
    * @dataProvider providerPrepareInputForAdd
    */
   public function testPrepareInputForAdd($input, $expected, $expectedError) {
      $instance = new \PluginFormcreatorQuestion();
      $output = $instance->prepareInputForAdd($input);

      if ($expectedError !== null) {
         $this->sessionHasMessage($expectedError, ERROR);
         $this->array($output)->hasSize(0);
      } else {
         $this->array($output)->hasKeys(array_keys($expected));
         /*
         // Disabled for now
         $this->array($output)->containsValues($expected);
         */
         $this->array($output)->hasKey('uuid');
         // The method added a UUID key
         $this->array($output)->size->isEqualTo(count($expected) + 1);
      }
   }

   public function providerPrepareInputForUpdate() {
      return providerPrepareInputForAdd();
   }

   /**
    * @dataProvider providerPrepareInputForUpdate
    */
   public function prepareInputForUpdate($input, $expected) {
      $section = $this->getSection();
      $input[$section::getForeignKeyField()] = $section->getID();

      $instance = new \PluginFormcreatorQuestion();
      $output = $instance->prepareInputForUpdate($input);
      $this->array($output)->hasKeys(array_keys($expected));
      $this->array($output)->containsValues($expected);
      $this->array($output)->hasKey('uuid');
      $this->array($output)->size->isEqualTo(count($expected));
   }

   public function testExport() {
      $instance = $this->newTestedInstance();

      // Try to export an empty item
      $output = $instance->export();
      $this->boolean($output)->isFalse();

      // Prepare an item to export
      $instance = $this->getQuestion();
      $instance->getFromDB($instance->getID());

      // Export the item without the ID and with UUID
      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
         'name',
         'fieldtype',
         'required',
         'show_empty',
         'default_values',
         'values',
         'description',
         'row',
         'col',
         'width',
         'show_rule',
      ];
      $extraFields = [
         '_conditions',
         '_parameters',
      ];
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['uuid'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));

      // Export the item without the UUID and with ID
      $output = $instance->export(true);
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['id'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));
   }

   public function testPost_purgeItem() {
      /**
       * Test 2 questions move up
       */
      $section = $this->getSection();
      $sectionFk = \PluginFormcreatorSection::getForeignKeyField();
      $questions = [
         0 => $toDelete = $this->getQuestion([
            $sectionFk => $section->getID(),
            'row' => 0,
         ]),
         1 => $this->getQuestion([
            $sectionFk => $section->getID(),
            'row' => 1,
         ]),
         2 => $this->getQuestion([
            $sectionFk => $section->getID(),
            'row' => 2,
         ]),
      ];

      // call to post_purgeItem() done here
      $toDelete->delete([
         'id' => $toDelete->getID(),
      ]);

      // reload questions
      $questions[1]->getFromDB($questions[1]->getID());
      $questions[2]->getFromDB($questions[2]->getID());

      // Check 1 and 2 moved up
      $this->integer((int) $questions[1]->fields['row'])->isEqualTo(0);
      $this->integer((int) $questions[2]->fields['row'])->isEqualTo(1);

      /**
       * Test no questions moved
       */
      $section = $this->getSection();
      $sectionFk = \PluginFormcreatorSection::getForeignKeyField();
      $questions = [
         0 => $this->getQuestion([
            $sectionFk => $section->getID(),
            'row' => 0,
         ]),
         1 => $this->getQuestion([
            $sectionFk => $section->getID(),
            'row' => 1,
         ]),
         2 => $toDelete = $this->getQuestion([
            $sectionFk => $section->getID(),
            'row' => 2,
         ]),
      ];

      // call to post_purgeItem() done here
      $toDelete->delete([
         'id' => $toDelete->getID(),
      ]);

      // reload questions
      $questions[0]->getFromDB($questions[0]->getID());
      $questions[1]->getFromDB($questions[1]->getID());

      // Check 1 and 2 moved up
      $this->integer((int) $questions[0]->fields['row'])->isEqualTo(0);
      $this->integer((int) $questions[1]->fields['row'])->isEqualTo(1);
   }
}
