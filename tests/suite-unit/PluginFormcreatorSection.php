<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorSection extends CommonTestCase {
   public function setup() {
      // instanciate classes
      $form           = new \PluginFormcreatorForm;
      $form_section   = new \PluginFormcreatorSection;
      $form_question  = new \PluginFormcreatorQuestion;
      $form_condition = new \PluginFormcreatorQuestion_Condition;
      $form_validator = new \PluginFormcreatorForm_Validator;
      $form_target    = new \PluginFormcreatorTarget;
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

   /**
    * @cover PluginFormcreatorSection::clone
    */
   public function testDuplicate() {
      $section       = new \PluginFormcreatorSection;
      $new_section   = new \PluginFormcreatorSection;
      $form_question = new \PluginFormcreatorQuestion;

      //get section
      plugin_formcreator_getFromDBByField($section, 'name', "test clone section");

      //clone it
      $newSection_id = $section->duplicate();
      $this->integer($newSection_id)->isGreaterThan(0);

      //get cloned section
      $new_section->getFromDB($newSection_id);

      // check uuid
      $this->string($new_section->getField('uuid'))->isNotEqualTo($section->getField('uuid'));

      // check questions
      $all_questions = $form_question->find("plugin_formcreator_sections_id = ".$section->getID());
      $all_new_questions = $form_question->find("plugin_formcreator_sections_id = ".$new_section->getID());
      $this->integer(count($all_new_questions))->isEqualTo(count($all_questions));

      // check that all question uuid are new
      $uuids = $new_uuids = [];
      foreach ($all_questions as $question) {
         $uuids[] = $question['uuid'];
      }
      foreach ($all_new_questions as $question) {
         $new_uuids[] = $question['uuid'];
      }
      $this->integer(count(array_diff($new_uuids, $uuids)))->isEqualTo(count($new_uuids));
   }
}
