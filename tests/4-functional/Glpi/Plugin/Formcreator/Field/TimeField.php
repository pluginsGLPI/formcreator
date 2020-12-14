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

namespace GlpiPlugin\Formcreator\Field\tests\units;

use GlpiPlugin\Formcreator\Tests\CommonFunctionalTestCase;
use GlpiPlugin\Formcreator\Tests\CommonQuestionTest;

class TimeField extends CommonFunctionalTestCase
{
   use CommonQuestionTest;

   public function testCreateForm() {
      // Use a clean entity for the tests
      $this->login('glpi', 'glpi');

      // Create a form and a section
      $section = $this->getSection([
         'name'          => __METHOD__ . ' ' . $this->getUniqueString(),
         'helpdesk_home' => '0',
      ]);
      $this->boolean($section->isNewItem())->isFalse();
      $form = new \PluginFormcreatorForm();
      $form->getFromDBBySection($section);
      $this->boolean($form->isNewItem())->isFalse();

      // navigate to the form designer
      $this->crawler = $this->client->request('GET', '/plugins/formcreator/front/form.form.php?id=' . $form->getID());
      $this->client->waitFor('footer');
      $this->browsing->openTab('Questions');
      $this->client->waitFor('#plugin_formcreator_form.plugin_formcreator_form_design');

      // show create question form
      $link = $this->crawler->filter('.plugin_formcreator_section .plugin_formcreator_question:not([data-id]) a');
      $this->crawler = $this->client->click($link->link());
      $this->client->waitForVisibility('form[data-itemtype="PluginFormcreatorQuestion"]');
      $this->client->executeScript(
         '$(\'form[data-itemtype="PluginFormcreatorQuestion"] [name="fieldtype"]\').val("time")
         $(\'form[data-itemtype="PluginFormcreatorQuestion"] [name="fieldtype"]\').select2().trigger("change")
         '
      );

      $this->client->waitForVisibility('form[data-itemtype="PluginFormcreatorQuestion"] select[name="required"]');
      $this->client->waitFor('form[data-itemtype="PluginFormcreatorQuestion"] input[name="default_values"]');

      $this->_testQuestionCreated($form, __METHOD__);
   }
}
