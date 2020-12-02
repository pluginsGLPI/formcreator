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
namespace GlpiPlugin\Formcreator\Field\tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class TextareaField extends CommonTestCase {
   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Textarea');
   }

   public function testIsAnonymousFormCompatible() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isTrue();
   }

   public function providerSerializeValue() {
      return [
         [
            'value' => '',
            'expected' => '',
         ],
         [
            'value' => "quote ' test",
            'expected' => "quote \' test",
         ],
      ];
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function testSerializeValue($value, $expected) {
      $instance = $this->newTestedInstance($this->getQuestion());
      $instance->prepareQuestionInputForSave([
         'default_values' => $value,
      ]);
      $output = $instance->serializeValue();
      $this->string($output)->isEqualTo($expected);
   }

   public function providerDeserializeValue() {
      return [
         [
            'value'     => '',
            'expected'  => '',
         ],
         [
            'value'     => 'foo',
            'expected'  => 'foo' ,
         ],
      ];
   }

   /**
    * @dataProvider providerDeserializeValue
    */
   public function testDeserializeValue($value, $expected) {
      $instance = $this->newTestedInstance($this->getQuestion());
      $instance->deserializeValue($value);
      $output = $instance->getValueForTargetText('', false);
      $this->string($output)->isEqualTo($expected);
   }

   public function testCanRequire() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->canRequire();
      $this->boolean($output)->isTrue();
   }

   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }

   public function providerEquals() {
      return [
         [
            'value'      => '',
            'comparison' => '',
            'expected'   => true,
         ],
         [
            'value'      => 'foo',
            'comparison' => 'bar',
            'expected'   => false,
         ],
         [
            'value'      => '',
            'comparison' => 'bar',
            'expected'   => false,
         ],
         [
            'value'      => 'foo',
            'comparison' => '',
            'expected'   => false,
         ],
         [
            'value'      => 'foo',
            'comparison' => 'foo',
            'expected'   => true,
         ],
      ];
   }

   /**
    * @dataProvider providerEquals
    *
    */
   public function testEquals($value, $comparison, $expected) {
      $question = $this->getQuestion();
      $key = 'formcreator_field_' . $question->getID();
      $instance = $this->newTestedInstance($question);
      $input = [
         $key => $value,
      ];
      $instance->parseAnswerValues($input, true);
      $output =$instance->equals($comparison);
      $this->boolean($output)->isEqualTo($expected);
   }

   /**
    * @dataProvider providerEquals
    *
    */
   public function testNotEquals($value, $comparison, $expected) {
      $question = $this->getQuestion();
      $key = 'formcreator_field_' . $question->getID();
      $instance = $this->newTestedInstance($question);
      $input = [
         $key => $value,
      ];
      $instance->parseAnswerValues($input, true);
      $output =$instance->notEquals($comparison);
      $this->boolean($output)->isEqualTo(!$expected);
   }

   public function providerGreaterThan() {
      return [
         [
            'value'      => '',
            'comparison' => '',
            'expected'   => false,
         ],
         [
            'value'      => 'foo',
            'comparison' => 'foo',
            'expected'   => false,
         ],
         [
            'value'      => 'foo',
            'comparison' => 'foo',
            'expected'   => false,
         ],
         [
            'value'      => 'foo',
            'comparison' => 'bar',
            'expected'   => true,
         ],
         [
            'value'      => 'bar',
            'comparison' => 'foo',
            'expected'   => false,
         ],
      ];
   }

   /**
    * @dataProvider providerGreaterThan
    *
    */
   public function testGreaterThan($value, $comparison, $expected) {
      $question = $this->getQuestion();
      $key = 'formcreator_field_' . $question->getID();
      $instance = $this->newTestedInstance($question);
      $input = [
         $key => $value,
      ];
      $instance->parseAnswerValues($input, true);
      $output = $instance->greaterThan($comparison);
      $this->boolean($output)->isEqualTo($expected);
   }
}
