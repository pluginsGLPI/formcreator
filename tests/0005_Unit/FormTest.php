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

class FormTest extends SuperAdminTestCase {

   protected $formData;

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
   }

   public function testCreateForm() {
      $form = new PluginFormcreatorForm();
      $formId = $form->add($this->formData);
      $this->assertFalse($form->isNewItem());

      return $form;
   }

   /**
    * @depends testCreateForm
    * @param PluginFormCreatorForm $form
    */
   public function testUpdateForm(PluginFormcreatorForm $form) {
      $success = $form->update(array(
         'id'                    => $form->getID(),
         'name'                  => 'an updated form',
         'validation_required'   => 0
      ));
      $this->assertTrue($success);

      return $form;
   }

   /**
    * @depends testUpdateForm
    * @param PluginFormCreatorForm $form
    */
   public function testPurgeForm(PluginFormcreatorForm $form) {
      $success = $form->delete(array(
         'id'              => $form->getID(),
      ), 1);
      $this->assertTrue($success);
   }

   public function testCreateValidationNotification() {
      global $CFG_GLPI;
      Config::setConfigurationValues(
         'core',
         ['use_notifications' => 1, 'notifications_mailing' => 1]
      );
      $CFG_GLPI['use_notifications'] = 1;
      $CFG_GLPI['notifications_mailing'] = 1;
      $user = new USer();
      $user->update([
         'id' => $_SESSION['glpiID'],
         '_useremails' => [
            'glpi@localhost.com',
         ]
      ]);
      $form = new PluginFormcreatorForm();
      $form->add([
         'name'                  => 'validation notification',
         'validation_required'   => PluginFormcreatorForm_Validator::VALIDATION_USER,
         '_validator_users'      => [$_SESSION['glpiID']],
      ]);
      $section = new PluginFormcreatorSection();
      $section->add([
         $form::getForeignKeyField() => $form->getID(),
         'name' => 'section',
      ]);

      $notification = new QueuedNotification();
      $notificationCount = count($notification->find());
      $formAnswer = new PluginFormcreatorForm_Answer();
      $formAnswer->saveAnswers([
         'formcreator_form'         => $form->getID(),
         'formcreator_validator'    => $_SESSION['glpiID'],
      ]);
      // 1 notification to the validator
      // 1 notification to the requester
      $this->assertCount($notificationCount + 2, $notification->find());
   }
}
