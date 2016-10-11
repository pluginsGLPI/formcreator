<?php
class Form_ValidatorTest extends SuperAdminTestCase {

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

      $this->groupData = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'completename'          => 'a group',
      );
   }

   public function testInitCreateForm() {
      $form = new PluginFormcreatorForm();
      $formId = $form->add($this->formData);
      $this->assertFalse($form->isNewItem());

      return $form;
   }

   public function testInitCreateGroup() {
      $group = new Group();
      $group->import($this->groupData);

      $this->assertFalse($group->isNewItem());

      return $group;
   }

   /**
    * @depends testInitCreateForm
    * @depends testInitCreateGroup
    * @param PluginFormcreatorForm $form
    * @param Group $group
    */
   public function testCreateFormValidator(PluginFormcreatorForm $form, Group $group) {
      $form_validator = new PluginFormcreatorForm_Validator();
      $form_validator->add(array(
            'itemtype'                    => $group::getType(),
            'items_id'                    => $group->getID(),
            'plugin_formcreator_forms_id' => $form->getID(),
      ));

      $this->assertFalse($form_validator->isNewItem());
   }
}