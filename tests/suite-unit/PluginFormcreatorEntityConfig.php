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
 * @copyright Copyright © 2011 - 2020 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorEntityconfig extends CommonTestCase {
   public function providerGetTabNameForItem() {
      return [
         [
            new \Entity,
            ['1' => 'Forms'],
         ],
         [
            new \Ticket,
            [],
         ]
      ];
   }

   /**
    * @dataProvider providerGetTabNameForItem
    */
   public function testGetTabNameForItem($item, $expected) {
      $instance = $this->newTestedInstance();
      $output = $instance->getTabNameForItem($item);
      $this->array($output)->isIdenticalTo($expected);
   }

   public function testGetUsedConfig() {
      $this->login('glpi', 'glpi');
      $base = 'Root entity > ' . $this->getUniqueString();
      $entity = new \Entity();
      $entityId = $entity->add([
         'name' => $this->getUniqueString(),
         'entities_id' => '0',
      ]);
      $entityId1 = $entity->add([
         'name' => "$base > a",
         'entities_id' => $entityId,
      ]);
      $entityId2 = $entity->add([
         'name' => "b",
         'entities_id' => $entityId1,
      ]);

      $instance = $this->newTestedInstance();
      $instance->add([
         'id' => $entityId1,
         'replace_helpdesk' => \PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG,
      ]);
      $instance->add([
         'id' => $entityId2,
         'replace_helpdesk' => \PluginFormcreatorEntityconfig::CONFIG_EXTENDED_SERVICE_CATALOG,
      ]);

      $output = $instance::getUsedConfig('replace_helpdesk', $entityId1);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG);
      $output = $instance::getUsedConfig('replace_helpdesk', $entityId2);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_EXTENDED_SERVICE_CATALOG);
      $instance->update([
         'id' => $entityId2,
         'replace_helpdesk' => \PluginFormcreatorEntityconfig::CONFIG_PARENT,
      ]);
      $output = $instance::getUsedConfig('replace_helpdesk', $entityId2);
       $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG);
   }
}
