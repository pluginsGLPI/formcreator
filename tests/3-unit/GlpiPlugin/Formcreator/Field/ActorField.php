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
use GlpiPlugin\Formcreator\Exception\ComparisonException;

class ActorField extends CommonTestCase {
   public function testGetDesignSpecializationField() {
      $instance = $this->newTestedInstance($this->getQuestion(['fieldtype' => 'actor']));
      $output = $instance->getDesignSpecializationField();
      $this->string($output['label'])->isEqualTo('');
      $this->string($output['field'])->isEqualTo('');
      $this->boolean($output['may_be_empty'])->isEqualTo(false);
      $this->boolean($output['may_be_required'])->isEqualTo(true);
   }

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
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
               'default_values'  => json_encode([]),
               'values'          => '',
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'expectedValue'   => [''],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => json_encode([]),
               'values'          => 'glpi',
               'order'           => '1',
               'show_rule'       =>\PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'expectedValue'   => [''],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => json_encode(['email@something.com']),
               'values'          => '',
               'order'           => '1',
               'show_rule'       =>\PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'expectedValue'   => ['email@something.com'],
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'actor',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => json_encode([$userId, 'email@something.com']),
               'values'          => '',
               'order'           => '1',
               'show_rule'       =>\PluginFormcreatorCondition::SHOW_RULE_ALWAYS
            ],
            'expectedValue'   => ['glpi', 'email@something.com'],
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
   public function testIsValid($fields, $expectedValue, $expectedValidity) {
      $question = $this->getQuestion($fields);
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($fields['default_values']);

      $isValid = $instance->isValid();
      $this->boolean((boolean) $isValid)->isEqualTo($expectedValidity);
   }

   public function providerSerializeValue() {
      return [
         [
            'value'     => null,
            'expected'  => json_encode([]),
         ],
         [
            'value'     => [],
            'expected'  => json_encode([]),
         ],
         [
            'value'     => ['2'],
            'expected'  => json_encode(['2']),
         ],
         [
            'value'     => ['2', '5'],
            'expected'  => json_encode(['2','5']),
         ],
         [
            'value'     => ['2', '5', 'user@localhost.local'],
            'expected'  => json_encode(['2','5','user@localhost.local']),
         ],
         [
            'value'     => ['user@localhost.local'],
            'expected'  => json_encode(['user@localhost.local']),
         ],
      ];
   }

   /**
    * @dataProvider providerSerializeValue
    */
   public function testSerializeValue($value, $expected) {
      $question = $this->getQuestion([
         'fieldtype' => 'actor'
      ]);
      $instance = $this->newTestedInstance($question);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $value]);
      $output = $instance->serializeValue();
      $this->string($output)->isEqualTo($expected);
   }

   public function providerDeserializeValue() {
      $user = new \User();
      $user->getFromDBbyName('glpi');
      $glpiId = $user->getID();
      $user->getFromDBbyName('normal');
      $normalId = $user->getID();
      return [
         [
            'value'     => null,
            'expected'  => [],
         ],
         [
            'value'     => '',
            'expected'  => [],
         ],
         [
            'value'     => json_encode(["$glpiId"]),
            'expected'  => ['glpi'],
         ],
         [
            'value'     => json_encode(["$glpiId","$normalId"]),
            'expected'  => ['glpi', 'normal'],
         ],
         [
            'value'     => json_encode(["$glpiId","$normalId","user@localhost.local"]),
            'expected'  => ['glpi', 'normal', 'user@localhost.local'],
         ],
         [
            'value'     => json_encode(["user@localhost.local"]),
            'expected'  => ['user@localhost.local'],
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
      $this->string($output)->isEqualTo(implode(', ', $expected));
   }

   public function providerGetValueForDesign() {
      $user = new \User();
      $user->getFromDBbyName('glpi');
      $glpiId = $user->getID();
      $user->getFromDBbyName('normal');
      $normalId = $user->getID();
      return [
         [
            'value' => '',
            'expected' => '',
         ],
         [
            'value'     => json_encode(["$glpiId"]),
            'expected'  => "glpi",
         ],
         [
            'value'     => json_encode(["$glpiId", "$normalId"]),
            'expected'  => "glpi\r\nnormal",
         ],
         [
            'value'     => json_encode(["$glpiId", "$normalId", "user@localhost.local"]),
            'expected'  => "glpi\r\nnormal\r\nuser@localhost.local",
         ],
         [
            'value'     => json_encode(["user@localhost.local"]),
            'expected'  => "user@localhost.local",
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

   public function providerEquals() {
      $glpiUser = new \User();
      $normalUser = new \User();
      $glpiUser->getFromDBByName('glpi');
      $normalUser->getFromDBByName('normal');

      $dataset = [
         [
            'value' => 'glpi',
            'answer' => '',
            'expected' => false,
         ],
         [
            'value' => 'glpi',
            'answer' => [$glpiUser->getID()],
            'expected' => true,
         ],
         [
            'value' => 'glpi',
            'answer' => [$glpiUser->getID(), $normalUser->getID()],
            'expected' => true,
         ],
         [
            'value' => 'glpi',
            'answer' => [$normalUser->getID()],
            'expected' => false,
         ],
         [
            'value' => 'nonexisting',
            'answer' => [$normalUser->getID()],
            'expected' => false,
         ],
         [
            'value' => 'nonexisting',
            'answer' => '',
            'expected' => false,
         ],
      ];

      return $dataset;
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
      $glpiUser = new \User();
      $normalUser = new \User();
      $glpiUser->getFromDBByName('glpi');
      $normalUser->getFromDBByName('normal');

      $dataset = [
         [
            'value' => 'glpi',
            'answer' => '',
            'expected' => true,
         ],
         [
            'value' => 'glpi',
            'answer' => [$glpiUser->getID()],
            'expected' => false,
         ],
         [
            'value' => 'glpi',
            'answer' => [$glpiUser->getID(), $normalUser->getID()],
            'expected' => false,
         ],
         [
            'value' => 'glpi',
            'answer' => [$normalUser->getID()],
            'expected' => true,
         ],
         [
            'value' => 'nonexisting',
            'answer' => [$normalUser->getID()],
            'expected' => true,
         ],
         [
            'value' => 'nonexisting',
            'answer' => '',
            'expected' => true,
         ],
      ];

      return $dataset;
   }

   /**
    * @dataProvider providerNotEquals
    */
   public function testNotEquals($value, $answer, $expected) {
      $question = $this->getQuestion(['fieldtype' => 'actor']);
      $instance = $this->newTestedInstance($question);
      $instance->parseAnswerValues(['formcreator_field_' . $question->getID() => $answer]);
      $this->boolean($instance->notEquals($value))->isEqualTo($expected);
   }

   public function testGreaterThan() {
      $this->exception(
         function() {
            $instance = $this->newTestedInstance($this->getQuestion());
            $instance->greaterThan('');
         }
      )->isInstanceOf(ComparisonException::class);
   }

   public function testLessThan() {
      $this->exception(
         function() {
            $instance = $this->newTestedInstance($this->getQuestion());
            $instance->lessThan('');
         }
      )->isInstanceOf(ComparisonException::class);
   }

   public function testIsAnonymousFormCompatible() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isFalse();
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

   public function providerIsValidValue() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $missingUserId = (new \User())->add([
         'name' => $this->getUniqueString(),
      ]);
      (new \User())->delete([
         'id' => $missingUserId,
      ]);
      return [
         'empty string' => [
            'instance' => $instance,
            'value' => '',
            'expected' => true,
         ],
         'empty array' => [
            'instance' => $instance,
            'value' => [],
            'expected' => true,
         ],
         'user ID' => [
            'instance' => $instance,
            'value' => ['4'],
            'expected' => true,
         ],
         'email' => [
            'instance' => $instance,
            'value' => ['test@foo.com'],
            'expected' => true,
         ],
         'ID and email' => [
            'instance' => $instance,
            'value' => ['4', 'test@foo.com'],
            'expected' => true,
         ],
         'invalid email' => [
            'instance' => $instance,
            'value' => ['foo'],
            'expected' => false,
         ],
         'invalid user' => [
            'instance' => $instance,
            'value' => ["$missingUserId"],
            'expected' => true,
         ],
      ];
   }

   /**
    * @dataProvider providerIsValidValue
    */
   public function testIsValidValue($instance, $value, $expected) {
      $output = $instance->isValidValue($value);
      $this->boolean($output)->isEqualTo($expected);
   }
}
