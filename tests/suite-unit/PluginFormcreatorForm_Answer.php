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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorForm_Answer extends CommonTestCase {

   public function providerPrepareInputForAdd() {
      $testme = "être ou pas, test d\\'aposrophe";
      $form = $this->createForm([
         'name' => $testme,
      ]);

      return [
         [
            'input' => [
               \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
               'description'  => "être ou pas, test d\\'aposrophe",
               'content'      => "&lt;p&gt;être ou pas, test d\\'apostrophe&lt;/p&gt;",
            ],
            'expected' => [
               \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
               'description'  => "être ou pas, test d\\'aposrophe",
               'content'      => "&lt;p&gt;être ou pas, test d\\'apostrophe&lt;/p&gt;",
               'name'         => $testme,
            ],
         ],
         [
            'input' => [
               \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
               'description'  => "être ou pas, test d\\'aposrophe",
               'content'      => "&lt;p&gt;être ou pas, test d\\'apostrophe&lt;/p&gt;",
            ],
            'expected' => [
               \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
               'description'  => "être ou pas, test d\\'aposrophe",
               'content'      => "&lt;p&gt;être ou pas, test d\\'apostrophe&lt;/p&gt;",
               'name'         => $testme,
            ],
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareInputForAdd
    * @engine inline
    */
   public function testPrepareInputForAdd($input, $expected) {
      $instance = $this->newTestedInstance();
      $output = $instance->prepareInputForAdd($input);
      $this->array($output)->size->isEqualTo(count($expected));
      $this->integer((int) $output[\PluginFormcreatorForm::getForeignKeyField()])->isEqualTo($expected[\PluginFormcreatorForm::getForeignKeyField()]);
      $this->string($output['description'])->isEqualTo($expected['description']);
      $this->string($output['content'])->isEqualTo($expected['content']);
   }
}