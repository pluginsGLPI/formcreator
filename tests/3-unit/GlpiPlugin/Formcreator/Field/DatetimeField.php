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

class DatetimeField extends CommonTestCase {

   public function providerGetValue() {
      $dataset = [
         [
            'question'           => $this->getQuestion([
               'fieldtype'       => 'datetime',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [],
            ]),
            'expectedValue'   => null,
            'expectedIsValid' => true
         ],
         [
            'question'           => $this->getQuestion([
               'fieldtype'       => 'datetime',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '2018-08-16 08:12:34',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [],
            ]),
            'expectedValue'   => '2018-08-16 08:12:34',
            'expectedIsValid' => true
         ],
         [
            'question'           => $this->getQuestion([
               'fieldtype'       => 'datetime',
               'name'            => 'question',
               'required'        => '1',
               'default_values'  => '',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [],
            ]),
            'expectedValue'   => null,
            'expectedIsValid' => false
         ],
         [
            'question'           => $this->getQuestion([
               'fieldtype'       => 'datetime',
               'name'            => 'question',
               'required'        => '1',
               'default_values'  => '2018-08-16 08:12:34',
               'values'          => "",
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               '_parameters'     => [],
            ]),
            'expectedValue'   => '2018-08-16 08:12:34',
            'expectedIsValid' => true
         ],
      ];

      return $dataset;
   }

   public function providerIsValid() {
      return $this->providerGetValue();
   }

   /**
    * @dataProvider providerIsValid
    */
   public function testIsValid($question, $expectedValue, $expectedValidity) {
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($question->fields['default_values']);

      $isValid = $instance->isValid();
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
   }

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Date & time');
   }

   public function providerParseAnswerValues() {
      return [
         [
            'question' => $this->getQuestion(),
            'value' => '',
            'expected' => true,
            'expectedValue' => ' ',
         ],
         [
            'question' => $this->getQuestion(),
            'value' => '2018-12-25 23:00:00',
            'expected' => true,
            'expectedValue' => '2018-12-25 23:00',
         ],
      ];
   }

   /**
    * @dataProvider providerParseAnswerValues
    */
   public function testParseAnswerValues($question, $value, $expected, $expectedValue) {
      $instance = $this->newTestedInstance($question);
      $output = $instance->parseAnswerValues([
         'formcreator_field_' . $question->getID() => $value
      ]);
      $this->boolean($output)->isEqualTo($expected);

      $outputValue = $instance->getValueForTargetText('', false);
      if ($expected === false) {
         $this->variable($outputValue)->isNull();
      } else {
         $this->string($outputValue)
            ->isEqualTo($expectedValue);
      }
   }

   public function providerSerializeValue() {
      return [
         [
            'id' => '1',
            'input' => [],
         ],
      ];
   }

   public function testSerializeValue() {
      $value = $expected = '2019-01-01 12:00:00';
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $value]);
      $output = $instance->serializeValue();
      $this->string($output)->isEqualTo($expected);
   }

   public function testGetValueForDesign() {
      $value = $expected = '2019-01-01 12:00:00';
      $instance = $this->newTestedInstance($this->getQuestion());
      $instance->deserializeValue($value);
      $output = $instance->getValueForDesign();
      $this->string($output)->isEqualTo($expected);
   }

   public function providerEquals() {
      return [
         [
            'value'     => '0000-00-00 00:00:00',
            'answer'    => '',
            'expected'  => true,
         ],
         [
            'value'     => '2019-01-01 00:00:00',
            'answer'    => '',
            'expected'  => false,
         ],
         [
            'value'     => '2019-01-01 02:00:00',
            'answer'    => '2019-01-01 03:00:00',
            'expected'  => false,
         ],
         [
            'value'     => '2019-01-01 03:00:00',
            'answer'    => '2019-01-01 03:00:00',
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
            'value'     => '2019-01-01 00:00:00',
            'answer'    => '',
            'expected'  => true,
         ],
         [
            'value'     => '2019-01-01 02:00:00',
            'answer'    => '2019-01-01 03:00:00',
            'expected'  => true,
         ],
         [
            'value'     => '2019-01-01 03:00:00',
            'answer'    => '2019-01-01 03:00:00',
            'expected'  => false,
         ],
      ];
   }

   /**
    * @dataProvider providerNotEquals
    */
   public function testNotEquals($value, $answer, $expected) {
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question, $answer);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $answer]);
      $this->boolean($instance->notEquals($value))->isEqualTo($expected);
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

   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }

   public function testCanRequire() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->canRequire();
      $this->boolean($output)->isTrue();
   }
}
