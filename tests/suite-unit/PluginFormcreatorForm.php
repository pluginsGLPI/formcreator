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
 *
 * @copyright Copyright © 2011 - 2018 Teclib'
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

   /**
    * @dataProvider formProvider
    */
   public function testPurgeForm($formData) {
      $form = new \PluginFormcreatorForm();
      $form->add($formData);
      $this->boolean($form->isNewItem())->isFalse();

      $success = $form->delete([
         'id'              => $form->getID(),
      ], 1);
      $this->boolean($success)->isTrue();
   }

   public function testCreateValidationNotification() {
      global $DB;

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
         $form::getForeignKeyField() => $form->getID(),
         'name' => 'section',
      ]);

      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswerId = $formAnswer->saveAnswers($form, [
         'formcreator_form'         => $form->getID(),
         'formcreator_validator'    => $_SESSION['glpiID'],
      ], []);
      $this->integer($formAnswerId)->isGreaterThan(0);

      // 1 notification to the validator
      // 1 notification to the requester
      $foundNotifications = $DB->request([
         'COUNT' => 'cpt',
         'FROM'  => \QueuedNotification::getTable(),
         'WHERE' => [
            'itemtype' => 'PluginFormcreatorFormAnswer',
            'items_id' => $formAnswerId,
         ]
      ])->next();
      $this->integer((int) $foundNotifications['cpt'])->isEqualTo(2);
   }

   /**
    * @cover PluginFormcreatorForm::export
    */
   public function testExportForm() {
      // instanciate classes
      $form                = new \PluginFormcreatorForm;
      $form_section        = new \PluginFormcreatorSection;
      $form_question       = new \PluginFormcreatorQuestion;
      $form_condition      = new \PluginFormcreatorQuestion_Condition;
      $form_validator      = new \PluginFormcreatorForm_Validator;
      $form_target         = new \PluginFormcreatorTarget;
      $form_profile        = new \PluginFormcreatorForm_Profile;
      $targetTicket        = new \PluginFormcreatorTargetTicket();
      $item_targetTicket   = new \PluginFormcreatorItem_TargetTicket();

      // create objects
      $forms_id = $form->add([
         'name'                => "test export form",
         'is_active'           => true,
         'validation_required' => \PluginFormcreatorForm_Validator::VALIDATION_USER
      ]);
      $sections_id = $form_section->add([
         'name'                        => "test export section",
         'plugin_formcreator_forms_id' => $forms_id
      ]);
      $questions_id_1 = $form_question->add([
         'name'                           => "test export question 1",
         'fieldtype'                      => 'text',
         'plugin_formcreator_sections_id' => $sections_id
      ]);
      $questions_id_2 = $form_question->add([
         'name'                           => "test export question 2",
         'fieldtype'                      => 'textarea',
         'plugin_formcreator_sections_id' => $sections_id
      ]);
      $form_condition->add([
         'plugin_formcreator_questions_id' => $questions_id_1,
         'show_field'                      => $questions_id_2,
         'show_condition'                  => '==',
         'show_value'                      => 'test'
      ]);
      $form_validator->add([
         'plugin_formcreator_forms_id' => $forms_id,
         'itemtype'                    => 'User',
         'items_id'                    => 2
      ]);
      $form_validator->add([
         'plugin_formcreator_forms_id' => $forms_id,
         'itemtype'                    => 'User',
         'items_id'                    => 3
      ]);
      $targets_id = $form_target->add([
         'plugin_formcreator_forms_id' => $forms_id,
         'itemtype'                    => \PluginFormcreatorTargetTicket::class,
         'name'                        => "test export target"
      ]);
      $targetTicket_id = $targetTicket->add([
         'name'         => $form_target->getField('name'),
         'content'      => '##FULLFORM##'
      ]);
      $form_target->getFromDB($targets_id);
      $form_target->fields['items_id'];
      $form_profile->add(['plugin_formcreator_forms_id' => $forms_id,
                                                   'profiles_id' => 1]);
      $item_targetTicket->add(['plugin_formcreator_targettickets_id' => $targetTicket_id,
                               'link'     => \Ticket_Ticket::LINK_TO,
                               'itemtype' => $form_target->getField('itemtype'),
                               'items_id' => $targets_id
      ]);

      $form->getFromDB($form->getID());
      $export = $form->export();

      $this->_checkForm($export);

      foreach ($export["_sections"] as $section) {
         $this->_checkSection($section);
      }

      foreach ($export["_validators"] as $validator) {
         $this->_checkValidator($validator);
      }

      foreach ($export["_targets"] as $target) {
         $this->_checkTarget($target);
      }

      foreach ($export["_profiles"] as $form_profile) {
         $this->_checkFormProfile($form_profile);
      }
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
         'not public' =>[
            'input' => [
               'access_rights' => (string) \PluginFormcreatorForm::ACCESS_PRIVATE,
               'name' => $this->getUniqueString()
            ],
            'expected' => false,
         ],
         'public' =>[
            'input' => [
               'access_rights' => (string) \PluginFormcreatorForm::ACCESS_PUBLIC,
               'name' => $this->getUniqueString()
            ],
            'expected' => true,
         ],
         'by profile' =>[
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

   public function testSaveForm() {
      global $CFG_GLPI;

      $this->login('glpi', 'glpi');

      // disable notifications as we may fail in some case (not the purpose of this test btw)
      $use_notifications = $CFG_GLPI['use_notifications'];
      $CFG_GLPI['use_notifications'] = 0;

      $answer = "test answer to question";

      // prepare a fake form with targets
      $question = $this->getQuestion();
      $section = new \PluginFormcreatorSection();
      $section->getFromDB($question->fields[\PluginFormcreatorSection::getForeignKeyField()]);
      $form = new \PluginFormcreatorForm();
      $form->getFromDB($section->fields[\PluginFormcreatorForm::getForeignKeyField()]);
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      $this->getTargetTicket([
         $formFk => $form->getID(),
      ]);
      $this->getTargetChange([
         $formFk => $form->getID(),
      ]);

      // prepare input
      $input = [
         'formcreator_form' => $form->getID(),
         'formcreator_field_'.$question->getID() => $answer
      ];

      // send for answer
      $formAnswerId = $form->saveForm($input);
      $this->integer($formAnswerId)->isGreaterThan(0);

      // check existence of generated target
      // - ticket
      $item_ticket = new \Item_Ticket;
      $this->boolean($item_ticket->getFromDBByCrit([
         'itemtype' => 'PluginFormcreatorFormAnswer',
         'items_id' => $formAnswerId,
      ]))->isTrue();
      $ticket = new \Ticket;
      $this->boolean($ticket->getFromDB($item_ticket->fields['tickets_id']))->isTrue();
      $this->string($ticket->fields['content'])->contains($answer);

      // - change
      $change_item = new \Change_Item;
      $this->boolean($change_item->getFromDBByCrit([
         'itemtype' => 'PluginFormcreatorFormAnswer',
         'items_id' => $formAnswerId,
      ]))->isTrue();
      $change = new \Change;
      $this->boolean($change->getFromDB($change_item->fields['changes_id']))->isTrue();
      $this->string($change->fields['content'])->contains($answer);

      // - issue
      $issue = new \PluginFormcreatorIssue;
      $this->boolean($issue->getFromDBByCrit([
        'sub_itemtype' => \Ticket::class,
        'original_id'  => $ticket->getID()
      ]))->isTrue();

      $CFG_GLPI['use_notifications'] = $use_notifications;
   }
}
