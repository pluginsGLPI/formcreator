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

class PluginFormcreatorRadiosField extends CommonTestCase {
   public function testPrepareQuestionInputForSave() {
      $fields = [
         'fieldtype'       => 'radios',
         'name'            => 'question',
         'required'        => '0',
         'default_values'  => "1\r\n2\r\n3\r\n5\r\n6",
         'values'          => "1\r\n2\r\n3\r\n4\r\n5\r\n6",
         'order'           => '1',
         'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
         'range_min'       => 3,
         'range_max'       => 4,
      ];
      $question = $this->getQuestion($fields);
      $fieldInstance = new \PluginFormcreatorRadiosField($question);

      // Test a value is mandatory
      $input = [
         'values'          => "",
         'name'            => 'foo',
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->integer(count($out))->isEqualTo(0);

      // Test accented chars are kept
      $input = [
         'values'          => "éè\r\nsomething else",
         'default_values'  => "éè",
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->string($out['values'])->isEqualTo("éè\r\nsomething else");
      $this->string($out['default_values'])->isEqualTo("éè");

      // Test values are trimmed
      $input = [
         'values'          => ' something \r\n  something else  ',
         'default_values'  => " something      ",
      ];
      $out = $fieldInstance->prepareQuestionInputForSave($input);
      $this->string($out['values'])->isEqualTo('something\r\nsomething else');
      $this->string($out['default_values'])->isEqualTo("something");
   }

   public function testGetName() {
      $output = \PluginFormcreatorRadiosField::getName();
      $this->string($output)->isEqualTo('Radios');
   }


   public function testIsAnonymousFormCompatible() {
      $instance = new \PluginFormcreatorRadiosField($this->getQuestion());
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isTrue();
   }

   public function testIsPrerequisites() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isPrerequisites();
      $this->boolean($output)->isEqualTo(true);
   }

   public function testCanRequire() {
      $instance = new \PluginFormcreatorRadiosField($this->getQuestion());
      $output = $instance->canRequire();
      $this->boolean($output)->isTrue();
   }

   public function providerSerializeValue() {
      return [
         [
            'value'     => null,
            'expected'  => '',
         ],
         [
            'value'     => '',
            'expected'  => '',
         ],
         [
            'value'     => 'foo',
            'expected'  => 'foo',
         ],
         [
            'value'     => "test d'apostrophe",
            'expected'  => "test d\'apostrophe",
         ],
      ];
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function testSerializeValue($value, $expected) {
      $question = $this->getQuestion(['values' => 'foo\r\nbarr\r\ntest d\'apostrophe']);
      $instance = new \PluginFormcreatorRadiosField($question);
      $instance->prepareQuestionInputForSave([
         'default_values' => $value,
      ]);
      $output = $instance->serializeValue();
      $this->string($output)->isEqualTo($expected);
   }

   public function providerDeserializeValue() {
      return [
         [
            'value'     => null,
            'expected'  => '',
         ],
         [
            'value'     => '',
            'expected'  => '',
         ],
         [
            'value'     => "foo",
            'expected'  => 'foo',
         ],
         [
            'value'     => "test d'apostrophe",
            'expected'  => "test d'apostrophe",
         ],
      ];
   }

   /**
    * @dataProvider providerDeserializeValue
    */
   public function testDeserializeValue($value, $expected) {
      $question = $this->getQuestion(['values' => 'foo\r\nbarr\r\ntest d\'apostrophe']);
      $instance = new \PluginFormcreatorRadiosField($question);
      $instance->deserializeValue($value);
      $output = $instance->getValueForTargetText(false);
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

      $outputValue = $instance->getValueForTargetText(false);
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
      $instance = new \PluginFormcreatorRadiosField($this->getQuestion());
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
      $instance = new \PluginFormcreatorRadiosField($question);
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
               'values' => "a\r\nb\r\nc"
            ],
            'value' => "a",
            'compare' => 'b',
            'expected' => false
         ],
         [
            'fields' => [
               'values' => "a\r\nb\r\nc"
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
