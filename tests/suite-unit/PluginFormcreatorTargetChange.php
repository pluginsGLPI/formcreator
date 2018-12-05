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
use GlpiPlugin\Formcreator\Tests\PluginFormcreatorTargetChangeDummy;

class PluginFormcreatorTargetChange extends CommonTestCase {

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testGetTargetEntity':
            $this->boolean($this->login('glpi', 'glpi'))->isTrue();
            break;
      }
   }

   /**
    * @engine inline
    *
    * @return void
    */
   public function  testGetTargetEntity() {
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
         'destination_entity' => 'current',
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
      $output = $instance->publicGetTargetEntity($formAnswer, $requesterId);
      $this->integer((int) $output)->isEqualTo($entityId);

      // Test requester's entity
      $targetChange->update([
         'id' => $targetChange->getID(),
         '_skip_checks' => true,
         'destination_entity' => 'requester',
         'destination_entity_value' => '0',
      ]);
      $instance->getFromDB($targetChange->getID());
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      \Session::changeActiveEntities($entityId);
      $requesterId = \Session::getLoginUserID();
      $output = $instance->publicGetTargetEntity($formAnswer, $requesterId);
      $this->integer((int) $output)->isEqualTo(0);

      // Test requester's first entity (alphanumeric order)
      $targetChange->update([
         'id' => $targetChange->getID(),
         '_skip_checks' => true,
         'destination_entity' => 'requester_dynamic_first',
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
      $output = $instance->publicGetTargetEntity($formAnswer, $requesterId);
      $this->integer((int) $output)->isEqualTo($entityId);

      // Test requester's last entity (alphanumeric order)
      $targetChange->update([
         'id' => $targetChange->getID(),
         '_skip_checks' => true,
         'destination_entity' => 'requester_dynamic_last',
         'destination_entity_value' => '0',
      ]);
      $instance->getFromDB($targetChange->getID());
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => $entityId,
      ]);
      $this->boolean($this->login($user->fields['name'], 'passwd'))->isTrue();
      $requesterId = \Session::getLoginUserID();
      $output = $instance->publicGetTargetEntity($formAnswer, $requesterId);
      $this->integer((int) $output)->isEqualTo(0);

      // Test specific entity
      $this->boolean($this->login('glpi', 'glpi'))->isTrue();
      $entityId = $entity->import([
         'entities_id' => '0',
         'name' => $this->getUniqueString(),
      ]);
      $targetChange->update([
         'id' => $targetChange->getID(),
         '_skip_checks' => true,
         'destination_entity' => 'specific',
         'destination_entity_value' => "$entityId",
      ]);
      $instance->getFromDB($targetChange->getID());
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'entities_id' => 0,
      ]);
      $requesterId = \Session::getLoginUserID();
      $output = $instance->publicGetTargetEntity($formAnswer, $requesterId);
      $this->integer((int) $output)->isEqualTo($entityId);

      // Test form's entity
      $entityId = $entity->import([
         'entities_id' => '0',
         'name' => $this->getUniqueString(),
      ]);
      $targetChange->update([
         'id' => $targetChange->getID(),
         '_skip_checks' => true,
         'destination_entity' => 'form',
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
      $output = $instance->publicGetTargetEntity($formAnswer, $requesterId);
      $this->integer((int) $output)->isEqualTo($entityId);
   }
}
