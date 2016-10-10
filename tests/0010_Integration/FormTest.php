<?php
class FormTest extends SuperAdminTestCase {

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

   public function testCrerateForm() {
      $form = new PluginFormcreatorForm();
      $formId = $form->add($this->formData);
      $this->assertFalse($form->isNewItem());

      return $form;
   }

   /**
    * @depends testCrerateForm
    * @param PluginFormCreatorForm $form
    */
   public function testUpdateForm($form) {
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
   public function testPurgeForm($form) {
      $success = $form->delete(array(
            'id'              => $form->getID(),
      ), 1);
      $this->assertTrue($success);
   }
}