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

class Form_ValidatorTest extends SuperAdminTestCase {

   public function setUp() {
      parent::setUp();

      $this->formDataForGroup = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'a form for group validator',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => PluginFormcreatorForm_Validator::VALIDATION_GROUP
      );

      $this->formDataForUser = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'name'                  => 'a form for user validator',
            'description'           => 'form description',
            'content'               => 'a content',
            'is_active'             => 1,
            'validation_required'   => PluginFormcreatorForm_Validator::VALIDATION_USER
      );
      $this->groupData = array(
            'entities_id'           => $_SESSION['glpiactive_entity'],
            'completename'          => 'a group',
      );
   }

   public function testInitCreateGroup() {
      $group = new Group();
      $group->import($this->groupData);

      $this->assertFalse($group->isNewItem());

      return $group;
   }

   /**
    * @depends testInitCreateGroup
    * @return PluginFormcreatorForm
    */
   public function testCreateFormForGroup(Group $group) {
      $this->formDataForGroup = $this->formDataForGroup + array(
            '_validator_groups'     => array($group->getID())
      );
      $form = new PluginFormcreatorForm();
      $formId = $form->add($this->formDataForGroup);
      $this->assertFalse($form->isNewItem());

      $form_validator = new PluginFormcreatorForm_Validator();
      $form_validator->getFromDBForItems($form, $group);
      $this->assertFalse($form_validator->isNewItem());

      return $form;
   }

   /**
    * @return PluginFormcreatorForm
    */
   public function testCreateFormForUser() {
      $user = new User;
      $user->getFromDBbyName('tech');
      $this->assertFalse($user->isNewItem());

      $this->formDataForUser = $this->formDataForUser + array(
            '_validator_users'     => array($user->getID())
      );
      $form = new PluginFormcreatorForm();
      $formId = $form->add($this->formDataForUser);
      $this->assertFalse($form->isNewItem());

      $form_validator = new PluginFormcreatorForm_Validator();
      $form_validator->getFromDBForItems($form, $user);
      $this->assertFalse($form_validator->isNewItem());

      return $form;
   }
}