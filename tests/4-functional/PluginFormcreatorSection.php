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

use GlpiPlugin\Formcreator\Tests\CommonFunctionalTestCase;

class PluginFormcreatorSection extends CommonFunctionalTestCase
{
   public function testSectionCreation() {
      // Use a clean entity for the tests
      $this->login('glpi', 'glpi');

      // Create a form
      $form = $this->getForm([
         'name'          => __METHOD__ . ' ' . $this->getUniqueString(),
         'helpdesk_home' => '0',
      ]);
      $section = new \PluginFormcreatorSection();
      $existingSections = $section->find(['plugin_formcreator_forms_id' => $form->getID()]);
      $existingSectionsId = [-1];
      foreach ($existingSections as $row) {
         $existingSectionsId[] = $row['id'];
      }
      $this->boolean($form->isNewItem())->isFalse();

      // Navigate to the form designer
      $this->crawler = $this->client->request('GET', '/plugins/formcreator/front/form.form.php?id=' . $form->getID());
      $this->client->waitFor('footer');
      $this->browsing->openTab('Questions');
      $this->client->waitFor('#plugin_formcreator_form.plugin_formcreator_form_design');

      // Add a section
      $this->client->executeScript("$('#plugin_formcreator_form.plugin_formcreator_form_design li:last-of-type a').click()");
      $formSelector = 'form[data-itemtype="PluginFormcreatorSection"]';
      $this->client->waitForVisibility($formSelector);

      // Fill create section form
      $sectionForm = $this->crawler->filter($formSelector)->form();
      $name = $this->crawler->filter($formSelector . ' input[name="name"]')->attr('name');
      $sectionForm[$name] = __METHOD__ . ' ' . $this->getuniqueString();

      $this->client->submit($sectionForm);
      sleep(1);
      $newSections = $section->find([
         'plugin_formcreator_forms_id' => $form->getID(),
         'NOT' => ['id' => $existingSectionsId]
      ]);
      $this->integer(count($newSections))->isEqualTo(1);
      $newSectionId = array_pop($newSections)['id'];
      $this->client->WaitForVisibility('li[data-itemtype="PluginFormcreatorSection"][data-id="' . $newSectionId . '"]');
   }
}
