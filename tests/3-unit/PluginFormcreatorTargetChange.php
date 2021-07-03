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
use GlpiPlugin\Formcreator\Tests\PluginFormcreatorTargetChangeDummy;

class PluginFormcreatorTargetChange extends CommonTestCase {

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testSetTargetEntity':
         case 'testImport':
            $this->boolean($this->login('glpi', 'glpi'))->isTrue();
            break;
      }
   }

   public function providerGetTypeName() {
      return [
         [
            'input' => 0,
            'expected' => 'Target changes',
         ],
         [
            'input' => 1,
            'expected' => 'Target change',
         ],
         [
            'input' => 2,
            'expected' => 'Target changes',
         ],
      ];
   }

   /**
    * @dataProvider providerGetTypeName
    * @param integer $number
    * @param string $expected
    */
   public function testGetTypeName($number, $expected) {
      $output = \PluginFormcreatorTargetChange::getTypeName($number);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerPrepareInputForUpdate() {
      return [
         [
            'input' => [
               'name' => '',
               'content' => '',
               'sla_rule' => (string) \PluginFormcreatorTargetChange::SLA_RULE_NONE,
               'ola_rule' => (string) \PluginFormcreatorTargetChange::OLA_RULE_NONE,
            ],
            'expected' => [
            ],
            'message' => 'The name cannot be empty!',
         ],
         [
            'input' => [
               'name' => 'something',
               'content' => '',
               'sla_rule' => (string) \PluginFormcreatorTargetChange::SLA_RULE_NONE,
               'ola_rule' => (string) \PluginFormcreatorTargetChange::OLA_RULE_NONE,
            ],
            'expected' => [
            ],
            'message' => 'The description cannot be empty!',
         ],
         [
            'input' => [
               'name' => 'something',
               'content' => 'foo',
               'destination_entity' => \PluginFormcreatorTargetChange::DESTINATION_ENTITY_SPECIFIC,
               '_destination_entity_value_specific' => '0',
               'urgency_rule' => \PluginFormcreatorTargetChange::URGENCY_RULE_SPECIFIC,
               '_urgency_specific' => '3',
               'category_rule' => \PluginFormcreatorTargetChange::CATEGORY_RULE_NONE,
               'category_question' => '0',
               'sla_rule' => (string) \PluginFormcreatorTargetChange::SLA_RULE_NONE,
               'ola_rule' => (string) \PluginFormcreatorTargetChange::OLA_RULE_NONE,
            ],
            'expected' => [
               'name' => 'something',
               'content' => 'foo',
               'destination_entity' => \PluginFormcreatorTargetChange::DESTINATION_ENTITY_SPECIFIC,
               'destination_entity_value' => '0',
               'urgency_rule' => \PluginFormcreatorTargetChange::URGENCY_RULE_SPECIFIC,
               'urgency_question' => '3',
               'category_rule' => \PluginFormcreatorTargetChange::CATEGORY_RULE_NONE,
               'category_question' => '0',
            ],
            'message' => null,
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareInputForUpdate
    */
   public function testPrepareInputForUpdate($input, $expected, $message) {
      $instance = $this->newTestedInstance();
      $output = $instance->prepareInputForUpdate($input);

      if ($message !== null) {
         $this->sessionHasMessage($message, ERROR);
         $this->array($output)->hasSize(0);
         return;
      }

      $this->string($output['name'])->isEqualTo($expected['name']);
      $this->string($output['content'])->isEqualTo($expected['content']);
      $this->string($output['name'])->isEqualTo($expected['name']);
      $this->integer($output['destination_entity'])->isEqualTo($expected['destination_entity']);
      $this->string($output['destination_entity_value'])->isEqualTo($expected['destination_entity_value']);
      $this->integer($output['urgency_rule'])->isEqualTo($expected['urgency_rule']);
      $this->string($output['urgency_question'])->isEqualTo($expected['urgency_question']);
      $this->integer($output['category_rule'])->isEqualTo($expected['category_rule']);
      $this->string($output['category_question'])->isEqualTo($expected['category_question']);
   }

   public function testGetEnumUrgencyRule() {
      $output = \PluginFormcreatorTargetChange::getEnumUrgencyRule();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorTargetTicket::URGENCY_RULE_NONE      => 'Urgency from template or Medium',
         \PluginFormcreatorTargetChange::URGENCY_RULE_SPECIFIC  => 'Specific urgency',
         \PluginFormcreatorTargetChange::URGENCY_RULE_ANSWER    => 'Equals to the answer to the question',
      ]);
   }

   public function testGetEnumCategoryRule() {
      $output = \PluginFormcreatorTargetChange::getEnumCategoryRule();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorTargetTicket::CATEGORY_RULE_NONE          => 'Category from template or none',
         \PluginFormcreatorTargetTicket::CATEGORY_RULE_SPECIFIC      => 'Specific category',
         \PluginFormcreatorTargetTicket::CATEGORY_RULE_ANSWER        => 'Equals to the answer to the question',
         \PluginFormcreatorTargetTicket::CATEGORY_RULE_LAST_ANSWER   => 'Last valid answer',
      ]);
   }

   public function testGetItem_User() {
      $instance = new PluginFormcreatorTargetChangeDummy();
      $output = $instance->publicGetItem_User();
      $this->object($output)->isInstanceOf(\Change_User::class);
      $this->boolean($output->isNewItem())->isTrue();
   }

   public function testGetItem_Group() {
      $instance = new PluginFormcreatorTargetChangeDummy();
      $output = $instance->publicGetItem_Group();
      $this->object($output)->isInstanceOf(\Change_Group::class);
      $this->boolean($output->isNewItem())->isTrue();
   }

   public function testGetItem_Supplier() {
      $instance = new PluginFormcreatorTargetChangeDummy();
      $output = $instance->publicGetItem_Supplier();
      $this->object($output)->isInstanceOf(\Change_Supplier::class);
      $this->boolean($output->isNewItem())->isTrue();
   }

   public function testGetItem_Item() {
      $instance = new PluginFormcreatorTargetChangeDummy();
      $output = $instance->publicGetItem_Item();
      $this->object($output)->isInstanceOf(\Change_Item::class);
      $this->boolean($output->isNewItem())->isTrue();
   }

   public function testGetCategoryFilter() {
      $instance = new PluginFormcreatorTargetChangeDummy();
      $output = $instance->publicGetCategoryFilter();
      $this->array($output)->isEqualTo([
         'is_change' => 1,
      ]);
   }

   public function testGetTaggableFields() {
      $instance = new PluginFormcreatorTargetChangeDummy();
      $output = $instance->publicGetTaggableFields();
      $this->array($output)->isEqualTo([
         'target_name',
         'content',
         'impactcontent',
         'controlistcontent',
         'rolloutplancontent',
         'backoutplancontent',
         'checklistcontent',
      ]);
   }

   public function testGetTargetItemtypeName() {
      $instance = new PluginFormcreatorTargetChangeDummy();
      $output = $instance->publicGetTargetItemtypeName();
      $this->string($output)->isEqualTo(\Change::class);
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
      $targetChange = $this->getTargetChange([
         $formFk => $form->getID(),
      ]);

      // Use a dummy class to access protected methods
      $instance = new PluginFormcreatorTargetChangeDummy();
      $instance->getFromDB($targetChange->getID());

      // Test current entity of the requester
      $entity = new \Entity();
      $entityId = $entity->import([
         'entities_id' => '0',
         'name' => $this->getUniqueString()
      ]);
      \Session::changeActiveEntities($entityId);
      $targetChange->update([
         'id' => $targetChange->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetChange::DESTINATION_ENTITY_CURRENT,
         'destination_entity_value' => '0',
      ]);
      $instance->getFromDB($targetChange->getID());

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

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
      $targetChange->update([
         'id' => $targetChange->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetChange::DESTINATION_ENTITY_REQUESTER,
         'destination_entity_value' => '0',
      ]);
      $instance->getFromDB($targetChange->getID());

      // Disable notification to avoid output to console
      $CFG_GLPI['use_notifications'] = '0';

      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      \Session::changeActiveEntities($entityId);
      $requesterId = \Session::getLoginUserID();
      $output = $instance->publicSetTargetEntity([], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo(0);

      // Test requester's first entity (alphanumeric order)
      $targetChange->update([
         'id' => $targetChange->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetChange::DESTINATION_ENTITY_REQUESTER_DYN_FIRST,
         'destination_entity_value' => '0',
      ]);
      $instance->getFromDB($targetChange->getID());
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
      $targetChange->update([
         'id' => $targetChange->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetChange::DESTINATION_ENTITY_REQUESTER_DYN_LAST,
         'destination_entity_value' => '0',
      ]);
      $instance->getFromDB($targetChange->getID());

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
      $targetChange->update([
         'id' => $targetChange->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetChange::DESTINATION_ENTITY_SPECIFIC,
         'destination_entity_value' => "$entityId",
      ]);
      $instance->getFromDB($targetChange->getID());

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
      $targetChange->update([
         'id' => $targetChange->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetChange::DESTINATION_ENTITY_FORM,
         'destination_entity_value' => '0',
      ]);
      $form->update([
         'id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      $instance->getFromDB($targetChange->getID());

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

   public function testExport() {
      $instance = $this->newTestedInstance();

      // Try to export an empty item
      $this->exception(function () use ($instance) {
         $instance->export();
      })->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ExportFailureException::class);

      // Prepare an item to export
      $instance = $this->getTargetChange();
      $instance->getFromDB($instance->getID());

      // Export the item without the ID and with UUID
      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
         'name',
         'content',
         'target_name',
         'impactcontent',
         'controlistcontent',
         'rolloutplancontent',
         'backoutplancontent',
         'checklistcontent',
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
         'show_rule',
         'sla_rule',
         'sla_question_tto',
         'sla_question_ttr',
         'ola_rule',
         'ola_question_tto',
         'ola_question_ttr',
      ];
      $extraFields = [
         '_changetemplate',
         '_actors',
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
         '_changetemplate' => '',
         'content' => $this->getUniqueString(),
         'impactcontent' => $this->getUniqueString(),
         'controlistcontent' => $this->getUniqueString(),
         'rolloutplancontent' => $this->getUniqueString(),
         'backoutplancontent' => $this->getUniqueString(),
         'checklistcontent' => $this->getUniqueString(),
         'due_date_rule' => \PluginFormcreatorTargetChange::DUE_DATE_RULE_NONE,
         'due_date_question' => '0',
         'due_date_value' => '',
         'due_date_period' => '',
         'urgency_rule' => \PluginFormcreatorTargetChange::URGENCY_RULE_NONE,
         'urgency_question' => '0',
         'validation_followup' => '1',
         'destination_entity' => '0',
         'destination_entity_value' => '',
         'tag_type' => \PluginFormcreatorTargetChange::TAG_TYPE_NONE,
         'tag_questions' => '0',
         'tag_specifics' => '',
         'category_rule' => \PluginFormcreatorTargetChange::CATEGORY_RULE_NONE,
         'category_question' => '0',
         'uuid' => $uuid,
      ];

      $linker = new \PluginFormcreatorLinker();
      $targetChangeId = \PluginFormcreatorTargetChange::import($linker, $input, $form->getID());
      $this->integer($targetChangeId)->isGreaterThan(0);

      unset($input['uuid']);

      $this->exception(
         function() use($linker, $input, $form) {
            \PluginFormcreatorTargetChange::import($linker, $input, $form->getID());
         }
      )->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ImportFailureException::class)
         ->hasMessage('UUID or ID is mandatory for Target change'); // passes

      $input['id'] = $targetChangeId;
      $targetChangeId2 = \PluginFormcreatorTargetChange::import($linker, $input, $form->getID());
      $this->integer((int) $targetChangeId)->isNotEqualTo($targetChangeId2);

      $this->newTestedInstance()->delete([
         'id' => $targetChangeId2,
      ]);

      // Check successful link with template
      $templateName = 'change template ' . $this->getUniqueString();
      $changeTemplate = new \ChangeTemplate();
      $changeTemplate->add([
         'name' => $templateName,
         'entities_id' => 0,
         'is_recursive' => 1,
      ]);
      $this->boolean($changeTemplate->isNewItem())->isFalse();
      $input['_changetemplate'] = $templateName;

      $linker = new \PluginFormcreatorLinker();
      $targetChangeId3 = \PluginFormcreatorTargetChange::import($linker, $input, $form->getID());
      $this->integer((int) $targetChangeId)->isNotEqualTo($targetChangeId3);
      $targetChange = $this->newTestedInstance();
      $targetChange->getFromDB($targetChangeId3);
      $this->integer((int) $targetChange->fields['changetemplates_id'])
         ->isEqualTo($changeTemplate->getID());
   }

   public function testIsEntityAssign() {
      $instance = $this->newTestedInstance();
      $this->boolean($instance->isEntityAssign())->isFalse();
   }

   public function testdeleteObsoleteItems() {
      $form = $this->getForm();
      $targetChange1 = $this->getTargetChange([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $targetChange2 = $this->getTargetChange([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $instance = $this->newTestedInstance();
      $instance->deleteObsoleteItems($form, [$targetChange2->getID()]);

      $checkDeleted = $this->newTestedInstance();
      $this->boolean($checkDeleted->getFromDB($targetChange1->getID()))->isFalse();
      $checkDeleted = $this->newTestedInstance();
      $this->boolean($checkDeleted->getFromDB($targetChange2->getID()))->isTrue();
   }
}
