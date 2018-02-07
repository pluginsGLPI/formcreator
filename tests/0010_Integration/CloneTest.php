<?php
class CloneTest extends SuperAdminTestCase {
   public static function setUpBeforeClass() {
      parent::setupBeforeClass();

      self::login('glpi', 'glpi');

      // instanciate classes
      $form           = new PluginFormcreatorForm;
      $form_section   = new PluginFormcreatorSection;
      $form_question  = new PluginFormcreatorQuestion;
      $form_condition = new PluginFormcreatorQuestion_Condition;
      $form_validator = new PluginFormcreatorForm_Validator;
      $form_target    = new PluginFormcreatorTarget;
      $form_profile   = new PluginFormcreatorForm_Profile;

      // create objects
      $forms_id = $form->add(['name'                => "test clone form",
                              'is_active'           => true,
                              'validation_required' => PluginFormcreatorForm_Validator::VALIDATION_USER]);

      $sections_id = $form_section->add(['name'                        => "test clone section",
                                         'plugin_formcreator_forms_id' => $forms_id]);

      $questions_id_1 = $form_question->add(['name'                           => "test clone question 1",
                                             'fieldtype'                      => 'text',
                                             'plugin_formcreator_sections_id' => $sections_id,
                                             '_parameters' => [
                                              'text' => [
                                               'regex' => '',
                                               'range' => ['min' => '', 'max' => ''],
                                              ]
                                             ],
                                            ]);
      $questions_id_2 = $form_question->add(['name'                           => "test clone question 2",
                                             'fieldtype'                      => 'textarea',
                                             'plugin_formcreator_sections_id' => $sections_id]);
   }

   /**
    * @cover PluginFormcreatorSection::clone
    */
   public function testCloneSection() {
      $section       = new PluginFormcreatorSection;
      $new_section   = new PluginFormcreatorSection;
      $form_question = new PluginFormcreatorQuestion;

      //get section
      plugin_formcreator_getFromDBByField($section, 'name', "test clone section");

      //clone it
      $this->assertTrue($section->duplicate());

      //get cloned section
      $originalId = $section->getID();
      $new_section->getFromDBByQuery("WHERE `name` = 'test clone section' AND `id` <> '$originalId'");

      // check uuid
      $this->assertNotEquals($section->getField('uuid'),
                             $new_section->getField('uuid'));

      // check questions
      $all_questions = $form_question->find("plugin_formcreator_sections_id = ".$section->getID());
      $all_new_questions = $form_question->find("plugin_formcreator_sections_id = ".$new_section->getID());
      $this->assertEquals(count($all_questions), count($all_new_questions));

      // check that all question uuid are new
      $uuids = $new_uuids = [];
      foreach ($all_questions as $question) {
         $uuids[] = $question['uuid'];
      }
      foreach ($all_new_questions as $question) {
         $new_uuids[] = $question['uuid'];
      }
      $this->assertEquals($new_uuids, array_diff($new_uuids, $uuids));
   }

   /**
    * @cover PluginFormcreatorQuestion::clone
    */
   public function testCloneQuestion() {
      $question      = new PluginFormcreatorQuestion;
      $new_question  = new PluginFormcreatorQuestion;

      //get question
      plugin_formcreator_getFromDBByField($question, 'name', "test clone question 1");

      //clone it
      $this->assertNotFalse($question->duplicate());

      //get cloned section
      $originalId = $question->getID();
      $new_question->getFromDBByQuery("WHERE `name` = 'test clone question 1' AND `id` <> '$originalId'");

      // check uuid
      $this->assertNotEquals($question->getField('uuid'),
                             $new_question->getField('uuid'));
   }
}