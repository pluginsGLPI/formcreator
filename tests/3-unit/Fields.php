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

namespace tests\units\GlpiPlugin\Formcreator;

use GlpiPlugin\Formcreator\Condition;
use GlpiPlugin\Formcreator\Form;
use GlpiPlugin\Formcreator\Question;
use GlpiPlugin\Formcreator\Section;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class Fields extends CommonTestCase {

   public function answersProvider() {
      return [
         'no condition' => [
           Condition::SHOW_RULE_ALWAYS,
            [
               'show_logic' => [],
               'plugin_formcreator_questions_id'   => [],
               'show_condition'  => [],
               'show_value' => [],
            ],
            [],
            true,
         ],
         'simple condition' => [
           Condition::SHOW_RULE_HIDDEN,
            [
               'show_logic' => [
                  Condition::SHOW_LOGIC_OR,
               ],
               'plugin_formcreator_questions_id'   => [
                  0,
               ],
               'show_condition'  => [
                  Condition::SHOW_CONDITION_EQ,
               ],
               'show_value' => [
                  'foo',
               ],
            ],
            [
               0 => 'foo',
            ],
            true,
         ],
         'failed condition' => [
           Condition::SHOW_RULE_HIDDEN,
            [
               'show_logic' => [
                  Condition::SHOW_LOGIC_OR,
               ],
               'plugin_formcreator_questions_id'   => [
                  0,
               ],
               'show_condition'  => [
                  Condition::SHOW_CONDITION_EQ,
               ],
               'show_value' => [
                  'bar',
               ],
            ],
            [
                  0 => 'foo',
            ],
            false,
         ],
         'multiple condition OR' => [
           Condition::SHOW_RULE_HIDDEN,
            [
               'show_logic' => [
                  Condition::SHOW_LOGIC_OR,
                  Condition::SHOW_LOGIC_OR,
               ],
               'plugin_formcreator_questions_id'   => [
                  0,
                  1,
               ],
               'show_condition'  => [
                  Condition::SHOW_CONDITION_EQ,
                  Condition::SHOW_CONDITION_EQ,
               ],
               'show_value' => [
                  'val1',
                  'val2',
               ],
            ],
            [
                  0 => 'val1',
                  1 => 'val2',
            ],
            true,
         ],
         'failed multiple condition OR' => [
           Condition::SHOW_RULE_HIDDEN,
            [
               'show_logic' => [
                  Condition::SHOW_LOGIC_OR,
                  Condition::SHOW_LOGIC_OR,
               ],
               'plugin_formcreator_questions_id'   => [
                  0,
                  1,
               ],
               'show_condition'  => [
                  Condition::SHOW_CONDITION_EQ,
                  Condition::SHOW_CONDITION_EQ,
               ],
               'show_value' => [
                  'val1',
                  'val2',
               ],
            ],
            [
               0 => 'val1',
               1 => 'not val2',
            ],
            true,
         ],
         'multiple condition AND' => [
           Condition::SHOW_RULE_HIDDEN,
            [
               'show_logic' => [
                  Condition::SHOW_LOGIC_OR,
                  Condition::SHOW_LOGIC_AND,
               ],
               'plugin_formcreator_questions_id'   => [
                  0,
                  1,
               ],
               'show_condition'  => [
                  Condition::SHOW_CONDITION_EQ,
                  Condition::SHOW_CONDITION_EQ,
               ],
               'show_value' => [
                  'val1',
                  'val2',
               ],
            ],
            [
               0 => 'val1',
               1 => 'val2',
            ],
            true,
         ],
         'failed multiple condition AND' => [
           Condition::SHOW_RULE_HIDDEN,
            [
               'show_logic' => [
                  Condition::SHOW_LOGIC_OR,
                  Condition::SHOW_LOGIC_AND,
               ],
               'plugin_formcreator_questions_id'   => [
                  0,
                  1,
               ],
               'show_condition'  => [
                  Condition::SHOW_CONDITION_EQ,
                  Condition::SHOW_CONDITION_EQ,
               ],
               'show_value' => [
                  'val1',
                  'val2',
               ],
            ],
            [
               0 => 'val1',
               1 => 'not val2',
            ],
            false,
         ],
         'operator priority' => [
           Condition::SHOW_RULE_HIDDEN,
            [
               'show_logic' => [
                  Condition::SHOW_LOGIC_OR,
                  Condition::SHOW_LOGIC_AND,
                  Condition::SHOW_LOGIC_OR,
                  Condition::SHOW_LOGIC_AND,
               ],
               'plugin_formcreator_questions_id'   => [
                  0,
                  1,
                  2,
                  3,
               ],
               'show_condition'  => [
                  Condition::SHOW_CONDITION_EQ,
                  Condition::SHOW_CONDITION_EQ,
                  Condition::SHOW_CONDITION_EQ,
                  Condition::SHOW_CONDITION_EQ,
               ],
               'show_value' => [
                  'val1',
                  'val2',
                  'val3',
                  'val4',
               ],
            ],
            [
               0 => 'val1',
               1 => 'val2',
               2 => 'val8',
               3 => 'val9',
            ],
            true,
         ],
      ];
   }

   /**
    * @dataProvider answersProvider
    */
   public function testIsVisible($show_rule, $conditions, $answers, $expectedVisibility) {
      // Create section
      $section = $this->getSection();
      $this->boolean($section->isNewItem())->isFalse();

      // Create a question
      $question = $this->getQuestion([
         'name'                           => 'text question',
         'fieldtype'                      => 'text',
         'plugin_formcreator_sections_id' => $section->getID(),
      ]);
      $this->boolean($question->isNewItem())->isFalse();

      $questionPool = [];
      for ($i = 0; $i < 4; $i++) {
         $item = $this->getQuestion([
            'fieldtype'                      => 'text',
            'name'                           => "question $i",
            'plugin_formcreator_sections_id' => $section->getID(),
         ]);
         $questionPool[$i] = $item;
      }

      foreach ($conditions['plugin_formcreator_questions_id'] as $id => &$showField) {
         $showField = $questionPool[$showField]->getID();
      }
      $realAnswers = [];
      $testedClass = $this->getTestedClassName();
      foreach ($answers as $id => $answer) {
         $realAnswers[$questionPool[$id]->getID()] = $testedClass::getFieldInstance(
            $questionPool[$id]->fields['fieldtype'], $questionPool[$id]
         );
         $realAnswers[$questionPool[$id]->getID()]->deserializeValue($answer);
      }
      $input = [
         'id'        => $question->getID(),
         'show_rule' => $show_rule,
         'default_values' => '',
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
         '_conditions' => $conditions,
      ];
      $question->update($input);
      $question->updateConditions($input);
      $isVisible = $testedClass::isVisible($question, $realAnswers);
      $this->boolean((boolean) $isVisible)->isEqualTo($expectedVisibility);
   }

   public function testGetFieldClassname() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getFieldClassname('dummy');
      $this->string($output)->isEqualTo('GlpiPlugin\Formcreator\Field\DummyField');
   }

   public function testFieldTypeExists() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::fieldTypeExists('dummy');
      $this->boolean($output)->isFalse();
      $output = $testedClass::fieldTypeExists('textarea');
      $this->boolean($output)->isTrue();
   }

   public function testUpdateVisibility() {
      $question1 = $this->getQuestion();
      $question2 = $this->getQuestion([
         'plugin_formcreator_sections_id' => $question1->fields['plugin_formcreator_sections_id'],
      ]);

      $form = new Form();
      $section = new Section();
      $section->getFromDB($question1->fields['plugin_formcreator_sections_id']);
      $form = Form::getByItem($section);
      $input = [
         'plugin_formcreator_forms_id' => $form->getID(),
         $question1->getID() => '',
         $question2->getID() => '',
      ];
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::updateVisibility($input);
      $this->array($output)->isIdenticalTo([
         Question::class => [
            $question1->getID() => true,
            $question2->getID() => true,
         ],
         Section::class => [
            $section->getID() => true,
         ],
         Form::class => true,
      ]);
   }

   public function testGetNames() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getNames();

      $this->array($output)
         ->hasSize(26);
   }
}
