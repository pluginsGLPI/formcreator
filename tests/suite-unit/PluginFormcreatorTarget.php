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
 *
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorTarget extends CommonTestCase {

   public function addUpdateFormProvider() {
      return [
         [
            'input' => [
               'name' => '',
               'itemtype' => \PluginFormcreatorTargetTicket::class
            ],
            'expected' => false,
         ],
         [
            'input' => [
               'name' => 'should fail',
               'itemtype' => ''
            ],
            'expected' => false,
         ],
         [
            'input' => [
               'name' => 'should pass',
               'itemtype' => \PluginFormcreatorTargetTicket::class
            ],
            'expected' => true,
         ],
         [
            'input' => [
               'name' => 'être ou ne pas être',
               'itemtype' => \PluginFormcreatorTargetTicket::class
            ],
            'expected' => true,
         ],
         [
            'input' => [
               'name' => 'test d\\\'apostrophe',
               'itemtype' => \PluginFormcreatorTargetTicket::class
            ],
            'expected' => true,
         ],
      ];
   }

   /**
    * @dataProvider addUpdateFormProvider
    * @param array $input
    * @param boolean $expected
    */
   public function testPrepareInputForAdd($input, $expected) {
      $target = new \PluginFormcreatorTarget();
      $output = $target->prepareInputForAdd($input);
      if ($expected === false) {
         $this->integer(count($output))->isEqualTo(0);
      } else {
         $this->string($output['name'])->isEqualTo($input['name']);
         $this->array($output)->hasKey('uuid');
      }
   }
}
