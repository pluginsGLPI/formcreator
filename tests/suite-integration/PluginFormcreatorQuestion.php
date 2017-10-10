<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorQuestion extends CommonTestCase {

   private $formData = null;

   private $sectionData = null;

   private $questionTextData = null;

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testCreateQuestionText':
            $this->login('glpi', 'glpi');
            break;
      }
   }

   public function testCreateQuestionText() {
      $this->formData = [
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => 'a form',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ];

      $this->sectionData = [
            'name'                  => 'a section',
      ];

      $this->questionTextData = [
            'name'                  => 'text question',
            'fieldtype'             => 'text'
      ];

      $form = new \PluginFormcreatorForm();
      $form->add($this->formData);
      $this->boolean($form->isNewItem())->isFalse();

      $section = new \PluginFormcreatorSection();
      $this->sectionData = $this->sectionData + [
            'plugin_formcreator_forms_id' => $form->getID()
      ];
      $section->add($this->sectionData);
      $this->boolean($section->isNewItem())->isFalse();

      $question = new \PluginFormcreatorQuestion();
      $this->questionTextData = $this->questionTextData  + ['plugin_formcreator_sections_id' => $section->getID()];
      $question->add($this->questionTextData);
      $this->boolean($question->isNewItem())->isFalse();

      return $question;
   }
}