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

class PluginFormcreatorCategory extends CommonTestCase {
   public function providerGetTypeName() {
      return [
         [
            0,
            'Form categories'
         ],
         [
            1,
            'Form category'
         ],
         [
            2,
            'Form categories'
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

   public function testDefineTabs() {
      $instance = $this->newTestedInstance();
      $output = $instance->defineTabs();
      $expected = [
         'PluginFormcreatorCategory$main' => "Form category",
         'PluginFormcreatorCategory$1' => 'Form categories',
         'Log$1' => "Historical",
      ];
      $this->array($output)
         ->isEqualTo($expected)
         ->hasSize(count($expected));
   }

   public function testGetCategoryTree() {
      $this->login('glpi', 'glpi');

      // create a sub entity which will take in the forms and cateory for this test
      // and not conflict with previous data
      $entity      = new \Entity();
      $rand        = mt_rand();
      $entities_id = $entity->add([
         'name'        => "test formcreator sub entity $rand",
         'entities_id' => 0
      ]);

      // create some categories for forms
      $category   = new \PluginFormcreatorCategory;
      $categories = [];
      for ($i = 0; $i < 5; $i++) {
         $root_cat = $category->add([
            'name' => "test category root $i",
         ]);
         $categories[] = $root_cat;

         $sub_cat = $category->add([
            'name'                             => "test sub category $i",
            'plugin_formcreator_categories_id' => $root_cat
         ]);
         $categories[] = $sub_cat;
      }

      // create some forms
      $form = new \PluginFormcreatorForm;
      for ($i = 0; $i < 10; $i++) {
         $form->add([
            'name'                             => "testgetCategoryTree form $i",
            'entities_id'                      => $entities_id,
            'is_active'                        => 1,
            'helpdesk_home'                    => 1,
            'plugin_formcreator_categories_id' => $categories[$i]
         ]);
      }

      // Set active entity
      \Session::changeActiveEntities($entities_id, true);

      //test method
      $tree = \PluginFormcreatorCategory::getCategoryTree(0, true);
      $this->array($tree)
         ->isNotEmpty()
         ->child['subcategories'](function($child) {
            $child->size->isGreaterThanOrEqualTo(5);
         });

      foreach ($tree['subcategories'] as $subcategory) {
         $this->array($subcategory)
            ->hasKeys(['name', 'parent', 'id', 'subcategories']);
      }

      // return to root entity
      \Session::changeActiveEntities(0, true);
   }
}
