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

class RadiosField extends CommonTestCase {
   public function testPrepareQuestionInputForSave() {
      $question = $this->getQuestion([
         'fieldtype'       => 'radios',
         'name'            => 'question',
         'required'        => '0',
         'default_values'  => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
         'values'          => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
         'order'           => '1',
         'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
         'range_min'       => 3,
         'range_max'       => 4,
      ]);
      $fieldInstance = $this->newTestedInstance($question);

      // Test a value is mandatory
      $input = [
         'values'          => "",
         'name'            => 'foo',
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->integer(count($out))->isEqualTo(0);

      // Test accented chars are kept
      $input = [
         'values'          => 'éè\r\nsomething else',
         'default_values'  => 'éè',
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->string($out['values'])->isEqualTo('[\"éè\",\"something else\"]');
      $this->string($out['default_values'])->isEqualTo("éè");

      // Test values are trimmed
      $input = [
         'values'          => ' something \r\n  something else  ',
         'default_values'  => ' something      ',
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->string($out['values'])->isEqualTo('[\"something\",\"something else\"]');
      $this->string($out['default_values'])->isEqualTo("something");
   }

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Radios');
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
      $this->boolean($output)->isTrue();
   }

   public function providerSerializeValue() {
      $question = $this->getQuestion([
         'fieldtype' => 'radios',
         'values' => json_encode(['foo', 'bar', 'test d\'apostrophe'])
      ]);
      $instance = $this->newTestedInstance($question);
      return [
         [
            'instance'  => $instance,
            'value'     => null,
            'expected'  => '',
         ],
         [
            'instance'  => $instance,
            'value'     => '',
            'expected'  => '',
         ],
         [
            'instance'  => $instance,
            'value'     => 'foo',
            'expected'  => 'foo',
         ],
         [
            'instance'  => $instance,
            'value'     => "test d'apostrophe",
            'expected'  => 'test d\\\'apostrophe',
         ],
      ];
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function testSerializeValue($instance, $value, $expected) {
      $instance->parseAnswerValues(['formcreator_field_' . $instance->getQuestion()->getID() => $value]);
      $output = $instance->serializeValue();
      $this->string($output)->isEqualTo($expected);
   }

   public function providerDeserializeValue() {
      $question = $this->getQuestion([
         'fieldtype' => 'radios',
         'values' => json_encode(['foo', 'bar', 'test d\'apostrophe'])
      ]);
      $instance = $this->newTestedInstance($question);
      return [
         [
            'instance'  => $instance,
            'value'     => null,
            'expected'  => '',
         ],
         [
            'instance'  => $instance,
            'value'     => '',
            'expected'  => '',
         ],
         [
            'instance'  => $instance,
            'value'     => "foo",
            'expected'  => 'foo',
         ],
         [
            'instance'  => $instance,
            'value'     => "test d'apostrophe",
            'expected'  => "test d'apostrophe",
         ],
      ];
   }

   /**
    * @dataProvider providerDeserializeValue
    */
   public function testDeserializeValue($instance, $value, $expected) {
      $instance->parseAnswerValues(['formcreator_field_' . $instance->getQuestion()->getID() => $value]);
      $instance->deserializeValue($value);
      $output = $instance->getValueForTargetText('', false);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerparseAnswerValues() {
      return [
         [
            'question' => $this->getQuestion(),
            'value' => '',
            'expected' => true,
            'expectedValue' => '',
         ],
         [
            'question' => $this->getQuestion(),
            'value' => 'test d\'apostrophe',
            'expected' => true,
            'expectedValue' => "test d'apostrophe",
         ],
      ];
   }

   /**
    * @dataProvider providerparseAnswerValues
    */
   public function testParseAnswerValues($question, $value, $expected, $expectedValue) {
      $instance = $this->newTestedInstance($question);
      $output = $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $value]);
      $this->boolean($output)->isEqualTo($expected);

      $outputValue = $instance->getValueForTargetText('', false);
      if ($expected === false) {
         $this->variable($outputValue)->isNull();
      } else {
         $this->string($outputValue)
            ->isEqualTo($expectedValue);
      }
   }

   public function providerGetValueForDesign() {
      return [
         [
            'value' => null,
            'expected' => '',
         ],
         [
            'value' => 'foo',
            'expected' => 'foo',
         ],
      ];
   }

   /**
    * @dataProvider providerGetValueForDesign
    */
   public function testGetValueForDesign($value, $expected) {
      $instance = $this->newTestedInstance($this->getQuestion());
      $instance->deserializeValue($value);
      $output = $instance->getValueForDesign();
      $this->string($output)->isEqualTo($expected);
   }

   public function providerIsValid() {
      return [
         [
            'fields' => [
               'fieldtype' => 'radios',
               'values' => 'a\r\nb',
               'required' => false,
            ],
            'value' => '',
            'expected' => true,
         ],
         [
            'fields' => [
               'fieldtype' => 'radios',
               'values' => 'a\r\nb',
               'required' => true,
            ],
            'value' => '',
            'expected' => false,
         ],
      ];
   }

   /**
    * @dataProvider providerIsValid
    */
   public function testIsValid($fields, $value, $expected) {
      $question = $this->getQuestion($fields);
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($value);

      $output = $instance->isValid();
      $this->boolean($output)->isEqualTo($expected);
   }

   public  function providerEquals() {
      return [
         [
            'fields' => [
               'values' => ""
            ],
            'value' => "",
            'compare' => '',
            'expected' => true
         ],
         [
            'fields' => [
               'values' => json_encode(['a', 'b', 'c'])
            ],
            'value' => "a",
            'compare' => 'b',
            'expected' => false
         ],
         [
            'fields' => [
               'values' => json_encode(['a', 'b', 'c'])
            ],
            'value' => "a",
            'compare' => 'a',
            'expected' => true
         ],
      ];
   }

   /**
    * @dataprovider providerEquals
    */
   public function testEquals($fields, $value, $compare, $expected) {
      $question = $this->getQuestion($fields);
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($value);

      $output = $instance->equals($compare);
      $this->boolean($output)->isEqualTo($expected);
   }

   public function providerNotEquals() {
      return $this->providerEquals();
   }

   /**
    * @dataprovider providerNotEquals
    */
   public function testNotEquals($fields, $value, $compare, $expected) {
      $question = $this->getQuestion($fields);
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($value);

      $output = $instance->notEquals($compare);
      $this->boolean($output)->isEqualTo(!$expected);
   }
}
