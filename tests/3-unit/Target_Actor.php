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

namespace tests\units\GlpiPlugin\Formcreator;

use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class Target_Actor extends CommonTestCase {
   public function testGetEnumActorType() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumActorType();
      $this->array($output)->isEqualTo([
         $testedClass::ACTOR_TYPE_AUTHOR                 => __('Form author', 'formcreator'),
         $testedClass::ACTOR_TYPE_VALIDATOR              => __('Form validator', 'formcreator'),
         $testedClass::ACTOR_TYPE_PERSON                 => __('Specific person', 'formcreator'),
         $testedClass::ACTOR_TYPE_QUESTION_PERSON        => __('Person from the question', 'formcreator'),
         $testedClass::ACTOR_TYPE_GROUP                  => __('Specific group', 'formcreator'),
         $testedClass::ACTOR_TYPE_QUESTION_GROUP         => __('Group from the question', 'formcreator'),
         $testedClass::ACTOR_TYPE_GROUP_FROM_OBJECT      => __('Group from an object', 'formcreator'),
         $testedClass::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT => __('Tech group from an object', 'formcreator'),
         $testedClass::ACTOR_TYPE_SUPPLIER               => __('Specific supplier', 'formcreator'),
         $testedClass::ACTOR_TYPE_QUESTION_SUPPLIER      => __('Supplier from the question', 'formcreator'),
         $testedClass::ACTOR_TYPE_QUESTION_ACTORS        => __('Actors from the question', 'formcreator'),
         $testedClass::ACTOR_TYPE_AUTHORS_SUPERVISOR     => __('Form author\'s supervisor', 'formcreator'),
      ]);
   }

   public function testGetEnumRole() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getEnumRole();
      $this->array($output)->isEqualTo([
         $testedClass::ACTOR_ROLE_REQUESTER => __('Requester'),
         $testedClass::ACTOR_ROLE_OBSERVER  => __('Observer'),
         $testedClass::ACTOR_ROLE_ASSIGNED  => __('Assigned to'),
      ]);
   }

   public function providerGetTypeName() {
      return [
         [
            'number' => 0,
            'expected' => 'Target actors',
         ],
         [
            'number' => 1,
            'expected' => 'Target actor',
         ],
         [
            'number' => 2,
            'expected' => 'Target actors',
         ],
      ];
   }

   /**
    * @dataProvider providerGetTypeName
    * @param integer $number
    * @param string $expected
    */
   public function testGetTypeName($number, $expected) {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::getTypeName($number);
      $this->string($output)->isEqualTo($expected);
   }

   public function testCountItemsToImport() {
      $testedClass = $this->getTestedClassName();
      $output = $testedClass::countItemsToImport([]);
      $this->integer($output)->isEqualTo(1);
   }
}
