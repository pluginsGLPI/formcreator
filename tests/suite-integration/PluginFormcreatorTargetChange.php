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
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorTargetChange extends CommonTestCase {

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);

      $this->login('glpi', 'glpi');
   }

   public function testTargetTicketActors() {
      $form = new \PluginFormcreatorForm();
      $form->add([
         'entities_id'           => $_SESSION['glpiactive_entity'],
         'name'                  => 'a form',
         'description'           => 'form description',
         'content'               => 'a content',
         'is_active'             => 1,
         'validation_required'   => 0
      ]);
      $this->boolean($form->isNewItem())->isFalse();

      $target = new \PluginFormcreatorTarget();
      $target->add([
         'name'                        => 'a target',
         'itemtype'                    => \PluginFormcreatorTargetChange::class,
         'plugin_formcreator_forms_id' => $form->getID()
      ]);
      $this->boolean($target->isNewItem())->isFalse();
      $this->integer((int) $target->getField('plugin_formcreator_forms_id'))
         ->isEqualTo((int) $form->getID());
      $this->string($target->getField('itemtype'))
         ->isEqualTo(\PluginFormcreatorTargetChange::class);

      $targetChange = $target->getField('items_id');
      $targetChange = new \PluginFormcreatorTargetChange();
      $targetChange->getFromDB($target->getField('items_id'));
      $this->boolean($targetChange->isNewItem())->isFalse();
      $this->string($targetChange->getField('name'))
         ->isEqualTo($target->getField('name'));

      $requesterActor = new \PluginFormcreatorTargetChange_Actor();
      $observerActor = new \PluginFormcreatorTargetChange_Actor();
      $targetChangeId = $targetChange->getID();

      $requesterActor->getFromDBByCrit([
         'AND' => [
            'plugin_formcreator_targetchanges_id' => $targetChangeId,
            'actor_role' => 'requester',
            'actor_type' => 'creator'
         ]
      ]);
      $observerActor->getFromDBByCrit([
            'AND' => [
               'plugin_formcreator_targetchanges_id' => $targetChangeId,
               'actor_role' => 'observer',
               'actor_type' => 'validator'
            ]
      ]);

      $this->boolean($requesterActor->isNewItem())->isFalse();
      $this->boolean($observerActor->isNewItem())->isFalse();
      $this->integer((int) $requesterActor->getField('use_notification'))->isEqualTo(1);
      $this->integer((int) $observerActor->getField('use_notification'))->isEqualTo(1);
   }
}