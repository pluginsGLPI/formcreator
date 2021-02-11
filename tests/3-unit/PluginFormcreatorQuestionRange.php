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

class PluginFormcreatorQuestionRange extends CommonTestCase {

   public function testGetParameterFormSize() {
      $question = $this->getQuestion();
      $fieldType = 'text';
      $instance = $this->newTestedInstance(
         \PluginFormcreatorFields::getFieldInstance($fieldType, $question),
         [
            'fieldName' => '',
            'label' => 'range',
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
            'label' => 'range',
         ]
      );
      $instance->post_getEmpty();
      $this->array($instance->fields)
         ->hasKeys([
            'range_min',
            'range_max'
         ])
         ->hasSize(2);

      $this->integer((int) $instance->fields['range_min'])->isEqualTo(0);
      $this->integer((int) $instance->fields['range_max'])->isEqualTo(0);
   }

   public function testExport() {
      $question = $this->getQuestion();
      $fieldType = 'text';
      $instance = $this->newTestedInstance(
         \PluginFormcreatorFields::getFieldInstance($fieldType, $question),
         [
            'fieldName' => '',
            'label' => 'range',
         ]
      );

      // Try to export an empty item
      $this->exception(function () use ($instance) {
         $instance->export();
      })->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ExportFailureException::class);

      // Prepare an item to export
      $question->updateParameters([
         'fieldtype' => $fieldType,
         '_parameters' => [
            $fieldType => [
               'range' => [
                  'range_min' => '1',
                  'range_max' => '5',
               ],
            ]
         ]
      ]);
      $instance->getFromDBByCrit([
         'plugin_formcreator_questions_id' => $question->getID(),
         'fieldname' => 'range',
      ]);

      // Export the item without the ID and with UUID
      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
         'range_min',
         'range_max',
         'fieldname',
      ];
      $extraFields = [
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
      $question = $this->getQuestion();

      $input = [
         'range_min' => '1',
         'range_max' => '5',
         'fieldname' => 'range',
         'uuid' => plugin_formcreator_getUuid(),
      ];

      $linker = new \PluginFormcreatorLinker();
      $parameterId = \PluginFormcreatorQuestionRange::import($linker, $input, $question->getID());
      $this->integer($parameterId)->isGreaterThan(0);

      unset($input['uuid']);

      $this->exception(
         function() use($linker, $input) {
            \PluginFormcreatorQuestionRange::import($linker, $input);
         }
      )->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ImportFailureException::class)
      ->hasMessage('UUID or ID is mandatory for Question range');

      $input['id'] = $parameterId;
      $parameterId2 = \PluginFormcreatorQuestionRange::import($linker, $input, $question->getID());
      $this->variable($parameterId2)->isNotFalse();
      $this->integer((int) $parameterId2)->isNotEqualTo($parameterId);
   }
}