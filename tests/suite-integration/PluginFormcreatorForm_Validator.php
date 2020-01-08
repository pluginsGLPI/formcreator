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


class PluginFormcreatorForm_Validator extends CommonTestCase {

   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testCreateFormForGroup':
         case 'testCreateFormForUser':
            $this->boolean(self::login('glpi', 'glpi', true))->isTrue();
            break;
      }
   }

   public function testCreateFormForGroup() {
      $group = new \Group();
      $groupId = $group->import([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'completename'          => 'a group',
      ]);

      $group->getFromDB($groupId);
      $this->boolean($group->isNewItem())->isFalse();

      $form = new \PluginFormcreatorForm();
      $form->add([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => 'a form for group validator',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => \PluginFormcreatorForm_Validator::VALIDATION_GROUP,
         '_validator_groups'     => [$group->getID()]
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $form_validator = new \PluginFormcreatorForm_Validator();
      $form_validator->getFromDBForItems($form, $group);
      $this->boolean($form_validator->isNewItem())->isFalse();
   }

   public function testCreateFormForUser() {
      $user = new \User;
      $user->getFromDBbyName('tech');
      $this->boolean($user->isNewItem())->isFalse();

      $form = new \PluginFormcreatorForm();
      $form->add([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => 'a form for user validator',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => \PluginFormcreatorForm_Validator::VALIDATION_USER,
         '_validator_users'     => [$user->getID()]
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $form_validator = new \PluginFormcreatorForm_Validator();
      $form_validator->getFromDBForItems($form, $user);
      $this->boolean($form_validator->isNewItem())->isFalse();
   }
}