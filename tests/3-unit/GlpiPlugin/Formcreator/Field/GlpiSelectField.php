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

class GlpiselectField extends CommonTestCase {

   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testIsValid':
            $this->login('glpi', 'glpi');
            break;
      }
   }

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('GLPI object');
   }

   public function providerGetAnswer() {
      $user = new \User();
      $user->add([
         'name' => $this->getUniqueString(),
         'realname' => 'John',
         'firstname' => 'Doe',
      ]);
      $this->boolean($user->isNewItem())->isFalse();

      $computer = new \Computer();
      $computer->add([
         'name' => $this->getUniqueString(),
         \Entity::getForeignKeyField() => 0,
      ]);
      $this->boolean($computer->isNewItem())->isFalse();

      $dataset = [
         [
            'fields'          => [
               'fieldtype'       => 'glpiselect',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $user->getID(),
               'glpi_objects'     => \User::class,
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => true,
               '_parameters'     => [],
            ],
            'expectedValue'   => (new \DbUtils())->getUserName($user->getID()),
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'glpiselect',
               'name'            => 'question',
               'required'        => '1',
               'default_values'  => '',
               'glpi_objects'    => \User::class,
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => true,
               '_parameters'     => [],
            ],
            'expectedValue'   => '',
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'glpiselect',
               'name'            => 'question',
               'required'        => '1',
               'default_values'  => $user->getID(),
               'glpi_objects'    => \User::class,
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => false,
               '_parameters'     => [],
            ],
            'expectedValue'   => (new \DbUtils())->getUserName($user->getID()),
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'glpiselect',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '0',
               'glpi_objects'    => \User::class,
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => false,
               '_parameters'     => [],
            ],
            'expectedValue'   => '',
            'expectedIsValid' => true
         ],

         [
            'fields'          => [
               'fieldtype'       => 'glpiselect',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $computer->getID(),
               'glpi_objects'    => \Computer::class,
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => true,
               '_parameters'     => [],
            ],
            'expectedValue'   => $computer->getName(),
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'glpiselect',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'glpi_objects'    => \Computer::class,
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => true,
               '_parameters'     => [],
            ],
            'expectedValue'   => '&nbsp;',
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'glpiselect',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => $computer->getID(),
               'glpi_objects'    => \Computer::class,
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => false,
               '_parameters'     => [],
            ],
            'expectedValue'   => $computer->getName(),
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'glpiselect',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '',
               'glpi_objects'    => \Computer::class,
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => false,
               '_parameters'     => [],
            ],
            'expectedValue'   => '&nbsp;',
            'expectedIsValid' => false
         ],
         [
            'fields'          => [
               'fieldtype'       => 'glpiselect',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '0',
               'glpi_objects'     => \Entity::class,
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => true,
               '_parameters'     => [],
            ],
            'expectedValue'   => (new \Entity())->getFromDB(0),
            'expectedIsValid' => true
         ],
         [
            'fields'          => [
               'fieldtype'       => 'glpiselect',
               'name'            => 'question',
               'required'        => '0',
               'default_values'  => '-1',
               'glpi_objects'     => \Entity::class,
               'order'           => '1',
               'show_rule'       => \PluginFormcreatorCondition::SHOW_RULE_ALWAYS,
               'show_empty'      => true,
               '_parameters'     => [],
            ],
            'expectedValue'   => '&nbsp;',
            'expectedIsValid' => true
         ],
      ];

      return $dataset;
   }

   public function providerIsValid() {
      return $this->providerGetAnswer();
   }

   /**
    * @dataProvider providerIsValid
    */
   public function testIsValid($fields, $expectedValue, $expectedValidity) {
      $question = $this->getQuestion($fields);
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($fields['default_values']);

      $output = $instance->isValid();
      $this->boolean($output)->isEqualTo($expectedValidity);
   }

   public function testIsAnonymousFormCompatible() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isAnonymousFormCompatible();
      $this->boolean($output)->isFalse();
   }

   public function testGetValueForTargetText() {
      $computer = new \Computer();
      $computer->add([
         'name' => 'computer foo',
         'entities_id' => 0,
      ]);

      // Create a question glpi Object / computer
      $question = $this->getQuestion([
         'fieldtype'    => 'glpiselect',
         'glpi_objects' => \Computer::class,
      ]);
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($computer->getID());

      // test for the target text
      $output = $instance->getValueForTargetText('', true);
      $this->string($output)->isEqualTo('computer foo');

      // Create a user with first and last name
      $user = new \User();
      $user->add([
         'name'       => 'foobar' . $this->getUniqueString(),
         'firstname'  => 'foo',
         'realname'   => 'bar',
      ]);
      $this->boolean($user->isNewItem())->isFalse();

      // Create a question glpi Object / User
      $question = $this->getQuestion([
         'fieldtype'    => 'glpiselect',
         'glpi_objects' => \User::class,
      ]);
      $instance = $this->newTestedInstance($question);
      $instance->deserializeValue($user->getID());

      // test the text for target
      $output = $instance->getValueForTargetText('', true);
      $this->string($output)->isEqualTo('bar foo');
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
}
