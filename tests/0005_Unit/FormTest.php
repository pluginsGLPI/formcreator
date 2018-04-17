<?php
class FormTest extends SuperAdminTestCase {

   protected $formData;

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
      $success = $form->update([
         'id'                    => $form->getID(),
         'name'                  => 'an updated form',
         'validation_required'   => 0
      ]);
      $this->assertTrue($success);

      return $form;
   }

   /**
    * @depends testUpdateForm
    * @param PluginFormCreatorForm $form
    */
   public function testPurgeForm(PluginFormcreatorForm $form) {
      $success = $form->delete([
         'id'              => $form->getID(),
      ], 1);
      $this->assertTrue($success);
   }

   public function testCreateValidationNotification() {
      global $CFG_GLPI;
      Config::setConfigurationValues(
         'core',
         ['use_notifications' => 1, 'notifications_mailing' => 1]
      );
      $CFG_GLPI['use_notifications'] = 1;
      $CFG_GLPI['notifications_mailing'] = 1;
      $user = new USer();
      $user->update([
         'id' => $_SESSION['glpiID'],
         '_useremails' => [
            'glpi@localhost.com',
         ]
      ]);
      $form = new PluginFormcreatorForm();
      $form->add([
         'name'                  => 'validation notification',
         'validation_required'   => PluginFormcreatorForm_Validator::VALIDATION_USER,
         '_validator_users'      => [$_SESSION['glpiID']],
      ]);
      $section = new PluginFormcreatorSection();
      $section->add([
         $form::getForeignKeyField() => $form->getID(),
         'name' => 'section',
      ]);

      $notification = new QueuedNotification();
      $notificationCount = count($notification->find());
      $formAnswer = new PluginFormcreatorForm_Answer();
      $formAnswer->saveAnswers([
         'formcreator_form'         => $form->getID(),
         'formcreator_validator'    => $_SESSION['glpiID'],
      ]);
      // 1 notification to the validator
      // 1 notification to the requester
      $this->assertCount($notificationCount + 2, $notification->find());
   }
}
