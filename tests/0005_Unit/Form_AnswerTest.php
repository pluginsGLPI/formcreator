<?php
class Form_AnswerTest extends SuperAdminTestCase {

   public function addUpdateFormProvider() {
      return [
         [
            'input' => [
               'name'         => 'être ou ne pas être',
               'description'  => 'être ou ne pas être',
               'content'      => '&lt;p&gt;être ou ne pas être&lt;/p&gt;',
            ],
            'expected' => true,
         ],
         [
            'input' => [
               'name'         => 'test d\\\'apostrophe',
               'description'  => 'test d\\\'apostrophe',
               'content'      => '&lt;p&gt;test d\\\'apostrophe&lt;/p&gt;',
            ],
            'expected' => true,
         ],
      ];
   }

   /**
    * @dataProvider addUpdateFormProvider
    * @param array $input
    */
   public function testPrepareInputForAdd($input) {
      $form = new PluginFormcreatorForm();
      $form->add($input);

      $formAnswer = new PluginFormcreatorForm_Answer();
      $output = $formAnswer->prepareInputForAdd([
         $form::getForeignKeyField() => $form->getID(),
      ]);
      $this->assertEquals($input['name'], $output['name']);
   }

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
