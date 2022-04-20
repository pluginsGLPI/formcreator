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

use AuthLDAP;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;
class LdapSelectField extends CommonTestCase {

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('LDAP Select');
   }

   public function testisPublicFormCompatible() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->isPublicFormCompatible();
      $this->boolean($output)->isFalse();
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
            'expected'  => "test d'apostrophe",
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
      $instance = $this->newTestedInstance($this->getQuestion());
      $instance->deserializeValue($value);
      $output = $instance->getValueForTargetText('', false);
      $this->string($output)->isEqualTo($expected);
   }

   public function providergetValueForDesign() {
      return [
         [
            'value' => '',
            'expected' => '',
         ],
      ];
   }

   /**
    * @dataProvider providergetValueForDesign
    */
   public function testGetValueForDesign($value, $expected) {
      $instance = $this->newTestedInstance($this->getQuestion());
      $instance->deserializeValue($value);
      $output = $instance->getValueForDesign();
      $this->string($output)->isEqualTo($expected);
   }


   public function providerPrepareQuestionInputForSave() {
      $authLdap = new AuthLDAP();
      $authLdap->add([]);

      return [
         [
            'question' => $this->getQuestion([
               'ldap_auth' => $authLdap->getID(),
               'fieldtype' => 'ldapselect',
               'ldap_filter' => '',
               'ldap_attribute' => '1',
            ]),
            'input' => [
               'ldap_auth'      => $authLdap->getID(),
               'ldap_filter'    => 'по',
               'ldap_attribute' => '1',
            ],
            'expected' => [
               'values' => json_encode([
                  'ldap_auth'      => $authLdap->getID(),
                  'ldap_attribute' => '1',
                  'ldap_filter'    => 'по',
               ], JSON_UNESCAPED_UNICODE),
            ]
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareQuestionInputForSave
    *
    * @param \PluginFormcreatorQuestion $question
    * @param array $input
    * @param array $expected
    * @return void
    */
   public function testPrepareQuestionInputForSave(\PluginFormcreatorQuestion $question, array $input, array $expected) {
      $instance = $this->newTestedInstance($question);

      $output = $instance->prepareQuestionInputForSave($input);
      $this->array($output)->isEqualTo($expected);
   }
}
