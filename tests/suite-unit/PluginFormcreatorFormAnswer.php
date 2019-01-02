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
   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testPre_purgeItem':
            $this->login('glpi', 'glpi');
            break;
      }
   }

   public function testPre_purgeItem() {
      // There is a problem when running this unit test in Travis
      // Need a debug session

      /*
      $form = $this->getForm();

      $targetTicket = $this->getTargetTicket([
         \PluginFormcreatorForm::getForeignKeyField()
            => $form->getID(),
      ]);
      $this->boolean($targetTicket->isNewItem())->isFalse();

      $targetChange = $this->getTargetChange([
         \PluginFormcreatorForm::getForeignKeyField()
            => $form->getID(),
      ]);
      $this->boolean($targetChange->isNewItem())->isFalse();

      // prepare input
      $input = [
         'formcreator_form' => $form->getID(),
      ];

      // send for answer
      $formAnswerId = $form->saveForm($input);
      $this->integer($formAnswerId)->isGreaterThan(0);

      // Check that the targets are created
      // Assumption that only 1 target per type is created
      $itemTicket = new \Item_Ticket();
      $itemTicket->getFromDBByCrit([
         'itemtype' => \PluginFormcreatorFormAnswer::getType(),
         'items_id' => $formAnswerId,
      ]);
      $this->boolean($itemTicket->isNewItem())->isFalse();

      $changeItem = new \Change_Item();
      $changeItem->getFromDBByCrit([
         'itemtype' => \PluginFormcreatorFormAnswer::getType(),
         'items_id' => $formAnswerId,
      ]);
      $this->boolean($changeItem->isNewItem())->isFalse();

      // Run pre_purgeItem
      $formAnswer = $this->newTestedInstance();
      $formAnswer->getFromDB($formAnswerId);
      $this->boolean($formAnswer->isNewItem())->isFalse();
      $output = $formAnswer->pre_purgeItem();
      $this->boolean($output)->isTrue();

      // Check that the targets are deleted
      $itemTicket = new \Item_Ticket();
      $itemTicket->getFromDBByCrit([
         'itemtype' => \PluginFormcreatorFormAnswer::getType(),
         'items_id' => $formAnswerId,
      ]);
      $this->boolean($itemTicket->isNewItem())->isTrue();

      $changeItem = new \Change_Item();
      $changeItem->getFromDBByCrit([
         'itemtype' => \PluginFormcreatorFormAnswer::getType(),
         'items_id' => $formAnswerId,
      ]);
      $this->boolean($changeItem->isNewItem())->isTrue();
      */
   }
}
