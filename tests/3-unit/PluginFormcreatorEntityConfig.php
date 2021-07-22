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

      // Create an entity with 2 sub entities
      $base = 'Root entity > ' . $this->getUniqueString();
      $entity = new \Entity();
      $entityId = $entity->add([
         'name' => $this->getUniqueString(),
         'entities_id' => '0',
      ]);
      $this->boolean($entity->isNewID($entityId))->isFalse();

      $entityId1 = $entity->add([
         'name' => "$base > a",
         'entities_id' => $entityId,
      ]);
      $this->boolean($entity->isNewID($entityId1))->isFalse();

      $entityId2 = $entity->add([
         'name' => "b",
         'entities_id' => $entityId1,
      ]);
      $this->boolean($entity->isNewID($entityId2))->isFalse();

      $entityId3 = $entity->add([
         'name' => "c",
         'entities_id' => $entityId,
      ]);
      $this->boolean($entity->isNewID($entityId3))->isFalse();

      // Set configuration for the 2 sub entities
      $instance = $this->newTestedInstance();
      $instance->add([
         'id' => $entityId,
         'replace_helpdesk' => \PluginFormcreatorEntityconfig::CONFIG_EXTENDED_SERVICE_CATALOG,
         'is_kb_separated'  => \PluginFormcreatorEntityconfig::CONFIG_KB_MERGED,
         'sort_order'       => \PluginFormcreatorEntityconfig::CONFIG_SORT_ALPHABETICAL,
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      $instance = $this->newTestedInstance();
      $instance->add([
         'id' => $entityId1,
         'replace_helpdesk' => \PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG,
         'is_kb_separated'  => \PluginFormcreatorEntityconfig::CONFIG_KB_MERGED,
         'sort_order'       => \PluginFormcreatorEntityconfig::CONFIG_SORT_ALPHABETICAL,
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      $instance = $this->newTestedInstance();
      $instance->add([
         'id' => $entityId2,
         'replace_helpdesk' => \PluginFormcreatorEntityconfig::CONFIG_EXTENDED_SERVICE_CATALOG,
         'is_kb_separated'  => \PluginFormcreatorEntityconfig::CONFIG_KB_DISTINCT,
         'sort_order'       => \PluginFormcreatorEntityconfig::CONFIG_SORT_POPULARITY,
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      $instance = $this->newTestedInstance();
      $instance->add([
         'id' => $entityId3,
         'replace_helpdesk' => \PluginFormcreatorEntityconfig::CONFIG_PARENT,
         'is_kb_separated'  => \PluginFormcreatorEntityconfig::CONFIG_PARENT,
         'sort_order'       => \PluginFormcreatorEntityconfig::CONFIG_PARENT,
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      // Test settings of entities
      $output = $instance::getUsedConfig('replace_helpdesk', $entityId1);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG);
      $output = $instance::getUsedConfig('is_kb_separated', $entityId1);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_KB_MERGED);
      $output = $instance::getUsedConfig('sort_order', $entityId1);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_SORT_ALPHABETICAL);

      $output = $instance::getUsedConfig('replace_helpdesk', $entityId2);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_EXTENDED_SERVICE_CATALOG);
      $output = $instance::getUsedConfig('is_kb_separated', $entityId2);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_KB_DISTINCT);
      $output = $instance::getUsedConfig('sort_order', $entityId2);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_SORT_POPULARITY);

      $output = $instance::getUsedConfig('replace_helpdesk', $entityId3);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_EXTENDED_SERVICE_CATALOG);
      $output = $instance::getUsedConfig('is_kb_separated', $entityId3);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_KB_MERGED);
      $output = $instance::getUsedConfig('sort_order', $entityId3);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_SORT_ALPHABETICAL);

      // Check change on parent entity propagates to child with inherited settings
      $instance = $this->newTestedInstance();
      $instance->update([
         'id' => $entityId,
         'replace_helpdesk' => \PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG,
         'is_kb_separated'  => \PluginFormcreatorEntityconfig::CONFIG_KB_DISTINCT,
         'sort_order'       => \PluginFormcreatorEntityconfig::CONFIG_SORT_POPULARITY,
      ]);

      $output = $instance::getUsedConfig('replace_helpdesk', $entityId3);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG);
      $output = $instance::getUsedConfig('is_kb_separated', $entityId3);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_KB_DISTINCT);
      $output = $instance::getUsedConfig('sort_order', $entityId3);
      $this->integer((int) $output)->isEqualTo(\PluginFormcreatorEntityconfig::CONFIG_SORT_POPULARITY);
   }

   public function testGetEnumHelpdeskMode() {
      $output = \PluginFormcreatorEntityconfig::getEnumHelpdeskMode();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorEntityconfig::CONFIG_PARENT                     => __('Inheritance of the parent entity'),
         \PluginFormcreatorEntityconfig::CONFIG_GLPI_HELPDSK               => __('GLPi\'s helpdesk', 'formcreator'),
         \PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG => __('Service catalog simplified', 'formcreator'),
         \PluginFormcreatorEntityconfig::CONFIG_EXTENDED_SERVICE_CATALOG   => __('Service catalog extended', 'formcreator'),
      ]);
   }

   public function testGetEnumSort() {
      $output = \PluginFormcreatorEntityconfig::getEnumSort();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorEntityconfig::CONFIG_PARENT            => __('Inheritance of the parent entity'),
         \PluginFormcreatorEntityconfig::CONFIG_SORT_POPULARITY   => __('Popularity sort', 'formcreator'),
         \PluginFormcreatorEntityconfig::CONFIG_SORT_ALPHABETICAL => __('Alphabetic sort', 'formcreator'),
      ]);
   }

   public function testGetEnumKbMode() {
      $output = \PluginFormcreatorEntityconfig::getEnumKbMode();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorEntityconfig::CONFIG_PARENT      => __('Inheritance of the parent entity'),
         \PluginFormcreatorEntityconfig::CONFIG_KB_MERGED   => __('Merged with Forms', 'formcreator'),
         \PluginFormcreatorEntityconfig::CONFIG_KB_DISTINCT => __('Distinct menu entry', 'formcreator'),
      ]);
   }

   public function testGetEnumSearchVisibility() {
      $output = \PluginFormcreatorEntityconfig::getEnumSearchVisibility();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorEntityconfig::CONFIG_PARENT         => __('Inheritance of the parent entity'),
         \PluginFormcreatorEntityconfig::CONFIG_SEARCH_VISIBLE => __('Visible', 'formcreator'),
         \PluginFormcreatorEntityconfig::CONFIG_SEARCH_HIDDEN  => __('Hidden', 'formcreator'),
      ]);
   }

   public function testGetEnumHeaderVisibility() {
      $output = \PluginFormcreatorEntityconfig::getEnumheaderVisibility();
      $this->array($output)->isEqualTo([
         \PluginFormcreatorEntityconfig::CONFIG_PARENT         => __('Inheritance of the parent entity'),
         \PluginFormcreatorEntityconfig::CONFIG_HEADER_VISIBLE => __('Visible', 'formcreator'),
         \PluginFormcreatorEntityconfig::CONFIG_HEADER_HIDDEN  => __('Hidden', 'formcreator'),
      ]);
   }
}
