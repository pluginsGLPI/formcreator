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
 * @copyright Copyright © 2011 - 2020 Teclib'
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
            self::login('glpi', 'glpi');
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
      $form = $this->getForm();
      $output = $form->defineTabs();
      $this->array($output)->isEqualTo([
         'PluginFormcreatorForm$main' => "Form",
         'PluginFormcreatorQuestion$1' => "Questions",
         'PluginFormcreatorForm_Profile$1' => "Access types",
         'PluginFormcreatorForm$1' => "Targets",
         'PluginFormcreatorForm$2' => "Preview",
         'PluginFormcreatorFormAnswer$1' => "Form answers",
         'Log$1' => "Historical <sup class='tab_nb'>1</sup>",
      ]);
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

   public  function testUpdateValidators() {
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
      $output = $instance->export();
      $this->boolean($output)->isFalse();

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
         'requesttype',
         'description',
         'content',
         'is_active',
         'language',
         'helpdesk_home',
         'is_deleted',
         'validation_required',
         'is_default',
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
      // Have a non existent form ID
      $form = $this->getForm();
      $form->delete([
         'id' => $form->getID()
      ], 1);
      return [
         [
            'input' => [
               'itemtype' => 'Nothing'
            ],
            'expected' => false,
         ],
         [
            'input' => [
               'name' => '',
               'itemtype' => \PluginFormcreatorTargetTicket::class,
               'plugin_formcreator_forms_id' => $form->getID(),
            ],
            'expected' => false,
         ],
         [
            'input' => [
               'name' => '',
               'itemtype' => \PluginFormcreatorTargetTicket::class,
               'plugin_formcreator_forms_id' => $this->getForm()->getID(),
            ],
            'expected' => true,
         ],
         [
            'input' => [
               'name' => '',
               'itemtype' => \PluginFormcreatorTargetChange::class,
               'plugin_formcreator_forms_id' => $this->getForm()->getID(),
            ],
            'expected' => true,
         ],
      ];
   }

   /**
    * @dataProvider providerAddTarget
    */
   public function testAddTarget($input, $expected) {
      $instance = $this->newTestedInstance();
      $output = $instance->addTarget($input);

      if ($expected === false) {
         //End of test on expected failure
         $this->boolean($output)->isEqualTo($expected);
         return;
      }

      $this->integer((int) $output);

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
         'name' => '',
         'itemtype' => \PluginFormcreatorTargetChange::class,
         'plugin_formcreator_forms_id' => $this->getForm()->getID(),
      ]);
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
      $all_targetTickets = (new \PluginFormcreatorTargetTicket())->getTargetTicketsForForm($form->getID());
      $this->integer(count($all_sections))->isEqualTo(count($section_ids));
      $all_new_targetTickets = (new \PluginFormcreatorTargetTicket())->getTargetTicketsForForm($new_form->getID());
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
      $all_targetChanges = (new \PluginFormcreatorTargetChange())->getTargetChangesForForm($form->getID());
      $this->integer(count($all_sections))->isEqualTo(count($section_ids));
      $all_new_targetChanges = (new \PluginFormcreatorTargetChange())->getTargetChangesForForm($new_form->getID());
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
}
