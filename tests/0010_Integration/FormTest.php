<?php
class FormTest extends SuperAdminTestCase {

   protected $formData;

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
   }

   public function testCreateForm() {
      $form = new PluginFormcreatorForm();
      $formId = $form->add($this->formData);
      $this->assertFalse($form->isNewItem());

      return $form;
   }

   /**
    * @depends testCreateForm
    * @param PluginFormCreatorForm $form
    */
   public function testUpdateForm(PluginFormcreatorForm $form) {
      $success = $form->update(array(
            'id'                    => $form->getID(),
            'name'                  => 'an updated form',
            'validation_required'   => 0
      ));
      $this->assertTrue($success);

      return $form;
   }

   /**
    * @depends testUpdateForm
    * @param PluginFormCreatorForm $form
    */
   public function testPurgeForm(PluginFormcreatorForm $form) {
      $success = $form->delete(array(
            'id'              => $form->getID(),
      ), 1);
      $this->assertTrue($success);
   }
}