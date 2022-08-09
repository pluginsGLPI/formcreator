<?php

namespace GlpiPlugin\Formcreator\Tests;
use PluginFormcreatorForm;

abstract class CommonTargetTestCase extends CommonTestCase
{
   /**
    * Test handling of uuid when adding an item
    */
   public function testPrepareInputForAdd_uuid() {
       $form = $this->getForm();

       // Test uuid creation
       $instance = $this->newTestedInstance();
       $input = [
          'name' => 'foo',
          PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
       ];
       $output = $instance->prepareInputForAdd($input);
       $this->string($output['uuid'])->isNotEmpty();

       // Test uuid is used when provided
       $instance = $this->newTestedInstance();
       $input = [
          'name' => 'foo',
          PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
          'uuid' => 'bar',
       ];
       $output = $instance->prepareInputForAdd($input);
       $this->string($output['uuid'])->isEqualTo('bar');
   }

    /**
     * Test handling of uuid when updating an itep
    */
   public function testPrepareInputForUpdate_uuid() {
       $form = $this->getForm();
       $instance = $this->newTestedInstance();
       $input = [
           'name' => 'foo',
           PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
       ];
       $instance->add($input);

       // Check uuid is not changed when not specified
       $input = [];
       $output = $instance->prepareInputForUpdate($input);
       $this->array($output)->notHasKey('uuid');

       // Check uuid is changed when specified
       $input = [
           'uuid' => 'foo',
       ];
       $output = $instance->prepareInputForUpdate($input);
       $this->array($output)->HasKey('uuid');
       $this->string($output['uuid'])->isEqualTo('foo');
   }
}