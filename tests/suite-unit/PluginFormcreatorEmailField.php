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
 * @copyright Copyright © 2011 - 2020 Teclib'
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
      $instance = $this->newTestedInstance($this->getQuestion());
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
            'expected' => '',
         ],
         [
            'input' => '',
            'expected' => '',
         ],
         [
            'input' => 'foo@bar.baz',
            'expected' => 'foo@bar.baz',
         ],
         [
            'input' => 'not an email',
            'expected' => 'not an email',
         ],
      ];
   }

   /**
    * @dataProvider providerParseAnswerValue
    */
   public function testParseAnswerValue($input, $expected) {
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question);
      $output = $instance->parseAnswerValues([
         'formcreator_field_' . $question->getID() => $input
      ]);
      $output = $instance->serializeValue();
      $this->string($output)->isEqualTo($expected);
   }

   public function providerSerializeValue() {
      return $this->providerParseAnswerValue();
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function testSerializeValue($value, $expected) {
      $question = $this->getQuestion();
      $instance = new \PluginFormcreatorEmailField($question);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $value]);
      $output = $instance->serializeValue();

      $this->string($output)->isEqualTo($expected ? $value : '');
   }

   public function  testIsAnonymousFormCompatible() {
      $question = $this->getQuestion();
      $instance = new \PluginFormcreatorEmailField($question);
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
      $question = $this->getQuestion();
      $instance = new \PluginFormcreatorEmailField($question);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $answer]);
      $this->boolean($instance->equals($value))->isEqualTo($expected);
   }

   public function providerNotEquals() {
      return $this->providerEquals();
   }

   /**
    * @dataProvider providerNotEquals
    */
   public function testNotEquals($value, $answer, $expected) {
      $question = $this->getQuestion();
      $instance = new \PluginFormcreatorEmailField($question, $answer);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $answer]);
      $this->boolean($instance->notEquals($value))->isEqualTo(!$expected);
   }

   public function testGreaterThan() {
      $this->exception(
         function() {
            $instance = new \PluginFormcreatorEmailField($this->getQuestion());
            $instance->greaterThan('');
         }
      )->isInstanceOf(\PluginFormcreatorComparisonException::class);
   }

   public function testLessThan() {
      $this->exception(
         function() {
            $instance = new \PluginFormcreatorEmailField($this->getQuestion());
            $instance->lessThan('');
         }
      )->isInstanceOf(\PluginFormcreatorComparisonException::class);
   }

   public function testCanRequire() {
      $instance = new \PluginFormcreatorEmailField($this->getQuestion());
      $output = $instance->canRequire();
      $this->boolean($output)->isTrue();
   }


   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }
}
