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

class PluginFormcreatorEmailField extends CommonTestCase {

   public function testIsPrerequisites() {
      $instance = $this->newTestedInstance([]);
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(true);
   }

   public function getName() {
      $output = \PluginFormcreatorEmailField::getName();
      $this->string($output)->isEqualTo('Email');
   }

   public function providerParseAnswerValue() {
      return [
         [
            'input' => 42,
            'expected' => false,
         ],
         [
            'input' => '',
            'expected' => true,
         ],
         [
            'input' => 'foo@bar.baz',
            'expected' => true,
         ],
         [
            'input' => 'not an email',
            'expected' => false,
         ],
      ];
   }

   /**
    * @dataProvider providerParseAnswerValue
    */
   public function testParseAnswerValue($input, $expected) {
      $instance = $this->newTestedInstance([
         'id' => 1
      ]);
      $output = $instance->parseAnswerValues([
         'formcreator_field_1' => $input
      ]);
      $this->boolean($output)->isEqualTo($expected);
   }

   public function providerSerializeValue() {
      return $this->providerParseAnswerValue();
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function testSerializeValue($value, $expected) {
      $instance = new \PluginFormcreatorEmailField(['id' => 1]);
      $instance->parseAnswerValues(['formcreator_field_1' => $value]);
      $output = $instance->serializeValue();

      $this->string($output)->isEqualTo($expected ? $value : '');
   }

   public  function  testIsAnonymousFormCompatible() {
      $instance = new \PluginFormcreatorEmailField(['id' => 1]);
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isEqualTo(true);
   }

   public function providerEquals() {
      return [
         [
            'value' => 'foo@bar.baz',
            'answer' => '',
            'expected' => false,
         ],
         [
            'value' => 'foo@bar.baz',
            'answer' => 'foo@bar.baz',
            'expected' => true,
         ],
         [
            'value' => 'foo@bar.baz',
            'answer' => 'foo@bar.com',
            'expected' => false,
         ],
      ];
   }

   /**
    * @dataProvider providerEquals
    */
   public function testEquals($value, $answer, $expected) {
      $instance = new \PluginFormcreatorEmailField(['id' => '1']);
      $instance->parseAnswerValues(['formcreator_field_1' => $answer]);
      $this->boolean($instance->equals($value))->isEqualTo($expected);
   }

   public function providerNotEquals() {
      return $this->providerEquals();
   }

   /**
    * @dataProvider providerNotEquals
    */
    public function testNotEquals($value, $answer, $expected) {
      $instance = new \PluginFormcreatorEmailField(['id' => '1'], $answer);
      $instance->parseAnswerValues(['formcreator_field_1' => $answer]);
      $this->boolean($instance->notEquals($value))->isEqualTo(!$expected);
   }

   public function testGreaterThan() {
      $this->exception(
         function() {
            $instance = new \PluginFormcreatorEmailField([]);
            $instance->greaterThan('');
         }
      )->isInstanceOf(\PluginFormcreatorComparisonException::class);
   }

   public function testLessThan() {
      $this->exception(
         function() {
            $instance = new \PluginFormcreatorEmailField([]);
            $instance->lessThan('');
         }
      )->isInstanceOf(\PluginFormcreatorComparisonException::class);
   }
}
