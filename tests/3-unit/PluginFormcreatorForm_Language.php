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
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;
use GlpiPlugin\Formcreator\Tests\CommonItemtypeTestCase;
use GlpiPlugin\Formcreator\Tests\CommonItemtypeInterface;

class PluginFormcreatorForm_language extends CommonTestCase
/* implements CommonItemtypeInterface */ {
   public function providerGetTypeName() {
      return [
         [
            0,
            'Form languages'
         ],
         [
            1,
            'Form language'
         ],
         [
            2,
            'Form languages'
         ],
      ];
   }

   public function testDefineTabs() {
      $instance = $this->newTestedInstance();
      $output = $instance->defineTabs();
      $expected = [
         'PluginFormcreatorForm_Language$main' => "Form language",
         'PluginFormcreatorForm_Language$1' => 'Translations',

      ];
      $this->array($output)
         ->isEqualTo($expected)
         ->hasSize(count($expected));
   }

   /**
    * @dataProvider providerGetTypeName
    *
    * @param integer $nb
    * @param string $expected
    * @return void
    */
   public function testGetTypeName($nb, $expected) {
      $instance = $this->newTestedInstance();
      $output = $instance->getTypeName($nb);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerPrepareInputForAdd() {
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      return [
         [
            'input' => [
               $formFk => 42
            ],
            'expected' => false,
            'expectedMessage' => 'The name cannot be empty!',
         ],
         [
            'input' => [
               'name' => 'foo',
               'comment' => 'bar',
            ],
            'expected' => false,
            'expectedMessage' => 'The language must be associated to a form!',
         ],
         [
            'input' => [
               'name' => 'foo',
               'comment' => 'bar',
               $formFk => 42,
            ],
            'expected' => [
               'name' => 'foo',
               'comment' => 'bar',
               $formFk => 42,
            ],
            'expectedMessage' => '',
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareInputForAdd
    *
    * @param array $input
    * @param mixed $expected
    * @param string $expectedMessage
    * @return void
    */
   public function testPrepareInputrForAdd($input, $expected, $expectedMessage) {
      $instance = $this->newTestedInstance();
      $output = $instance->prepareInputForAdd($input);
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      if ($expected === false) {
         $this->array($output)->size->isEqualTo(0);
         $this->sessionHasMessage($expectedMessage, ERROR);
      } else {
         $this->string($output['name'])->isEqualTo($input['name']);
         $this->string($output['comment'])->isEqualTo($output['comment']);
         $this->integer((int) $output[$formFk])->isEqualTo($output[$formFk]);
         $this->array($output)->hasKey('uuid');
      }
   }

   public function providerPrepareInputForUpdate() {
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      return [
         [
            'input' => [
               $formFk => 42
            ],
            'expected' => [
            ],
            'expectedMessage' => '',
         ],
         [
            'input' => [
               'name' => 'foo',
               'comment' => 'bar',
            ],
            'expected' => [],
            'expectedMessage' => '',
         ],
         [
            'input' => [
               'name' => 'foo',
               'comment' => 'bar',
               $formFk => 42,
            ],
            'expected' => [],
            'expectedMessage' => '',
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareInputForUpdate
    *
    * @param array $input
    * @param array $expected
    * @return void
    */
   public function testPrepareInputrForUpdate($input, $expected, $expectedMessage) {
      $instance = $this->newTestedInstance();
      $output = $instance->prepareInputForUpdate($input);
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      if ($expected === false) {
         $this->array($output)->size->isEqualTo(0);
         $this->sessionHasMessage($expectedMessage, ERROR);
      } else {
         $this->array($output)->notHasKeys(['name', $formFk]);
         $this->array($output)->hasKey('uuid');
      }
   }

   public function testExport() {
      $question = $this->getQuestion();
      $this->boolean($question->isNewItem())->isFalse();
      $form = new \PluginFormcreatorForm();
      $form->getFromDBByQuestion($question);
      $this->boolean($form->isNewItem())->isFalse();

      $instance = $this->newTestedInstance();
      $instance->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'comment' => 'foo',
         'name' => 'en_US',
      ]);

      // Find a string to translate
      $strings = $form->getTranslatableStrings([
         'language' => $instance->fields['name'],
      ]);
      reset($strings['itemlink']);
      $stringId = key($strings['itemlink']);
      $stringValue = $strings['itemlink'][$stringId];

      // Translate the found string
      $translation = new \PluginFormcreatorTranslation();
      $translation->add([
         'plugin_formcreator_forms_languages_id' => $instance->getID(),
         'plugin_formcreator_forms_id' => $form->getID(),
         'id' => $stringId,
         'value' => "$stringValue translated"
      ]);

      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
         'name',
         'comment',
      ];
      $extraFields = [
         '_strings',
      ];
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['uuid'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));

      $output = $instance->export(true);
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['id'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));
   }

   public function testImport() {
      $form = $this->getForm();
      $uuid = plugin_formcreator_getUuid();
      $input = [
         'name' => 'en_US',
         'comment' => 'foo',
         'uuid' => $uuid,
      ];
      $linker = new \PluginFormcreatorLinker();
      $formLanguageId = \PluginFormcreatorForm_Language::import($linker, $input, $form->getID());
      $this->integer($formLanguageId)->isGreaterThan(0);

      unset($input['uuid']);

      $this->exception(
         function() use($linker, $input, $form) {
            \PluginFormcreatorForm_Language::import($linker, $input, $form->getID());
         }
      )->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ImportFailureException::class)
      ->hasMessage('UUID or ID is mandatory for Form language'); // passes

      $input['id'] = $formLanguageId;
      $formLanguageId1 = \PluginFormcreatorForm_Language::import($linker, $input, $form->getID());
      $this->variable($formLanguageId1)->isNotFalse();
      $this->integer((int) $formLanguageId)->isNotEqualTo($formLanguageId1);
   }

   public  function testCountItemsToImport() {
      $instance = $this->newTestedInstance();
      $output = $instance->countItemsToImport([]);
      $this->integer($output)->isEqualTo(1);
   }
}