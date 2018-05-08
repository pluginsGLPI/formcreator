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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class TargetTicketTest extends SuperAdminTestCase {

   public function setUp() {
      parent::setUp();

      $this->formData = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'a form',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => 0
      );

      $this->target = array(
            'name'                  => 'a target',
            'itemtype'              => 'PluginFormcreatorTargetTicket'
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
   public function testCreateTarget(PluginFormcreatorForm $form) {
      $target = new PluginFormcreatorTarget();
      $this->target = $this->target + array(
            'plugin_formcreator_forms_id' => $form->getID()
      );
      $target->add($this->target);
      $this->assertFalse($target->isNewItem());
      $this->assertEquals($form->getID(), $target->getField('plugin_formcreator_forms_id'));
      $this->assertEquals('PluginFormcreatorTargetTicket', $target->getField('itemtype'));

      return $target;
   }

   /**
    * @depends testInitCreateForm
    * @depends testCreateTarget
    * @param PluginFormcreatorForm $form
    * @param PluginFormcreatorTarget $target
    */
   public function testTargetTicket(PluginFormcreatorForm $form, PluginFormcreatorTarget $target) {
      $targetTicket = $target->getField('items_id');
      $targetTicket = new PluginFormcreatorTargetTicket();
      $targetTicket->getFromDB($target->getField('items_id'));
      $this->assertFalse($targetTicket->isNewItem());
      $this->assertEquals($target->getField('name'), $targetTicket->getField('name'));

      return $targetTicket;
   }

   /**
    * @depends testTargetTicket
    * @param PluginFormcreatorTargetTicket $target
    */
   public function testTargetTicketActors(PluginFormcreatorTargetTicket $targetTicket) {
      $requesterActor = new PluginFormcreatorTargetTicket_Actor();
      $observerActor = new PluginFormcreatorTargetTicket_Actor();
      $targetTicketId = $targetTicket->getID();

      $requesterActor->getFromDBByCrit([
         'AND' => [
            'plugin_formcreator_targettickets_id' => $targetTicketId,
            'actor_role'                          => 'requester',
            'actor_type'                          => 'creator'
         ]
      ]);
      $observerActor->getFromDBByCrit([
         'AND' => [
            'plugin_formcreator_targettickets_id' => $targetTicketId,
            'actor_role'                          => 'observer',
            'actor_type'                          => 'validator'
         ]
      ]);

      $this->assertFalse($requesterActor->isNewItem());
      $this->assertFalse($observerActor->isNewItem());
      $this->assertEquals(1, $requesterActor->getField('use_notification'));
      $this->assertEquals(1, $observerActor->getField('use_notification'));
   }
}