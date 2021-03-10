<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorForm extends CommonTestCase {

   protected $formData;

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testImport':
         case 'testCanPurgeItem':
         case 'testDuplicate':
         case 'testCreateValidationNotification':
         case 'testGetTranslatableStrings':
            $this->login('glpi', 'glpi');
      }
   }

   public function providerGetTypeName() {
      return [
         [
            0,
            'Forms'
         ],
         [
            1,
            'Form'
         ],
         [
            2,
            'Forms'
         ],
      ];
   }

   /**
    * @dataProvider providerGetTypeName
    *
    * @param integer $nb
    * @param string $expected
    * @return void
    */
   public function testGetTypeName($nb, $expected) {
      $instance = new $this->newTestedInstance();
      $output = $instance->getTypeName($nb);
      $this->string($output)->isEqualTo($expected);
   }

   public function testGetEnumAccessType() {
      $output = \PluginFormcreatorForm::getEnumAccessType();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorForm::ACCESS_PUBLIC     => __('Public access', 'formcreator'),
         \PluginFormcreatorForm::ACCESS_PRIVATE    => __('Private access', 'formcreator'),
         \PluginFormcreatorForm::ACCESS_RESTRICTED => __('Restricted access', 'formcreator'),
      ]);
   }

   public function testCanCreate() {
      $this->login('glpi', 'glpi');
      $output = \PluginFormcreatorForm::canCreate();
      $this->boolean((bool) $output)->isTrue();

      $this->login('normal', 'normal');
      $output = \PluginFormcreatorForm::canCreate();
      $this->boolean((bool) $output)->isFalse();

      $this->login('post-only', 'postonly');
      $output = \PluginFormcreatorForm::canCreate();
      $this->boolean((bool) $output)->isFalse();
   }

   public function testCanView() {
      $this->login('glpi', 'glpi');
      $output = \PluginFormcreatorForm::canView();
      $this->boolean((bool) $output)->isTrue();

      $this->login('normal', 'normal');
      $output = \PluginFormcreatorForm::canView();
      $this->boolean((bool) $output)->isTrue();

      $this->login('post-only', 'postonly');
      $output = \PluginFormcreatorForm::canView();
      $this->boolean((bool) $output)->isTrue();
   }

   public function testCanDelete() {
      $this->login('glpi', 'glpi');
      $output = \PluginFormcreatorForm::canDelete();
      $this->boolean((bool) $output)->isTrue();

      $this->login('normal', 'normal');
      $output = \PluginFormcreatorForm::canDelete();
      $this->boolean((bool) $output)->isFalse();

      $this->login('post-only', 'postonly');
      $output = \PluginFormcreatorForm::canCreate();
      $this->boolean((bool) $output)->isFalse();
   }

   public function testCanPurge() {
      $this->login('glpi', 'glpi');
      $output = \PluginFormcreatorForm::canPurge();
      $this->boolean((bool) $output)->isTrue();

      $this->login('normal', 'normal');
      $output = \PluginFormcreatorForm::canPurge();
      $this->boolean((bool) $output)->isFalse();
      $this->login('post-only', 'postonly');
      $output = \PluginFormcreatorForm::canCreate();
      $this->boolean((bool) $output)->isFalse();
   }

   public function testCanPurgeItem() {
      $form = $this->getForm();
      $output = $form->canPurgeItem();
      $this->boolean((boolean) $output)->isTrue();

      $this->disableDebug();
      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswer->add([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
      ]);
      $this->restoreDebug();

      $output = $form->canPurgeItem();
      $this->boolean((boolean) $output)->isFalse();
   }

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

   public function providerPrepareInputForAdd() {
      return [
         [
            'input' => [
               'name'         => '',
               'description'  => '',
               'content'      => '',
            ],
            'expected' => false, // An empty name should be rejected
            'message'  => 'The name cannot be empty!',
         ],
         [
            'input' => [
               'name'         => 'être ou ne pas être',
               'description'  => 'être ou ne pas être',
               'content'      => '&lt;p&gt;être ou ne pas être&lt;/p&gt;',
            ],
            'expected' => true,
            'message' => '',
         ],
         [
            'input' => [
               'name'         => 'test d\\\'apostrophe',
               'description'  => 'test d\\\'apostrophe',
               'content'      => '&lt;p&gt;test d\\\'apostrophe&lt;/p&gt;',
            ],
            'expected' => true,
            'message' => '',
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareInputForAdd
    * @param array $input
    * @param array|boolean $expected
    * @param string $message
    */
   public function testPrepareInputForAdd($input, $expected, $expectedMessage) {
      $instance = $this->newTestedInstance();
      $output = $instance->prepareInputForAdd($input);
      if ($expected === false) {
         $this->array($output)->size->isEqualTo(0);
         $this->sessionHasMessage($expectedMessage, ERROR);
      } else {
         $this->string($output['name'])->isEqualTo($input['name']);
         $this->string($output['description'])->isEqualTo($output['description']);
         $this->string($output['content'])->isEqualTo($output['content']);
         $this->array($output)->hasKey('uuid');
      }
   }

   public function providerPrepareInputForUpdate() {
      return $this->providerPrepareInputForAdd();
   }

   /**
    * @dataProvider providerPrepareInputForUpdate
    * @param array $input
    * @param boolean $expected
    */
   public function testPrepareInputForUpdate($input, $expected, $expectedMessage) {
      $instance = new \PluginFormcreatorForm();
      $instance->add([
         'name' => 'anything',
      ]);
      $output = $instance->prepareInputForUpdate($input);
      if ($expected === false) {
         $this->array($output)->size->isEqualTo(0);
      } else {
         $this->string($output['name'])->isEqualTo($input['name']);
         $this->string($output['description'])->isEqualTo($output['description']);
         $this->string($output['content'])->isEqualTo($output['content']);
      }
   }

   public function testDefineTabs() {
      $instance = $this->newTestedInstance();
      $output = $instance->defineTabs();
      $expected = [
         'PluginFormcreatorForm$main' => "Form",
         'PluginFormcreatorQuestion$1' => "Questions",
         'PluginFormcreatorForm_Profile$1' => "Access types",
         'PluginFormcreatorForm$1' => "Targets",
         'PluginFormcreatorForm$2' => "Preview",
         'PluginFormcreatorFormAnswer$1' => "Form answers",
         'PluginFormcreatorForm_Language$1' => 'Form languages',
         'Log$1' => "Historical",
      ];
      $this->array($output)
         ->isEqualTo($expected)
         ->hasSize(count($expected));
   }

   public function testGetTabNameForItem() {
      $form = $this->getForm();
      $item = new \Central();
      $output = $form->getTabNameForItem($item);
      $this->string($output)->isEqualTo('Forms');

      $item = $form;
      $output = $form->getTabNameForItem($item);
      $this->array($output)->isEqualTo([
         1 => 'Targets',
         2 => 'Preview',
      ]);

      $item = new \User();
      $output = $form->getTabNameForItem($item);
      $this->string($output)->isEqualTo('');
   }

   public function testPost_purgeItem() {
      $form = $this->getForm([
         'validation_required' => \PluginFormcreatorForm_Validator::VALIDATION_USER,
         'users_id' => 2, // glpi
      ]);
      $section = $this->getSection([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $targetChange = $this->getTargetChange([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $targetTicket = $this->getTargetTicket([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $validator = new \PluginFormcreatorForm_Validator();
      $validator->getFromDBByCrit([
         'plugin_formcreator_forms_id' => $form->getID(),
         'itemtype' => \User::class,
      ]);

      $formProfile = new \PluginFormcreatorForm_Profile();
      $formProfile->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'profiles_id' => 6 // technician
      ]);

      $form->delete([
         'id' => $form->getID(),
      ], 1);

      $output = $section->getFromDB($section->getID());
      $this->boolean($output)->isFalse();

      $output = $targetChange->getFromDB($targetChange->getID());
      $this->boolean($output)->isFalse();

      $output = $targetTicket->getFromDB($targetTicket->getID());
      $this->boolean($output)->isFalse();

      $output = $validator->getFromDB($validator->getID());
      $this->boolean($output)->isFalse();

      $output = $formProfile->getFromDB($formProfile->getID());
      $this->boolean($output)->isFalse();
   }

   public function testUpdateValidators() {
      $form = $this->getForm();

      $formValidator = new \PluginFormcreatorForm_Validator();
      $rows = $formValidator->find([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->array($rows)->hasSize(0);

      $form = $this->getForm([
         'validation_required' => \PluginFormcreatorForm_Validator::VALIDATION_USER,
         '_validator_users' => ['2'], // glpi account
      ]);

      $rows = $formValidator->find([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->array($rows)->hasSize(1);
      $formValidator->getFromResultSet(array_pop($rows));
      $this->integer((int) $formValidator->fields['items_id'])->isEqualTo(2);
      $this->string( $formValidator->fields['itemtype'])->isEqualTo(\User::class);
      $this->integer((int) $formValidator->fields['plugin_formcreator_forms_id'])->isEqualTo($form->getID());

      $form = $this->getForm([
         'validation_required' => \PluginFormcreatorForm_Validator::VALIDATION_GROUP,
         '_validator_groups' => ['1'], // a group ID (not created in this test)
      ]);
      $rows = $formValidator->find([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->array($rows)->hasSize(1);
      $formValidator->getFromResultSet(array_pop($rows));
      $this->integer((int) $formValidator->fields['items_id'])->isEqualTo(1);
      $this->string( $formValidator->fields['itemtype'])->isEqualTo(\Group::class);
      $this->integer((int) $formValidator->fields['plugin_formcreator_forms_id'])->isEqualTo($form->getID());
   }

   public function testIncreateUsageCount() {
      $form = $this->getForm();
      $this->integer((int) $form->fields['usage_count'])->isEqualTo(0);

      $form->increaseUsageCount();
      $this->integer((int) $form->fields['usage_count'])->isEqualTo(1);
   }

   public function testGetByQuestionId() {
      $question = $this->getQuestion();
      $section = new \PluginFormcreatorSection();
      $section->getFromDB($question->fields['plugin_formcreator_sections_id']);
      $expected = $section->fields['plugin_formcreator_forms_id'];
      $form = $this->newTestedInstance();
      $form->getByQuestionId($question->getID());

      $this->integer((int) $form->getID())->isEqualTo($expected);
   }

   public function testCreateValidationNotification() {
      global $DB, $CFG_GLPI;

      // Enable notifications in GLPI
      \Config::setConfigurationValues(
         'core',
         ['use_notifications' => 1, 'notifications_mailing' => 1]
      );
      $CFG_GLPI['use_notifications'] = 1;
      $CFG_GLPI['notifications_mailing'] = 1;
      $user = new \User();
      $user->getFromDBbyName('glpi');
      $_SESSION['glpiID'] = $user->getID();
      $useremail = new \UserEmail();
      $useremail->deleteByCriteria([
         'users_id' => $user->getID(),
      ]);
      $user->update([
         'id' => $_SESSION['glpiID'],
         '_useremails' => [
            'glpi@localhost.com',
         ]
      ]);
      $form = $this->getForm([
         'name'                  => 'validation notification',
         'validation_required'   => \PluginFormcreatorForm_Validator::VALIDATION_USER,
         '_validator_users'      => [$_SESSION['glpiID']],
      ]);
      $this->getSection([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
         'name' => 'section',
      ]);

      $formAnswer = new \PluginFormcreatorFormAnswer();
      $this->disableDebug();
      $formAnswerId = $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'formcreator_validator'       => $_SESSION['glpiID'],
      ]);
      $this->restoreDebug();
      $this->boolean($formAnswer->isNewItem())->isFalse();

      // 1 notification to the validator
      // 1 notification to the requester
      $foundNotifications = $DB->request([
         'COUNT' => 'cpt',
         'FROM'  => \QueuedNotification::getTable(),
         'WHERE' => [
            'itemtype' => \PluginFormcreatorFormAnswer::class,
            'items_id' => $formAnswerId,
         ]
      ])->next();
      $this->integer((int) $foundNotifications['cpt'])->isEqualTo(2);
   }

   public function testExport() {
      $instance = $this->newTestedInstance();

      // Try to export an empty item
      $this->exception(function () use ($instance) {
         $instance->export();
      })->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ExportFailureException::class);

      // Prepare an item to export
      $instance = $this->getForm();
      $instance->getFromDB($instance->getID());

      // Export the item without the ID and with UUID
      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
         'name',
         'is_recursive',
         'icon',
         'icon_color',
         'background_color',
         'access_rights',
         'description',
         'content',
         'is_active',
         'language',
         'helpdesk_home',
         'is_deleted',
         'validation_required',
         'is_default',
         'is_captcha_enabled',
         'show_rule',
      ];
      $extraFields = [
         '_entity',
         '_plugin_formcreator_category',
         '_profiles',
         '_sections',
         '_targets',
         '_validators',
         '_conditions',
         '_translations',
      ];
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['uuid'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));

      // Export the item without the UUID and with ID
      $output = $instance->export(true);
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['id'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));
   }

   protected function _checkForm($form = []) {
      $keys = [
         'is_recursive',
         'access_rights',
         'requesttype',
         'name',
         'description',
         'content',
         'is_active',
         'language',
         'helpdesk_home',
         'is_deleted',
         'validation_required',
         'is_default',
         'uuid',
         '_sections',
         '_validators',
         '_targets',
         '_profiles',
      ];
      $this->array($form)->notHasKeys([
         'id',
         'plugin_formcreator_categories_id',
         'entities_id',
         'usage_count',
      ]);
      $this->array($form)
         ->hasKeys($keys)
         ->size->isEqualTo(count($keys));
   }

   public function testGetInterface() {
      // test Public access
      \Session::destroy();
      $output = \PluginFormcreatorForm::getInterface();
      $this->string($output)->isEqualTo('public');

      // test normal interface
      $this->login('glpi', 'glpi');
      $output = \PluginFormcreatorForm::getInterface();
      $this->string($output)->isEqualTo('central');

      // test simplified interface
      $entityConfig = new \PluginFormcreatorEntityConfig();
      $entityConfig->update([
         'id' => '0',
         'replace_helpdesk' => '0',
      ]);
      $this->login('post-only', 'postonly');
      $output = \PluginFormcreatorForm::getInterface();
      $this->string($output)->isEqualTo('self-service');

      // test service catalog
      $entityConfig = new \PluginFormcreatorEntityConfig();
      $entityConfig->update([
         'id' => '0',
         'replace_helpdesk' => \PluginFormcreatorEntityConfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG,
      ]);
      $this->login('post-only', 'postonly');
      $output = \PluginFormcreatorForm::getInterface();
      $this->string($output)->isEqualTo('servicecatalog');

      $entityConfig = new \PluginFormcreatorEntityConfig();
      $entityConfig->update([
         'id' => '0',
         'replace_helpdesk' => \PluginFormcreatorEntityConfig::CONFIG_EXTENDED_SERVICE_CATALOG,
      ]);
      $this->login('post-only', 'postonly');
      $output = \PluginFormcreatorForm::getInterface();
      $this->string($output)->isEqualTo('servicecatalog');

   }


   public function providerIsPublicAcess() {
      return [
         'not public' => [
            'input' => [
               'access_rights' => (string) \PluginFormcreatorForm::ACCESS_PRIVATE,
               'name' => $this->getUniqueString()
            ],
            'expected' => false,
         ],
         'public' => [
            'input' => [
               'access_rights' => (string) \PluginFormcreatorForm::ACCESS_PUBLIC,
               'name' => $this->getUniqueString()
            ],
            'expected' => true,
         ],
         'by profile' => [
            'input' => [
               'access_rights' => (string) \PluginFormcreatorForm::ACCESS_RESTRICTED,
               'name' => $this->getUniqueString()
            ],
            'expected' => false,
         ],
      ];
   }

   /**
    * @dataProvider providerIsPublicAcess
    */
   public function testIsPublicAcess($input, $expected) {
      $instance = new $this->newTestedInstance();
      $instance->add($input);
      $this->boolean($instance->isNewItem())->isFalse();
      $output = $instance->isPublicAccess();
      $this->boolean($output)->isEqualTo($expected);
   }
   public function providerGetFromSection() {
      $section = $this->getSection();
      $section->getField(\PluginFormcreatorForm::getForeignKeyField());
      $dataset = [
         [
            'section'  => $section,
            'expectedId' => true,
         ],
         [
            'section'  => new \PluginFormcreatorSection(),
            'expected' => false,
         ],
      ];
      return $dataset;
   }

   /**
    * @dataProvider providerGetFromSection
    */
   public function testgetFormFromSection($section, $expected) {
      $form = new \PluginFormcreatorForm();
      $output = $form->getFromDBBySection($section);
      $this->boolean($output)->isEqualTo($expected);
   }

   public function testImport() {
      $uuid = plugin_formcreator_getUuid();
      $input = [
         'name' => $this->getUniqueString(),
         '_entity' => 'Root entity',
         'is_recursive' => '1',
         'access_rights' => \PluginFormcreatorForm::ACCESS_RESTRICTED,
         'description' => '',
         'content' => '',
         '_plugin_formcreator_category' => '',
         'is_active' => '1',
         'language' => '',
         'helpdesk_home' => '1',
         'is_deleted' => '0',
         'validation_required' => '0',
         'usage_count' => '0',
         'is_default' => '0',
         'show_rule'  => '1',
         'uuid' => $uuid,
      ];

      $linker = new \PluginFormcreatorLinker ();
      $formId = \PluginFormcreatorForm::import($linker, $input);
      $this->integer($formId)->isGreaterThan(0);

      unset($input['uuid']);

      $this->exception(
         function() use($linker, $input) {
            \PluginFormcreatorForm::import($linker, $input);
         }
      )->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ImportFailureException::class)
      ->hasMessage('UUID or ID is mandatory for Form'); // passes

      $input['id'] = $formId;
      $formId2 = \PluginFormcreatorForm::import($linker, $input);
      $this->variable($formId2)->isNotFalse();
      $this->integer((int) $formId)->isNotEqualTo($formId2);
   }

   /**
    * not to be run by atoum because testEnableDocumentType depends on the
    * existence of the json document type
    *
    * @return void
    */
   public function _testCreateDocumentType() {
      $documentType = new \DocumentType();
      $documentType->deleteByCriteria([
         'ext' => 'json'
      ]);

      $rows = $documentType->find([
         'ext' => 'json',
      ]);
      $this->array($rows)->hasSize(0);

      $instance = $this->newTestedInstance();
      $instance->createDocumentType();
      $rows = $documentType->find([
         'ext' => 'json',
      ]);
      $this->array($rows)->hasSize(1);
   }

   public function testEnableDocumentType() {
      $this->_testCreateDocumentType();

      $documentType = new \DocumentType();
      $documentType->getFromDBByCrit([
        'ext' => 'json'
      ]);
      $success = $documentType->update([
        'id' => $documentType->getID(),
        'is_uploadable' => '0',
      ]);
      $this->boolean($success)->isTrue();

      $instance = $this->newTestedInstance();
      $instance->enableDocumentType();
      $rows = $documentType->find([
        'ext' => 'json',
      ]);
      $this->array($rows)->hasSize(1);
      $row = array_pop($rows);
      $this->integer((int) $row['is_uploadable'])->isEqualTo(1);
   }

   public function providerAddTarget() {
      // Empty error messages
      $_SESSION["MESSAGE_AFTER_REDIRECT"] = [];

      // Have a non existent form ID
      $form = $this->getForm();
      $form->delete([
         'id' => $form->getID()
      ], 1);
      return [
         [
            'input' => [
               'itemtype' => 'Nothing',
               'plugin_formcreator_forms_id' => $form->getID(),
            ],
            'expected' => false,
            'message'  => 'Unsupported target type.',
         ],
         [
            'input' => [
               'name' => 'foo',
               'itemtype' => \PluginFormcreatorTargetTicket::class,
               'plugin_formcreator_forms_id' => $form->getID(),
            ],
            'expected' => false,
            'message'  => 'A target must be associated to an existing form.',
         ],
         [
            'input' => [
               'name' => 'foo',
               'itemtype' => \PluginFormcreatorTargetTicket::class,
               'plugin_formcreator_forms_id' => $this->getForm()->getID(),
            ],
            'expected' => true,
            'message'  => null,
         ],
         [
            'input' => [
               'name' => 'foo',
               'itemtype' => \PluginFormcreatorTargetChange::class,
               'plugin_formcreator_forms_id' => $this->getForm()->getID(),
            ],
            'expected' => true,
            'message'  => null,
         ],
      ];
   }

   /**
    * @dataProvider providerAddTarget
    */
   public function testAddTarget($input, $expected, $message) {
      // Clean error messages
      $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];

      $instance = $this->newTestedInstance();
      $output = $instance->addTarget($input);

      if ($expected === false) {
         //End of test on expected failure
         $this->boolean($output)->isEqualTo($expected);
         $this->sessionHasMessage($message, ERROR);
         return;
      }
      $this->variable($output)->isNotFalse();
      $this->integer($output);

      $target = new $input['itemtype']();
      $rows = $target->find([
         'plugin_formcreator_forms_id' => $input['plugin_formcreator_forms_id'],
      ]);
      $this->array($rows)->hasSize(1);
   }

   public function testDeleteTarget() {
      $instance = $this->newTestedInstance();

      $output = $instance->deleteTarget([
         'itemtype' => 'Nothing',
      ]);
      $this->boolean($output)->isFalse();

      $output = $instance->addTarget([
         'name' => 'foo',
         'itemtype' => \PluginFormcreatorTargetChange::class,
         'plugin_formcreator_forms_id' => $this->getForm()->getID(),
      ]);
      $this->variable($output)->isNotFalse();
      $this->integer($output);
      $instance->deleteTarget([
         'itemtype' => \PluginFormcreatorTargetChange::class,
         'items_id' => $output,
      ]);

      $target = new \PluginFormcreatorTargetChange();
      $output = $target->getFromDB($output);
      $this->boolean($output)->isFalse();

   }

   public function testDuplicate() {
      // get form
      $form = $this->getForm([
         'name'                  => 'a form',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0,
      ]);

      $section_ids = [];
      $section_ids[] = $this->getSection([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      $section_ids[] = $this->getSection([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      $targetTicket_ids = [];
      $targetChange_ids = [];

      $targetTicket_ids[] = $this->getTargetTicket([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      $targetChange_ids[] = $this->getTargetChange([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      // clone it
      $newForm_id = $form->duplicate();
      $this->integer($newForm_id)->isGreaterThan(0);

      // get cloned form
      $new_form = new \PluginFormcreatorForm();
      $new_form->getFromDB($newForm_id);

      // check uuid
      $this->string($new_form->getField('uuid'))->isNotEqualTo($form->getField('uuid'));

      // check sections
      $all_sections = (new \PluginFormcreatorSection())->getSectionsFromForm($form->getID());
      $this->integer(count($all_sections))->isEqualTo(count($section_ids));
      $all_new_sections = (new \PluginFormcreatorSection())->getSectionsFromForm($new_form->getID());
      $this->integer(count($all_sections))->isEqualTo(count($section_ids));

      // check that all sections uuid are new
      $uuids = $new_uuids = [];
      foreach ($all_sections as $section) {
         $uuids[] = $section->fields['uuid'];
      }
      foreach ($all_new_sections as $section) {
         $new_uuids[] = $section->fields['uuid'];
      }
      $this->integer(count(array_diff($new_uuids, $uuids)))->isEqualTo(count($new_uuids));

      // check target tickets
      $all_targetTickets = (new \PluginFormcreatorTargetTicket())->getTargetsForForm($form->getID());
      $this->integer(count($all_sections))->isEqualTo(count($section_ids));
      $all_new_targetTickets = (new \PluginFormcreatorTargetTicket())->getTargetsForForm($new_form->getID());
      $this->integer(count($all_sections))->isEqualTo(count($section_ids));

      // check that all sections uuid are new
      foreach ($all_targetTickets as $targetTicket) {
         $uuids[] = $targetTicket->fields['uuid'];
      }
      foreach ($all_new_targetTickets as $targetTicket) {
         $new_uuids[] = $targetTicket->fields['uuid'];
      }
      $this->integer(count(array_diff($new_uuids, $uuids)))->isEqualTo(count($new_uuids));

      // check target changes
      $all_targetChanges = (new \PluginFormcreatorTargetChange())->getTargetsForForm($form->getID());
      $this->integer(count($all_sections))->isEqualTo(count($section_ids));
      $all_new_targetChanges = (new \PluginFormcreatorTargetChange())->getTargetsForForm($new_form->getID());
      $this->integer(count($all_sections))->isEqualTo(count($section_ids));

      // check that all sections uuid are new
      foreach ($all_targetChanges as $targetChange) {
         $uuids[] = $targetChange->fields['uuid'];
      }
      foreach ($all_new_targetChanges as $targetChange) {
         $new_uuids[] = $targetChange->fields['uuid'];
      }
      $this->integer(count(array_diff($new_uuids, $uuids)))->isEqualTo(count($new_uuids));
   }

   public function providerGetBestLanguage() {
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      $form0 = $this->newTestedInstance();
      $form1 = $this->getForm([
         'language' => '',
      ]);
      $form2 = $this->getForm([
         'language' => 'fr_FR',
      ]);
      $form3 = $this->getForm([
         'language' => '',
      ]);

      $form4 = $this->getForm([
         'language' => 'fr_FR',
      ]);

      $formLanguage = new \PluginFormcreatorForm_Language();
      $formLanguage->add([
         $formFk => $form3->getID(),
         'name'  => 'en_GB',
      ]);
      $this->boolean($formLanguage->isNewItem())->isFalse();

      $formLanguage = new \PluginFormcreatorForm_Language();
      $formLanguage->add([
         $formFk => $form3->getID(),
         'name'  => 'it_IT',
      ]);
      $this->boolean($formLanguage->isNewItem())->isFalse();

      $formLanguage = new \PluginFormcreatorForm_Language();
      $formLanguage->add([
         $formFk => $form4->getID(),
         'name'  => 'en_GB',
      ]);
      $this->boolean($formLanguage->isNewItem())->isFalse();

      $formLanguage = new \PluginFormcreatorForm_Language();
      $formLanguage->add([
         $formFk => $form4->getID(),
         'name'  => 'it_IT',
      ]);
      $this->boolean($formLanguage->isNewItem())->isFalse();

      return [
         'not instanciated, no session language' => [
            'form' => $form0,
            'sessionLanguage' => '',
            'expected' => '',
         ],
         'not instanciated' => [
            'form' => $form0,
            'sessionLanguage' => 'fr_CA',
            'expected' => 'fr_CA',
         ],
         'no language set, no session langnage' => [
            'form' => $form1,
            'sessionLanguage' => '',
            'expected' => '',
         ],
         'no language set' => [
            'form' => $form1,
            'sessionLanguage' => 'pt_PT',
            'expected' => 'pt_PT',
         ],
         'only a language set' => [
            'form' => $form2,
            'sessionLanguage' => '',
            'expected' => 'fr_FR',
         ],
         'only translations set 1' => [
            'form' => $form3,
            'sessionLanguage' => 'it_IT',
            'expected' => 'it_IT',
         ],
         'only translations set 2' => [
            'form' => $form3,
            'sessionLanguage' => 'en_GB',
            'expected' => 'en_GB',
         ],
         'language and translations set 1' => [
            'form' => $form4,
            'sessionLanguage' => 'fr_FR',
            'expected' => 'fr_FR',
         ],
         'language and translations set 2' => [
            'form' => $form4,
            'sessionLanguage' => 'it_IT',
            'expected' => 'it_IT',
         ],
         'language and translations set 2' => [
            'form' => $form4,
            'sessionLanguage' => 'fr_CA',
            'expected' => 'fr_FR',
         ],
      ];
   }

   /**
    * @dataProvider providerGetBestLanguage
    *
    * @return void
    */
   public function testGetBestLanguage(\PluginFormcreatorForm $form, string $sessionLanguage, string $expected) {
      $backupLanguage = $_SESSION['glpilanguage'];
      $_SESSION['glpilanguage'] = $sessionLanguage;
      $output = $form->getBestLanguage();
      $_SESSION['glpilanguage'] = $backupLanguage;
      $this->string($output)->isEqualTo($expected);
   }

   public function testGetTranslationDomain() {
      $instance = $this->getForm();

      $output = \PluginFormcreatorForm::getTranslationDomain($instance->getID(), 'en_US');
      $this->string($output)->isEqualTo('form_' . $instance->getID() . '_en_US' );

      $output = \PluginFormcreatorForm::getTranslationDomain($instance->getID(), 'it_IT');
      $this->string($output)->isEqualTo('form_' . $instance->getID() . '_it_IT' );
   }

   public function testGetTranslationFile() {
      $instance = $this->getForm();

      $output = \PluginFormcreatorForm::getTranslationFile($instance->getID(), 'en_US');
      $this->string($output)->isEqualTo(GLPI_LOCAL_I18N_DIR . '/formcreator/form_' . $instance->getID() . '_en_US.php' );

      $output = \PluginFormcreatorForm::getTranslationFile($instance->getID(), 'fr_CA');
      $this->string($output)->isEqualTo(GLPI_LOCAL_I18N_DIR . '/formcreator/form_' . $instance->getID() . '_fr_CA.php' );
   }

   public function testGetTranslatableStrings() {
      $data = file_get_contents(dirname(__DIR__) . '/fixture/all_question_types_form.json');
      $data = json_decode($data, true);
      foreach ($data['forms'] as $formData) {
         $form = $this->newTestedInstance();
         $formId = $form->import(new \PluginFormcreatorLinker(), $formData);
         $this->boolean($form->isNewID($formId))->isFalse();
      }

      $form->getFromDB($formId);
      $this->boolean($form->isNewItem())->isFalse();
      $output = $form->getTranslatableStrings();
      $this->array($output)->isIdenticalTo([
         'itemlink' =>
         [
           '4cd9f030d78c8eda21a284aa32fae318' => 'form with all question types',
           '73d5342eba070f636ac3246f319bf77f' => 'section',
           '8c647f55ac463429f736aea1ad64d318' => 'actors question',
           'de1ece2a98dacb86a2b65334373ccb99' => 'checkboxes question',
           'e121a8d9e19bf923a648d6bfb33094d8' => 'date question',
           '7d3246feb9616461eee152642ad9f1fb' => 'datetime question',
           '824d1cc309c56586a33b52858cbc146b' => 'description question',
           '8347ce048fc3fe8b954dbc6cd9c4b716' => 'dropdown question',
           '895472a7be51fe6b1b9591a150fb55d8' => 'email question',
           '75c4f52e98ebd4a57410d882780353db' => 'file question',
           '037cad549bb834c2fab44fe14480f9a9' => 'float question',
           '97ee07194ba5af1c81eb5a9b22141241' => 'GLPI object question',
           '74b8be9aff59bf5ddd149248d6156baa' => 'hidden question',
           '0550a71495224d60dfcd00826345f0fa' => 'hostname question',
           'd767bdc805e010bfd2302c2516501ffb' => 'IP address question',
           'b5c09bbe5587577a8c86ada678664877' => 'integer question',
           '5b3ebb576a3977eaa267f0769bdd8e98' => 'LDAP question',
           '35226e073fabdcce01c547c5bce62d14' => 'multiselect question',
           '58e2a2355ba7ac135d42f558591d6a6a' => 'radio question',
           '2637b4d11281dffbaa2e340561347ebc' => 'request type question',
           '212afc3240debecf859880ea9ab4fc2e' => 'select question',
           '6fd6eacf3005974a7489a199ed7b45ee' => 'text question',
           'b99b0833f1dab41a14eb421fa2ce690d' => 'textarea question',
           'e3a0dfbc9d24603beddcbd1388808a7a' => 'time question',
           '49dce550d75300e99052ed4e8006b65a' => 'urgency question',
         ],
         'string' =>
         [
           'bc41fd6c06a851dc3e5f52ef82c46357' => 'a (checkbox)',
           '2e2682dc7fe28972eede52a085f9b8da' => 'b (checkbox)',
           'a212352098d74d20ad0869e8b11870dd' => 'c (checkbox)',
           '2ee11338e1d5571cdcdc959e05d13fdd' => 'hidden value',
           '26b6a3b22c4a9eacd9bcca663c6bfb98' => 'a (multiselect)',
           'fe3ba23b6c304bcfccab1c4037170043' => 'b (multiselect)',
           '76abd40f08cc003cfb75e02d8603a618' => 'c (multiselect)',
           'aa08e69f50f9d7e4a280b5e395a926f3' => 'a (radio)',
           '3d8f74862a3f325c160d5b4090cc1344' => 'b (radio)',
           '60459f8c72beb121493ec56bd0b41473' => 'c (radio)',
           '3e6b3c27f45682bbe11ed102ff9cbd31' => 'a (select)',
           '12f59df90d7b53129d8e6da91f60cf86' => 'b (select)',
           '1dd65ffc0516477159ec9ba8c170ef94' => 'c (select)',
           '4f87be8f6e593d167f5fd1ab238cfc2d' => '/foo/',
         ],
         'text' =>
         [
           '06ff4080ef6f9ee755cc45cba5f80360' => 'actors description',
           '874e42442b551ef2769cc498157f542d' => 'checkboxes description',
           '42be0556a01c9e0a28da37d2e3c5153d' => 'date description',
           'b698fbcd4b9acf232b8b88755a1728f0' => 'datetime description',
           'ab87cc96356a7d5c1d37c877fd56c6b0' => 'description text',
           '59ef614a194389f0b54e46b728fe22a2' => 'dropdown description',
           'b70e872f17f616049c642f2db8f35c8a' => 'email description',
           '2b4f8f08c4162a2dac4a9b82e97605c0' => 'file description',
           'b1a3d83a831e20619e1f14f6dbc64105' => 'float description',
           '54ee213f0c0aae084d5712dc96bac833' => 'GLPI object description',
           '91ca037d3ec611f6c684114abce7296f' => 'hidden description',
           '98443bed844ba97392d8a8fb364b5d66' => 'hostname description',
           '4b2e461a0b3c307923176188fb6273c6' => 'IP address description',
           '51d8d951cf91a008f5b87c7d36ee6789' => 'integer description',
           'c0117d3ded05c5c672425a48a63c83d7' => 'LDAP description',
           '2d0b83793d10440b70c33a2229c88a09' => 'multiselect description',
           '06cdb33e33e576a973d7bf54fcded96e' => 'radios description',
           '471217363e6922ff6b1c9fd9cd57cd2a' => 'request type description',
           '64dfbbc489b074af269e0b0fbf0d901b' => 'select description',
           'b371eae37f18f0b6125002999b2404ba' => 'text description',
           'f81bad6b9c8f01a40099a140881313a8' => 'textarea description',
           '8d544ed7c846a47654b2f55db879d7b2' => 'time description',
           'e634ce2f4abe0deaa3f7cd44e13f4af6' => 'urgency description',
         ],
         'id' =>
         [
           '4cd9f030d78c8eda21a284aa32fae318' => 'itemlink',
           '73d5342eba070f636ac3246f319bf77f' => 'itemlink',
           '8c647f55ac463429f736aea1ad64d318' => 'itemlink',
           '06ff4080ef6f9ee755cc45cba5f80360' => 'text',
           'de1ece2a98dacb86a2b65334373ccb99' => 'itemlink',
           '874e42442b551ef2769cc498157f542d' => 'text',
           'bc41fd6c06a851dc3e5f52ef82c46357' => 'string',
           '2e2682dc7fe28972eede52a085f9b8da' => 'string',
           'a212352098d74d20ad0869e8b11870dd' => 'string',
           'e121a8d9e19bf923a648d6bfb33094d8' => 'itemlink',
           '42be0556a01c9e0a28da37d2e3c5153d' => 'text',
           '7d3246feb9616461eee152642ad9f1fb' => 'itemlink',
           'b698fbcd4b9acf232b8b88755a1728f0' => 'text',
           '824d1cc309c56586a33b52858cbc146b' => 'itemlink',
           'ab87cc96356a7d5c1d37c877fd56c6b0' => 'text',
           '8347ce048fc3fe8b954dbc6cd9c4b716' => 'itemlink',
           '59ef614a194389f0b54e46b728fe22a2' => 'text',
           '895472a7be51fe6b1b9591a150fb55d8' => 'itemlink',
           'b70e872f17f616049c642f2db8f35c8a' => 'text',
           '75c4f52e98ebd4a57410d882780353db' => 'itemlink',
           '2b4f8f08c4162a2dac4a9b82e97605c0' => 'text',
           '037cad549bb834c2fab44fe14480f9a9' => 'itemlink',
           'b1a3d83a831e20619e1f14f6dbc64105' => 'text',
           '97ee07194ba5af1c81eb5a9b22141241' => 'itemlink',
           '54ee213f0c0aae084d5712dc96bac833' => 'text',
           '74b8be9aff59bf5ddd149248d6156baa' => 'itemlink',
           '91ca037d3ec611f6c684114abce7296f' => 'text',
           '2ee11338e1d5571cdcdc959e05d13fdd' => 'string',
           '0550a71495224d60dfcd00826345f0fa' => 'itemlink',
           '98443bed844ba97392d8a8fb364b5d66' => 'text',
           'd767bdc805e010bfd2302c2516501ffb' => 'itemlink',
           '4b2e461a0b3c307923176188fb6273c6' => 'text',
           'b5c09bbe5587577a8c86ada678664877' => 'itemlink',
           '51d8d951cf91a008f5b87c7d36ee6789' => 'text',
           '5b3ebb576a3977eaa267f0769bdd8e98' => 'itemlink',
           'c0117d3ded05c5c672425a48a63c83d7' => 'text',
           '35226e073fabdcce01c547c5bce62d14' => 'itemlink',
           '2d0b83793d10440b70c33a2229c88a09' => 'text',
           '26b6a3b22c4a9eacd9bcca663c6bfb98' => 'string',
           'fe3ba23b6c304bcfccab1c4037170043' => 'string',
           '76abd40f08cc003cfb75e02d8603a618' => 'string',
           '58e2a2355ba7ac135d42f558591d6a6a' => 'itemlink',
           '06cdb33e33e576a973d7bf54fcded96e' => 'text',
           'aa08e69f50f9d7e4a280b5e395a926f3' => 'string',
           '3d8f74862a3f325c160d5b4090cc1344' => 'string',
           '60459f8c72beb121493ec56bd0b41473' => 'string',
           '2637b4d11281dffbaa2e340561347ebc' => 'itemlink',
           '471217363e6922ff6b1c9fd9cd57cd2a' => 'text',
           '212afc3240debecf859880ea9ab4fc2e' => 'itemlink',
           '64dfbbc489b074af269e0b0fbf0d901b' => 'text',
           '3e6b3c27f45682bbe11ed102ff9cbd31' => 'string',
           '12f59df90d7b53129d8e6da91f60cf86' => 'string',
           '1dd65ffc0516477159ec9ba8c170ef94' => 'string',
           '6fd6eacf3005974a7489a199ed7b45ee' => 'itemlink',
           'b371eae37f18f0b6125002999b2404ba' => 'text',
           'b99b0833f1dab41a14eb421fa2ce690d' => 'itemlink',
           'f81bad6b9c8f01a40099a140881313a8' => 'text',
           '4f87be8f6e593d167f5fd1ab238cfc2d' => 'string',
           'e3a0dfbc9d24603beddcbd1388808a7a' => 'itemlink',
           '8d544ed7c846a47654b2f55db879d7b2' => 'text',
           '49dce550d75300e99052ed4e8006b65a' => 'itemlink',
           'e634ce2f4abe0deaa3f7cd44e13f4af6' => 'text',
         ],
      ]);
   }

   public function providerCheckImportVersion() {
      $currentMinorVersion = explode('.', PLUGIN_FORMCREATOR_SCHEMA_VERSION);

      $lower = $currentMinorVersion;
      $lower[1]--;

      $evenLower = $currentMinorVersion;
      $evenLower[0]--;

      $equal = $currentMinorVersion;

      $higher = $currentMinorVersion;
      $higher[1]++;

      $evenHigher = $currentMinorVersion;
      $evenHigher[0]++;

      return [
         'evenLower_1' => [
            'version'  => implode('.', $evenLower) . '.0',
            'expected' => false,
         ],
         'evenLower_2' => [
            'version'  => implode('.', $evenLower) . '.1',
            'expected' => false,
         ],
         'evenLower_3' => [
            'version'  => implode('.', $evenLower) . '.0-dev',
            'expected' => false,
         ],
         'evenLower_4' => [
            'version'  => implode('.', $evenLower),
            'expected' => false,
         ],
         'lower_1' => [
            'version'  => implode('.', $lower) . '.0',
            'expected' => false,
         ],
         'lower_2' => [
            'version'  => implode('.', $lower) . '.1',
            'expected' => false,
         ],
         'lower_3' => [
            'version'  => implode('.', $lower) . '.0-dev',
            'expected' => false,
         ],
         'lower_4' => [
            'version'  => implode('.', $lower),
            'expected' => false,
         ],
         'equal_1' => [
            'version'  => implode('.', $equal) . '.0',
            'expected' => true,
         ],
         'equal_2' => [
            'version'  => implode('.', $equal) . '.1',
            'expected' => true,
         ],
         'equal_3' => [
            'version'  => implode('.', $equal) . '.0-dev',
            'expected' => true,
         ],
         'equal_4' => [
            'version'  => implode('.', $equal),
            'expected' => true,
         ],
         'higher_1' => [
            'version'  => implode('.', $higher) . '.0',
            'expected' => false,
         ],
         'higher_2' => [
            'version'  => implode('.', $higher) . '.1',
            'expected' => false,
         ],
         'higher_3' => [
            'version'  => implode('.', $higher) . '.0-dev',
            'expected' => false,
         ],
         'higher_4' => [
            'version'  => implode('.', $higher),
            'expected' => false,
         ],
         'evenHigher_1' => [
            'version'  => implode('.', $evenHigher) . '.0',
            'expected' => false,
         ],
         'evenHigher_2' => [
            'version'  => implode('.', $evenHigher) . '.1',
            'expected' => false,
         ],
         'evenHigher_3' => [
            'version'  => implode('.', $evenHigher) . '.0-dev',
            'expected' => false,
         ],
         'evenHigher_4' => [
            'version'  => implode('.', $evenHigher),
            'expected' => false,
         ],
      ];
   }

   /**
    * @dataProvider providerCheckImportVersion
    *
    * @param string $version
    * @param bool $expected
    * @return void
    */
   public function testCheckImportVersion($version, $expected) {
      $output = \PluginFormcreatorForm::checkImportVersion($version);
      $this->boolean($output)->isEqualTo($expected);
   }
}
