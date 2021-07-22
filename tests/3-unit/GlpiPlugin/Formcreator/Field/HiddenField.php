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

class HiddenField extends CommonTestCase {
   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Hidden field');
   }

   public function testIsValid() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isValid('');
      $this->boolean($output)->isTrue();
   }

   public function testIsAnonymousFormCompatible() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isTrue();
   }

   public function testIsPrerequisites() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(true);
   }

   public function testCanRequire() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->canRequire();
      $this->boolean($output)->isFalse();
   }

   public function providerSerializeValue() {
      return [
         [
            'value' => '',
            'expected' => '',
         ],
         [
            'value' => "foo",
            'expected' => "foo",
         ],
      ];
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function serializeValue($value, $expected) {
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

   public function testGetValueForDesign() {
      $value = 'foo';
      $instance = $this->newTestedInstance($this->getQuestion());
      $instance->deserializeValue($value);
      $output = $instance->getValueForDesign();
      $this->string($output)->isEqualTo('foo');
   }

   public function providerGetValueForTargetText() {
      return [
         [
            'fields' => [
               'values' => ''
            ],
            'value' => "",
            'expected' => ''
         ],
         [
            'fields' => [
               'values' => ""
            ],
            'value' => "foo",
            'expected' => 'foo'
         ],
      ];
   }

   /**
    * @dataprovider providerGetValueForTargetText
    */
   public function testGetValueForTargetText($fields, $value, $expected) {
      $question = $this->getQuestion($fields);
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($value);

      $output = $instance->getValueForTargetText('', true);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerEquals() {
      return [
         [
            'value'     => '',
            'answer'    => '',
            'expected'  => true,
         ],
         [
            'value'     => 'foo',
            'answer'    => 'bar',
            'expected'  => false,
         ],
         [
            'value'     => 'foo',
            'answer'    => 'foo',
            'expected'  => true,
         ],
      ];
   }

   /**
    * @dataProvider providerEquals
    */
   public function testEquals($value, $answer, $expected) {
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $answer]);
      $this->boolean($instance->equals($value))->isEqualTo($expected);
   }

   public function providerNotEquals() {
      return [
         [
            'value'     => '',
            'answer'    => '',
            'expected'  => false,
         ],
         [
            'value'     => 'foo',
            'answer'    => 'bar',
            'expected'  => true,
         ],
         [
            'value'     => 'foo',
            'answer'    => 'foo',
            'expected'  => false,
         ],
      ];
   }

   /**
    * @dataProvider providerNotEquals
    */
   public function testNotEquals($value, $answer, $expected) {
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $answer]);
      $this->boolean($instance->notEquals($value))->isEqualTo($expected);
   }

   public function providerGreaterThan() {
      return [
         [
            'value'     => '',
            'answer'    => '',
            'expected'  => false,
         ],
         [
            'value'     => 'foo',
            'answer'    => 'bar',
            'expected'  => false,
         ],
         [
            'value'     => 'bar',
            'answer'    => 'foo',
            'expected'  => true,
         ],
         [
            'value'     => 'foo',
            'answer'    => 'foo',
            'expected'  => false,
         ],
      ];
   }

   /**
    * @dataProvider providerGreaterThan
    */
   public function testGreaterThan($value, $answer, $expected) {
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $answer]);
      $this->boolean($instance->greaterThan($value))->isEqualTo($expected);
   }

   public function providerLessThan() {
      return [
         [
            'value'     => '',
            'answer'    => '',
            'expected'  => false,
         ],
         [
            'value'     => 'foo',
            'answer'    => 'bar',
            'expected'  => true,
         ],
         [
            'value'     => 'bar',
            'answer'    => 'foo',
            'expected'  => false,
         ],
         [
            'value'     => 'foo',
            'answer'    => 'foo',
            'expected'  => false,
         ],
      ];
   }

   /**
    * @dataProvider providerLessThan
    */
   public function testLessThan($value, $answer, $expected) {
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $answer]);
      $this->boolean($instance->lessThan($value))->isEqualTo($expected);
   }

   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }

   public function testGetDesignSpecializationField() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->getDesignSpecializationField();
      $this->string($output['label'])->isEqualTo('');
      $this->string($output['field'])->isEqualTo('');
      $this->boolean($output['may_be_empty'])->isEqualTo(false);
      $this->boolean($output['may_be_required'])->isEqualTo(false);
   }
}
