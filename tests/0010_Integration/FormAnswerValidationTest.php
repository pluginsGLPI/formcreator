<?php
class FormAnswerValidationTest extends SuperAdminTestCase {

   public function setUp() {
      parent::setUp();

      $this->formData = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'a form',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => PluginFormcreatorForm_Validator::VALIDATION_USER,
      );

      $this->sectionData = array(
            'name'                  => 'a section',
      );

      $this->userValidatorData = array(
            'itemtype'              => 'User',
            'users_id'              => '2', // user is glpi
      );

      $this->formAnswersData = array(
            'status'                => 'waiting',
            'formcreator_validator' => '2',
      );

      $this->otherValidatorUser = array(
            'name'                  => 'superadmin',
            'password'              => 'superadmin',
            '_profiles_id'          => '4',
            '_entities_id'          => 0,
            '_is_recursive'         => 1,
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
    * @param PluginFormcreatorForm $form
    */
   public function testInitCreateSection(PluginFormcreatorForm $form) {
      $section = new PluginFormcreatorSection();
      $this->sectionData = $this->sectionData + array(
            'plugin_formcreator_forms_id' => $form->getID()
      );
      $section->add($this->sectionData);
      $this->assertFalse($section->isNewItem());

      return $section;
   }

   /**
    * @depends testInitCreateForm
    * @param PluginFormcreatorForm  $form
    */
    public function testInitCreateValidator(PluginFormcreatorForm $form) {
      $formValidator = new PluginFormcreatorForm_Validator();
      $formValidator->add(
            $this->userValidatorData
            + array('plugin_formcreator_forms_id' => $form->getID())
      );
      $this->assertFalse($formValidator->isNewItem());

      return $formValidator;
   }

   /**
    * @depends testInitCreateForm
    * @param PluginFormcreatorForm $form
    */
    public function testInitCreateFormAnswer(PluginFormcreatorForm $form) {
       global $DB;

      $this->formAnswersData['formcreator_form'] = $form->getID();

      $formAnswer = new PluginFormcreatorForm_Answer();
      $formAnswer_table = PluginFormcreatorForm_Answer::getTable();
      $_POST = $this->formAnswersData;

      $result = $DB->query("SELECT MAX(`id`) AS `max_id` FROM `$formAnswer_table`");
      $maxId = $DB->fetch_assoc($result);
      $maxId = $maxId['max_id'];
      $maxId === null ? 0 : $maxId;

      $form->saveForm($this->formAnswersData);

      $result = $DB->query("SELECT MAX(`id`) AS `max_id` FROM `$formAnswer_table`");
      $newId = $DB->fetch_assoc($result);
      $newId = $newId['max_id'];

      $this->assertGreaterThan($maxId, $newId);
      $formAnswer->getFromDB($newId);
      $this->assertFalse($formAnswer->isNewItem());

      unset($_POST); // $_POST was populated but triggers CSRF check when runing next test

      return $formAnswer;
   }

   public function testInitCreateUser() {
      $this->otherValidatorUser['password2'] = $this->otherValidatorUser['password'];

      $user = new User();
      $user->add($this->otherValidatorUser);
      $this->assertFalse($user->isNewItem());

      return $user;
   }

   /**
    * @depends testInitCreateUser
    * @depends testInitCreateForm
    * @depends testInitCreateFormAnswer
    * @param User $user
    * @param PluginFormcreatorForm $form
    * @param PluginFormcreatorForm_Answer $formAnswer
    */
   public function testOtherUserValidates(User $user, PluginFormcreatorForm $form, PluginFormcreatorForm_Answer $formAnswer) {
      // Login as other user
      $this->assertTrue(self::login('superadmin', 'superadmin', true));

      $this->assertFalse($formAnswer->canValidate($form, $formAnswer));
   }

   /**
    * @depends testInitCreateUser
    * @depends testInitCreateForm
    * @depends testInitCreateFormAnswer
    * @param User $user
    * @param PluginFormcreatorForm $form
    * @param PluginFormcreatorForm_Answer $formAnswer
    */
    public function testUserValidates(User $user, PluginFormcreatorForm $form, PluginFormcreatorForm_Answer $formAnswer) {
       // Login as glpi
       $this->assertTrue(self::login('glpi', 'glpi', true));

       $this->assertTrue($formAnswer->canValidate($form, $formAnswer));
   }
}