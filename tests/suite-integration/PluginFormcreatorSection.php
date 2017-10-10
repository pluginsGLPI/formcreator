<?php
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorSection extends CommonTestCase {

   private $form = null;

   private $sectionData = null;

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);

      switch ($method) {
         case 'testAdd':
         case 'testUpdate':
         case 'testDelete':
            $this->login('glpi', 'glpi');
            $this->form = new \PluginFormcreatorForm();
            $this->form->add([
               'entities_id'           => $_SESSION['glpiactive_entity'],
               'name'                  => $method . ' ' . $this->getUniqueString(),
               'description'           => 'form description',
               'content'               => 'a content',
               'is_active'             => 1,
               'validation_required'   => 0
            ]);
            break;
      }
   }

   public function testAdd() {
      $instance = new \PluginFormcreatorSection();
      $instance->add([
         'plugin_formcreator_forms_id' => $this->form->getID(),
         'name'                        => $this->getUniqueString()
      ]);
      $this->boolean($instance->isNewItem())->isFalse();
   }

   public function testUpdate() {
      $instance = new \PluginFormcreatorSection();
      $instance->add([
         'plugin_formcreator_forms_id' => $this->form->getID(),
         'name'                        => $this->getUniqueString()
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      $success = $instance->update([
            'id'     => $instance->getID(),
            'name'   => 'section renamed'
      ]);
      $this->boolean($success)->isTrue();
   }

   public function testDelete() {
      $instance = new \PluginFormcreatorSection();
      $instance->add([
         'plugin_formcreator_forms_id' => $this->form->getID(),
         'name'                        => $this->getUniqueString()
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      $success = $instance->delete([
            'id' => $instance->getID()
      ], 1);
      $this->boolean($success)->isTrue();
   }
}
