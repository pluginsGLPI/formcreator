<?php

namespace GlpiPlugin\Formcreator\Tests;
use PluginFormcreatorForm;
use PluginFormcreatorFormAnswer;
use PluginFormcreatorSection;

abstract class CommonTargetTestCase extends CommonTestCase
{
   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testXSS':
            $this->login('glpi', 'glpi');
            break;
      }
   }

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

   public function testXSS() {
      $question = $this->getQuestion([
         'fieldtype' => 'text',
      ]);
      $section = new PluginFormcreatorSection();
      $section->update([
            'id' => $question->fields['plugin_formcreator_sections_id'],
            'name' => 'section',
      ]);
      $form = PluginFormcreatorForm::getByItem($question);
      $testedClassName = $this->getTestedClassName();
      $target = new $testedClassName();
      $target->add([
         'name' => $this->getUniqueString(),
         'plugin_formcreator_forms_id' => $form->getID(),
         'target_name' => '##answer_' . $question->getID() . '##',
         'content'     => '##FULLFORM##',
      ]);
      $formAnswer = new PluginFormcreatorFormAnswer();
      $formAnswer->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'formcreator_field_' . $question->getID() => '"&#62;&#60;img src=x onerror="alert(1337)" x=x&#62;"',
      ]);
      $generated = $formAnswer->targetList[0] ?? null;
      $this->object($generated);
      $this->string($generated->fields['name'])
         ->isEqualTo('"&#62;&#60;img src=x onerror="alert(1337)" x=x&#62;"');
      $this->string($generated->fields['content'])
         ->isEqualTo('&#60;h1&#62;Form data&#60;/h1&#62;&#60;h2&#62;section&#60;/h2&#62;&#60;div&#62;&#60;b&#62;1) question : &#60;/b&#62;"&#38;#62;&#38;#60;img src=x onerror="alert(1337)" x=x&#38;#62;"&#60;/div&#62;');
   }
}
