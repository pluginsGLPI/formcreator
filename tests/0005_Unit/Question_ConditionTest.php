<?php
class Question_ConditionTest extends SuperAdminTestCase {

   static $question;

   static $questionPool = array();

   public static function setUpBeforeClass() {
      parent::setupBeforeClass();

      // create form
      $form = new PluginFormcreatorForm();
      $form->add(array(
            'entities_id'           => '0',
            'name'                  => 'a form',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => 0
      ));

      // Create section
      $section = new PluginFormcreatorSection();
      $section->add(array(
            'name'                           => 'a section',
            'plugin_formcreator_forms_id'    => $form->getID(),
      ));

      // Create a question
      self::$question = new PluginFormcreatorQuestion();
      self::$question->add(array(
            'name'                           => 'text question',
            'fieldtype'                      => 'text',
            'plugin_formcreator_sections_id' => $section->getID(),
      ));

      for ($i = 0; $i < 4; $i++) {
         $item = new PluginFormcreatorQuestion();
         $item->add(array(
               'fieldtype'                      => 'text',
               'name'                           => "question $i",
               'plugin_formcreator_sections_id' => $section->getID(),
         ));
         self::$questionPool[$i] = $item->getID();
      }
   }

   public function answersProvider() {
      $test = 0;
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
                  array(),
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
            'failed multiple condition AND' => array(
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
      foreach ($conditions['show_field'] as $id => &$showField) {
         $showField = self::$questionPool[$showField];
      }
      $realAnswers = array();
      foreach ($answers as $id => $answer) {
         $realAnswers[self::$questionPool[$id]] = $answers[$id];
      }
      $input = $conditions + array(
            'id'        => self::$question->getID(),
            'fieldtype' => 'text',
            'show_rule' => $show_rule,
      );
      self::$question->update($input);
      self::$question->updateConditions($input);
      $isVisible = PluginFormcreatorFields::isVisible(self::$question->getID(), $realAnswers);
      $this->assertEquals($expectedVisibility, $isVisible);
   }

}
