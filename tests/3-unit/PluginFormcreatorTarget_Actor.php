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

namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorTarget_Actor extends CommonTestCase {
   public function testGetEnumActorType() {
      $output = \PluginFormcreatorTarget_Actor::getEnumActorType();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHOR                 => __('Form author', 'formcreator'),
         \PluginFormcreatorTarget_Actor::ACTOR_TYPE_VALIDATOR              => __('Form validator', 'formcreator'),
         \PluginFormcreatorTarget_Actor::ACTOR_TYPE_PERSON                 => __('Specific person', 'formcreator'),
         \PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_PERSON        => __('Person from the question', 'formcreator'),
         \PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP                  => __('Specific group', 'formcreator'),
         \PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_GROUP         => __('Group from the question', 'formcreator'),
         \PluginFormcreatorTarget_Actor::ACTOR_TYPE_GROUP_FROM_OBJECT      => __('Group from an object', 'formcreator'),
         \PluginFormcreatorTarget_Actor::ACTOR_TYPE_TECH_GROUP_FROM_OBJECT => __('Tech group from an object', 'formcreator'),
         \PluginFormcreatorTarget_Actor::ACTOR_TYPE_SUPPLIER               => __('Specific supplier', 'formcreator'),
         \PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_SUPPLIER      => __('Supplier from the question', 'formcreator'),
         \PluginFormcreatorTarget_Actor::ACTOR_TYPE_QUESTION_ACTORS        => __('Actors from the question', 'formcreator'),
         \PluginFormcreatorTarget_Actor::ACTOR_TYPE_AUTHORS_SUPERVISOR     => __('Form author\'s supervisor', 'formcreator'),
      ]);
   }

   public function testGetEnumRole() {
      $output = \PluginFormcreatorTarget_Actor::getEnumRole();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorTarget_Actor::ACTOR_ROLE_REQUESTER => __('Requester'),
         \PluginFormcreatorTarget_Actor::ACTOR_ROLE_OBSERVER  => __('Observer'),
         \PluginFormcreatorTarget_Actor::ACTOR_ROLE_ASSIGNED  => __('Assigned to'),
      ]);
   }

   public function providerGetTypeName() {
      return [
         [
            'input' => 0,
            'expected' => 'Target actors',
         ],
         [
            'input' => 1,
            'expected' => 'Target actor',
         ],
         [
            'input' => 2,
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
      $output = \PluginFormcreatorTarget_Actor::getTypeName($number);
      $this->string($output)->isEqualTo($expected);
   }

   public function testCountItemsToImport() {
      $output = \PluginFormcreatorTarget_Actor::countItemsToImport([]);
      $this->integer($output)->isEqualTo(1);
   }
}