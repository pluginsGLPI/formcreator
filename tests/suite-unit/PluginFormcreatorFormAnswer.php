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
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorFormAnswer extends CommonTestCase {
   public function testGetFullForm() {
      $form = $this->getForm();
      $section = $this->getSection([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
         'name' => \Toolbox::addslashes_deep("section '1'"),
      ]);
      $question = $this->getQuestion([
         \PluginFormcreatorSection::getForeignKeyField() => $section->getID(),
         'name' => \Toolbox::addslashes_deep("question '1'"),
      ]);

      $formAnswerId = $form->saveForm([
         \PluginFormcreatorForm::getForeignKeyField() => $form->getID(),
         'formcreator_field_' . $question->getID() => ''
      ]);

      $instance = $this->newTestedInstance();
      $instance->getFromDB($formAnswerId);
      $questionId = $question->getID();

      if (version_compare(GLPI_VERSION, '9.4') < 0) {
         $output = $instance->getFullForm(false);
         // Test the encoding of new lines
         $this->string($output)->contains(
            "Form data\r\n" .
            "=================\r\n"
         );
         $this->string($output)->contains("section '1'");
         $this->string($output)->contains("##question_$questionId##");
      }

      $output = $instance->getFullForm(true);
      $this->string($output)->contains("section '1'");
      $this->string($output)->contains("##question_$questionId##");

   }
}
