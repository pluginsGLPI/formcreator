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
use PluginFormcreatorForm;
use PluginFormcreatorFormAnswer;
use PluginFormcreatorForm_Validator;
use User;

class PluginFormcreatorFormanswerValidation extends CommonTestCase {
   public function providerGetCurentValidationLevel() {
       $form = $this->getForm();
       $form_fk = PluginFormcreatorForm::getForeignKeyField();
       $formValidator = new PluginFormcreatorForm_Validator();
       $formValidator->add([
           $form_fk => $form->getID(),
           'itemtype' => User::class,
           'items_id' => User::getIdByName('glpi'),
           'level'    => 1,
       ]);
       $this->boolean($formValidator->isNewItem())->isFalse();

       $formValidator = new PluginFormcreatorForm_Validator();
       $formValidator->add([
           $form_fk => $form->getID(),
           'itemtype' => User::class,
           'items_id' => User::getIdByName('normal'),
           'level'    => 2,
       ]);
       $this->boolean($formValidator->isNewItem())->isFalse();

       $formValidator = new PluginFormcreatorForm_Validator();
       $formValidator->add([
           $form_fk => $form->getID(),
           'itemtype' => User::class,
           'items_id' => User::getIdByName('tech'),
           'level'    => 3,
       ]);

       $formanswer = new PluginFormcreatorFormAnswer();
       $formanswer->add([
           'plugin_formcreator_forms_id' => $form->getID(),
           'requester_id' => User::getIdByName('glpi'),
           'validator_id' => User::getIdByName('glpi'),
       ]);
       $this->boolean($formanswer->isNewItem())->isFalse();

       yield [
           'formanswer' => $formanswer,
           'expected' => 1
       ];

       $formanswervalidation = $this->newTestedInstance();
       $formanswervalidation->updateValidationStatus($formanswer, PluginFormcreatorForm_Validator::VALIDATION_STATUS_ACCEPTED);

       yield [
           'formanswer' => $formanswer,
           'expected' => 2
       ];

       $formanswervalidation = $this->newTestedInstance();
       $formanswervalidation->updateValidationStatus($formanswer, PluginFormcreatorForm_Validator::VALIDATION_STATUS_ACCEPTED);

       yield [
           'formanswer' => $formanswer,
           'expected' => 3
       ];
   }

    /**
     * @dataProvider providerGetCurentValidationLevel
     *
     * @param PluginFormcreatorFormAnswer $formanswer
     * @return void
     */
   public function testGetCurrentValidationLevel($formanswer, $expected) {
       $formanswervalidation = $this->newTestedInstance();
       $output = $formanswervalidation->getCurrentValidationLevel($formanswer);
       $this->integer($output)->isEqualTo($expected);
   }

   public function testUpdateValidationStatus() {
       $form = $this->getForm();
       $form_fk = PluginFormcreatorForm::getForeignKeyField();
       $formValidator = new PluginFormcreatorForm_Validator();
       $formValidator->add([
           $form_fk => $form->getID(),
           'itemtype' => User::class,
           'items_id' => User::getIdByName('glpi'),
           'level'    => 1,
       ]);
       $this->boolean($formValidator->isNewItem())->isFalse();

       $formValidator = new PluginFormcreatorForm_Validator();
       $formValidator->add([
           $form_fk => $form->getID(),
           'itemtype' => User::class,
           'items_id' => User::getIdByName('normal'),
           'level'    => 2,
       ]);
       $this->boolean($formValidator->isNewItem())->isFalse();

       $formValidator = new PluginFormcreatorForm_Validator();
       $formValidator->add([
           $form_fk => $form->getID(),
           'itemtype' => User::class,
           'items_id' => User::getIdByName('tech'),
           'level'    => 3,
       ]);

       $formanswer = new PluginFormcreatorFormAnswer();
       $formanswer->add([
           'plugin_formcreator_forms_id' => $form->getID(),
           'requester_id' => User::getIdByName('glpi'),
           'validator_id' => User::getIdByName('glpi'),
       ]);
       $this->boolean($formanswer->isNewItem())->isFalse();

       $testedClassName = $this->getTestedClassName();
       $testedClassName::updateValidationStatus($formanswer, PluginFormcreatorForm_Validator::VALIDATION_STATUS_ACCEPTED);

       $formanswervalidation = $this->newTestedInstance();
       $rows = $formanswervalidation->find([
           'plugin_formcreator_formanswers_id' => $formanswer->getID(),
           'level'                             => 1,
       ]);
       $this->array($rows)->hasSize(1);
      foreach ($rows as $row) {
          $this->integer($row['status'])->isEqualTo(PluginFormcreatorForm_Validator::VALIDATION_STATUS_ACCEPTED);
      }
       $rows = $formanswervalidation->find([
           'plugin_formcreator_formanswers_id' => $formanswer->getID(),
           'level'                             => ['>', 1],
       ]);
       $this->array($rows)->hasSize(2);
      foreach ($rows as $row) {
          $this->integer($row['status'])->isEqualTo(PluginFormcreatorForm_Validator::VALIDATION_STATUS_WAITING);
      }

       $formanswervalidation = $this->newTestedInstance();
       $testedClassName::updateValidationStatus($formanswer, PluginFormcreatorForm_Validator::VALIDATION_STATUS_REFUSED);

       // Check level is stil accepted
       $rows = $formanswervalidation->find([
           'plugin_formcreator_formanswers_id' => $formanswer->getID(),
           'level'                             => 1,
       ]);
       $this->array($rows)->hasSize(1);
      foreach ($rows as $row) {
          $this->integer($row['status'])->isEqualTo(PluginFormcreatorForm_Validator::VALIDATION_STATUS_ACCEPTED);
      }
       $rows = $formanswervalidation->find([
           'plugin_formcreator_formanswers_id' => $formanswer->getID(),
           'level'                             => 2,
       ]);
       $this->array($rows)->hasSize(1);
      foreach ($rows as $row) {
          $this->integer($row['status'])->isEqualTo(PluginFormcreatorForm_Validator::VALIDATION_STATUS_REFUSED);
      }
       $rows = $formanswervalidation->find([
           'plugin_formcreator_formanswers_id' => $formanswer->getID(),
           'level'                             => ['>', 2],
       ]);
       $this->array($rows)->hasSize(1);
      foreach ($rows as $row) {
          $this->integer($row['status'])->isEqualTo(PluginFormcreatorForm_Validator::VALIDATION_STATUS_WAITING);
      }
   }
}