<?php
class QuestionConditionTest extends SuperAdminTestCase
{
   protected static $formData;
   protected static $sectionData;
   protected static $form;
   protected static $targetData;
   protected static $sections = [];
   protected static $questions = [];

   public static function setUpBeforeClass() {
      parent::setUpBeforeClass();

      self::$formData = [
         'entities_id'           => 0,
         'name'                  => 'a form',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ];

      self::$sectionData = [
         [
            'name'                  => 'a section',
            'questions'             => [
               [
                  'name'                  => 'text question',
                  'fieldtype'             => 'text',
               ],
               [
                  'name'                  => 'other text question',
                  'fieldtype'             => 'text',
               ],
               [
                  'name'                  => 'third text question',
                  'fieldtype'             => 'text',
               ],
            ],
         ],
      ];

      self::$targetData = [];

      self::login('glpi', 'glpi', true);
      self::$form = new PluginFormcreatorForm();
      $formId = self::$form->add(self::$formData);

      foreach (self::$sectionData as $sectionData) {
         // Keep questions data set apart from sections data
         $questionsData = $sectionData['questions'];
         unset($sectionData['questions']);

         // Create section
         $sectionData['plugin_formcreator_forms_id'] = self::$form->getID();
         $section = new PluginFormcreatorSection();
         $section->add($sectionData);
         self::$sections[] = $section;
         $sectionId = $section->getID();
         foreach ($questionsData as $questionData) {
            // Create question
            $questionData['plugin_formcreator_sections_id'] = $section->getID();
            $question = new PluginFormcreatorQuestion();
            $question->add($questionData);
            self::$questions[] = $question;

            $questionData['id'] = $question->getID();
            if (isset($questionData['show_rule']) && $questionData['show_rule'] != 'always') {
               $showFieldName = $questionData['show_field'];
               $showfield = new PluginFormcreatorQuestion();
               $showfield->getFromDBByCrit([
                  'AND' => [
                     'plugin_formcreator_sections_id' => $sectionId,
                     'name'                           => $showFieldName
                  ]
               ]);
               $questionData['show_field'] = $showfield->getID();
               $question->updateConditions($questionData);
            }
         }
         foreach (self::$targetData as $targetData) {
            $target = new PluginFormcreatorTarget();
            $targetData['plugin_formcreator_forms_id'] = $formId;
            $target->add($targetData);
         }
      }

      if (count(self::$questions) < 2) {
         throw new Exception("Need at least two questions");
      }
   }

   public function conditionDataProvider() {
      return [
         [
            [
               'show_rule'       => 'hidden',
               'show_question'   => ['dummy'],
               'show_condition'  => ['=='],
               'show_value'      => ['an accented è character'],
               'show_logic'      => ['AND'],
            ]
         ],
         /* This test currently fails due to Html::clean() in plugin_formcreator_encode()
          * No solution found yet, then test disabled
         [
            [
               'show_rule'       => 'hidden',
               'show_question'   => ['dummy'],
               'show_condition'  => ['=='],
               'show_value'      => ['a doubled  space'],
            ]
         ],*/
         [
            [
               'show_rule'       => 'hidden',
               'show_question'   => ['dummy'],
               'show_condition'  => ['=='],
               'show_value'      => ['a euro € character'],
               'show_logic'      => ['AND'],
            ]
         ],
      ];
   }

   /**
    * Checks a question shows and hides when conditions applies, assuming the reference question is single valued
    * (ie: a text field)
    *
    * @dataProvider conditionDataProvider
    */
   public function testConditionForSingleValue($condition) {
      // Setup the condition
      $question = self::$questions[1];
      $firstQuestionId = self::$questions[0]->getID();
      $secondQuestionId = $question->getID();
      $condition['show_field'] = [self::$questions[0]->getID()];
      $condition['id'] = $secondQuestionId;
      $question->update($condition + $question->fields);
      $question->updateConditions($condition);

      //Run the condition
      $currentValues = [
          "formcreator_field_$firstQuestionId"  => $condition['show_value'][0] . " and now for something completely different",
          "formcreator_field_$secondQuestionId" => '',
      ];
      $visibility = PluginFormcreatorFields::updateVisibility($currentValues);

      // Check the result
      if ($condition['show_rule'] == 'hidden') {
         $expected = false;
      } else {
         $expected = true;
      }
      $this->assertEquals($expected, $visibility["formcreator_field_$secondQuestionId"]);

      // Run the reversed condition
      $currentValues = [
          "formcreator_field_$firstQuestionId"  => $condition['show_value'][0],
          "formcreator_field_$secondQuestionId" => '',
      ];
      $visibility = PluginFormcreatorFields::updateVisibility($currentValues);

      // Check the result
      $this->assertEquals(!$expected, $visibility["formcreator_field_$secondQuestionId"]);

   }

