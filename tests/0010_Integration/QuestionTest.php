<?php
class QuestionTest extends SuperAdminTestCase {

   public function setUp() {
      parent::setUp();

      $this->formData = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'a form',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => 0
      );

      $this->sectionData = array(
            'name'                  => 'a section',
      );

      $this->questionTextData = array(
            'name'                  => 'text question',
            'fieldtype'             => 'text'
      );
   }

   public function testInitCreateForm() {
      $form = new PluginFormcreatorForm();
      $form->add($this->formData);
      $this->assertFalse($form->isNewItem());

      return $form;
   }

   /**
    * @depends testInitCreateForm
    * @param PluginFormcreatorForm  $form
    */
   public function testInitCreateSection(PluginFormcreatorForm  $form) {
      $section = new PluginFormcreatorSection();
      $this->sectionData = $this->sectionData + array(
            'plugin_formcreator_forms_id' => $form->getID()
      );
      $section->add($this->sectionData);
      $this->assertFalse($section->isNewItem());

      return $section;
   }

   /**
    * @depends testInitCreateSection
    * @param PluginFormcreatorSection $section
    */
   public function testCreateQuestionText(PluginFormcreatorSection $section) {
      $question = new PluginFormcreatorQuestion();
      $this->questionTextData = $this->questionTextData  + array('plugin_formcreator_sections_id' => $section->getID());
      $question->add($this->questionTextData);
      $this->assertFalse($question->isNewItem());

      return $question;
   }

}