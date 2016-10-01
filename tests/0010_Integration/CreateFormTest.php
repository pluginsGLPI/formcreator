<?php
class CreateFormTest extends SuperAdminTestCase
{
   public function testCrerateForm() {
      $form = new PluginFormcreatorForm();
      $formId = $form->add(array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'a form',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => '0'
      ));
      $this->assertFalse($form->isNewItem());
      return $form;
   }
}