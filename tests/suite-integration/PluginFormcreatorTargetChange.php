<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorTargetChange extends CommonTestCase {

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);

      $this->login('glpi', 'glpi');
   }

   public function testTargetTicketActors() {
      $form = new \PluginFormcreatorForm();
      $form->add([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => 'a form',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $target = new \PluginFormcreatorTarget();
      $target->add([
         'name'                        => 'a target',
         'itemtype'                    => \PluginFormcreatorTargetChange::class,
         'plugin_formcreator_forms_id' => $form->getID()
      ]);
      $this->boolean($target->isNewItem())->isFalse();
      $this->integer((int) $target->getField('plugin_formcreator_forms_id'))
         ->isEqualTo((int) $form->getID());
      $this->string($target->getField('itemtype'))
         ->isEqualTo(\PluginFormcreatorTargetChange::class);

      $targetChange = $target->getField('items_id');
      $targetChange = new \PluginFormcreatorTargetChange();
      $targetChange->getFromDB($target->getField('items_id'));
      $this->boolean($targetChange->isNewItem())->isFalse();
      $this->string($targetChange->getField('name'))
         ->isEqualTo($target->getField('name'));

      $requesterActor = new \PluginFormcreatorTargetChange_Actor();
      $observerActor = new \PluginFormcreatorTargetChange_Actor();
      $targetChangeId = $targetChange->getID();

      $requesterActor->getFromDBByCrit([
         'AND' => [
            'plugin_formcreator_targetchanges_id' => $targetChangeId,
            'actor_role' => 'requester',
            'actor_type' => 'creator'
         ]
      ]);
      $observerActor->getFromDBByCrit([
            'AND' => [
               'plugin_formcreator_targetchanges_id' => $targetChangeId,
               'actor_role' => 'observer',
               'actor_type' => 'validator'
            ]
      ]);

      $this->boolean($requesterActor->isNewItem())->isFalse();
      $this->boolean($observerActor->isNewItem())->isFalse();
      $this->integer((int) $requesterActor->getField('use_notification'))->isEqualTo(1);
      $this->integer((int) $observerActor->getField('use_notification'))->isEqualTo(1);
   }
}