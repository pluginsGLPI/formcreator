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
 * @copyright Copyright © 2011 - 2019 Teclib'
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

   public function testGetEnumUrgencyRule() {
      $output = \PluginFormcreatorTargetChange::getEnumUrgencyRule();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorTargetBase::URGENCY_RULE_NONE      => 'Medium',
         \PluginFormcreatorTargetBase::URGENCY_RULE_SPECIFIC  => 'Specific urgency',
         \PluginFormcreatorTargetBase::URGENCY_RULE_ANSWER    => 'Equals to the answer to the question',
      ]);
   }

   public function getEnumCategoryRule() {
      $output = \PluginFormcreatorTargetChange::getEnumUrgencyRule();
      $this->array($output)->isEqualTo([
         'none'      => 'None',
         'specific'  => 'Specific category',
         'answer'    => 'Equals to the answer to the question',
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

   public function testGetItem_Actor() {
      $instance = new PluginFormcreatorTargetChangeDummy();
      $output = $instance->publicGetItem_Actor();
      $this->object($output)->isInstanceOf(\PluginFormcreatorTargetChange_Actor::class);
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
         'name',
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
      $targetChange->update([
         'id' => $targetChange->getID(),
         '_skip_checks' => true,
         'destination_entity' => \PluginFormcreatorTargetChange::DESTINATION_ENTITY_CURRENT,
         'destination_entity_value' => '0',
      ]);
      $instance->getFromDB($targetChange->getID());
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
      $profileUser->add([
         \User::getForeignKeyField() => $user->getID(),
         \Profile::getForeignKeyField() => 4, // Super admin
         \Entity::getForeignKeyField() => 0,
      ]);
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => 0,
      ]);
      $this->boolean($this->login($user->fields['name'], 'passwd'))->isTrue();
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
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      $this->boolean($this->login($user->fields['name'], 'passwd'))->isTrue();
      $requesterId = \Session::getLoginUserID();
      $output = $instance->publicSetTargetEntity([], $formAnswer, $requesterId);
      $this->integer((int) $output['entities_id'])->isEqualTo(0);

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
      $output = $instance->export();
      $this->boolean($output)->isFalse();

      // Prepare an item to export
      $instance = $this->getTargetChange();
      $instance->getFromDB($instance->getID());

      // Export the item without the ID and with UUID
      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
         'name',
         'content',
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
      ];
      $extraFields = [
         '_actors',
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

}
