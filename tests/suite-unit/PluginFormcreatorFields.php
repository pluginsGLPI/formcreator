<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorFields extends CommonTestCase {

   public function answersProvider() {
      return [
         'no condition' => [
            'always',
            [
               'show_logic' => [],
               'show_field'   => [],
               'show_condition'  => [],
               'show_value' => [],
            ],
            [],
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
   public function testIsVisible($show_rule, $conditions, $answers, $expectedVisibility) {
      // create form
      $form = new \PluginFormcreatorForm();
      $form->add([
         'entities_id'           => '0',
         'name'                  => 'a form',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ]);

      // Create section
      $section = new \PluginFormcreatorSection();
      $section->add([
         'name'                           => 'a section',
         'plugin_formcreator_forms_id'    => $form->getID(),
      ]);

      // Create a question
      $question = new \PluginFormcreatorQuestion();
      $question->add([
         'name'                           => 'text question',
         'fieldtype'                      => 'text',
         'plugin_formcreator_sections_id' => $section->getID(),
      ]);

      $questionPool = [];
      for ($i = 0; $i < 4; $i++) {
         $item = new \PluginFormcreatorQuestion();
         $item->add([
            'fieldtype'                      => 'text',
            'name'                           => "question $i",
            'plugin_formcreator_sections_id' => $section->getID(),
         ]);
         $questionPool[$i] = $item->getID();
      }

      foreach ($conditions['show_field'] as $id => &$showField) {
         $showField = $questionPool[$showField];
      }
      $realAnswers = [];
      foreach ($answers as $id => $answer) {
         $realAnswers['formcreator_field_' . $questionPool[$id]] = $answers[$id];
      }
      $input = $conditions + [
            'id'        => $question->getID(),
            'fieldtype' => 'text',
            'show_rule' => $show_rule,
      ];
      $question->update($input);
      $question->updateConditions($input);
      $isVisible = \PluginFormcreatorFields::isVisible($question->getID(), $realAnswers);
      $this->boolean((boolean) $isVisible)->isEqualTo($expectedVisibility);
   }
}
