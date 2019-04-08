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

class PluginFormcreatorQuestion extends CommonTestCase {

   private $form;

   private $section;

   public function setup() {
      // instanciate classes
      $form           = new \PluginFormcreatorForm;
      $form_section   = new \PluginFormcreatorSection;
      $form_question  = new \PluginFormcreatorQuestion;
      $form_condition = new \PluginFormcreatorQuestion_Condition;
      $form_validator = new \PluginFormcreatorForm_Validator;
      $form_profile   = new \PluginFormcreatorForm_Profile;

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

      $questions_id_1 = $form_question->add([
         'name'                           => "test clone question 1",
         'fieldtype'                      => 'text',
         'plugin_formcreator_sections_id' => $sections_id
      ]);
      $questions_id_2 = $form_question->add([
         'name'                           => "test clone question 2",
         'fieldtype'                      => 'textarea',
         'plugin_formcreator_sections_id' => $sections_id
      ]);
   }

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
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

   /**
    * @cover\PluginFormcreatorQuestion::clone
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
               'order'                          => '1',
               'show_rule'                      => \PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
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
               'order'                          => '1',
               'show_rule'                      => \PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
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
               'order'                          => '1',
               'show_rule'                      => \PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
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
               'order'                          => '1',
               'show_rule'                      => \PluginFormcreatorQuestion::SHOW_RULE_ALWAYS,
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

   public function testMoveUp() {
      $sectionFk = \PluginFormcreatorSection::getForeignKeyField();
      $section = $this->getSection();
      $question = $this->getQuestion(
         [
            $sectionFk => $section->getID(),
         ]
      );
      $questionToMove = $this->getQuestion(
         [
            $sectionFk => $section->getID(),
         ]
      );

      // Move up the question
      $expectedOrder = $questionToMove->fields['order'] - 1;
      $questionToMove->moveUp();

      // Check the order of the question
      $this->integer((int) $questionToMove->fields['order'])
         ->isEqualTo($expectedOrder);

      // check the order of the other question
      $expectedOrder = $question->fields['order'] + 1;
      $question->getFromDB($question->getID());
      $this->integer((int) $question->fields['order'])
         ->isEqualTo($expectedOrder);
   }

   public function testMoveDown() {
      $sectionFk = \PluginFormcreatorSection::getForeignKeyField();
      $section = $this->getSection();
      $questionToMove = $this->getQuestion(
         [
            $sectionFk => $section->getID(),
         ]
      );
      $question = $this->getQuestion(
         [
            $sectionFk => $section->getID(),
         ]
      );

      // Move down the question
      $expectedOrder = $questionToMove->fields['order'] + 1;
      $questionToMove->moveDown();

      // Check the order of the question
      $this->integer((int) $questionToMove->fields['order'])
         ->isEqualTo($expectedOrder);

      // check the order of the other question
      $expectedOrder = $question->fields['order'] - 1;
      $question->getFromDB($question->getID());
      $this->integer((int) $question->fields['order'])
         ->isEqualTo($expectedOrder);
   }
}
