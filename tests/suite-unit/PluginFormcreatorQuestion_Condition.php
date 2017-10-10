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
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class Question_ConditionTest extends SuperAdminTestCase {

   static $question;

   static $questionPool = [];

   public static function beforeTestMethod($method) {
      parent::beforeTestMethod();

      self::login('glpi', 'glpi');
   }

   public function answersProvider() {
      return array(
         'no condition' => array(
            'always',
            array(
               'show_logic' => array(
               ),
               'show_field'   => array(
               ),
               'show_condition'  => array(
               ),
               'show_value' => array(
               ),
            ),
            [],
            true,
         ),
         'simple condition' => array(
            'hidden',
            array(
               'show_logic' => array(
                     'OR',
               ),
               'show_field'   => array(
                     0,
               ),
               'show_condition'  => array(
                     '==',
               ),
               'show_value' => array(
                     'foo',
               ),
            ),
            array(
                  0 => 'foo',
            ),
            true,
         ),
         'failed condition' => array(
            'hidden',
            array(
               'show_logic' => array(
                     'OR',
               ),
               'show_field'   => array(
                     0,
               ),
               'show_condition'  => array(
                     '==',
               ),
               'show_value' => array(
                     'bar',
               ),
            ),
            array(
                  0 => 'foo',
            ),
            false,
         ),
         'multiple condition OR' => array(
            'hidden',
            array(
               'show_logic' => array(
                  'OR',
                  'OR',
               ),
               'show_field'   => array(
                  0,
                  1,
               ),
               'show_condition'  => array(
                  '==',
                  '==',
               ),
               'show_value' => array(
                  'val1',
                  'val2',
               ),
            ),
            array(
               0 => 'val1',
               1 => 'val2',
            ),
            true,
         ),
         'failed multiple condition OR' => array(
            'hidden',
            array(
               'show_logic' => array(
                  'OR',
                  'OR',
               ),
               'show_field'   => array(
                  0,
                  1,
               ),
               'show_condition'  => array(
                  '==',
                  '==',
               ),
               'show_value' => array(
                  'val1',
                  'val2',
               ),
            ),
            array(
               0 => 'val1',
               1 => 'not val2',
            ),
            true,
         ),
         'multiple condition AND' => array(
            'hidden',
            array(
               'show_logic' => array(
                  'OR',
                  'AND',
               ),
               'show_field'   => array(
                  0,
                  1,
               ),
               'show_condition'  => array(
                  '==',
                  '==',
               ),
               'show_value' => array(
                  'val1',
                  'val2',
               ),
            ),
            array(
               0 => 'val1',
               1 => 'val2',
            ),
            true,
         ),
         'failed multiple condition AND' => array(
            'hidden',
            array(
               'show_logic' => array(
                  'OR',
                  'AND',
               ),
               'show_field'   => array(
                  0,
                  1,
               ),
               'show_condition'  => array(
                  '==',
                  '==',
               ),
               'show_value' => array(
                  'val1',
                  'val2',
               ),
            ),
            array(
               0 => 'val1',
               1 => 'not val2',
            ),
            false,
         ),
         'operator priority' => array(
            'hidden',
            array(
               'show_logic' => array(
                  'OR',
                  'AND',
                  'OR',
                  'AND',
               ),
               'show_field'   => array(
                  0,
                  1,
                  2,
                  3,
               ),
               'show_condition'  => array(
                  '==',
                  '==',
                  '==',
                  '==',
               ),
               'show_value' => array(
                  'val1',
                  'val2',
                  'val3',
                  'val4',
               ),
            ),
            array(
               0 => 'val1',
               1 => 'val2',
               2 => 'val8',
               3 => 'val9',
            ),
            true,
         ),
      );
   }

   /**
    * @dataProvider answersProvider
    */
   public function testConditionsEvaluation($show_rule, $conditions, $answers, $expectedVisibility) {
      // create form
      $form = new PluginFormcreatorForm();
      $form->add([
         'entities_id'           => '0',
         'name'                  => __METHOD__,
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ]);

      // Create section
      $section = new PluginFormcreatorSection();
      $section->add([
         'name'                           => 'a section',
         'plugin_formcreator_forms_id'    => $form->getID(),
      ]);

      // Create a question
      self::$question = new PluginFormcreatorQuestion();
      self::$question->add([
         'name'                           => 'text question',
         'fieldtype'                      => 'text',
         'plugin_formcreator_sections_id' => $section->getID(),
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

      for ($i = 0; $i < 4; $i++) {
         $item = new PluginFormcreatorQuestion();
         $item->add([
            'fieldtype'                      => 'text',
            'name'                           => "question $i",
            'plugin_formcreator_sections_id' => $section->getID(),
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
         self::$questionPool[$i] = $item->getID();
      }

      foreach ($conditions['show_field'] as $id => &$showField) {
         $showField = self::$questionPool[$showField];
      }
      $realAnswers = [];
      foreach ($answers as $id => $answer) {
         $realAnswers['formcreator_field_' . self::$questionPool[$id]] = $answers[$id];
      }
      $input = $conditions + [
         'id'        => self::$question->getID(),
         'fieldtype' => 'text',
         'show_rule' => $show_rule,
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
      self::$question->update($input);
      self::$question->updateConditions($input);
      $isVisible = PluginFormcreatorFields::isVisible(self::$question->getID(), $realAnswers);
      $this->boolean($isVisible)->isEqualTo($expectedVisibility);
   }

}
