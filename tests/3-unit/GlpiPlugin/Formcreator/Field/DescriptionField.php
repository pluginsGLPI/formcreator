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

class DescriptionField extends CommonTestCase {

   public function testIsValid() {
      $instance = $this->newTestedInstance($this->getQuestion());
      $this->boolean($instance->isValid(''))->isTrue();
   }

   public function testGetName() {
      $itemtype = $this->getTestedClassName();
      $output = $itemtype::getName();
      $this->string($output)->isEqualTo('Description');
   }

   public function providerPrepareQuestionInputForSave() {
      return [
         'empty description' => [
            'input' => [
               'name' => $this->getUniqueString(),
               'description' => ''
            ],
            'expected' => [
            ],
            'message' => 'A description field should have a description:',
         ],
         'escaping test' => [
            'input' => [
               'name' => "test d'apostrophe",
               'description' => "test d'apostrophe",
            ],
            'expected' => [
               'name' => "test d'apostrophe",
               'description' => "test d'apostrophe",
            ],
            'message' => 'A description field should have a description:',
         ],
      ];
   }
   /**
    * @dataProvider providerPrepareQuestionInputForSave
    */
   public function testPrepareQuestionInputForSave($input, $expected, $message) {
      $instance = $this->newTestedInstance($this->getQuestion());
      $output = $instance->prepareQuestionInputForSave($input);
      if (count($expected) === 0 || $expected === false) {
         $this->string($_SESSION["MESSAGE_AFTER_REDIRECT"][ERROR][0])
            ->isEqualTo($message . ' ' . $input['name']);
         $this->array($output)->isEmpty();
      } else {
         $this->array($output)->hasSize(count($expected));
         foreach ($expected as $key => $value) {
            $this->array($output)->hasKey($key)
               ->variable($output[$key])->isIdenticalTo($value);
         }
      }
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
      $this->boolean($output)->isFalse();
   }
}
