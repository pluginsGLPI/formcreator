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
         case 'testGetTranslatableStrings':
            $this->login('glpi', 'glpi');
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

   public function providerPrepareInputForAddEmail() {
      $section1 = $this->getSection(
         [],
         []
      );

      return [
         [
            'input' => [
               'plugin_formcreator_sections_id' => $section1->getID(),
               'fieldtype'                      => 'email',
               'name'                           => "email field",
               'values'                         => "",
               'required'                       => '0',
               'default_values'                 => 'empty@example.com',
               'desription'                     => "",
               'row'                            => '1',
               'col'                            => '0',
               'width'                          => '4',
               'height'                         => '1',
               'show_rule'                      => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
            ],
            'expected' => [
               'plugin_formcreator_sections_id' => $section1->getID(),
               'fieldtype'                      => 'email',
               'name'                           => "email field",
               'values'                         => "",
               'required'                       => '1',
               'default_values'                 => 'empty@example.com',
               'desription'                     => "",
               'row'                            => '1',
               'col'                            => '0',
               'width'                          => '4',
               'height'                         => '1',
               'show_rule'                      => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
            ],
            'expectedError' => null,
         ],
      ];
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
    * @dataProvider providerPrepareInputForAddEmail
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
      return $this->providerPrepareInputForAdd();
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
      $this->exception(function () use ($instance) {
         $instance->export();
      })->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ExportFailureException::class);

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

   public function providerGetTranslatableStrings() {
      $data = [
         'actor' => [
            'questionType' => 'actor',
            'expeected' => [
               'itemlink' =>
               [
                 '8c647f55ac463429f736aea1ad64d318' => "actors question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  '06ff4080ef6f9ee755cc45cba5f80360' => 'actors description',
               ],
               'id' =>
               [
                  '8c647f55ac463429f736aea1ad64d318' => 'itemlink',
                  '06ff4080ef6f9ee755cc45cba5f80360' => 'text',
               ]
            ]
         ],
         'checkboxes' => [
            'questionType' => 'checkboxes',
            'expeected' => [
               'itemlink' =>
               [
                 'de1ece2a98dacb86a2b65334373ccb99' => "checkboxes question",
               ],
               'string' =>
               [
                 'bc41fd6c06a851dc3e5f52ef82c46357' => "a (checkbox)",
                 '2e2682dc7fe28972eede52a085f9b8da' => "b (checkbox)",
                 'a212352098d74d20ad0869e8b11870dd' => "c (checkbox)",
               ],
               'text' =>
               [
                  '874e42442b551ef2769cc498157f542d' => 'checkboxes description',
               ],
               'id' =>
               [
                  'de1ece2a98dacb86a2b65334373ccb99' => 'itemlink',
                  '874e42442b551ef2769cc498157f542d' => 'text',
                  'bc41fd6c06a851dc3e5f52ef82c46357' => 'string',
                  '2e2682dc7fe28972eede52a085f9b8da' => 'string',
                  'a212352098d74d20ad0869e8b11870dd' => 'string',
               ]
            ]
         ],
         'date' => [
            'questionType' => 'date',
            'expeected' => [
               'itemlink' =>
               [
                 'e121a8d9e19bf923a648d6bfb33094d8' => "date question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  '42be0556a01c9e0a28da37d2e3c5153d' => 'date description',
               ],
               'id' =>
               [
                  'e121a8d9e19bf923a648d6bfb33094d8' => 'itemlink',
                  '42be0556a01c9e0a28da37d2e3c5153d' => 'text',
               ]
            ]
         ],
         'datetime' =>[
            'questionType' => 'datetime',
            'expeected' => [
               'itemlink' =>
               [
                 '7d3246feb9616461eee152642ad9f1fb' => "datetime question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  'b698fbcd4b9acf232b8b88755a1728f0' => 'datetime description',
               ],
               'id' =>
               [
                  '7d3246feb9616461eee152642ad9f1fb' => 'itemlink',
                  'b698fbcd4b9acf232b8b88755a1728f0' => 'text',
               ]
            ]
         ],
         'description' => [
            'questionType' => 'description',
            'expeected' => [
               'itemlink' =>
               [
                 '824d1cc309c56586a33b52858cbc146b' => "description question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  'ab87cc96356a7d5c1d37c877fd56c6b0' => 'description text',
               ],
               'id' =>
               [
                  '824d1cc309c56586a33b52858cbc146b' => 'itemlink',
                  'ab87cc96356a7d5c1d37c877fd56c6b0' => 'text',
               ]
            ]
         ],
         'dropdown' => [
            'questionType' => 'dropdown',
            'expeected' => [
               'itemlink' =>
               [
                 '8347ce048fc3fe8b954dbc6cd9c4b716' => "dropdown question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  '59ef614a194389f0b54e46b728fe22a2' => 'dropdown description',
               ],
               'id' =>
               [
                  '8347ce048fc3fe8b954dbc6cd9c4b716' => 'itemlink',
                  '59ef614a194389f0b54e46b728fe22a2' => 'text',
               ]
            ]
         ],
         'email' => [
            'questionType' => 'email',
            'expeected' => [
               'itemlink' =>
               [
                 '895472a7be51fe6b1b9591a150fb55d8' => "email question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  'b70e872f17f616049c642f2db8f35c8a' => 'email description',
               ],
               'id' =>
               [
                  '895472a7be51fe6b1b9591a150fb55d8' => 'itemlink',
                  'b70e872f17f616049c642f2db8f35c8a' => 'text',
               ]
            ]
         ],
         'file' => [
            'questionType' => 'file',
            'expeected' => [
               'itemlink' =>
               [
                 '75c4f52e98ebd4a57410d882780353db' => "file question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  '2b4f8f08c4162a2dac4a9b82e97605c0' => 'file description',
               ],
               'id' =>
               [
                  '75c4f52e98ebd4a57410d882780353db' => 'itemlink',
                  '2b4f8f08c4162a2dac4a9b82e97605c0' => 'text',
               ]
            ]
         ],
         'float' => [
            'questionType' => 'float',
            'expeected' => [
               'itemlink' =>
               [
                 '037cad549bb834c2fab44fe14480f9a9' => "float question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  'b1a3d83a831e20619e1f14f6dbc64105' => 'float description',
               ],
               'id' =>
               [
                  '037cad549bb834c2fab44fe14480f9a9' => 'itemlink',
                  'b1a3d83a831e20619e1f14f6dbc64105' => 'text',
               ]
            ]
         ],
         'glpiselect' => [
            'questionType' => 'glpiselect',
            'expeected' => [
               'itemlink' =>
               [
                 '97ee07194ba5af1c81eb5a9b22141241' => "GLPI object question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  '54ee213f0c0aae084d5712dc96bac833' => 'GLPI object description',
               ],
               'id' =>
               [
                  '97ee07194ba5af1c81eb5a9b22141241' => 'itemlink',
                  '54ee213f0c0aae084d5712dc96bac833' => 'text',
               ]
            ]
         ],
         'hidden' => [
            'questionType' => 'hidden',
            'expeected' => [
               'itemlink' =>
               [
                 '74b8be9aff59bf5ddd149248d6156baa' => "hidden question",
               ],
               'string' =>
               [
                  '2ee11338e1d5571cdcdc959e05d13fdd' => 'hidden value'
               ],
               'text' =>
               [
                  '91ca037d3ec611f6c684114abce7296f' => 'hidden description',
               ],
               'id' =>
               [
                  '74b8be9aff59bf5ddd149248d6156baa' => 'itemlink',
                  '91ca037d3ec611f6c684114abce7296f' => 'text',
                  '2ee11338e1d5571cdcdc959e05d13fdd' => 'string'
               ]
            ]
         ],
         'hostname' => [
            'questionType' => 'hostname',
            'expeected' => [
               'itemlink' =>
               [
                 '0550a71495224d60dfcd00826345f0fa' => "hostname question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  '98443bed844ba97392d8a8fb364b5d66' => 'hostname description',
               ],
               'id' =>
               [
                  '0550a71495224d60dfcd00826345f0fa' => 'itemlink',
                  '98443bed844ba97392d8a8fb364b5d66' => 'text',
               ]
            ]
         ],
         'integer' => [
            'questionType' => 'integer',
            'expeected' => [
               'itemlink' =>
               [
                 'b5c09bbe5587577a8c86ada678664877' => "integer question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  '51d8d951cf91a008f5b87c7d36ee6789' => 'integer description',
               ],
               'id' =>
               [
                  'b5c09bbe5587577a8c86ada678664877' => 'itemlink',
                  '51d8d951cf91a008f5b87c7d36ee6789' => 'text',
               ]
            ]
         ],
         'ip' => [
            'questionType' => 'ip',
            'expeected' => [
               'itemlink' =>
               [
                 'd767bdc805e010bfd2302c2516501ffb' => "IP address question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  '4b2e461a0b3c307923176188fb6273c6' => 'IP address description',
               ],
               'id' =>
               [
                  'd767bdc805e010bfd2302c2516501ffb' => 'itemlink',
                  '4b2e461a0b3c307923176188fb6273c6' => 'text',
               ]
            ]
         ],
         'ldapselect' => [
            'questionType' => 'ldapselect',
            'expeected' => [
               'itemlink' =>
               [
                 '5b3ebb576a3977eaa267f0769bdd8e98' => "LDAP question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  'c0117d3ded05c5c672425a48a63c83d7' => 'LDAP description',
               ],
               'id' =>
               [
                  '5b3ebb576a3977eaa267f0769bdd8e98' => 'itemlink',
                  'c0117d3ded05c5c672425a48a63c83d7' => 'text',
               ]
            ]
         ],
         'multiselect' => [
            'questionType' => 'multiselect',
            'expeected' => [
               'itemlink' =>
               [
                 '35226e073fabdcce01c547c5bce62d14' => "multiselect question",
               ],
               'string' =>
               [
                  '26b6a3b22c4a9eacd9bcca663c6bfb98' => 'a (multiselect)',
                  'fe3ba23b6c304bcfccab1c4037170043' => 'b (multiselect)',
                  '76abd40f08cc003cfb75e02d8603a618' => 'c (multiselect)',
               ],
               'text' =>
               [
                  '2d0b83793d10440b70c33a2229c88a09' => 'multiselect description',
               ],
               'id' =>
               [
                  '35226e073fabdcce01c547c5bce62d14' => 'itemlink',
                  '2d0b83793d10440b70c33a2229c88a09' => 'text',
                  '26b6a3b22c4a9eacd9bcca663c6bfb98' => 'string',
                  'fe3ba23b6c304bcfccab1c4037170043' => 'string',
                  '76abd40f08cc003cfb75e02d8603a618' => 'string',
               ]
            ]
         ],
         'radios' => [
            'questionType' => 'radios',
            'expeected' => [
               'itemlink' =>
               [
                 '58e2a2355ba7ac135d42f558591d6a6a' => "radio question",
               ],
               'string' =>
               [
                  'aa08e69f50f9d7e4a280b5e395a926f3' => 'a (radio)',
                  '3d8f74862a3f325c160d5b4090cc1344' => 'b (radio)',
                  '60459f8c72beb121493ec56bd0b41473' => 'c (radio)',
               ],
               'text' =>
               [
                  '06cdb33e33e576a973d7bf54fcded96e' => 'radios description',
               ],
               'id' =>
               [
                  '58e2a2355ba7ac135d42f558591d6a6a' => 'itemlink',
                  '06cdb33e33e576a973d7bf54fcded96e' => 'text',
                  'aa08e69f50f9d7e4a280b5e395a926f3' => 'string',
                  '3d8f74862a3f325c160d5b4090cc1344' => 'string',
                  '60459f8c72beb121493ec56bd0b41473' => 'string',
               ]
            ]
         ],
         'requesttype' => [
            'questionType' => 'requesttype',
            'expeected' => [
               'itemlink' =>
               [
                 '2637b4d11281dffbaa2e340561347ebc' => "request type question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  '471217363e6922ff6b1c9fd9cd57cd2a' => 'request type description',
               ],
               'id' =>
               [
                  '2637b4d11281dffbaa2e340561347ebc' => 'itemlink',
                  '471217363e6922ff6b1c9fd9cd57cd2a' => 'text',
               ]
            ]
         ],
         'select' => [
            'questionType' => 'select',
            'expeected' => [
               'itemlink' =>
               [
                 '212afc3240debecf859880ea9ab4fc2e' => "select question",
               ],
               'string' =>
               [
                  '3e6b3c27f45682bbe11ed102ff9cbd31' => 'a (select)',
                  '12f59df90d7b53129d8e6da91f60cf86' => 'b (select)',
                  '1dd65ffc0516477159ec9ba8c170ef94' => 'c (select)',
               ],
               'text' =>
               [
                  '64dfbbc489b074af269e0b0fbf0d901b' => 'select description',
               ],
               'id' =>
               [
                  '212afc3240debecf859880ea9ab4fc2e' => 'itemlink',
                  '64dfbbc489b074af269e0b0fbf0d901b' => 'text',
                  '3e6b3c27f45682bbe11ed102ff9cbd31' => 'string',
                  '12f59df90d7b53129d8e6da91f60cf86' => 'string',
                  '1dd65ffc0516477159ec9ba8c170ef94' => 'string',
               ]
            ]
         ],
         // 'tag' => [
         //    'questionType' => 'tag',
         //    'expeected' => [
         //       'itemlink' =>
         //       [
         //         'e121a8d9e19bf923a648d6bfb33094d8' => "tag question",
         //       ],
         //       'string' =>
         //       [
         //       ],
         //       'text' =>
         //       [
         //          '42be0556a01c9e0a28da37d2e3c5153d' => 'tag description',
         //       ],
         //       'id' =>
         //       [
         //          'e121a8d9e19bf923a648d6bfb33094d8' => 'itemlink',
         //          '42be0556a01c9e0a28da37d2e3c5153d' => 'text',
         //       ]
         //    ]
         // ],
         'textarea' => [
            'questionType' => 'textarea',
            'expeected' => [
               'itemlink' =>
               [
                 'b99b0833f1dab41a14eb421fa2ce690d' => "textarea question",
               ],
               'string' =>
               [
                  '4f87be8f6e593d167f5fd1ab238cfc2d' => "/foo/",
               ],
               'text' =>
               [
                  'f81bad6b9c8f01a40099a140881313a8' => 'textarea description',
               ],
               'id' =>
               [
                  'b99b0833f1dab41a14eb421fa2ce690d' => 'itemlink',
                  'f81bad6b9c8f01a40099a140881313a8' => 'text',
                  '4f87be8f6e593d167f5fd1ab238cfc2d' => 'string',
               ]
            ]
         ],
         'text' => [
            'questionType' => 'text',
            'expeected' => [
               'itemlink' =>
               [
                 '6fd6eacf3005974a7489a199ed7b45ee' => "text question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  'b371eae37f18f0b6125002999b2404ba' => 'text description',
               ],
               'id' =>
               [
                  '6fd6eacf3005974a7489a199ed7b45ee' => 'itemlink',
                  'b371eae37f18f0b6125002999b2404ba' => 'text',
               ]
            ]
         ],
         'time' => [
            'questionType' => 'time',
            'expeected' => [
               'itemlink' =>
               [
                 'e3a0dfbc9d24603beddcbd1388808a7a' => "time question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  '8d544ed7c846a47654b2f55db879d7b2' => 'time description',
               ],
               'id' =>
               [
                  'e3a0dfbc9d24603beddcbd1388808a7a' => 'itemlink',
                  '8d544ed7c846a47654b2f55db879d7b2' => 'text',
               ]
            ]
         ],
         'urgency' => [
            'questionType' => 'urgency',
            'expeected' => [
               'itemlink' =>
               [
                 '49dce550d75300e99052ed4e8006b65a' => "urgency question",
               ],
               'string' =>
               [
               ],
               'text' =>
               [
                  'e634ce2f4abe0deaa3f7cd44e13f4af6' => 'urgency description',
               ],
               'id' =>
               [
                  '49dce550d75300e99052ed4e8006b65a' => 'itemlink',
                  'e634ce2f4abe0deaa3f7cd44e13f4af6' => 'text',
               ]
            ]
         ],
      ];
      //$expectedCount = count(\PluginFormcreatorFields::getTypes());
      //$this->array($data)->hasSize($expectedCount);
      return $data;
   }

   /**
    * @dataProvider providerGetTranslatableStrings
    *
    * @return void
    */
   public function testGetTranslatableStrings($questionType, $expected) {
      $data = file_get_contents(dirname(__DIR__) . '/fixture/all_question_types_form.json');
      $data = json_decode($data, true);
      foreach ($data['forms'] as $formData) {
         $form = new \PluginFormcreatorForm();
         $formId = $form->import(new \PluginFormcreatorLinker(), $formData);
         $this->boolean($form->isNewID($formId))->isFalse();
      }

      $form->getFromDB($formId);
      $this->boolean($form->isNewItem())->isFalse();
      $section = new \PluginFormcreatorSection();
      $section->getFromDBByCrit([
         'plugin_formcreator_forms_id' => $formId,
         'name' => 'section',
      ]);
      $this->boolean($section->isNewItem())->isFalse();
      $question = $this->newTestedInstance();
      $question->getFromDBByCrit([
         'plugin_formcreator_sections_id' => $section->getID(),
         'fieldtype' => $questionType
      ]);
      $this->boolean($question->isNewItem())->isFalse();
      $output = $question->getTranslatableStrings();
      //if ($questionType == 'float') $this->dumpOnFailure($output);
      $this->array($output)->isIdenticalTo($expected);
   }
}
