<?php
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
      $_POST['formcreator_form'] = $form->getID();
      $form->saveForm();
      unset($_POST);

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