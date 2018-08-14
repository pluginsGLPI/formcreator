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

class QuestionTest extends SuperAdminTestCase {

   public function setUp() {
      parent::setUp();

      $this->formData = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'a form',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => 0
      );

      $this->sectionData = array(
            'name'                  => 'a section',
      );

      $this->questionTextData = array(
            'name'                  => 'text question',
            'fieldtype'             => 'text'
      );
   }

   public function testInitCreateForm() {
      $form = new PluginFormcreatorForm();
      $form->add($this->formData);
      $this->assertFalse($form->isNewItem());

      return $form;
   }

   /**
    * @depends testInitCreateForm
    * @param PluginFormcreatorForm  $form
    */
   public function testInitCreateSection(PluginFormcreatorForm $form) {
      $section = new PluginFormcreatorSection();
      $this->sectionData = $this->sectionData + array(
            'plugin_formcreator_forms_id' => $form->getID()
      );
      $section->add($this->sectionData);
      $this->assertFalse($section->isNewItem());

      return $section;
   }

   /**
    * @depends testInitCreateSection
    * @param PluginFormcreatorSection $section
    */
   public function testCreateQuestionText(PluginFormcreatorSection $section) {
      $question = new PluginFormcreatorQuestion();
      $this->questionTextData = $this->questionTextData  + array('plugin_formcreator_sections_id' => $section->getID());
      $question->add($this->questionTextData);
      $this->assertFalse($question->isNewItem());

      return $question;
   }

}