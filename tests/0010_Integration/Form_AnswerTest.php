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

class Form_AnswerTest extends SuperAdminTestCase {

   public function testNotificationFormAnswerCreated() {
      global $DB, $CFG_GLPI;

      $user = new User();
      $user->getFromDBbyName('glpi');
      $user->update([
         'id' => $user->getID(),
         '_useremails' => ['glpi@localhost.local'],
      ]);

      config::setConfigurationValues('core', ['notifications_mailing' => '1']);
      $CFG_GLPI['notifications_mailing'] = '1';

      $form = new PluginFormcreatorForm();
      $form->add([
         'name'                  => 'a form',
         'validation_required'   => '0'
      ]);
      $this->assertFalse($form->isNewItem());

      // Answer the form
      $form->saveForm(['formcreator_form' => $form->getID()]);

      // Check a notification was created with the expected template
      $result = $DB->request([
         'SELECT' => Notification_NotificationTemplate::getTable() . '.' . NotificationTemplate::getForeignKeyField(),
         'FROM' => Notification_NotificationTemplate::getTable(),
         'INNER JOIN' => [
            Notification::getTable() => [
               'FKEY' => [
                  Notification::getTable() => 'id',
                  Notification_NotificationTemplate::getTable() => Notification::getForeignKeyField()
               ]
            ]
         ],
         'WHERE' => [
            'itemtype'  => PluginFormcreatorForm_Answer::class,
            'event'     => 'plugin_formcreator_form_created',
         ]
      ]);
      $this->assertCount(1, $result);
      $row = $result->next();
      $queued = new QueuedNotification();
      $queued->getFromDBByCrit([NotificationTemplate::getForeignKeyField() => $row[NotificationTemplate::getForeignKeyField()]]);

      // Check the notification is linked to the expected itemtype
      $this->assertEquals($queued->getField('itemtype'), PluginFormcreatorForm_Answer::class);
   }
}