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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

abstract class PluginFormcreatorCommonOrdered extends CommonDBTM
{

   /** @var string $order column name containing the order number */
   static public $order = 'order';

   static public $containerItemtype = '';

   /**
    * get the first available order
    *
    * @param integer $items_id
    * @return integer
    */
   protected function getNextOrder($items_id) {
      $fk = (static::$containerItemtype)::getForeignKeyField();
      $maxOrder = $this->getMax(
         $this, [
            $fk => $items_id
         ],
         'order'
      );
      if ($maxOrder === null) {
         return 1;
      }

      return $maxOrder + 1;
   }

   /**
    * Update order in the old container and get the next avaialble order index
    * for the new container
    *
    * @param integer $oldItems_id ID of the old container
    * @param integer $newItems_id ID of the new container
    * @return integer
    */
   protected function changeContainer($oldItems_id, $newItems_id) {
      global $DB;

      if ($oldItems_id == $newItems_id) {
         // No change, then return the curent order value
         return $this->fields[$this->order];
      }

      $orderColumn = $this->order;
      // Reorder other questions from the old section
      $table = static::getTable();
      $DB->update(
         $table, [
            $orderColumn => new QueryExpression("`$orderColumn` - 1"),
         ], [
            $orderColumn => ['>', $oldItems_id]
         ]
      );

      // return the next available order value in the new container
      return $this->getNextOrder($newItems_id);
   }

   /**
    * Get the maximum value of a column
    * @param array $condition
    * @param string $fieldName
    * @return null|integer
    */
    public function getMax(array $condition, $fieldName) {
      global $DB;

      $line = $DB->request([
         'SELECT' => [$fieldName],
         'FROM'   => static::getTable(),
         'WHERE'  => $condition,
         'ORDER'  => "$fieldName DESC",
         'LIMIT'  => 1
      ])->next();

      if ($line === false) {
         return null;
      }

      return (int) $line[$fieldName];
   }
}
