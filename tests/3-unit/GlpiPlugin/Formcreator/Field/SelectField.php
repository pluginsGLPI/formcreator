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

class SelectField extends CommonTestCase {

   public function providerGetAvailableValue() {
      $dataset = [
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '0',
                  'show_empty'      => '0',
                  'default_values'  => '',
                  'values'          => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
                  'order'           => '1',
                  'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'expectedValue'   => '1',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '0',
                  'show_empty'      => '1',
                  'default_values'  => '',
                  'values'          => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
                  'order'           => '1',
                  'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'expectedValue'   => '',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '0',
                  'show_empty'      => '0',
                  'default_values'  => '3',
                  'values'          => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
                  'order'           => '1',
                  'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'expectedValue'   => '3',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '1',
                  'show_empty'      => '0',
                  'default_values'  => '',
                  'values'          => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
                  'order'           => '1',
                  'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'expectedValue'   => '1',
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '1',
                  'show_empty'      => '1',
                  'default_values'  => '',
                  'values'          => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
                  'order'           => '1',
                  'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'expectedValue'   => '',
            'expectedIsValid' => true
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider providerGetAvailableValue
    */
   public function testFieldAvailableValue($fields, $expectedValue, $expectedValidity) {
      $question = $this->getQuestion($fields);
      $instance = $this->newTestedInstance($question);

      $availableValues = $instance->getAvailableValues();
      $expectedAvaliableValues = explode('\r\n', $fields['values']);

      $this->integer(count($availableValues))->isEqualTo(count($expectedAvaliableValues));

      foreach ($expectedAvaliableValues as $expectedValue) {
         $this->array($availableValues)->contains($expectedValue);
      }
   }

   public function providerIsValid() {
      return [
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '0',
                  'show_empty'      => '0',
                  'default_values'  => '',
                  'values'          => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
                  'order'           => '1',
                  'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'value'   => '1',
            'expected' => true
         ],
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '0',
                  'show_empty'      => '1',
                  'default_values'  => '',
                  'values'          => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
                  'order'           => '1',
                  'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'value'   => '0',
            'expected' => true
         ],
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '0',
                  'show_empty'      => '0',
                  'default_values'  => '3',
                  'values'          => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
                  'order'           => '1',
                  'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'value'   => '3',
            'expected' => true
         ],
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '1',
                  'show_empty'      => '0',
                  'default_values'  => '1',
                  'values'          => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
                  'order'           => '1',
                  'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'value'   => '1',
            'expected' => true
         ],
         [
            'fields'          => [
                  'fieldtype'       => 'select',
                  'name'            => 'question',
                  'required'        => '1',
                  'show_empty'      => '1',
                  'default_values'  => '',
                  'values'          => '1\r\n2\r\n3\r\n4\r\n5\r\n6',
                  'order'           => '1',
                  'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'value'   => '0',
            'expected' => false
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
      $this->boolean((boolean) $output)->isEqualTo($expected);
   }

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Select');
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

   public function testGetDocumentsForTarget() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $this->array($instance->getDocumentsForTarget())->hasSize(0);
   }

   public function testGetEmptyParameters() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->getEmptyParameters();
      $this->array($output)
         ->isIdenticalTo([]);
   }

   public function providerSerializeValue() {
      return [
         [
            'value'     => null,
            'expected'  => '',
         ],
         [
            'value' => '',
            'expected' => '',
         ],
         [
            'value' => 'foo',
            'expected' => "foo",
         ],
         [
            'value'     => 'test d\'apostrophe',
            'expected'  => "test d\'apostrophe",
         ],
      ];
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function testSerializeValue($value, $expected) {
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $value]);
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
            'value'     => 'foo',
            'expected'  => 'foo' ,
         ],
         [
            'value'     => 'test d\'apostrophe',
            'expected'  => 'test d\'apostrophe',
         ],
      ];
   }

   /**
    * @dataProvider providerDeserializeValue
    */
   public function testDeserializeValue($value, $expected) {
      $question = $this->getQuestion();
      $instance = $this->newTestedInstance($question);
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
}
