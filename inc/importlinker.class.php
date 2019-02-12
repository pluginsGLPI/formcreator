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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class PluginFormcreatorImportLinker
{
   private $imported = [];

   private $postponed = [];

   /**
    *
    * @param string $uuid
    * @param CommonDBTM $object
    */
   public function addImportedObject($uuid, CommonDBTM $object) {
      if (!isset($this->imported[$object->getType()])) {
         $this->imported[$object->getType()] = [];
      }
      $this->imported[$object->getType()][$uuid] = $object;
   }

   /**
    *
    * @param string $uuid
    * @param string $itemtype
    * @param array $object
    */
   public function postponeImport($uuid, $itemtype, array $object, $relationId) {
      if (!isset($this->postponed[$itemtype])) {
         $this->postponed[$itemtype] = [];
      }
      $this->postponed[$itemtype][$uuid] = ['object' => $object, 'relationId' => $relationId];
   }

   public function importPostponed() {
      do {
         $postponedCount = 0;
         $postponedAgainCount = 0;
         foreach ($this->postponed as $itemtype => $postponedItemtypeList) {
            $postponedCount += count($postponedItemtypeList);
            $newList = $postponedItemtypeList;
            foreach ($postponedItemtypeList as $uuid => $postponedItem) {
               if ($itemtype::import($this, $postponedItem['relationId'], $postponedItem['object']) === false) {
                  $newList[$uuid] = $postponedItem;
                  $postponedAgainCount++;
               }
            }
         }

         // If no item was successfully imported,  then the import is in a deadlock and fails
         if ($postponedAgainCount > 0 && $postponedCount == $postponedAgainCount) {
            return false;
         }
      } while ($postponedCount > 0);
   }
}