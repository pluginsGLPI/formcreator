<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;


class PluginFormcreatorForm_Validator extends CommonTestCase {

   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testCreateFormForGroup':
         case 'testCreateFormForUser':
            $this->boolean(self::login('glpi', 'glpi', true))->isTrue();
            break;
      }
   }

   public function testCreateFormForGroup() {
      $group = new \Group();
      $groupId = $group->import([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'completename'          => 'a group',
      ]);

      $group->getFromDB($groupId);
      $this->boolean($group->isNewItem())->isFalse();

      $form = new \PluginFormcreatorForm();
      $formId = $form->add([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => 'a form for group validator',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => \PluginFormcreatorForm_Validator::VALIDATION_GROUP,
         '_validator_groups'     => [$group->getID()]
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $form_validator = new \PluginFormcreatorForm_Validator();
      $form_validator->getFromDBForItems($form, $group);
      $this->boolean($form_validator->isNewItem())->isFalse();
   }

   public function testCreateFormForUser() {
      $user = new \User;
      $user->getFromDBbyName('tech');
      $this->boolean($user->isNewItem())->isFalse();

      $form = new \PluginFormcreatorForm();
      $formId = $form->add([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => 'a form for user validator',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => \PluginFormcreatorForm_Validator::VALIDATION_USER,
         '_validator_users'     => [$user->getID()]
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $form_validator = new \PluginFormcreatorForm_Validator();
      $form_validator->getFromDBForItems($form, $user);
      $this->boolean($form_validator->isNewItem())->isFalse();
   }
}