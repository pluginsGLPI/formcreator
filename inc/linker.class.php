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

use GlpiPlugin\Formcreator\Exception\ImportFailureException;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorLinker
{
   private $imported = [];

   private $postponed = [];

   private $progress = 0;

   private $totalCount = 0;

   private $options = [];

   public function __construct($options = []) {
      $params              = [];
      $params['progress']  = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }
      $this->options = $params;
   }

   public function countItems($input, $itemtype) {
      // Get the total count of objects to import, for the progressbar
      $this->totalCount += $itemtype::countItemsToImport($input);
   }

   public function getProgress() {
      return $this->progress;
   }

   public  function getTotalCount() {
      return $this->totalCount;
   }

   public function initProgressBar() {
      if (!isCommandLine() && !isAPI() && $this->options['progress']) {
         echo "<div class='center'>";
         echo "<table class='tab_cadrehov'><tr><th>".__('Importing', 'formcreator')."</th></tr>";
         echo "<tr class='tab_bg_2'><td>";
         Html::createProgressBar(__('Import in progress'));
         echo "</td></tr></table></div>\n";
      }
   }

   /**
    * Store an object added in the DB
    *
    * @param string|integer $originalId
    * @param PluginFormcreatorExportableInterface $object
    * @return void
    */
   public function addObject($originalId, PluginFormcreatorExportableInterface $object) {
      /** @var CommonDBTM $object  */
      if (!isset($this->imported[$object->getType()])) {
         $this->imported[$object->getType()] = [];
      }
      if (isset($this->imported[$object->getType()][$originalId])) {
         // throw new ImportFailureException(sprintf('Attempt to create twice the item "%1$s" with original ID "%2$s"', $object->getType(), $originalId));
         // Object already added
         return;
      }
      $this->imported[$object->getType()][$originalId] = $object;
      $this->progress++;
      if (!isCommandLine() && !isAPI() && $this->options['progress']) {
         Html::changeProgressBarPosition($this->getProgress(), $this->getTotalCount(), $this->getProgress() . ' / ' . $this->getTotalCount());
      }
   }

   /**
    * Get a previously imported object
    *
    * @param int $originalId
    * @param string $itemtype
    * @return PluginFormcreatorExportableInterface|false
    */
   public function getObject($originalId, $itemtype) {
      if (!isset($this->imported[$itemtype][$originalId])) {
         return false;
      }
      return $this->imported[$itemtype][$originalId];
   }

   public function getObjectsByType($itemtype) {
      if (!isset($this->imported[$itemtype])) {
         return false;
      }
      return $this->imported[$itemtype];
   }

   /**
    * Find an object in the DB
    * Contrary to getObject(), this method also searches in objects which
    * are not and will not be imported
    *
    * @param string $itemtype itemtype of object to find
    * @param int $id ID of object to find
    * @param string $idField fieldname where the ID is searched for
    * @return CommonDBTM
    */
   public function findObject($itemtype, $id, $idField) {
      if (!strpos($itemtype, 'PluginFormcreator') === 0) {
         // The itemtype is not part of Formcreator
         // Cannot use uuid column
         $idField = 'id';
      }
      $item = new $itemtype();
      plugin_formcreator_getFromDBByField($item, $idField, $id);

      return $item;
   }

   /**
    * Store input data of an object to add it later
    *
    * @param string|integer $originalId
    * @param string $itemtype
    * @param array $input
    * @return void
    */
   public function postpone($originalId, $itemtype, array $input, $relationId) {
      if (!isset($this->postponed[$itemtype])) {
         $this->postponed[$itemtype] = [];
      }
      $this->postponed[$itemtype][$originalId] = ['input' => $input, 'relationId' => $relationId];
   }

   /**
    * Add in DB all postponed objects
    *
    * @return boolean true on success, false otherwise
    */
   public function linkPostponed() {
      do {
         $postponedCount = 0;
         $postponedAgainCount = 0;
         foreach ($this->postponed as $itemtype => &$postponedItemtypeList) {
            $postponedCount += count($postponedItemtypeList);
            $newList = [];
            foreach ($postponedItemtypeList as $originalId => $postponedItem) {
               if ($itemtype::import($this, $postponedItem['input'], $postponedItem['relationId']) === false) {
                  $newList[$originalId] = $postponedItem;
                  $postponedAgainCount++;
               }
            }
            $postponedItemtypeList = $newList;
         }
         unset($postponedItemtypeList);

         // If no item was successfully imported,  then the import is in a deadlock and fails
         if ($postponedAgainCount > 0 && $postponedCount == $postponedAgainCount) {
            return false;
         }
      } while ($postponedCount > 0);

      return true;
   }

   public function reset() {
      $this->imported = [];
      $this->postponed = [];
   }
}
