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

class PluginFormcreatorQuestionDependency extends CommonTestCase {
   public function testGetParameterFormSize() {
      $question = $this->getQuestion();
      $fieldType = 'text';
      $instance = $this->newTestedInstance(
         \PluginFormcreatorFields::getFieldInstance($fieldType, $question),
         [
            'fieldName' => '',
            'label' => 'dependency',
         ]
      );
      $output = $instance->getParameterFormSize();
      $this->integer($output)->isEqualTo(0);
   }

   public function testPost_getEmpty() {
      $question = $this->getQuestion();
      $fieldType = 'text';
      $instance = $this->newTestedInstance(
         \PluginFormcreatorFields::getFieldInstance($fieldType, $question),
         [
            'fieldName' => '',
            'label' => 'dependency',
         ]
      );
      $instance->post_getEmpty();
      $this->array($instance->fields)
         ->hasKeys([
            'plugin_formcreator_questions_id_2',
         ])
         ->hasSize(1);

      $this->integer((int) $instance->fields['plugin_formcreator_questions_id_2'])->isEqualTo(0);
   }

   public function testExport() {

   }

   public function testImport() {
      require_once(__DIR__ . '/../fixture/PluginFormcreatorDependentField.php');
      $question = $this->getQuestion([
         'fieldtype' => 'dependent', // A fictional field type for unit tests
         '_parameters' => [
            'dependent' => [
               'firstname' => [
                  'plugin_formcreator_questions_id_1' => '1',
               ],
               'lastname' => [
                  'plugin_formcreator_questions_id_2' => '2',
               ],
            ]
         ]
      ]);
      $form = new \PluginFormcreatorForm();
      $form->getFromDBByQuestion($question);
      $question2 = $this->getQuestion([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      $input = [
         'plugin_formcreator_questions_id_2' => $question2->fields['uuid'],
         'fieldname' => 'firstname',
         'uuid' => plugin_formcreator_getUuid(),
      ];

      $linker = new \PluginFormcreatorLinker();
      $linker->addObject($question2->fields['uuid'], $question2);
      $parameterId = \PluginFormcreatorQuestionDependency::import($linker, $input, $question->getID());
      $this->integer($parameterId)->isGreaterThan(0);

      unset($input['uuid']);

      $this->exception(
         function() use($linker, $input) {
            \PluginFormcreatorQuestionDependency::import($linker, $input);
         }
      )->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ImportFailureException::class)
      ->hasMessage('UUID or ID is mandatory for Question dependency');

      $linker = new \PluginFormcreatorLinker();
      $linker->addObject($question2->getID(), $question2);
      $input['id'] = $parameterId;
      $input['plugin_formcreator_questions_id_2'] = $question2->getID();
      $parameterId2 = \PluginFormcreatorQuestionDependency::import($linker, $input, $question->getID());
      $this->variable($parameterId2)->isNotFalse();
      $this->integer((int) $parameterId)->isNotEqualTo($parameterId2);
   }

   public function isEditableField() {
      return true;
   }

   public function isVisibleField() {
      return true;
   }
}
