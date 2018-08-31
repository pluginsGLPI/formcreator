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
 *
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorQuestion extends CommonTestCase {

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);
      switch ($method) {
         case 'testCreateQuestionText':
            $this->login('glpi', 'glpi');
            break;
      }
   }

   public function testDuplicate() {
      $section = $this->getSection();

      $question = new \PluginFormcreatorQuestion();
      $questions_id_1 = $question->add(['name'                           => "test clone question 1",
                                        'fieldtype'                      => 'text',
                                        'plugin_formcreator_sections_id' => $section->getID(),
                                        '_parameters' => [
                                           'text' => [
                                           'regex' => ['regex' => ''],
                                           'range' => ['min' => '', 'max' => ''],
                                           ]
                                         ],
                                        ]);

      //clone the question
      $this->integer($question->duplicate());

      //get cloned section
      $originalId = $question->getID();
      $new_question  = new \PluginFormcreatorQuestion();
      $new_question->getFromDBByCrit([
          'AND' => [
              'name'                           => 'test clone question 1',
              'NOT'                            => ['uuid' => $question->getField('uuid')],  // operator <> available in GLPI 9.3+ only
              'plugin_formcreator_sections_id' => $question->getField('plugin_formcreator_sections_id')
          ]
      ]);
      $this->boolean($new_question->isNewItem())->isFalse();
   }
}