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

   public function addUpdateFormProvider() {
      return [
         [

            'input' => [
               'name'         => '',
               'description'  => '',
               'content'      => '',
            ],
            'expected' => false, // An empty name should be rejected
         ],
         [
            'input' => [
               'name'         => 'être ou ne pas être',
               'description'  => 'être ou ne pas être',
               'content'      => '&lt;p&gt;être ou ne pas être&lt;/p&gt;',
            ],
            'expected' => true,
         ],
         [
            'input' => [
               'name'         => 'test d\\\'apostrophe',
               'description'  => 'test d\\\'apostrophe',
               'content'      => '&lt;p&gt;test d\\\'apostrophe&lt;/p&gt;',
            ],
            'expected' => true,
         ],
      ];
   }

   /**
    * @dataProvider addUpdateFormProvider
    * @param array $input
    * @param boolean $expected
    */
   public function testPrepareInputForAdd($input, $expected) {
      $form = new PluginFormcreatorForm();
      $output = $form->prepareInputForAdd($input);
      if ($expected === false) {
         $this->assertCount(0, $output);
      } else {
         $this->assertEquals($input['name'], $output['name']);
         $this->assertEquals($input['description'], $output['description']);
         $this->assertEquals($input['content'], $output['content']);
         $this->assertArrayHasKey('uuid', $output);
      }
   }

   /**
    * @dataProvider addUpdateFormProvider
    * @param array $input
    * @param boolean $expected
    */
   public function testPrepareInputForUpdate($input, $expected) {
      $form = new PluginFormcreatorForm();
      $form->add([
         'name' => 'anything',
      ]);
      $output = $form->prepareInputForUpdate($input);
      if ($expected === false) {
         $this->assertCount(0, $output);
      } else {
         $this->assertEquals($input['name'], $output['name']);
         $this->assertEquals($input['description'], $output['description']);
         $this->assertEquals($input['content'], $output['content']);
      }
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
