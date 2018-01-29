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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class IntegerFieldTest extends SuperAdminTestCase {

   public function provider() {
      $dataset = array(
         array(
            'fields'          => array(
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ),
            'data'            => null,
            'expectedValue'   => '',
            'expectedIsValid' => true
         ),
         array(
            'fields'          => array(
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '2',
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => '',
                        'range_max' => '',
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ),
            'data'            => null,
            'expectedValue'   => '2',
            'expectedIsValid' => true
         ),
         array(
            'fields'          => array(
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "2",
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => '0',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => 3,
                        'range_max' => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ),
            'data'            => null,
            'expectedValue'   => '2',
            'expectedIsValid' => false
         ),
         array(
            'fields'          => array(
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "5",
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => 3,
                        'range_max' => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ),
            'data'            => null,
            'expectedValue'   => '5',
            'expectedIsValid' => false
         ),
         array(
            'fields'          => array(
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "3.4",
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => 3,
                        'range_max' => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ),
            'data'            => null,
            'expectedValue'   => '3.4',
            'expectedIsValid' => false
         ),
         array(
            'fields'          => array(
               'fieldtype'       => 'integer',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => "4",
               'order'           => '1',
               'show_rule'       => 'always',
               'show_empty'      => '0',
               'values'          => '',
               '_parameters'     => [
                  'integer' => [
                     'range' => [
                        'range_min' => 3,
                        'range_max' => 4,
                     ],
                     'regex' => ['regex' => ''],
                  ]
               ],
            ),
            'data'            => null,
            'expectedValue'   => '4',
            'expectedIsValid' => true
         ),
      );

      return $dataset;
   }

   /**
    * @dataProvider provider
    */
   public function testFieldValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new PluginFormcreatorIntegerField($fields, $data);

      $value = $fieldInstance->getValue();
      $this->assertEquals($expectedValue, $value);
   }

   /**
    * @dataProvider provider
    */
   public function testFieldIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $section = $this->getSection();
      $fields[$section::getForeignKeyField()] = $section->getID();

      $question = new PluginFormcreatorQuestion();
      $question->add($fields);
      $question->updateParameters($fields);

      $fieldInstance = new PluginFormcreatorIntegerField($question->fields, $data);
      $isValid = $fieldInstance->isValid($fields['default_values']);
      $this->assertEquals($expectedValidity, $isValid);
   }

   private function getSection() {
      $form = new PluginFormcreatorForm();
      $form->add([
         'name' => 'form'
      ]);
      $section = new PluginFormcreatorSection();
      $section->add([
         $form::getForeignKeyField() => $form->getID(),
         'name' => 'section',
      ]);
      return $section;
   }
}