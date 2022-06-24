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

namespace tests\units\GlpiPlugin\Formcreator\Target;

use GlpiPlugin\Formcreator\Target_Actor;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

;

class Change extends CommonTestCase {

   public function beforeTestMethod($method) {
      parent::beforeTestMethod($method);

      $this->login('glpi', 'glpi');
   }

   public function testTargetChangeActors() {
      // Create a form with a target change
      $form = $this->getForm();

      $instance = new $this->newTestedInstance();
      $instance->add([
         'name'                        => 'a target',
         'plugin_formcreator_forms_id' => $form->getID()
      ]);
      $this->boolean($instance->isNewItem())->isFalse();

      $requesterActor = new Target_Actor();
      $observerActor = new Target_Actor();
      $instanceId = $instance->getID();

      // find the actors created by default
      $requesterActor->getFromDBByCrit([
         'AND' => [
            'itemtype'   => $instance->getType(),
            'items_id'   => $instanceId,
            'actor_role' => Target_Actor::ACTOR_ROLE_REQUESTER,
            'actor_type' => Target_Actor::ACTOR_TYPE_AUTHOR,
         ]
      ]);
      $observerActor->getFromDBByCrit([
         'AND' => [
            'itemtype'   => $instance->getType(),
            'items_id'   => $instanceId,
            'actor_role' => Target_Actor::ACTOR_ROLE_OBSERVER,
            'actor_type' => Target_Actor::ACTOR_TYPE_VALIDATOR
            ]
      ]);
      $this->boolean($requesterActor->isNewItem())->isFalse();
      $this->boolean($observerActor->isNewItem())->isFalse();

      // check the settings of the default actors
      $this->integer((int) $requesterActor->getField('use_notification'))
         ->isEqualTo(1);
      $this->integer((int) $observerActor->getField('use_notification'))
         ->isEqualTo(1);
   }
}
