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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;
class PluginFormcreatorQuestion_Condition extends CommonTestCase {
   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);

      self::login('glpi', 'glpi');
   }

   public function testGetEnumShowLogic() {
      $output = \PluginFormcreatorQuestion_Condition::getEnumShowLogic();
      $this->array($output)
         ->isIdenticalTo([
            '1' => 'AND',
            '2' => 'OR'
         ]);
   }

   public function testGetEnumShowCondition() {
      $output = \PluginFormcreatorQuestion_Condition::getEnumShowCondition();
      $this->array($output)
         ->isIdenticalTo([
            '1' => '=',
            '2' => '≠',
            '3' => '<',
            '4' => '>',
            '5' => '≤',
            '6' => '≥',
         ]);
   }

   public function testGetConditionsFromQuestion() {
      // crete a question with some conditions
      $question = $this->getQuestion();

      $questionFk = \PluginFormcreatorQuestion::getForeignKeyField();
      $questionCondition = $this->newTestedInstance();
      $questionCondition->add([
         $questionFk => $question->getID(),
      ]);
      $this->boolean($questionCondition->isNewItem())->isFalse();

      $questionCondition = $this->newTestedInstance();
      $questionCondition->add([
         $questionFk => $question->getID(),
      ]);
      $this->boolean($questionCondition->isNewItem())->isFalse();

      // Check that all conditions are retrieved
      $output = $questionCondition->getConditionsFromQuestion($question->getID());
      $this->array($output)->hasSize(2);
   }

   public function testImport() {
      $question = $this->getQuestion();
      $form = $question->getForm();
      $question2 = $this->getQuestion([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);

      $input = [
         'show_field' => $question2->fields['uuid'],
         'show_value' => 'foo',
         'show_condition' => '=',
         'show_logic' => '1',
         'order' => '1',
         'uuid' => plugin_formcreator_getUuid(),
      ];

      $linker = new \PluginFormcreatorLinker();
      $linker->addObject($question2->fields['uuid'], $question2);
      $conditionId = \PluginFormcreatorQuestion_Condition::import($linker, $input, $question->getID());
      $this->integer($conditionId)->isGreaterThan(0);

      unset($input['uuid']);

      $this->exception(
         function() use($linker, $input) {
            \PluginFormcreatorQuestion_Condition::import($linker, $input);
         }
      )->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ImportFailureException::class)
      ->hasMessage('UUID or ID is mandatory');

      $linker = new \PluginFormcreatorLinker();
      $linker->addObject($question2->getID(), $question2);
      $input['id'] = $conditionId;
      $input['show_field'] = $question2->getID();
      $conditionId2 = \PluginFormcreatorQuestion_Condition::import($linker, $input);
      $this->variable($conditionId2)->isNotFalse();
      $this->integer((int) $conditionId)->isNotEqualTo($conditionId2);
   }

   public function testExport() {
      $instance = $this->newTestedInstance();

      // Try to export an empty item
      $output = $instance->export();
      $this->boolean($output)->isFalse();

      // Prepare an item to export
      $form = $this->getForm();
      $question1 = $this->getQuestion([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $question2 = $this->getQuestion([
         'plugin_formcreator_forms_id' => $form->getID(),
      ]);
      $question2->updateConditions([
         'id' => $question2->getID(),
         'show_rule' => \PluginFormcreatorQuestion::SHOW_RULE_HIDDEN,
         'show_field' => [
            $question1->getID(),
         ],
         'show_condition' =>[
            \PluginFormcreatorQuestion_Condition::SHOW_CONDITION_EQ,
         ],
         'show_value' => [
            'foo',
         ],
         'show_logic' => [
            \PluginFormcreatorQuestion_Condition::SHOW_LOGIC_AND,
         ]
      ]);
      $instance = $this->getTargetTicket();
      $instance->getFromDB($instance->getID());

      $instance = $this->newTestedInstance();
      $instance->getFromDBByCrit([
         'plugin_formcreator_questions_id' => $question2->getID(),
         'order' => '1',
      ]);

      // Export the item without the ID and with UUID
      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
         'show_field',
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
