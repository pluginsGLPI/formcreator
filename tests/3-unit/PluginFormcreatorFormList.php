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

class PluginFormcreatorFormList extends CommonTestCase {
   public function providerGetTypeName() {
      return [
         [
            0,
            'Forms'
         ],
         [
            1,
            'Form'
         ],
         [
            2,
            'Forms'
         ],
      ];
   }

   /**
    * @dataProvider providerGetTypeName
    *
    * @param integer $nb
    * @param string $expected
    * @return void
    */
   public function testGetTypeName($nb, $expected) {
      $instance = new $this->newTestedInstance();
      $output = $instance->getTypeName($nb);
      $this->string($output)->isEqualTo($expected);
   }

   public function testGetMenuContent() {
      $output = \PluginFormcreatorFormList::getMenuContent();
      $plugindir = '/' . basename(dirname(dirname(dirname(__DIR__))));
      $this->string($output['links']['search'])->isEqualTo($plugindir . '/formcreator/front/formlist.php');
      $this->array($output['links'])->notHasKey('add');
      $this->string($output['links']['config'])->isEqualTo($plugindir . '/formcreator/front/form.php');

      $this->login('glpi', 'glpi');
      $output = \PluginFormcreatorFormList::getMenuContent();
      $this->string($output['links']['search'])->isEqualTo($plugindir . '/formcreator/front/formlist.php');
      $this->string($output['links']['add'])->isEqualTo($plugindir . '/formcreator/front/form.form.php');
      $this->string($output['links']['config'])->isEqualTo($plugindir . '/formcreator/front/form.php');
   }
}
