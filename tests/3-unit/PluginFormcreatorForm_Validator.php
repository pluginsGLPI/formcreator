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
use User;
use Group;

class PluginFormcreatorForm_Validator extends CommonTestCase {
   public function testPrepareInputForAdd() {
      $instance = $this->newTestedInstance();
      $output = $instance->prepareInputForAdd([
         'plugin_formcreator_forms_id' => 1,
         'itemtype' => User::class,
         'users_id' => 2,
         'uuid' => '0000',
      ]);

      $this->array($output)->HasKey('uuid');
      $this->string($output['uuid'])->isEqualTo('0000');
   }

   public function testExport() {
      $user = new \User;
      $user->getFromDBbyName('glpi');

      $form = $this->getForm();

      $form_validator = $this->newTestedInstance();
      $testedClass = $this->getTestedClassName();
      $form_validator->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'itemtype'                    => User::class,
         'users_id'                    => $user->getID(),
      ]);
      $this->boolean($form_validator->isNewItem())->isFalse();
      $instance = $this->newTestedInstance();

      // Try to export an empty item
      $this->exception(function () use ($instance) {
         $instance->export();
      })->isInstanceOf(\GlpiPlugin\Formcreator\Exception\ExportFailureException::class);

      $instance->getFromDBByCrit([
         'plugin_formcreator_forms_id' => $form->getID(),
         'itemtype' => \User::class,
         'items_id' => $user->getID(),
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      // Export the item without the ID and with UUID
      $output = $instance->export(false);

      // Test the exported data
      $fieldsWithoutID = [
         'itemtype',
      ];
      $extraFields = [
         '_item',
      ];
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['uuid'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));
      $this->array($output)->isEqualTo([
         'itemtype' => \User::class,
         '_item' => $user->fields['name'],
         'uuid'  => $instance->fields['uuid'],
      ]);

      // Export the item without the UUID and with ID
      $output = $instance->export(true);
      $this->array($output)
         ->hasKeys($fieldsWithoutID + $extraFields + ['id'])
         ->hasSize(1 + count($fieldsWithoutID) + count($extraFields));
      $this->array($output)->isEqualTo([
         'itemtype' => \User::class,
         '_item' => $user->fields['name'],
         'id'  => $instance->fields['id'],
      ]);
   }

   public function testImport() {
      $linker = new \PluginFormcreatorLinker();
      $input = [
         'itemtype' => \User::class,
         '_item' => 'normal',
         'uuid' => plugin_formcreator_getUuid(),
      ];
      $form = $this->getForm();
      $formId = $form->getID();
      $formValidatorId = \PluginFormcreatorForm_Validator::import($linker, $input, $formId);
      $validId = \PluginFormcreatorForm_Validator::isNewId($formValidatorId);
      $this->boolean($validId)->isFalse();
   }

   public function testGetValidatorsForForm() {
      $form = $this->getForm();

      $formValidator = $this->newTestedInstance();
      // Test when form has no validation
      $output = $formValidator::getValidatorsForForm($form);
      $this->array($output)->hasSize(0);

      $groupA = $this->getGlpiCoreItem(\group::class, [
         'name' => 'group A' . $this->getUniqueString()
      ]);
      $groupB = $this->getGlpiCoreItem(\group::class, [
         'name' => 'group B' . $this->getUniqueString()
      ]);

      $userA = $this->getGlpiCoreItem(\User::class, [
         'name' => 'user A' . $this->getUniqueString(),
      ]);
      $userB = $this->getGlpiCoreItem(\User::class, [
         'name' => 'user B' . $this->getUniqueString(),
      ]);
      $userC = $this->getGlpiCoreItem(\User::class, [
         'name' => 'user C' . $this->getUniqueString(),
      ]);
      $userD = $this->getGlpiCoreItem(\User::class, [
         'name' => 'user D' . $this->getUniqueString(),
      ]);

      // Add users into groups
      $this->getGlpiCoreItem(\Group_User::class, [
         'users_id' => $userA->getID(),
         'groups_id' => $groupA->getID(),
      ]);
      $this->getGlpiCoreItem(\Group_User::class, [
         'users_id' => $userB->getID(),
         'groups_id' => $groupB->getID(),
      ]);

      $formValidator->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'itemtype' => $groupA->gettype(),
         'groups_id' => $groupA->getID(),
      ]);
      $this->boolean($formValidator->isNewItem())->isFalse();
      $formValidator->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'itemtype' => $groupB->gettype(),
         'groups_id' => $groupB->getID(),
      ]);
      $this->boolean($formValidator->isNewItem())->isFalse();

      $formValidator->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'itemtype'                    => $userC::getType(),
         'users_id'                    => $userC->getID(),
      ]);
      $this->boolean($formValidator->isNewItem())->isFalse();
      $formValidator->add([
         'plugin_formcreator_forms_id' => $form->getID(),
         'itemtype'                    => $userD::getType(),
         'users_id'                    => $userD->getID(),
      ]);
      $this->boolean($formValidator->isNewItem())->isFalse();
      $output = $formValidator::getValidatorsForForm($form);

      $this->array($output)->hasSize(2);
      $this->array($output[User::class])
         ->hasKeys([
            $userC->getID(),
            $userD->getID(),
         ])->hasSize(2);
      $this->array($output[Group::class])
         ->hasKeys([
            $groupA->getID(),
            $groupB->getID(),
         ])->hasSize(2);
   }
}