   /**
    * Checks a question shows and hides when conditions applies, assuming the reference question is multi valued
    * (ie: checkboxes field)
    * @dataProvider conditionDataProvider
    */
   public function testConditionForMultipleValue($condition) {
      // Setup the condition
      $question = self::$questions[1];
      $firstQuestionId = self::$questions[0]->getID();
      $secondQuestionId = $question->getID();
      $condition['show_field'] = [self::$questions[0]->getID()];
      $condition['id'] = $secondQuestionId;
      $question->update($condition + $question->fields);
      $question->updateConditions($condition);

      //Run the condition
      $currentValues = [
          "formcreator_field_$firstQuestionId"  => [$condition['show_value'][0] . " and now for something completely different"],
          "formcreator_field_$secondQuestionId" => '',
      ];
      $visibility = PluginFormcreatorFields::updateVisibility($currentValues);

      // Check the result
      if ($condition['show_rule'] == 'hidden') {
         $expected = false;
      } else {
         $expected = true;
      }
      $this->assertEquals($expected, $visibility["formcreator_field_$secondQuestionId"]);

      // Run the reversed condition
      $currentValues = [
          "formcreator_field_$firstQuestionId"  => [$condition['show_value'][0]],
          "formcreator_field_$secondQuestionId" => '',
      ];
      $visibility = PluginFormcreatorFields::updateVisibility($currentValues);

      // Check the result
      $this->assertEquals(!$expected, $visibility["formcreator_field_$secondQuestionId"]);
   }

   public  function testConditionForManyQuestions() {
      $form = new PluginFormcreatorForm();
      $form->add([
         'entities_id'           => 0,
         'name'                  => 'a form',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ]);

      $section = new PluginFormcreatorSection();
      $sectionId = $section->add([
         'name' => 'section',
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $questions = [];
      $questions[0] = new PluginFormcreatorQuestion();
      $questions[1] = new PluginFormcreatorQuestion();
      $questions[2] = new PluginFormcreatorQuestion();

      // create questions
      $firstQuestionId = $questions[0]->add([
         'name'                  => 'text question 0',
         'fieldtype'             => 'text',
         'plugin_formcreator_sections_id' => $sectionId,
      ]);
      $secondQuestionId = $questions[1]->add([
         'name'                  => 'text question 1',
         'fieldtype'             => 'text',
         'plugin_formcreator_sections_id' => $sectionId,
         'show_rule'             => 'hidden',
      ]);
      $thirdQuestionId = $questions[2]->add([
         'name'                  => 'text question 2',
         'fieldtype'             => 'text',
         'plugin_formcreator_sections_id' => $sectionId,
         'show_rule'             => 'hidden',
      ]);

      // create conditions
      $condition = new PluginFormcreatorQuestion_Condition();
      $condition->add([
         'plugin_formcreator_questions_id' => $secondQuestionId,
         'show_field'      => $firstQuestionId,
         'show_condition'  => '==',
         'show_value'      => 'a',
         'show_logic'      => 'OR',
      ]);
      $condition->add([
         'plugin_formcreator_questions_id' => $thirdQuestionId,
         'show_field'      => $firstQuestionId,
         'show_condition'  => '==',
         'show_value'      => 'c',
         'show_logic'      => 'OR',
      ]);
      $condition->add([
         'plugin_formcreator_questions_id' => $thirdQuestionId,
         'show_field'      => $secondQuestionId,
         'show_condition'  => '==',
         'show_value'      => 'c',
         'show_logic'      => 'OR',
      ]);

      // test the conditions engine
      $currentValues = [
         "formcreator_field_$firstQuestionId"  => '',
         "formcreator_field_$secondQuestionId" => '',
         "formcreator_field_$thirdQuestionId" => '',
      ];
      $visibility = PluginFormcreatorFields::updateVisibility($currentValues);
      $this->assertEquals(true, $visibility["formcreator_field_$firstQuestionId"]);
      $this->assertEquals(false, $visibility["formcreator_field_$secondQuestionId"]);
      $this->assertEquals(false, $visibility["formcreator_field_$thirdQuestionId"]);

      $currentValues = [
         "formcreator_field_$firstQuestionId"  => 'a',
         "formcreator_field_$secondQuestionId" => '',
         "formcreator_field_$thirdQuestionId" => '',
      ];
      $visibility = PluginFormcreatorFields::updateVisibility($currentValues);
      $this->assertEquals(true, $visibility["formcreator_field_$firstQuestionId"]);
      $this->assertEquals(true, $visibility["formcreator_field_$secondQuestionId"]);
      $this->assertEquals(false, $visibility["formcreator_field_$thirdQuestionId"]);

      $currentValues = [
         "formcreator_field_$firstQuestionId"  => 'a',
         "formcreator_field_$secondQuestionId" => 'c',
         "formcreator_field_$thirdQuestionId" => '',
      ];
      $visibility = PluginFormcreatorFields::updateVisibility($currentValues);
      $this->assertEquals(true, $visibility["formcreator_field_$firstQuestionId"]);
      $this->assertEquals(true, $visibility["formcreator_field_$secondQuestionId"]);
      $this->assertEquals(true, $visibility["formcreator_field_$thirdQuestionId"]);

      $currentValues = [
         "formcreator_field_$firstQuestionId"  => '',
         "formcreator_field_$secondQuestionId" => 'c',
         "formcreator_field_$thirdQuestionId" => '',
      ];
      $visibility = PluginFormcreatorFields::updateVisibility($currentValues);
      $this->assertEquals(true, $visibility["formcreator_field_$firstQuestionId"]);
      $this->assertEquals(false, $visibility["formcreator_field_$secondQuestionId"]);
      $this->assertEquals(true, $visibility["formcreator_field_$thirdQuestionId"]);
   }
}