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

class PluginFormcreatorActorField extends CommonTestCase {

   public function testGetName() {
      $output = \PluginFormcreatorActorField::getName();
      $this->string($output)->isEqualTo('Actor');
   }

   public function providerGetValue() {
      $user = new \User();
      $user->getFromDBbyName('glpi');
      $userId = $user->getID();
      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => [''],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => 'glpi',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => [''],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => 'nonexistent',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => [''],
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => 'email@incomplete',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => [''],
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => 'email@something.com',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => ['email@something.com'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $userId . ',email@something.com',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => ['glpi', 'email@something.com'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $userId . ',email@something.com,nonexistent',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => ['glpi', 'email@something.com'],
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $userId . ',email@something.com,email@incomplete',
               'values'          => '',
               'order'           => '1',
               'show_rule'       => 'always'
            ],
            'data'            => null,
            'expectedValue'   => ['glpi', 'email@something.com'],
            'expectedIsValid' => false
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider providerGetValue
    */
   public function testGetValue($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorActorField($fields, $data);

      $value = $fieldInstance->getValue();
      $this->integer(count(explode(',', $value)))->isEqualTo(count($expectedValue));
      foreach ($expectedValue as $expectedSubValue) {
         if (!empty($expectedSubValue)) {
            $this->boolean(in_array($expectedSubValue, explode(',', $value)))->isTrue();
         }
      }
   }

   public function providerIsValid() {
      return $this->providerGetValue();
   }

   /**
    * @dataProvider providerIsValid
    */
   public function testIsValid($fields, $data, $expectedValue, $expectedValidity) {
      $fieldInstance = new \PluginFormcreatorActorField($fields, $data);

      $values = $fields['default_values'];
      $isValid = $fieldInstance->isValid($values);
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
   }

   public function providerSerializeValue() {
      return [
         [
            'value'     => 'glpi',
            'expected'  => '2',
         ],
         [
            'value'     => "glpi\r\nnormal",
            'expected'  => '2,5',
         ],
         [
            'value'     => "glpi\r\nnormal\r\nuser@localhost.local",
            'expected'  => '2,5,user@localhost.local',
         ],
         [
            'value'     => 'user@localhost.local',
            'expected'  => 'user@localhost.local',
         ],
      ];
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function testSerializeValue($value, $expected) {
      $instance = new \PluginFormcreatorActorField([]);
      $output = $instance->serializeValue($value);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerDeserializeValue() {
      // swap value and expected
      $dataSet = $this->providerSerializeValue();
      foreach ($dataSet as &$data) {
         $tmp = $data['expected'];
         $data['expected'] = $data['value'];
         $data['value'] = $tmp;
      }
      return $dataSet;
   }

   /**
    * @dataProvider providerDeserializeValue
    */
   public function testDeserializeValue($value, $expected) {
      $instance = new \PluginFormcreatorActorField([]);
      $output = $instance->deserializeValue($value);
      $this->string($output)->isEqualTo($expected);
   }
}
