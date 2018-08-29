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
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

/**
 * The the methods conflict when running in parallel
 * @engine inline
 */
class PluginFormcreatorForm_Answer extends CommonTestCase {

   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testNotificationFormAnswerCreated':
         case 'testOtherUserValidates':
            $this->boolean(self::login('glpi', 'glpi', true))->isTrue();
            break;
      }
   }

   public function afterTestMethod($method) {
      parent::afterTestMethod($method);
      switch ($method) {
         case 'testNotificationFormAnswerCreated':
            $user = new \User();
            $user->getFromDBbyName('glpi');
            $userEmail = new \Useremail();
            $userEmail->deleteByCriteria([
               'users_id' => $user->getID(),
            ]);
            break;
      }
   }

   public function providerPrepareInputForAdd() {
      return [
         [
            'input' => [
               'name'         => 'être ou ne pas être',
            ],
         ],
         [
            'input' => [
               'name'         => 'test d\\\'apostrophe',
            ],
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareInputForAdd
    * @param array $input
    */
   /*
   public function testPrepareInputForAdd($input) {
      $form = new \PluginFormcreatorForm();
      $form->add($input);

      $formAnswer = new \PluginFormcreatorForm_Answer();
      $output = $formAnswer->prepareInputForAdd([
         $form::getForeignKeyField() => $form->getID(),
      ]);
      $this->string($output['name'])->isEqualTo($input['name']);
   }
   */

   public function testNotificationFormAnswerCreated() {
      global $DB, $CFG_GLPI;

      $user = new \User();
      $user->getFromDBbyName('glpi');
      $user->update([
         'id' => $user->getID(),
         '_useremails' => [$this->getUniqueEmail()],
      ]);

      \config::setConfigurationValues(
         'core',
         [
            'use_notifications'     => '1',
            'notifications_mailing' => '1'
         ]
      );
      $CFG_GLPI['use_notifications'] = '1';
      $CFG_GLPI['notifications_mailing'] = '1';

      $form = new \PluginFormcreatorForm();
      $form->add([
         'name'                  => __METHOD__,
         'validation_required'   => '0'
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      // Answer the form
      $form->saveForm(['formcreator_form' => $form->getID()]);

      // Check a notification was created with the expected template
      $result = $DB->request([
         'SELECT' => \Notification_NotificationTemplate::getTable() . '.' . \NotificationTemplate::getForeignKeyField(),
         'FROM' => \Notification_NotificationTemplate::getTable(),
         'INNER JOIN' => [
            \Notification::getTable() => [
               'FKEY' => [
                  \Notification::getTable() => 'id',
                  \Notification_NotificationTemplate::getTable() => \Notification::getForeignKeyField()
               ]
            ]
         ],
         'WHERE' => [
            'itemtype'  => \PluginFormcreatorForm_Answer::class,
            'event'     => 'plugin_formcreator_form_created',
         ]
      ]);
      $this->integer($result->count())->isEqualTo(1);
      $row = $result->next();

      $formAnswer = new \PluginFormcreatorForm_Answer();
      $formAnswer->getFromDBByCrit([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $this->boolean($formAnswer->isNewItem())->isFalse();
      $queued = new \QueuedNotification();
      $queued->getFromDBByCrit([
         \NotificationTemplate::getForeignKeyField() => $row[\NotificationTemplate::getForeignKeyField()],
         'itemtype'  => \PluginFormcreatorForm_Answer::class,
         'items_id'  => $formAnswer->getID(),
      ]);

      // Check the notification is linked to the expected itemtype
      $this->boolean($queued->isNewItem())->isFalse();
   }

   public function testOtherUserValidates() {
      global $DB;

      $form = new \PluginFormcreatorForm();
      $form->add([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => __METHOD__,
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => \PluginFormcreatorForm_Validator::VALIDATION_USER,
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $section = new \PluginFormcreatorSection();
      $section->add([
         'name'                        => 'a section',
         'plugin_formcreator_forms_id' => $form->getID()
      ]);
      $this->boolean($section->isNewItem())->isFalse();

      $formValidator = new \PluginFormcreatorForm_Validator();
      $formValidator->add([
         'itemtype'              => 'User',
         'users_id'              => '2', // user is glpi
         'plugin_formcreator_forms_id' => $form->getID()
      ]);
      $this->boolean($formValidator->isNewItem())->isFalse();

      $formAnswer = new \PluginFormcreatorForm_Answer();
      $formAnswer_table = \PluginFormcreatorForm_Answer::getTable();

      $result = $DB->query("SELECT MAX(`id`) AS `max_id` FROM `$formAnswer_table`");
      $maxId = $DB->fetch_assoc($result);
      $maxId = $maxId['max_id'];
      $maxId === null ? 0 : $maxId;

      $form->saveForm([
         'formcreator_form'      => $form->getID(),
         'status'                => 'waiting',
         'formcreator_validator' => $_SESSION['glpiID'],
      ]);

      $result = $DB->query("SELECT MAX(`id`) AS `max_id` FROM `$formAnswer_table`");
      $newId = $DB->fetch_assoc($result);
      $newId = $newId['max_id'];

      $this->integer((int) $newId)->isGreaterThan((int) $maxId);
      $formAnswer->getFromDB($newId);
      $this->boolean($formAnswer->isNewItem())->isFalse();

      $login = $this->getUniqueString();
      $user = new \User();
      $user->add([
         'name'                  => $login,
         'password'              => 'superadmin',
         'password2'             => 'superadmin',
         '_profiles_id'          => '4',
         '_entities_id'          => 0,
         '_is_recursive'         => 1,
      ]);
      $this->boolean($user->isNewItem())
         ->isFalse(json_encode(
            $_SESSION['MESSAGE_AFTER_REDIRECT'],
            JSON_PRETTY_PRINT));

      // Login as other user
      $this->boolean(self::login($login, 'superadmin', true))->isTrue();

      $this->boolean($formAnswer->canValidate($form, $formAnswer))->isFalse();

      // Login as glpi
      $this->boolean(self::login('glpi', 'glpi', true))->istrue();

      $this->boolean($formAnswer->canValidate($form, $formAnswer))->isTrue();
   }
}