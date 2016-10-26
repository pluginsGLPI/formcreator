<?php
class Form_ValidatorTest extends SuperAdminTestCase {

   public function setUp() {
      parent::setUp();

      $this->formDataForGroup = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'a form for group validator',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => PluginFormcreatorForm_Validator::VALIDATION_GROUP
      );

      $this->formDataForUser = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'a form for user validator',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => PluginFormcreatorForm_Validator::VALIDATION_USER
      );
      $this->groupData = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'completename'          => 'a group',
      );
   }

   public function testInitCreateGroup() {
      $group = new Group();
      $group->import($this->groupData);

      $this->assertFalse($group->isNewItem());

      return $group;
   }

   /**
    * @depends testInitCreateGroup
    * @return PluginFormcreatorForm
    */
   public function testCreateFormForGroup(Group $group) {
      $this->formDataForGroup = $this->formDataForGroup + array(
            '_validator_groups'     => array($group->getID())
      );
      $form = new PluginFormcreatorForm();
      $formId = $form->add($this->formDataForGroup);
      $this->assertFalse($form->isNewItem());

      $form_validator = new PluginFormcreatorForm_Validator();
      $form_validator->getFromDBForItems($form, $group);
      $this->assertFalse($form_validator->isNewItem());

      return $form;
   }

   /**
    * @return PluginFormcreatorForm
    */
   public function testCreateFormForUser() {
      $user = new User;
      $user->getFromDBbyName('tech');
      $this->assertFalse($user->isNewItem());

      $this->formDataForUser = $this->formDataForUser + array(
            '_validator_users'     => array($user->getID())
      );
      $form = new PluginFormcreatorForm();
      $formId = $form->add($this->formDataForUser);
      $this->assertFalse($form->isNewItem());

      $form_validator = new PluginFormcreatorForm_Validator();
      $form_validator->getFromDBForItems($form, $user);
      $this->assertFalse($form_validator->isNewItem());

      return $form;
   }
}