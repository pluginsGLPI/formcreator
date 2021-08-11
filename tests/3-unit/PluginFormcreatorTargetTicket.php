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
use GlpiPlugin\Formcreator\Tests\PluginFormcreatorTargetTicketDummy;

class PluginFormcreatorTargetTicket extends CommonTestCase {

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testSetTargetEntity':
         case 'testSetTargetCategory':
         case 'testSetTargetType':
         case 'testPrepareTemplate':
         case 'testDeleteLinkedTickets':
         case 'testSetTargetAssociatedItem':
         case 'testImport':
            $this->boolean($this->login('glpi', 'glpi'))->isTrue();
            break;
      }
   }

   public function providerGetTypeName() {
      return [
         [
            'input' => 0,
            'expected' => 'Target tickets',
         ],
         [
            'input' => 1,
            'expected' => 'Target ticket',
         ],
         [
            'input' => 2,
            'expected' => 'Target tickets',
         ],
      ];
   }

   /**
    * @dataProvider providerGetTypeName
    * @param integer $number
    * @param string $expected
    */
   public function testGetTypeName($number, $expected) {
      $output = \PluginFormcreatorTargetTicket::getTypeName($number);
      $this->string($output)->isEqualTo($expected);
   }

   public function testGetEnumDestinationEntity() {
      $output = \PluginFormcreatorTargetTicket::getEnumDestinationEntity();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_CURRENT      => 'Current active entity',
         \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_REQUESTER  => "Default requester user's entity",
         \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_REQUESTER_DYN_FIRST    => "First dynamic requester user's entity (alphabetical)",
         \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_REQUESTER_DYN_LAST      => "Last dynamic requester user's entity (alphabetical)",
         \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_FORM  => 'The form entity',
         \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_VALIDATOR    => 'Default entity of the validator',
         \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_SPECIFIC      => 'Specific entity',
         \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_USER  => 'Default entity of a user type question answer',
         \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_ENTITY    => 'From a GLPI object > Entity type question answer',
      ]);
   }

   public function testGetEnumTagType() {
      $output = \PluginFormcreatorTargetTicket::getEnumTagType();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorTargetTicket::TAG_TYPE_NONE                   => __('None'),
         \PluginFormcreatorTargetTicket::TAG_TYPE_QUESTIONS              => __('Tags from questions', 'formcreator'),
         \PluginFormcreatorTargetTicket::TAG_TYPE_SPECIFICS              => __('Specific tags', 'formcreator'),
         \PluginFormcreatorTargetTicket::TAG_TYPE_QUESTIONS_AND_SPECIFIC => __('Tags from questions and specific tags', 'formcreator'),
         \PluginFormcreatorTargetTicket::TAG_TYPE_QUESTIONS_OR_SPECIFIC  => __('Tags from questions or specific tags', 'formcreator')
      ]);
   }

   public function testGetEnumDateType() {
      $output = \PluginFormcreatorTargetTicket::getEnumDueDateRule();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorTargetTicket::DUE_DATE_RULE_ANSWER => __('equals to the answer to the question', 'formcreator'),
         \PluginFormcreatorTargetTicket::DUE_DATE_RULE_TICKET => __('calculated from the ticket creation date', 'formcreator'),
         \PluginFormcreatorTargetTicket::DUE_DATE_RULE_CALC => __('calculated from the answer to the question', 'formcreator'),
      ]);
   }

   public function testGetEnumLocationType() {
      $output = \PluginFormcreatorTargetTicket::getEnumLocationRule();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorTargetTicket::LOCATION_RULE_NONE      => __('Location from template or none', 'formcreator'),
         \PluginFormcreatorTargetTicket::LOCATION_RULE_SPECIFIC  => __('Specific location', 'formcreator'),
         \PluginFormcreatorTargetTicket::LOCATION_RULE_ANSWER    => __('Equals to the answer to the question', 'formcreator'),
      ]);
   }

   public function testGetEnumUrgencyRule() {
      $output = \PluginFormcreatorTargetTicket::getEnumUrgencyRule();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorTargetTicket::URGENCY_RULE_NONE      => 'Urgency from template or Medium',
         \PluginFormcreatorTargetTicket::URGENCY_RULE_SPECIFIC  => 'Specific urgency',
         \PluginFormcreatorTargetTicket::URGENCY_RULE_ANSWER    => 'Equals to the answer to the question',
      ]);
   }

   public function testGetEnumAssociateRule() {
      $output = \PluginFormcreatorTargetTicket::getEnumAssociateRule();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorTargetTicket::ASSOCIATE_RULE_NONE         => 'None',
         \PluginFormcreatorTargetTicket::ASSOCIATE_RULE_SPECIFIC     => 'Specific asset',
         \PluginFormcreatorTargetTicket::ASSOCIATE_RULE_ANSWER       => 'Equals to the answer to the question',
         \PluginFormcreatorTargetTicket::ASSOCIATE_RULE_LAST_ANSWER  => 'Last valid answer',
      ]);
   }

   public function testGetEnumCategoryRule() {
      $output = \PluginFormcreatorTargetTicket::getEnumCategoryRule();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorTargetTicket::CATEGORY_RULE_NONE          => 'Category from template or none',
         \PluginFormcreatorTargetTicket::CATEGORY_RULE_SPECIFIC      => 'Specific category',
         \PluginFormcreatorTargetTicket::CATEGORY_RULE_ANSWER        => 'Equals to the answer to the question',
         \PluginFormcreatorTargetTicket::CATEGORY_RULE_LAST_ANSWER   => 'Last valid answer',
      ]);
   }

   public function testGetItem_User() {
      $instance = new PluginFormcreatorTargetTicketDummy();
      $output = $instance->publicGetItem_User();
      $this->object($output)->isInstanceOf(\Ticket_User::class);
      $this->boolean($output->isNewItem())->isTrue();
   }

   public function testGetItem_Group() {
      $instance = new PluginFormcreatorTargetTicketDummy();
      $output = $instance->publicGetItem_Group();
      $this->object($output)->isInstanceOf(\Group_Ticket::class);
      $this->boolean($output->isNewItem())->isTrue();
   }

   public function testGetItem_Supplier() {
      $instance = new PluginFormcreatorTargetTicketDummy();
      $output = $instance->publicGetItem_Supplier();
      $this->object($output)->isInstanceOf(\Supplier_Ticket::class);
      $this->boolean($output->isNewItem())->isTrue();
   }

   public function testGetItem_Item() {
      $instance = new PluginFormcreatorTargetTicketDummy();
      $output = $instance->publicGetItem_Item();
      $this->object($output)->isInstanceOf(\Item_Ticket::class);
      $this->boolean($output->isNewItem())->isTrue();
   }

   public function testGetCategoryFilter() {
      $instance = new PluginFormcreatorTargetTicketDummy();
      $output = $instance->publicGetCategoryFilter();
      $this->array($output)->isEqualTo([
         'OR' => [
            'is_request'  => 1,
            'is_incident' => 1
         ]
      ]);
   }

   public function testGetTaggableFields() {
      $instance = new PluginFormcreatorTargetTicketDummy();
      $output = $instance->publicGetTaggableFields();
      $this->array($output)->isEqualTo([
         'target_name',
         'content',
      ]);
   }

   public function testGetTargetItemtypeName() {
      $instance = new PluginFormcreatorTargetTicketDummy();
      $output = $instance->publicGetTargetItemtypeName();
      $this->string($output)->isEqualTo(\Ticket::class);
   }

   /**
    * Tests that deleting a target ticket of a form also deletes relations between tickets and generated tickets
    *
    * @covers PluginFormcreatorTargetTicket::pre_deleteItem
    */
   public function testDeleteLinkedTickets() {
      global $CFG_GLPI;

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      // setup the test
      $ticket = new \Ticket();
      $ticket->add([
         'name'               => 'ticket',
         'content'            => 'help !',
         'users_id_recipient' => '0',
      ]);
      $this->boolean($ticket->isNewItem())->isFalse();

      $form = new \PluginFormcreatorForm();
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      $form->add([
         'name' => 'a form'
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $targetTicket_1 = new \PluginFormcreatorTargetTicket();
      $targetTicket_1->add([
         'name'      => 'target 1',
         $formFk     => $form->getID(),
      ]);
      $this->boolean($targetTicket_1->isNewItem())->isFalse();

      $targetTicket_2 = new \PluginFormcreatorTargetTicket();
      $targetTicket_2->add([
         'name'      => 'target 2',
         $formFk     => $form->getID(),
      ]);
      $this->boolean($targetTicket_2->isNewItem())->isFalse();

      $targetTicketFk = \PluginFormcreatorTargetTicket::getForeignKeyField();
      $item_targetticket_1 = new \PluginFormcreatorItem_TargetTicket();
      $item_targetticket_1->add([
         $targetTicketFk   => $targetTicket_1->getID(),
         'link'            => \Ticket_Ticket::LINK_TO,
         'itemtype'        => \Ticket::class,
         'items_id'        => $ticket->getID(),
      ]);
      $this->boolean($item_targetticket_1->isNewItem())->isFalse();

      $item_targetticket_2 = new \PluginFormcreatorItem_TargetTicket();
      $item_targetticket_2->add([
         $targetTicketFk   => $targetTicket_1->getID(),
         'link'            => \Ticket_Ticket::LINK_TO,
         'itemtype'        => \PluginFormcreatorTargetTicket::class,
         'items_id'        => $targetTicket_2->getID(),
      ]);
      $this->boolean($item_targetticket_2->isNewItem())->isFalse();

      // delete the target ticket
      $targetTicket_1->delete(['id' => $targetTicket_1->getID()]);

      // Check the linked ticket or target ticket are deleted
      $this->boolean($item_targetticket_1->getFromDB($item_targetticket_1->getID()))->isFalse();
      $this->boolean($item_targetticket_2->getFromDB($item_targetticket_2->getID()))->isFalse();
   }

   /**
    *
    * @return void
    */
   public function  testSetTargetEntity() {
      global $CFG_GLPI;

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $form = $this->getForm();
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      $targetTicket = $this->getTargetTicket([
         $formFk => $form->getID(),
      ]);

      // Use a dummy class to access protected methods
      $instance = new PluginFormcreatorTargetTicketDummy();
      $instance->getFromDB($targetTicket->getID());

      // Test current entity of the requester
      $entity = new \Entity();
      $entityId = $entity->import([
         'entities_id' => '0',
         'name' => $this->getUniqueString()
      ]);
      \Session::changeActiveEntities($entityId);
      $targetTicket->update([
         'id' => $targetTicket->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_CURRENT,
         'destination_entity_value' => '0',
      ]);
      $instance->getFromDB($targetTicket->getID());
      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      $formAnswer->getFromDB($formAnswer->getID());
      $requesterId = \Session::getLoginUserID();
      $output = $instance->publicSetTargetEntity([], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo($entityId);

      // Test requester's entity
      $targetTicket->update([
         'id' => $targetTicket->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_REQUESTER,
         'destination_entity_value' => '0',
      ]);
      $instance->getFromDB($targetTicket->getID());
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      \Session::changeActiveEntities($entityId);
      $requesterId = \Session::getLoginUserID();
      $output = $instance->publicSetTargetEntity([], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo(0);

      // Test requester's first entity (alphanumeric order)
      $targetTicket->update([
         'id' => $targetTicket->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_REQUESTER_DYN_FIRST,
         'destination_entity_value' => '0',
      ]);
      $instance->getFromDB($targetTicket->getID());
      $entityId = $entity->import([
         'entities_id' => '0',
         'name' => $this->getUniqueString(),
      ]);
      $user = new \User();
      $user->add([
         'name' => $this->getUniqueString(),
         'password' => 'passwd',
         'password2' => 'passwd',
         '_profiles_id' => '3', // Admin
         '_entities_id' => $entityId,
      ]);
      $entity = new \Entity();
      $profileUser = new \Profile_User();
      // A login resyncs a user. Must login nefore adding the dynamic profile
      $this->boolean($this->login($user->fields['name'], 'passwd'))->isTrue();
      $profileUser->add([
         \User::getForeignKeyField()    => $user->getID(),
         \Profile::getForeignKeyField() => 4, // Super admin
         \Entity::getForeignKeyField()  => $entityId,
         'is_dynamic'                   => '1',
      ]);

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => 0,
      ]);
      $requesterId = \Session::getLoginUserID();
      $output = $instance->publicSetTargetEntity([], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo($entityId);

      // Test requester's last entity (alphanumeric order)
      $targetTicket->update([
         'id' => $targetTicket->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_REQUESTER_DYN_LAST,
         'destination_entity_value' => '0',
      ]);
      $instance->getFromDB($targetTicket->getID());

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      $requesterId = \Session::getLoginUserID();
      $output = $instance->publicSetTargetEntity([], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo($entityId);

      // Test specific entity
      $this->boolean($this->login('glpi', 'glpi'))->isTrue();
      $entityId = $entity->import([
         'entities_id' => '0',
         'name' => $this->getUniqueString(),
      ]);
      $targetTicket->update([
         'id' => $targetTicket->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_SPECIFIC,
         'destination_entity_value' => "$entityId",
      ]);
      $instance->getFromDB($targetTicket->getID());
      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => 0,
      ]);
      $requesterId = \Session::getLoginUserID();
      $output = $instance->publicSetTargetEntity([], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo($entityId);

      // Test form's entity
      $entityId = $entity->import([
         'entities_id' => '0',
         'name' => $this->getUniqueString(),
      ]);
      $targetTicket->update([
         'id' => $targetTicket->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetTicket::DESTINATION_ENTITY_FORM,
         'destination_entity_value' => '0',
      ]);
      $form->update([
         'id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      $instance->getFromDB($targetTicket->getID());
      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => 0,
      ]);
      $requesterId = \Session::getLoginUserID();
      $output = $instance->publicSetTargetEntity([], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo($entityId);
   }

   public function providerSetTargetType() {
      global $CFG_GLPI;

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $question1 = $this->getQuestion([
         'fieldtype' => 'requesttype',
      ]);
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      $form1 = new \PluginFormcreatorForm();
      $form1->getFromDBByQuestion($question1);
      $form1->update([
         'id' => $form1->getID(),
         'validation_required' => \PluginFormcreatorForm::VALIDATION_USER,
         '_validator_users' => [2] // Glpi user
      ]);
      $targetTicket1 = $this->getTargetTicket([
         $formFk     => $form1->getID(),
         'type_rule'     => \PluginFormcreatorTargetTicket::REQUESTTYPE_SPECIFIC,
         'type_question' => \Ticket::INCIDENT_TYPE,
      ]);

      $question2 = $this->getQuestion([
         'fieldtype' => 'requesttype',
      ]);
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      $form2 = new \PluginFormcreatorForm();
      $form2->getFromDBByQuestion($question2);
      $form2->update([
         'id' => $form2->getID(),
         'validation_required' => \PluginFormcreatorForm::VALIDATION_USER,
         '_validator_users' => [2] // Glpi user
      ]);
      $targetTicket2 = $this->getTargetTicket([
         $formFk     => $form2->getID(),
         'type_rule' => \PluginFormcreatorTargetTicket::REQUESTTYPE_ANSWER,
         'type_question' => $question2->getID(),
      ]);
      return [
         [
            'instance'   => $targetTicket1,
            'formanswerid' => (new \PluginFormcreatorFormAnswer())->add([
               \PluginFormcreatorForm::getForeignKeyField() => $form1->getID(),
               'name' => $form1->fields['name'],
               'requester_id' => 2, // glpi user id
               'status' => \PluginFormcreatorFormAnswer::STATUS_WAITING,
               'formcreator_validator' => 2, // Glpi user ID
               'formcreator_field_' . $question1->getID() => (string) \Ticket::INCIDENT_TYPE,
            ]),
            'expected'   => \Ticket::INCIDENT_TYPE,
         ],
         [
            'instance'   => $targetTicket1,
            'formanswerid' => (new \PluginFormcreatorFormAnswer())->add([
               \PluginFormcreatorForm::getForeignKeyField() => $form1->getID(),
               'name' => $form1->fields['name'],
               'requester_id' => 2, // glpi user id
               'status' => \PluginFormcreatorFormAnswer::STATUS_WAITING,
               'formcreator_validator' => 2, // Glpi user ID
               'formcreator_field_' . $question1->getID() => (string) \Ticket::DEMAND_TYPE,
            ]),
            'expected'   => \Ticket::INCIDENT_TYPE,
         ],
         [
            'instance'   => $targetTicket2,
            'formanswerid' => (new \PluginFormcreatorFormAnswer())->add([
               \PluginFormcreatorForm::getForeignKeyField() => $form2->getID(),
               'name' => $form2->fields['name'],
               'requester_id' => 2, // glpi user id
               'status' => \PluginFormcreatorFormAnswer::STATUS_WAITING,
               'formcreator_validator' => 2, // Glpi user ID
               'formcreator_field_' . $question2->getID() => (string) \Ticket::DEMAND_TYPE,
            ]),
            'expected'   => \Ticket::DEMAND_TYPE,
         ],
         [
            'instance'   => $targetTicket2,
            'formanswerid' => (new \PluginFormcreatorFormAnswer())->add([
               \PluginFormcreatorForm::getForeignKeyField() => $form2->getID(),
               'name' => $form2->fields['name'],
               'requester_id' => 2, // glpi user id
               'status' => \PluginFormcreatorFormAnswer::STATUS_WAITING,
               'formcreator_validator' => 2, // Glpi user ID
               'formcreator_field_' . $question2->getID() => (string) \Ticket::INCIDENT_TYPE,
            ]),
            'expected'   => \Ticket::INCIDENT_TYPE,
         ],
      ];
   }

   /**
    * @dataProvider providerSetTargetType
    */
   public function testSetTargetType(\PluginFormcreatorTargetTicket $originalInstance, $formAnswerId, $expected) {
      // reload the instance with the helper class
      $instance = new PluginFormcreatorTargetTicketDummy();
      $instance->getFromDB($originalInstance->getID());

      // load the form answer
      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswer->getFromDB($formAnswerId);

      $output = $instance->publicSetTargetType(
         [
         ],
         $formAnswer
      );
      $this->integer((int) $output['type'])->isEqualTo($expected);
   }

   public function providerPrepareTemplate() {
      global $CFG_GLPI;

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';
      $question = $this->getQuestion([
         'fieldtype' => 'textarea',
         '_parameters' => [
            'textarea' => [
               'range' => [
                  'range_min' => '',
                  'range_max' => '',
               ],
               'regex' => [
                  'regex' => ''
               ],
            ],
         ],
      ]);
      $this->boolean($question->isNewItem())->isFalse();
      $section = new \PluginFormcreatorSection();
      $section->getFromDB($question->fields[\PluginFormcreatorSection::getForeignKeyField()]);
      $form = new \PluginFormcreatorForm();
      $form->getFromDB($section->fields[\PluginFormcreatorForm::getForeignKeyField()]);
      $formAnswer = new \PluginFormcreatorFormAnswer();
      $formAnswerId = $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'validation_required' => 0,
         'formcreator_field_' . $question->getID() => 'foo',
      ]);
      $formAnswer->getFromDB($formAnswerId);
      $sectionName = $section->fields['name'];
      $questionTag = '##question_' . $question->getID() . '##';
      $answerTag = '##answer_' . $question->getID() . '##';
      $eolSimple = "\r\n";
      // 2 expected values
      // 0 : Rich text mode disabled
      // 1 : Rich text mode enabled
      return [
         [
            'template' => '##FULLFORM##',
            'form_answer' => $formAnswer,
            'expected' => [
               0 => 'Form data' . $eolSimple
                  . '=================' . $eolSimple
                  . $eolSimple
                  . $eolSimple . \Toolbox::addslashes_deep($sectionName) . $eolSimple
                  . '---------------------------------' . $eolSimple
                  . '1) ' . $questionTag . ' : ' . $answerTag . $eolSimple . $eolSimple,
               1 => '&lt;h1&gt;Form data&lt;/h1&gt;'
                  . '&lt;h2&gt;' . \Toolbox::addslashes_deep($sectionName) . '&lt;/h2&gt;'
                  . '&lt;div&gt;&lt;b&gt;1) ' . $questionTag . ' : &lt;/b&gt;' . $answerTag . '&lt;/div&gt;',
            ],
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareTemplate
    */
   public function testPrepareTemplate($template, $formAnswer, $expected) {
      $instance = new PluginFormcreatorTargetTicketDummy();
      $output = $instance->publicPrepareTemplate($template, $formAnswer);
      $this->string($output)->isEqualTo($expected[0]);

      $output = $instance->publicPrepareTemplate($template, $formAnswer, true);
      $this->string($output)->isEqualTo($expected[1]);
   }

   public function testExport() {
      $instance = $this->newTestedInstance();

      // Try to export an empty item
      $this->exception(function () use ($instance) {
         $instance->export();
      })->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ExportFailureException::class);

      // Prepare an item to export
      $instance = $this->getTargetTicket();
      $instance->getFromDB($instance->getID());

      // Export the item without the ID and with UUID
      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
         'name',
         'target_name',
         'type_rule',
         'type_question',
         'content',
         'due_date_rule',
         'due_date_question',
         'due_date_value',
         'due_date_period',
         'urgency_rule',
         'urgency_question',
         'validation_followup',
         'destination_entity',
         'destination_entity_value',
         'tag_type',
         'tag_questions',
         'tag_specifics',
         'category_rule',
         'category_question',
         'associate_rule',
         'associate_question',
         'location_rule',
         'location_question',
         'show_rule',
         'sla_rule',
         'sla_question_tto',
         'sla_question_ttr',
         'ola_rule',
         'ola_question_tto',
         'ola_question_ttr',
      ];
      $extraFields = [
         '_tickettemplate',
         '_actors',
         '_ticket_relations',
         'conditions',
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

   public function testImport() {
      $form = $this->getForm();
      $uuid = plugin_formcreator_getUuid();
      $input = [
         'name' => $this->getUniqueString(),
         'target_name' => $this->getUniqueString(),
         '_tickettemplate' => '',
         'content' => $this->getUniqueString(),
         'due_date_rule' => \PluginFormcreatorTargetTicket::DUE_DATE_RULE_NONE,
         'due_date_question' => '0',
         'due_date_value' => '',
         'due_date_period' => '',
         'urgency_rule' => \PluginFormcreatorTargetTicket::URGENCY_RULE_NONE,
         'urgency_question' => '0',
         'location_rule' => \PluginFormcreatorTargetTicket::LOCATION_RULE_NONE,
         'location_question' => '0',
         'validation_followup' => '1',
         'destination_entity' => '0',
         'destination_entity_value' => '',
         'tag_type' => \PluginFormcreatorTargetTicket::TAG_TYPE_NONE,
         'tag_questions' => '0',
         'tag_specifics' => '',
         'category_rule' => \PluginFormcreatorTargetTicket::CATEGORY_RULE_NONE,
         'category_question' => '0',
         'associate_rule' => \PluginFormcreatorTargetTicket::ASSOCIATE_RULE_NONE,
         'associate_question' => '0',
         'type_rule' => 1,
         'type_question' => 0,
         'uuid' => $uuid,
      ];

      // Check successful import with UUID
      $linker = new \PluginFormcreatorLinker();
      $targetTicketId = \PluginFormcreatorTargetTicket::import($linker, $input, $form->getID());
      $this->integer($targetTicketId)->isGreaterThan(0);

      unset($input['uuid']);

      // Check error if UUID and ID are mising
      $this->exception(
         function() use($linker, $input, $form) {
            \PluginFormcreatorTargetTicket::import($linker, $input, $form->getID());
         }
      )->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ImportFailureException::class)
         ->hasMessage('UUID or ID is mandatory for Target ticket'); // passes

      // Check sucessful import with ID
      $input['id'] = $targetTicketId;
      $targetTicketId2 = \PluginFormcreatorTargetTicket::import($linker, $input, $form->getID());
      $this->integer((int) $targetTicketId)->isNotEqualTo($targetTicketId2);

      $this->newTestedInstance()->delete([
         'id' => $targetTicketId2,
      ]);

      // Check successful link with template
      $templateName = 'ticket template ' . $this->getUniqueString();
      $ticketTemplate = new \TicketTemplate();
      $ticketTemplate->add([
         'name' => $templateName,
         'entities_id' => 0,
         'is_recursive' => 1,
      ]);
      $this->boolean($ticketTemplate->isNewItem())->isFalse();
      $input['_tickettemplate'] = $templateName;

      $linker = new \PluginFormcreatorLinker();
      $targetTicketId3 = \PluginFormcreatorTargetTicket::import($linker, $input, $form->getID());
      $this->integer((int) $targetTicketId)->isNotEqualTo($targetTicketId3);
      $targetTicket = $this->newTestedInstance();
      $targetTicket->getFromDB($targetTicketId3);
      $this->integer((int) $targetTicket->fields['tickettemplates_id'])
         ->isEqualTo($ticketTemplate->getID());
   }

   public function providerSetTargetCategory_nothing() {
      $form = $this->getForm();
      $formanswer = new \PluginFormcreatorFormanswer();
      $formanswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($formanswer->isNewItem())->isFalse();
      $targetTicket = new \PluginFormcreatorTargetTicket();
      $targetTicket->add([
         'name' => 'target ticket',
         'target_name' => 'target ticket',
         'plugin_formcreator_forms_id' => $form->getID(),
         'category_rule' => \PluginFormcreatorTargetTicket::CATEGORY_RULE_NONE,
      ]);
      $this->boolean($targetTicket->isNewItem())->isFalse();

      return [
         [
            'instance'   => $targetTicket,
            'formanswer' => $formanswer,
            'expected'   => 0
         ],
      ];
   }

   public function providerSetTargetCategory_noTemplate() {
      $category1 = new \ITILCategory();
      $category1Id = $category1->import([
         'name' => 'category 1',
         'entities_id' => 0,
      ]);
      $category2 = new \ITILCategory();
      $category2Id = $category2->import([
         'name' => 'category 2',
         'entities_id' => 0,
      ]);

      // Create a task category and ensure its ID is not the
      // same as the ticket categories created above
      $taskCategoryId = 0;
      do {
         $taskCategory = new \TaskCategory();
         $taskCategoryId = $taskCategory->import([
            'name' => $this->getUniqueString(),
            'entities_id' => 0,
         ]);
      } while ($taskCategoryId == $category1Id || $taskCategoryId == $category2Id);

      $question1 = $this->getQuestion([
         'name'      => 'request type',
         'fieldtype' => 'requesttype',
      ]);
      $this->boolean($question1->isNewItem())->isFalse();
      $section = new \PluginFormcreatorSection();
      $section->getFromDB($question1->fields['plugin_formcreator_sections_id']);
      $this->boolean($section->isNewItem())->isFalse();
      $question2 = $this->getQuestion([
         'plugin_formcreator_sections_id' => $section->getID(),
         'name'                           => 'request category',
         'fieldtype'                      => 'dropdown',
         'dropdown_values'                => \ITILCategory::class,
         'show_rule'  => \PluginFormcreatorCondition::SHOW_RULE_HIDDEN,
         '_conditions'                    => [
            'show_logic' => [\PluginFormcreatorCondition::SHOW_LOGIC_AND],
            'plugin_formcreator_questions_id' => [$question1->getID()],
            'show_condition'                  => [\PluginFormcreatorCondition::SHOW_CONDITION_EQ],
            'show_value'                      => ['Incident'],
         ]
      ]);
      $question3 = $this->getQuestion([
         'plugin_formcreator_sections_id' => $section->getID(),
         'name'                           => 'incident category',
         'fieldtype'                      => 'dropdown',
         'dropdown_values'                => \ITILCategory::class,
         'show_rule'  => \PluginFormcreatorCondition::SHOW_RULE_HIDDEN,
         '_conditions'                    => [
            'show_logic' => [\PluginFormcreatorCondition::SHOW_LOGIC_AND],
            'plugin_formcreator_questions_id' => [$question1->getID()],
            'show_condition'                  => [\PluginFormcreatorCondition::SHOW_CONDITION_EQ],
            'show_value'                      => ['Request'],
         ]
      ]);
      $question4 = $this->getQuestion([
         'plugin_formcreator_sections_id' => $section->getID(),
         'name'                           => 'other category',
         'fieldtype'                      => 'dropdown',
         'dropdown_values'                => \TaskCategory::class,
         '_conditions'                    => [
            'show_logic' => [],
            'plugin_formcreator_questions_id' => [],
            'show_condition'                  => [],
            'show_value'                      => [],
         ]
      ]);

      $formanswer1 = new \PluginFormcreatorFormAnswer();
      $formanswer1->add([
         'plugin_formcreator_forms_id' => $section->fields['plugin_formcreator_forms_id'],
         'formcreator_field_' . $question1->getID() => (string) \Ticket::INCIDENT_TYPE,
         'formcreator_field_' . $question2->getID() => (string) $category1Id,
         'formcreator_field_' . $question3->getID() => (string) $category2Id,
         'formcreator_field_' . $question4->getID() => (string) $taskCategoryId,
      ]);

      $formanswer2 = new \PluginFormcreatorFormAnswer();
      $formanswer2->add([
         'plugin_formcreator_forms_id' => $section->fields['plugin_formcreator_forms_id'],
         'formcreator_field_' . $question1->getID() => (string) \Ticket::DEMAND_TYPE,
         'formcreator_field_' . $question2->getID() => (string) $category1Id,
         'formcreator_field_' . $question3->getID() => (string) $category2Id,
         'formcreator_field_' . $question4->getID() => (string) $taskCategoryId,
      ]);

      $formanswer3 = new \PluginFormcreatorFormAnswer();
      $formanswer3->add([
         'plugin_formcreator_forms_id' => $section->fields['plugin_formcreator_forms_id'],
         'formcreator_field_' . $question1->getID() => (string) \Ticket::INCIDENT_TYPE,
         'formcreator_field_' . $question2->getID() => (string) $category1Id,
         'formcreator_field_' . $question3->getID() => (string) 0,
         'formcreator_field_' . $question4->getID() => (string) $taskCategoryId,
      ]);

      $instance1 = $this->newTestedInstance();
      $instance1->add([
         'name' => 'target ticket',
         'target_name' => 'target ticket',
         'plugin_formcreator_forms_id' => $formanswer1->getForm()->getID(),
         'category_rule' => \PluginFormcreatorTargetTicket::CATEGORY_RULE_LAST_ANSWER,
      ]);

      return [
         // Check visibility is taken into account
         'visibility taken into account' => [
            'instance'   => $instance1,
            'formanswer' => $formanswer1,
            'expected'   => $category1Id,
         ],
         // Check ticketcategory dropdown is ignored
         '1st ticket category question is ignored' => [
            'instance'   => $instance1,
            'formanswer' => $formanswer2,
            'expected'   => $category2Id,
         ],
         // Check zero value is ignored
         'zero value is ignored' => [
            'instance'   => $instance1,
            'formanswer' => $formanswer3,
            'expected'   => $category1Id,
         ]
      ];
   }

   /**
    * Test if a template with a predefined category is properly applied
    *
    * @return array
    */
   public function providerSetTargetCategory_FromTemplate() {
      // When the target ticket uses a ticket template and does not specify a category
      $category1 = new \ITILCategory();
      $category1Id = $category1->import([
         'name' => 'category 1',
         'entities_id' => 0,
      ]);

      $ticketTemplate = $this->getGlpiCoreItem(
         \TicketTemplate::getType(), [
            'name' => 'template with predefined category',
         ]
      );
      $this->getGlpiCoreItem(\TicketTemplatePredefinedField::getType(), [
         'tickettemplates_id' => $ticketTemplate->getID(),
         'num'                => 7, // ITIL category
         'value'              => $category1Id
      ]);

      $form = $this->getForm();

      $formanswer1 = new \PluginFormcreatorFormAnswer();
      $formanswer1->add([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($formanswer1->isNewItem())->isFalse();

      $instance1 = $this->newTestedInstance();
      $instance1->add([
         'name' => 'target ticket',
         'target_name' => 'target ticket',
         'plugin_formcreator_forms_id' => $form->getID(),
         'tickettemplates_id' => $ticketTemplate->getID(),
         'category_rule' => \PluginFormcreatorTargetTicket::CATEGORY_RULE_NONE,
      ]);
      $this->boolean($instance1->isNewItem())->isFalse();

      return [
         [
            'instance'   => $instance1,
            'formanswer' => $formanswer1,
            'expected'   => $category1Id,
         ],
      ];
   }

   public function providerSetTargetCategory() {
      return array_merge(
         $this->providerSetTargetCategory_nothing(),
         $this->providerSetTargetCategory_noTemplate(),
         $this->providerSetTargetCategory_FromTemplate()
      );
   }

   /**
    * @dataProvider providerSetTargetCategory
    */
   public function testSetTargetCategory($instance, $formanswer, $expected) {
      // Substitute a dummy class to access protected / private methods
      $dummyItemtype = 'GlpiPlugin\Formcreator\Tests\\' . $this->getTestedClassName() . 'Dummy';
      $dummyInstance = new $dummyItemtype();
      /**@var \GlpiPlugin\Formcreator\Tests\PluginFormcreatorTargetTicketDummy  */
      $instance->getFromDB($instance->getID());
      $dummyInstance->fields = $instance->fields;

      \PluginFormcreatorFields::resetVisibilityCache();
      $data = $dummyInstance->publicGetDefaultData($formanswer);
      $output = $dummyInstance->publicSetTargetCategory($data, $formanswer);

      $this->integer((int) $output['itilcategories_id'])->isEqualTo($expected);
   }

   public function providerSetTargetAssociatedItem_1() {
      // Prepare form
      $question = $this->getQuestion([
         'fieldtype' => 'glpiselect',
         'glpi_objects' => \Computer::class,
      ]);
      $form = new \PluginFormcreatorForm();
      $form->getByQuestionId($question->getID());

      // Have an item to associate
      $computer = new \Computer();
      $computer->add([
         'name' => $this->getUniqueString(),
         'entities_id' => '0',
      ]);
      $this->boolean($computer->isNewItem())->isFalse();

      // Prepare form answer
      $formAnswer = new \PluginFormcreatorFormAnswer;
      $formAnswer->add([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
         'name' => $form->fields['name'],
         'requester_d' => 2, // glpi user id
         'status' => \PluginFormcreatorFormAnswer::STATUS_WAITING,
         'formcreator_field_' . $question->getID() => (string) $computer->getID(),
      ]);
      $this->boolean($formAnswer->isNewItem())->isFalse();

      // Prepare target ticket
      $instance = new PluginFormcreatorTargetTicketDummy();
      $instance->add([
         'name' => 'foo',
         'target_name' => '',
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
         'content' => '##FULLFORM',
         'associate_rule' => \PluginFormcreatorTargetTicket::ASSOCIATE_RULE_ANSWER,
         'associate_question' => $question->getID(),
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      return [
         [
            'instance' => $instance,
            'formanswer' => $formAnswer,
            'expected' => [
               'Computer' => [
                  $computer->getID() => (string) $computer->getID()
               ]
            ],
         ],
      ];
   }

   public function providerSetTargetAssociatedItem_LastItem() {
      global $CFG_GLPI;

      // Prepare form
      $validItemtype = $CFG_GLPI["asset_types"][0];
      if (array_search(\Computer::getType(), $CFG_GLPI['asset_types']) === false) {
         $CFG_GLPI['asset_types'][] = \Computer::getType();
      }
      $invalidItemtype = \Monitor::getType();

      // Ensure an itemtype is not in the asset types
      $CFG_GLPI['asset_types'] = array_filter($CFG_GLPI['asset_types'], function ($itemtype) use ($invalidItemtype) {
         return ($itemtype != $invalidItemtype);
      });

      $item1 = new $validItemtype();
      $item1->add([
         'name' => $this->getUniqueString(),
         'entities_id' => \Session::getActiveEntity(),
      ]);
      $this->boolean($item1->isNewItem())->isFalse();
      $item2 = new $validItemtype();
      $item2->add([
         'name' => $this->getUniqueString(),
         'entities_id' => \Session::getActiveEntity(),
      ]);
      $this->boolean($item2->isNewItem())->isFalse();

      $question1 = $this->getQuestion([
         'fieldtype' => 'glpiselect',
         'glpi_objects' => $validItemtype,
      ]);
      $form1 = new \PluginFormcreatorForm();
      $form1->getByQuestionId($question1->getID());
      $sectionId = $question1->fields['plugin_formcreator_sections_id'];
      $question2 = $this->getQuestion([
         'plugin_formcreator_sections_id' => $sectionId,
         'fieldtype'                      => 'glpiselect',
         'glpi_objects'                   => $validItemtype
      ]);
      $instance1 = new PluginFormcreatorTargetTicketDummy();
      $instance1->add([
         'name' => 'foo',
         'target_name' => '',
         \PluginFormcreatorForm::getForeignKeyField() => $form1->getID(),
         'content' => '##FULLFORM',
         'associate_rule' => \PluginFormcreatorTargetTicket::ASSOCIATE_RULE_LAST_ANSWER,
         'associate_question' => $question2->getID(),
      ]);
      $this->boolean($instance1->isNewItem())->isFalse();
      $formAnswer1 = new \PluginFormcreatorFormAnswer();
      $formAnswer1->add([
         'plugin_formcreator_forms_id' => $form1->getID(),
         'formcreator_field_' . $question1->getID() => (string) $item1->getID(),
         'formcreator_field_' . $question2->getID() => (string) $item2->getID(),
      ]);
      $this->boolean($formAnswer1->isNewItem())->isFalse();

      $question3 = $this->getQuestion([
         'fieldtype' => 'glpiselect',
         'glpi_objects' => $validItemtype,
      ]);
      $form2 = new \PluginFormcreatorForm();
      $form2->getByQuestionId($question3->getID());
      $sectionId = $question3->fields['plugin_formcreator_sections_id'];
      $question4 = $this->getQuestion([
         'plugin_formcreator_sections_id' => $sectionId,
         'fieldtype'                      => 'glpiselect',
         'glpi_objects'                   => $invalidItemtype
      ]);

      $instance2 = new PluginFormcreatorTargetTicketDummy();
      $instance2->add([
         'name' => 'foo',
         'target_name' => '',
         \PluginFormcreatorForm::getForeignKeyField() => $form2->getID(),
         'content' => '##FULLFORM',
         'associate_rule' => \PluginFormcreatorTargetTicket::ASSOCIATE_RULE_LAST_ANSWER,
         'associate_question' => $question3->getID(),
      ]);
      $this->boolean($instance2->isNewItem())->isFalse();
      $monitor = $this->getGlpiCoreItem(\Monitor::getType(), ['name' => $this->getUniqueString()]);
      $this->boolean($monitor->isNewItem())->isFalse();
      $formAnswer2 = new \PluginFormcreatorFormAnswer();
      $formAnswer2->add([
         'plugin_formcreator_forms_id' => $form2->getID(),
         'formcreator_field_' . $question3->getID() => (string) $item1->getID(),
         'formcreator_field_' . $question4->getID() => (string) $monitor->getID(),
      ]);
      $this->boolean($formAnswer2->isNewItem())->isFalse();

      $question5 = $this->getQuestion([
         'fieldtype' => 'glpiselect',
         'glpi_objects' => $invalidItemtype,
      ]);
      $form3 = new \PluginFormcreatorForm();
      $form3->getByQuestionId($question5->getID());
      $sectionId = $question5->fields['plugin_formcreator_sections_id'];
      $question6 = $this->getQuestion([
         'plugin_formcreator_sections_id' => $sectionId,
         'fieldtype'                      => 'glpiselect',
         'glpi_objects'                   => $invalidItemtype
      ]);
      $instance3 = new PluginFormcreatorTargetTicketDummy();
      $instance3->add([
         'name' => 'foo',
         'target_name' => '',
         \PluginFormcreatorForm::getForeignKeyField() => $form3->getID(),
         'content' => '##FULLFORM',
         'associate_rule' => \PluginFormcreatorTargetTicket::ASSOCIATE_RULE_LAST_ANSWER,
         'associate_question' => $question5->getID(),
      ]);
      $this->boolean($instance3->isNewItem())->isFalse();
      $monitor = $this->getGlpiCoreItem(\Monitor::getType(), ['name' => $this->getUniqueString()]);
      $this->boolean($monitor->isNewItem())->isFalse();
      $monitor2 = $this->getGlpiCoreItem(\Monitor::getType(), ['name' => $this->getUniqueString()]);
      $this->boolean($monitor->isNewItem())->isFalse();
      $formAnswer3 = new \PluginFormcreatorFormAnswer();
      $formAnswer3->add([
         'plugin_formcreator_forms_id' => $form3->getID(),
         'formcreator_field_' . $question5->getID() => (string) $monitor->getID(),
         'formcreator_field_' . $question6->getID() => (string) $monitor2->getID(),
      ]);
      $this->boolean($formAnswer3->isNewItem())->isFalse();

      $question7 = $this->getQuestion([
         'fieldtype' => 'glpiselect',
         'glpi_objects' => $validItemtype,
      ]);
      $form4 = new \PluginFormcreatorForm();
      $form4->getByQuestionId($question7->getID());
      $sectionId = $question7->fields['plugin_formcreator_sections_id'];
      $question8 = $this->getQuestion([
         'plugin_formcreator_sections_id' => $sectionId,
         'fieldtype'                      => 'glpiselect',
         'glpi_objects'                   => $validItemtype
      ]);

      $instance4 = new PluginFormcreatorTargetTicketDummy();
      $instance4->add([
         'name' => 'foo',
         'target_name' => '',
         \PluginFormcreatorForm::getForeignKeyField() => $form4->getID(),
         'content' => '##FULLFORM',
         'associate_rule' => \PluginFormcreatorTargetTicket::ASSOCIATE_RULE_LAST_ANSWER,
         'associate_question' => $question7->getID(),
      ]);
      $this->boolean($instance4->isNewItem())->isFalse();
      $formAnswer4 = new \PluginFormcreatorFormAnswer();
      // use non existing items ids and existing itemtypes
      $item7 = new $validItemtype();
      $item7->add([
         'name' => $this->getUniqueString(),
         'entities_id' => \Session::getActiveEntity(),
      ]);
      $this->boolean($item7->isNewItem())->isFalse();
      $item8 = new $validItemtype();
      $item8->add([
         'name' => $this->getUniqueString(),
         'entities_id' => \Session::getActiveEntity(),
      ]);
      $this->boolean($item8->isNewItem())->isFalse();
      $formAnswer4->add([
         'plugin_formcreator_forms_id' => $form4->getID(),
         'formcreator_field_' . $question7->getID() => (string) $item7->getID(),
         'formcreator_field_' . $question8->getID() => (string) $item8->getID(),
      ]);
      $this->boolean($formAnswer4->isNewItem())->isFalse();
      // Make items non existing for ticket generation
      $item7->delete($item7->fields, 1);
      $item8->delete($item8->fields, 1);

      return [
         [
            'instance'   => $instance1,
            'formanswer' => $formAnswer1,
            'expected'   => [
               $validItemtype => [
                  $item2->getID() => (string) $item2->getID()
               ]
            ],
         ],
         [
            'instance'   => $instance2,
            'formanswer' => $formAnswer2,
            'expected'   => [
               $validItemtype => [
                  $item1->getID() => (string) $item1->getID()
               ]
            ],
         ],
         [
            'instance'   => $instance3,
            'formanswer' => $formAnswer3,
            'expected'   => null,
         ],
         [
            'instance'   => $instance4,
            'formanswer' => $formAnswer4,
            'expected'   => null,
         ],
      ];
   }

   public function providerSetTargetAssociatedItem() {
      global $CFG_GLPI;

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      return array_merge(
         $this->providerSetTargetAssociatedItem_1(),
         $this->providerSetTargetAssociatedItem_LastItem()
      );
   }

   /**
    * @dataProvider providerSetTargetAssociatedItem
    */
   public function testSetTargetAssociatedItem($instance, $formanswer, $expected) {
      $output = $instance->publicSetTargetAssociatedItem([], $formanswer);
      if ($expected !== null) {
         $this->array($output['items_id'])->isIdenticalTo($expected);
      } else {
         $this->array($output)->notHasKey('items_id');
      }
   }

   public function testIsEntityAssign() {
      $instance = $this->newTestedInstance();
      $this->boolean($instance->isEntityAssign())->isFalse();
   }

   public function testDeleteObsoleteItems() {
      $form = $this->getForm();
      $targetTicket1 = $this->getTargetTicket([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $targetTicket2 = $this->getTargetTicket([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $instance = $this->newTestedInstance();
      $instance->deleteObsoleteItems($form, [$targetTicket2->getID()]);

      $checkDeleted = $this->newTestedInstance();
      $this->boolean($checkDeleted->getFromDB($targetTicket1->getID()))->isFalse();
      $checkDeleted = $this->newTestedInstance();
      $this->boolean($checkDeleted->getFromDB($targetTicket2->getID()))->isTrue();
   }

   public function providerPrepareInputForAdd() {
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      $form = $this->getForm();
      $name = $this->getUniqueString();
      return [
         'name is mandatory' => [
            'input'    => [
               $formFk => $form->getID(),
            ],
            'expected' => [],
            'message' => 'Name is required.',
         ],
         [
            'input'    => [
               $formFk => $form->getID(),
               'name' => $name,
            ],
            'expected' => [
               $formFk => $form->getID(),
               'name' => $name,
               'target_name' => $name,
               'content' => '##FULLFORM##',
               'type_rule'     => \PluginFormcreatorTargetTicket::REQUESTTYPE_SPECIFIC,
               'type_question' => \Ticket::INCIDENT_TYPE,
            ],
            'message' => null,
         ],
         [
            'input'    => [
               $formFk => $form->getID(),
               'name' => $name,
               'type_rule'     => \PluginFormcreatorTargetTicket::REQUESTTYPE_SPECIFIC,
               'type_question' => \Ticket::DEMAND_TYPE,
            ],
            'expected' => [
               $formFk => $form->getID(),
               'name' => $name,
               'target_name' => $name,
               'content' => '##FULLFORM##',
               'type_rule'     => \PluginFormcreatorTargetTicket::REQUESTTYPE_SPECIFIC,
               'type_question' => \Ticket::DEMAND_TYPE,
            ],
            'message' => null,
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareInputForAdd
    *
    */
   public function testPrepareInputForAdd($input, $expected, $message) {
      $instance = $this->newTestedInstance();
      $output = $instance->prepareInputForAdd($input);
      if (count($expected) > 0) {
         $this->array($output)->hasKey('uuid');
         unset($output['uuid']);
         $this->array($output)->isEqualTo($expected);
      } else {
         $this->boolean($output)->isFalse();
         $this->sessionHasMessage($message, ERROR);
      }
   }
}
