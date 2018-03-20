<?php
class TargetChangeTest extends SuperAdminTestCase {

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
            'itemtype'              => 'PluginFormcreatorTargetChange'
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
      $this->assertEquals('PluginFormcreatorTargetChange', $target->getField('itemtype'));

      return $target;
   }

   /**
    * @depends testInitCreateForm
    * @depends testCreateTarget
    * @param PluginFormcreatorForm $form
    * @param PluginFormcreatorTarget $target
    */
   public function testTargetChange(PluginFormcreatorForm $form, PluginFormcreatorTarget $target) {
      $targetChange = $target->getField('items_id');
      $targetChange = new PluginFormcreatorTargetChange();
      $targetChange->getFromDB($target->getField('items_id'));
      $this->assertFalse($targetChange->isNewItem());
      $this->assertEquals($target->getField('name'), $targetChange->getField('name'));

      return $targetChange;
   }

   /**
    * @depends testTargetChange
    * @param PluginFormcreatorTargetTicket $target
    */
   public function testTargetTicketActors(PluginFormcreatorTargetChange $targetChange) {
      $requesterActor = new PluginFormcreatorTargetChange_Actor();
      $observerActor = new PluginFormcreatorTargetChange_Actor();
      $targetChangeId = $targetChange->getID();

      $requesterActor->getFromDBByCrit([
         'AND' => [
            'plugin_formcreator_targetchanges_id' => $targetChangeId,
            'actor_role'                          => 'requester',
            'actor_type'                          => 'creator'
         ]
      ]);
      $observerActor->getFromDBByCrit([
         'AND' => [
            'plugin_formcreator_targetchanges_id' => $targetChangeId,
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