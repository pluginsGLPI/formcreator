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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;
class PluginFormcreatorQuestion_Condition extends CommonTestCase {
   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);

      self::login('glpi', 'glpi');
   }

   public function answersProvider() {
      return [
         'no condition' => [
            'always',
            [
               'show_logic' => [
               ],
               'show_field'   => [
               ],
               'show_condition'  => [
               ],
               'show_value' => [
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
         'simple condition' => [
            'hidden',
            [
               'show_logic' => [
                     'OR',
               ],
               'show_field'   => [
                     0,
               ],
               'show_condition'  => [
                     '==',
               ],
               'show_value' => [
                     'foo',
               ],
            ],
            [
               0 => 'foo',
               1 => 'val2',
               2 => 'val8',
               3 => 'val9',
            ],
            true,
         ],
         'failed condition' => [
            'hidden',
            [
               'show_logic' => [
                     'OR',
               ],
               'show_field'   => [
                     0,
               ],
               'show_condition'  => [
                     '==',
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
            'hidden',
            [
               'show_logic' => [
                  'OR',
                  'OR',
               ],
               'show_field'   => [
                  0,
                  1,
               ],
               'show_condition'  => [
                  '==',
                  '==',
               ],
               'show_value' => [
                  'val1',
                  'val2',
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
         'failed multiple condition OR' => [
            'hidden',
            [
               'show_logic' => [
                  'OR',
                  'OR',
               ],
               'show_field'   => [
                  0,
                  1,
               ],
               'show_condition'  => [
                  '==',
                  '==',
               ],
               'show_value' => [
                  'val1',
                  'val2',
               ],
            ],
            [
               0 => 'val1',
               1 => 'not val2',
               2 => 'val8',
               3 => 'val9',
            ],
            true,
         ],
         'multiple condition AND' => [
            'hidden',
            [
               'show_logic' => [
                  'OR',
                  'AND',
               ],
               'show_field'   => [
                  0,
                  1,
               ],
               'show_condition'  => [
                  '==',
                  '==',
               ],
               'show_value' => [
                  'val1',
                  'val2',
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
         'failed multiple condition AND' => [
            'hidden',
            [
               'show_logic' => [
                  'OR',
                  'AND',
               ],
               'show_field'   => [
                  0,
                  1,
               ],
               'show_condition'  => [
                  '==',
                  '==',
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
            'hidden',
            [
               'show_logic' => [
                  'OR',
                  'AND',
                  'OR',
                  'AND',
               ],
               'show_field'   => [
                  0,
                  1,
                  2,
                  3,
               ],
               'show_condition'  => [
                  '==',
                  '==',
                  '==',
                  '==',
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
   public function testConditionsEvaluation($show_rule, $conditions, $answers, $expectedVisibility) {
      $section = $this->getSection();
      $form = new \PluginFormcreatorForm();
      $form->getFromDB($section->fields[\PluginFormcreatorForm::getForeignKeyField()]);
      $questionPool = [];

      // Create a question
      $question = new \PluginFormcreatorQuestion();
      $question->add([
         'name'                           => 'text question',
         'fieldtype'                      => 'text',
         'plugin_formcreator_sections_id' => $section->getID(),
         'default_values'                 => '',
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
      ]);
      $questionPool[] = $question;

      for ($i = 0; $i < 4; $i++) {
         $item = new \PluginFormcreatorQuestion();
         $item->add([
            'fieldtype'                      => 'text',
            'name'                           => "question $i",
            'plugin_formcreator_sections_id' => $section->getID(),
            'default_values'                 => '',
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
         ]);
         $questionPool[] = $item;
      }

      foreach ($conditions['show_field'] as $id => &$showField) {
         $showField = $questionPool[$showField];
      }
      $saveFormData = [];
      foreach ($answers as $id => $answer) {
         $saveFormData['formcreator_field_' . $questionPool[$id]->getID()] = $answer;
      }
      $saveFormData['formcreator_form'] = $form->getID();
      $form->saveForm($saveFormData);
      $input = $conditions + [
         'id'                             => $question->getID(),
         'fieldtype'                      => 'text',
         'show_rule'                      => $show_rule,
         'default_values'                 => '',
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
      ];
      $question->update($input);
      $question->updateConditions($input);
      $isVisible = \PluginFormcreatorFields::isVisible($question->getID(), $questionPool);
      $this->boolean($isVisible)->isEqualTo($expectedVisibility);
   }
}
