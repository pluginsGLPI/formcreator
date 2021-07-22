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
class PluginFormcreatorCondition extends CommonTestCase {
   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);

      self::login('glpi', 'glpi');
   }

   public function testGetEnumShowLogic() {
      $output = \PluginFormcreatorCondition::getEnumShowLogic();
      $this->array($output)
         ->isIdenticalTo([
            '1' => 'AND',
            '2' => 'OR'
         ]);
   }

   public function testGetEnumShowCondition() {
      $output = \PluginFormcreatorCondition::getEnumShowCondition();
      $this->array($output)
         ->isIdenticalTo([
            '1' => '=',
            '2' => '≠',
            '3' => '<',
            '4' => '>',
            '5' => '≤',
            '6' => '≥',
            '7' => 'is visible',
            '8' => 'is not visible',
            '9' => 'regular expression matches'
         ]);
   }

   public function testGetConditionsFromItem() {
      // crete a question with some conditions
      $question = $this->getQuestion();

      $condition = $this->newTestedInstance();
      $condition->add([
         'itemtype'  => \PluginFormcreatorQuestion::class,
         'items_id' => $question->getID(),
      ]);
      $this->boolean($condition->isNewItem())->isFalse();

      $condition = $this->newTestedInstance();
      $condition->add([
         'itemtype'  => \PluginFormcreatorQuestion::class,
         'items_id' => $question->getID(),
      ]);
      $this->boolean($condition->isNewItem())->isFalse();

      // Check that all conditions are retrieved
      $output = $condition->getConditionsFromItem($question);
      $this->array($output)->hasSize(2);
   }

   public function testImport() {
      $question = $this->getQuestion();
      $form = new \PluginFormcreatorForm();
      $form->getFromDBByQuestion($question);
      $question2 = $this->getQuestion([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      $input = [
         'plugin_formcreator_questions_id' => $question2->fields['uuid'],
         'show_value'                      => 'foo',
         'show_condition'                  => '=',
         'show_logic'                      => '1',
         'order'                           => '1',
         'itemtype'                        => \PluginFormcreatorQuestion::class,
         'uuid'                            => plugin_formcreator_getUuid(),
      ];

      // Check the import is successful
      $linker = new \PluginFormcreatorLinker();
      $linker->addObject($question2->fields['uuid'], $question2);
      $conditionId = \PluginFormcreatorCondition::import($linker, $input, $question->getID());
      $this->integer($conditionId)->isGreaterThan(0);

      // Check the import fails if uuid is missing
      unset($input['uuid']);
      $this->exception(
         function() use($linker, $input) {
            \PluginFormcreatorCondition::import($linker, $input);
         }
      )->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ImportFailureException::class)
      ->hasMessage('UUID or ID is mandatory for Condition');

      // Check a new item is created when id is not an uuid (duplication use case)
      $linker = new \PluginFormcreatorLinker();
      $linker->addObject($question2->getID(), $question2);
      $input['id'] = $conditionId;
      $input['plugin_formcreator_questions_id'] = $question2->getID();
      $conditionId2 = \PluginFormcreatorCondition::import($linker, $input, $question->getID());
      $this->variable($conditionId2)->isNotFalse();
      $this->integer((int) $conditionId)->isNotEqualTo($conditionId2);
   }

   public function testExport() {
      $instance = $this->newTestedInstance();

      // Try to export an empty item
      $this->exception(function () use ($instance) {
         $instance->export();
      })->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ExportFailureException::class);

      // Prepare an item to export
      $form = $this->getForm();
      $question1 = $this->getQuestion([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $question2 = $this->getQuestion([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $question2->updateConditions([
         'show_rule' => \PluginFormcreatorCondition::SHOW_RULE_HIDDEN,
         '_conditions' => [
            'id' => $question2->getID(),
            'plugin_formcreator_questions_id' => [
               $question1->getID(),
            ],
            'show_condition' =>[
               \PluginFormcreatorCondition::SHOW_CONDITION_EQ,
            ],
            'show_value' => [
               'foo',
            ],
            'show_logic' => [
               \PluginFormcreatorCondition::SHOW_LOGIC_AND,
            ]
         ],
      ]);
      $instance = $this->getTargetTicket();
      $instance->getFromDB($instance->getID());

      $instance = $this->newTestedInstance();
      $instance->getFromDBByCrit([
         'itemtype' => \PluginFormcreatorQuestion::class,
         'items_id' => $question2->getID(),
         'order' => '1',
      ]);

      // Export the item without the ID and with UUID
      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
         'itemtype',
         'plugin_formcreator_questions_id',
         'show_condition',
         'show_value',
         'show_logic',
         'order',
      ];
      $extraFields = [];

      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['uuid'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));

      // Export the item without the UUID and with ID
      $output = $instance->export(true);
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['id'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));
   }
}
