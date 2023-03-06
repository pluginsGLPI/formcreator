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

namespace GlpiPlugin\Formcreator\Tests;

use PluginFormcreatorFormanswer;

abstract class AbstractItilTargetTestCase extends CommonTargetTestCase {
   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testSetTargetPriority':
            $this->boolean($this->login('glpi', 'glpi'))->isTrue();
            break;
      }
   }

   public function setTargetPriorityProvider() {
      $form = $this->getForm();
      $target = $this->newTestedInstance();
      $target->add([
         'name' => $this->getUniqueString(),
         'plugin_formcreator_forms_id' => $form->getID(),
         'urgency_rule' => \PluginFormcreatorTargetTicket::URGENCY_RULE_NONE,
      ]);

      yield 'no urgency and no template' => [
         'formanswerData' => [
            $form::getForeignKeyField() => $form->getID(),
         ],
         'expected'   => 3
      ];

      $testedClassName = $this->getTestedClassName();
      $section = $this->getSection([
         $form::getForeignKeyField() => $form->getID(),
      ]);
      $question = $this->getQuestion([
         'plugin_formcreator_sections_id' => $section->getID(),
         'fieldtype' => 'urgency',
      ]);
      $target->update([
         'id' => $target->getID(),
         'urgency_rule' => $testedClassName::URGENCY_RULE_SPECIFIC,
         '_urgency_specific' => $question->getID(),
      ]);

      yield 'urgency from question' => [
         'formanswerData' => [
            $form::getForeignKeyField() => $form->getID(),
            'formcreator_field_' . $question->getID() => '5',
         ],
         'expected'   => 4, // Urgency 5 and impact 3 gives priority 4 with default matrix
      ];

      // Ugly, but GLPI itself does the same internally...
      $templateType = $target->getTargetItemtypeName() . 'Template';
      $predefinedType = $target->getTargetItemtypeName() . 'TemplatePredefinedField';

      $template = $this->getGlpiCoreItem($templateType, [
         'name' => $this->getUniqueString(),
      ]);
      $predefined = new $predefinedType();
      $predefined->add([
         $templateType::getForeignKeyField() => $template->getID(),
         'num' => 11, // Impact search option ID,
         'value' => '5', // Very high impact
      ]);
      $target->update([
         'id' => $target->getID(),
         'urgency_rule' => $testedClassName::URGENCY_RULE_NONE,
         '_urgency_question' => '0',
         $templateType::getForeignKeyField() => $template->getID(),
      ]);

      yield 'impact from template' => [
         'formanswerData' => [
            $form::getForeignKeyField() => $form->getID(),
         ],
         'expected'   => 4, // Urgency 3 and impact 5 gives priority 4 with default matrix
      ];
   }

   /**
    * @dataProvider setTargetPriorityProvider
    */
   public function testSetTargetPriority($formanswerData, $expected) {
      $formanswer = new PluginFormcreatorFormanswer();
      $formanswer->add($formanswerData);
      $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];
      $this->boolean($formanswer->isNewItem())->isFalse(json_encode($_SESSION['MESSAGE_AFTER_REDIRECT'], JSON_PRETTY_PRINT));
      $generatedTarget = $formanswer->targetList[0]; // Assume the target has been generated
      $generatedTarget->fields['priority'] = $expected;
   }
}
