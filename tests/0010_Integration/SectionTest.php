<?php
class SectionTest extends SuperAdminTestCase {

   public function setUp() {
      parent::setUp();

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
   }

   public function testInitCreateForm() {
      $form = new PluginFormcreatorForm();
      $form->add($this->formData);

      return $form;
   }

   /**
    * @depends testInitCreateForm
    * @param PluginFormCreatorForm $form
    */
   public function testCreateSection(PluginFormCreatorForm $form) {
      $section = new PluginFormcreatorSection();
      $this->sectionData = $this->sectionData + ['plugin_formcreator_forms_id' => $form->getID()];
      $section->add($this->sectionData);
      $this->assertFalse($section->isNewItem());

      return $section;
   }

   /**
    * @depends testCreateSection
    * @param PluginFormCreatorSection $section
    */
   public function testUpdateSection(PluginFormCreatorSection $section) {
      $success = $section->update([
            'id'     => $section->getID(),
            'name'   => 'section renamed'
      ]);
      $this->assertTrue($success);
   }

   /**
    * @depends testCreateSection
    * @param PluginFormCreatorSection $section
    */
   public function testPurgeSection(PluginFormCreatorSection $section) {
      $success = $section->delete([
            'id' => $section->getID()
      ], 1);
      $this->assertTrue($success);
   }
}
