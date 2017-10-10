<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorForm extends CommonTestCase {

   protected $formData;

   protected function formProvider() {
      return [
         [
            [
               'entities_id'           => '0',
               'name'                  => 'a form',
               'description'           => 'form description',
               'content'               => 'a content',
               'is_active'             => 1,
               'validation_required'   => 0
            ]
         ]
      ];
   }

   /**
    * @dataProvider formProvider
    */
   public function testCreateForm($formData) {
      $form = new \PluginFormcreatorForm();
      $form->add($formData);
      $this->boolean($form->isNewItem())->isFalse();
   }

   /**
    * @dataProvider formProvider
    */
   public function testUpdateForm($formData) {
      $form = new \PluginFormcreatorForm();
      $form->add($formData);
      $this->boolean($form->isNewItem())->isFalse();

      $success = $form->update(array(
         'id'                    => $form->getID(),
         'name'                  => 'an updated form',
         'validation_required'   => 0
      ));
      $this->boolean($success)->isTrue(json_encode($_SESSION['MESSAGE_AFTER_REDIRECT'], JSON_PRETTY_PRINT));
   }

   /**
    * @dataProvider formProvider
    */
   public function testPurgeForm($formData) {
      $form = new \PluginFormcreatorForm();
      $form->add($formData);
      $this->boolean($form->isNewItem())->isFalse();

      $success = $form->delete(array(
         'id'              => $form->getID(),
      ), 1);
      $this->boolean($success)->isTrue();
   }

   public function testCreateValidationNotification() {
      \Config::setConfigurationValues(
         'core',
         ['use_notifications' => 1, 'notifications_mailing' => 1]
      );
      $CFG_GLPI['use_notifications'] = 1;
      $CFG_GLPI['notifications_mailing'] = 1;
      $user = new \User();
      $user->getFromDBbyName('glpi');
      $_SESSION['glpiID'] = $user->getID();
      $user->update([
         'id' => $_SESSION['glpiID'],
         '_useremails' => [
            'glpi@localhost.com',
         ]
      ]);
      $form = new \PluginFormcreatorForm();
      $form->add([
         'name'                  => 'validation notification',
         'validation_required'   => \PluginFormcreatorForm_Validator::VALIDATION_USER,
         '_validator_users'      => [$_SESSION['glpiID']],
      ]);
      $section = new \PluginFormcreatorSection();
      $section->add([
         $form::getForeignKeyField() => $form->getID(),
         'name' => 'section',
      ]);

      $formAnswer = new \PluginFormcreatorForm_Answer();
      $formAnswerId = $formAnswer->saveAnswers([
         'formcreator_form'         => $form->getID(),
         'formcreator_validator'    => $_SESSION['glpiID'],
      ]);
      $this->integer($formAnswerId)->isGreaterThan(0);

      // 1 notification to the validator
      // 1 notification to the requester
      $notification = new \QueuedNotification();
      $foundNotifications = $notification->find("`itemtype` = 'PluginFormcreatorForm_Answer' AND `items_id` = '$formAnswerId'");
      $this->integer(count($foundNotifications))->isEqualTo(2);
   }
}
