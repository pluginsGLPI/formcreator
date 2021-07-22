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

class PluginFormcreatorSection extends CommonTestCase {
   public function setup() {
      // instanciate classes
      $form           = new \PluginFormcreatorForm;
      $form_section   = new \PluginFormcreatorSection;
      $form_question  = new \PluginFormcreatorQuestion;

      // create objects
      $forms_id = $form->add([
         'name'                => "test clone form",
         'is_active'           => true,
         'validation_required' => \PluginFormcreatorForm_Validator::VALIDATION_USER
      ]);

      $sections_id = $form_section->add([
         'name'                        => "test clone section",
         'plugin_formcreator_forms_id' => $forms_id
      ]);

      $form_question->add([
         'name'                           => "test clone question 1",
         'fieldtype'                      => 'text',
         'plugin_formcreator_sections_id' => $sections_id
      ]);
      $form_question->add([
         'name'                           => "test clone question 2",
         'fieldtype'                      => 'textarea',
         'plugin_formcreator_sections_id' => $sections_id
      ]);
   }

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testImport':
         case 'testGetTranslatableStrings':
            $this->login('glpi', 'glpi');
      }
   }

   /**
    * @cover PluginFormcreatorSection::clone
    */
   public function testDuplicate() {
      global $DB;

      // instanciate classes
      $form      = new \PluginFormcreatorForm;
      $section   = new \PluginFormcreatorSection;
      $question  = new \PluginFormcreatorQuestion;

      // create objects
      $forms_id = $form->add(['name'                => "test clone form",
                                   'is_active'           => true,
                                   'validation_required' => \PluginFormcreatorForm_Validator::VALIDATION_USER]);
      $sections_id = $section->add(['name'                        => "test clone section",
                                         'plugin_formcreator_forms_id' => $forms_id]);
      $question->add(['name'                           => "test clone question 1",
                                             'fieldtype'                      => 'text',
                                             'plugin_formcreator_sections_id' => $sections_id]);
      $question->add(['name'                           => "test clone question 2",
                                             'fieldtype'                      => 'textarea',
                                             'plugin_formcreator_sections_id' => $sections_id]);

      //get section
      $section->getFromDB($sections_id);

      //clone it
      $newSection_id = $section->duplicate();
      $this->integer($newSection_id)->isGreaterThan(0);

      //get cloned section
      $new_section = new \PluginFormcreatorSection;
      $new_section->getFromDB($newSection_id);

      // check uuid
      $this->string($new_section->getField('uuid'))->isNotEqualTo($section->getField('uuid'));

      // check questions
      $all_questions = $DB->request([
         'SELECT' => ['uuid'],
         'FROM'   => \PluginFormcreatorQuestion::getTable(),
         'WHERE'  => [
            'plugin_formcreator_sections_id' => $section->getID()
         ]
      ]);
      $all_new_questions = $DB->request([
         'SELECT' => ['uuid'],
         'FROM'   => \PluginFormcreatorQuestion::getTable(),
         'WHERE'  => [
            'plugin_formcreator_sections_id' => $new_section->getID()
         ]
      ]);
      $this->integer(count($all_new_questions))->isEqualTo(count($all_questions));

      // check that all question uuid are new
      $uuids = $new_uuids = [];
      foreach ($all_questions as $question) {
         $uuids[] = $question['uuid'];
      }
      foreach ($all_new_questions as $question) {
         $new_uuids[] = $question['uuid'];
      }
      $this->integer(count(array_diff($new_uuids, $uuids)))->isEqualTo(count($new_uuids));
   }

   public function testExport() {
      $instance = $this->newTestedInstance();

      // Try to export an empty item
      $this->exception(function () use ($instance) {
         $instance->export();
      })->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ExportFailureException::class);

      // Prepare an item to export
      $instance = $this->getSection();
      $instance->getFromDB($instance->getID());

      // Export the item without the ID and with UUID
      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
         'name',
         'order',
         'show_rule',
      ];
      $extraFields = [
         '_questions',
         '_conditions',
      ];
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['uuid'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));

      // Export the item without the UUID and with ID
      $output = $instance->export(true);
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['id'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));
   }

   public function testImport() {
      $form = $this->getForm();
      $uuid = plugin_formcreator_getUuid();
      $input = [
         'name'       => $this->getUniqueString(),
         'order'      => '1',
         'show_rule'  => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
         'uuid'       => $uuid,
      ];

      $linker = new \PluginFormcreatorLinker();
      $sectionId = \PluginFormcreatorSection::import($linker, $input, $form->getID());
      $this->integer($sectionId)->isGreaterThan(0);

      unset($input['uuid']);

      $this->exception(
         function() use($linker, $input, $form) {
            \PluginFormcreatorSection::import($linker, $input, $form->getID());
         }
      )->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ImportFailureException::class)
      ->hasMessage('UUID or ID is mandatory for Section'); // passes

      $input['id'] = $sectionId;
      $sectionId2 = \PluginFormcreatorSection::import($linker, $input, $form->getID());
      $this->variable($sectionId2)->isNotFalse();
      $this->integer((int) $sectionId)->isNotEqualTo($sectionId2);
   }

   public function testMoveUp() {
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      $form = $this->getForm();
      $section = $this->getSection(
         [
            $formFk => $form->getID(),
         ]
      );
      $sectionToMove = $this->getSection(
         [
            $formFk => $form->getID(),
         ]
      );

      // Move up the section
      $expectedOrder = $sectionToMove->fields['order'] - 1;
      $sectionToMove->moveUp();

      // Check the order of the section
      $this->integer((int) $sectionToMove->fields['order'])
         ->isEqualTo($expectedOrder);

      // check the order of the other section
      $expectedOrder = $section->fields['order'] + 1;
      $section->getFromDB($section->getID());
      $this->integer((int) $section->fields['order'])
         ->isEqualTo($expectedOrder);
   }

   public function testMoveDown() {
      $formFk = \PluginFormcreatorForm::getForeignKeyField();
      $form = $this->getForm();
      $sectionToMove = $this->getSection(
         [
            $formFk => $form->getID(),
         ]
      );
      $section = $this->getSection(
         [
            $formFk => $form->getID(),
         ]
      );

      // Move down the section
      $expectedOrder = $sectionToMove->fields['order'] + 1;
      $sectionToMove->moveDown();

      // Check the order of the section
      $this->integer((int) $sectionToMove->fields['order'])
         ->isEqualTo($expectedOrder);

      // check the order of the other section
      $expectedOrder = $section->fields['order'] - 1;
      $section->getFromDB($section->getID());
      $this->integer((int) $section->fields['order'])
         ->isEqualTo($expectedOrder);
   }

   public function testIsEmptyRow() {
      $section = $this->getSection();
      $sectionFk = \PluginFormcreatorSection::getForeignKeyField();
      [
         0 => $this->getQuestion([
            $sectionFk => $section->getID(),
            'row' => 0,
         ]),
         1 => $this->getQuestion([
            $sectionFk => $section->getID(),
            'row' => 2,
         ]),
         2 => $this->getQuestion([
            $sectionFk => $section->getID(),
            'row' => 4,
         ]),
      ];

      $this->boolean($section->isRowEmpty(0))->isFalse();
      $this->boolean($section->isRowEmpty(1))->isFalse();
      $this->boolean($section->isRowEmpty(2))->isFalse();
      $this->boolean($section->isRowEmpty(3))->isTrue();
      $this->boolean($section->isRowEmpty(4))->isTrue();
      $this->boolean($section->isRowEmpty(5))->isTrue();
   }

   public function testGetTranslatableStrings() {
      $data = file_get_contents(dirname(__DIR__) . '/fixture/all_question_types_form.json');
      $data = json_decode($data, true);
      foreach ($data['forms'] as $formData) {
         $form = new \PluginFormcreatorForm();
         $formId = $form->import(new \PluginFormcreatorLinker(), $formData);
         $this->boolean($form->isNewID($formId))->isFalse();
      }

      $form->getFromDB($formId);
      $this->boolean($form->isNewItem())->isFalse();
      $section = $this->newTestedInstance();
      $section->getFromDBByCrit([
         'plugin_formcreator_forms_id' => $formId,
         'name' => 'section',
      ]);
      $this->boolean($section->isNewItem())->isFalse();
      $output = $section->getTranslatableStrings();
      $this->array($output)->isIdenticalTo([
         'itemlink' =>
         [
           '73d5342eba070f636ac3246f319bf77f' => 'section',
           '8c647f55ac463429f736aea1ad64d318' => 'actors question',
           'de1ece2a98dacb86a2b65334373ccb99' => 'checkboxes question',
           'e121a8d9e19bf923a648d6bfb33094d8' => 'date question',
           '7d3246feb9616461eee152642ad9f1fb' => 'datetime question',
           '824d1cc309c56586a33b52858cbc146b' => 'description question',
           '8347ce048fc3fe8b954dbc6cd9c4b716' => 'dropdown question',
           '895472a7be51fe6b1b9591a150fb55d8' => 'email question',
           '75c4f52e98ebd4a57410d882780353db' => 'file question',
           '037cad549bb834c2fab44fe14480f9a9' => 'float question',
           '97ee07194ba5af1c81eb5a9b22141241' => 'GLPI object question',
           '74b8be9aff59bf5ddd149248d6156baa' => 'hidden question',
           '0550a71495224d60dfcd00826345f0fa' => 'hostname question',
           'd767bdc805e010bfd2302c2516501ffb' => 'IP address question',
           'b5c09bbe5587577a8c86ada678664877' => 'integer question',
           '5b3ebb576a3977eaa267f0769bdd8e98' => 'LDAP question',
           '35226e073fabdcce01c547c5bce62d14' => 'multiselect question',
           '58e2a2355ba7ac135d42f558591d6a6a' => 'radio question',
           '2637b4d11281dffbaa2e340561347ebc' => 'request type question',
           '212afc3240debecf859880ea9ab4fc2e' => 'select question',
           '6fd6eacf3005974a7489a199ed7b45ee' => 'text question',
           'b99b0833f1dab41a14eb421fa2ce690d' => 'textarea question',
           'e3a0dfbc9d24603beddcbd1388808a7a' => 'time question',
           '49dce550d75300e99052ed4e8006b65a' => 'urgency question',
         ],
         'string' =>
         [
           'bc41fd6c06a851dc3e5f52ef82c46357' => 'a (checkbox)',
           '2e2682dc7fe28972eede52a085f9b8da' => 'b (checkbox)',
           'a212352098d74d20ad0869e8b11870dd' => 'c (checkbox)',
           '2ee11338e1d5571cdcdc959e05d13fdd' => 'hidden value',
           '26b6a3b22c4a9eacd9bcca663c6bfb98' => 'a (multiselect)',
           'fe3ba23b6c304bcfccab1c4037170043' => 'b (multiselect)',
           '76abd40f08cc003cfb75e02d8603a618' => 'c (multiselect)',
           'aa08e69f50f9d7e4a280b5e395a926f3' => 'a (radio)',
           '3d8f74862a3f325c160d5b4090cc1344' => 'b (radio)',
           '60459f8c72beb121493ec56bd0b41473' => 'c (radio)',
           '3e6b3c27f45682bbe11ed102ff9cbd31' => 'a (select)',
           '12f59df90d7b53129d8e6da91f60cf86' => 'b (select)',
           '1dd65ffc0516477159ec9ba8c170ef94' => 'c (select)',
           '4f87be8f6e593d167f5fd1ab238cfc2d' => '/foo/',
         ],
         'text' =>
         [
           '06ff4080ef6f9ee755cc45cba5f80360' => 'actors description',
           '874e42442b551ef2769cc498157f542d' => 'checkboxes description',
           '42be0556a01c9e0a28da37d2e3c5153d' => 'date description',
           'b698fbcd4b9acf232b8b88755a1728f0' => 'datetime description',
           'ab87cc96356a7d5c1d37c877fd56c6b0' => 'description text',
           '59ef614a194389f0b54e46b728fe22a2' => 'dropdown description',
           'b70e872f17f616049c642f2db8f35c8a' => 'email description',
           '2b4f8f08c4162a2dac4a9b82e97605c0' => 'file description',
           'b1a3d83a831e20619e1f14f6dbc64105' => 'float description',
           '54ee213f0c0aae084d5712dc96bac833' => 'GLPI object description',
           '91ca037d3ec611f6c684114abce7296f' => 'hidden description',
           '98443bed844ba97392d8a8fb364b5d66' => 'hostname description',
           '4b2e461a0b3c307923176188fb6273c6' => 'IP address description',
           '51d8d951cf91a008f5b87c7d36ee6789' => 'integer description',
           'c0117d3ded05c5c672425a48a63c83d7' => 'LDAP description',
           '2d0b83793d10440b70c33a2229c88a09' => 'multiselect description',
           '06cdb33e33e576a973d7bf54fcded96e' => 'radios description',
           '471217363e6922ff6b1c9fd9cd57cd2a' => 'request type description',
           '64dfbbc489b074af269e0b0fbf0d901b' => 'select description',
           'b371eae37f18f0b6125002999b2404ba' => 'text description',
           'f81bad6b9c8f01a40099a140881313a8' => 'textarea description',
           '8d544ed7c846a47654b2f55db879d7b2' => 'time description',
           'e634ce2f4abe0deaa3f7cd44e13f4af6' => 'urgency description',
         ],
         'id' =>
         [
           '73d5342eba070f636ac3246f319bf77f' => 'itemlink',
           '8c647f55ac463429f736aea1ad64d318' => 'itemlink',
           '06ff4080ef6f9ee755cc45cba5f80360' => 'text',
           'de1ece2a98dacb86a2b65334373ccb99' => 'itemlink',
           '874e42442b551ef2769cc498157f542d' => 'text',
           'bc41fd6c06a851dc3e5f52ef82c46357' => 'string',
           '2e2682dc7fe28972eede52a085f9b8da' => 'string',
           'a212352098d74d20ad0869e8b11870dd' => 'string',
           'e121a8d9e19bf923a648d6bfb33094d8' => 'itemlink',
           '42be0556a01c9e0a28da37d2e3c5153d' => 'text',
           '7d3246feb9616461eee152642ad9f1fb' => 'itemlink',
           'b698fbcd4b9acf232b8b88755a1728f0' => 'text',
           '824d1cc309c56586a33b52858cbc146b' => 'itemlink',
           'ab87cc96356a7d5c1d37c877fd56c6b0' => 'text',
           '8347ce048fc3fe8b954dbc6cd9c4b716' => 'itemlink',
           '59ef614a194389f0b54e46b728fe22a2' => 'text',
           '895472a7be51fe6b1b9591a150fb55d8' => 'itemlink',
           'b70e872f17f616049c642f2db8f35c8a' => 'text',
           '75c4f52e98ebd4a57410d882780353db' => 'itemlink',
           '2b4f8f08c4162a2dac4a9b82e97605c0' => 'text',
           '037cad549bb834c2fab44fe14480f9a9' => 'itemlink',
           'b1a3d83a831e20619e1f14f6dbc64105' => 'text',
           '97ee07194ba5af1c81eb5a9b22141241' => 'itemlink',
           '54ee213f0c0aae084d5712dc96bac833' => 'text',
           '74b8be9aff59bf5ddd149248d6156baa' => 'itemlink',
           '91ca037d3ec611f6c684114abce7296f' => 'text',
           '2ee11338e1d5571cdcdc959e05d13fdd' => 'string',
           '0550a71495224d60dfcd00826345f0fa' => 'itemlink',
           '98443bed844ba97392d8a8fb364b5d66' => 'text',
           'd767bdc805e010bfd2302c2516501ffb' => 'itemlink',
           '4b2e461a0b3c307923176188fb6273c6' => 'text',
           'b5c09bbe5587577a8c86ada678664877' => 'itemlink',
           '51d8d951cf91a008f5b87c7d36ee6789' => 'text',
           '5b3ebb576a3977eaa267f0769bdd8e98' => 'itemlink',
           'c0117d3ded05c5c672425a48a63c83d7' => 'text',
           '35226e073fabdcce01c547c5bce62d14' => 'itemlink',
           '2d0b83793d10440b70c33a2229c88a09' => 'text',
           '26b6a3b22c4a9eacd9bcca663c6bfb98' => 'string',
           'fe3ba23b6c304bcfccab1c4037170043' => 'string',
           '76abd40f08cc003cfb75e02d8603a618' => 'string',
           '58e2a2355ba7ac135d42f558591d6a6a' => 'itemlink',
           '06cdb33e33e576a973d7bf54fcded96e' => 'text',
           'aa08e69f50f9d7e4a280b5e395a926f3' => 'string',
           '3d8f74862a3f325c160d5b4090cc1344' => 'string',
           '60459f8c72beb121493ec56bd0b41473' => 'string',
           '2637b4d11281dffbaa2e340561347ebc' => 'itemlink',
           '471217363e6922ff6b1c9fd9cd57cd2a' => 'text',
           '212afc3240debecf859880ea9ab4fc2e' => 'itemlink',
           '64dfbbc489b074af269e0b0fbf0d901b' => 'text',
           '3e6b3c27f45682bbe11ed102ff9cbd31' => 'string',
           '12f59df90d7b53129d8e6da91f60cf86' => 'string',
           '1dd65ffc0516477159ec9ba8c170ef94' => 'string',
           '6fd6eacf3005974a7489a199ed7b45ee' => 'itemlink',
           'b371eae37f18f0b6125002999b2404ba' => 'text',
           'b99b0833f1dab41a14eb421fa2ce690d' => 'itemlink',
           'f81bad6b9c8f01a40099a140881313a8' => 'text',
           '4f87be8f6e593d167f5fd1ab238cfc2d' => 'string',
           'e3a0dfbc9d24603beddcbd1388808a7a' => 'itemlink',
           '8d544ed7c846a47654b2f55db879d7b2' => 'text',
           '49dce550d75300e99052ed4e8006b65a' => 'itemlink',
           'e634ce2f4abe0deaa3f7cd44e13f4af6' => 'text',
         ],
      ]);
   }
}
