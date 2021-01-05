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

class IpField extends CommonTestCase {

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('IP address');
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

   public function providerEquals() {
      return [
         [
            'value'     => '',
            'answer'    => '',
            'expected'  => true,
         ],
         [
            'value'     => '127.0.1.1',
            'answer'    => '127.0.0.1',
            'expected'  => false,
         ],
         [
            'value'     => '127.0.0.1',
            'answer'    => '127.0.0.1',
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
            'value'     => '127.0.1.1',
            'answer'    => '127.0.0.1',
            'expected'  => true,
         ],
         [
            'value'     => '127.0.0.1',
            'answer'    => '127.0.0.1',
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
}
