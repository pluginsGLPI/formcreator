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

class PluginFormcreatorDropdownField extends CommonTestCase {
   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testPrepareQuestionInputForSave':
            $this->login('glpi', 'glpi');
      }
   }

   public function testGetName() {
      $instance = $this->newTestedInstance([]);
      $output = $instance->getName();
      $this->string($output)->isEqualTo('Dropdown');
   }

   public function providerPrepareQuestionInputForSave() {
      $name = $this->getUniqueString();
      return [
         [
            'input' => [
               'name' => $name,
               'dropdown_values' => \Location::class,
            ],
            'expected' => [
               'name' => $name,
               'values' => json_encode(['itemtype' => \Location::class]),
               'dropdown_values' => \Location::class,
            ]
         ],
         [
            'input' => [
               'name' => $name,
               'dropdown_values' => \ITILCategory::class,
               'show_ticket_categories' => '2',
               'show_ticket_categories_depth' => '3',
            ],
            'expected' => [
               'name' => $name,
               'values' => json_encode([
                  'itemtype' => \ITILCategory::class,
                  'show_ticket_categories' => '2',
                  'show_ticket_categories_depth' => '3',
               ]),
               'dropdown_values' => \ITILCategory::class,
            ]
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareQuestionInputForSave
    */
   public function testPrepareQuestionInputForSave($input, $expected) {
      $instance = new \PluginFormcreatorDropdownField([]);
      $output = $instance->prepareQuestionInputForSave($input);
      $this->array($output)->hasSize(count($expected));
      foreach ($expected as $key => $value) {
         $this->variable($output[$key])->isIdenticalTo($value);
      }
   }
}
