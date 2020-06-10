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

trait PluginFormcreatorExportable
{
    /**
     * Insert the export of sub items in the export
     *
     * @param array $subItems key/value pair list of sub items
     * @param array $export the export of the object
     * @param boolean $remove_uuid
     * @return array
     */
    public function exportChildrenObjects($subItems, $export, $remove_uuid = false) {
        global $DB;

        foreach ($subItems as $key => $itemtypes) {
            if (!is_array($itemtypes)) {
                $itemtypes = [$itemtypes];
            }
            $export[$key] = [];
            foreach ($itemtypes as $itemtype) {
                // $itemtype may be a CommonDBRelation type
                // In such case it is still to the itemtype to build the export data
                // because it may contain additinal data
                // @see PluginFormcreatorItem_Ticket and its attribute 'link'
                $list = [];
                $allSubItems = $itemtype::getSQLCriteriaToSearchForItem($this->getType(), $this->getID());
                foreach ($DB->request($allSubItems) as $row) {
                    /** @var CommonDBConnexity $subItem */
                    $subItem = new $itemtype();
                    $subItem->getFromDB($row['id']);
                    if (is_subclass_of($subItem, CommonDBRelation::class)) {
                        if ($row['itemtype_1'] == $row['itemtype_2'] && $row['is_1']) {
                            // the linked object is the same itemtype as the parent's itemtype
                            // this relation will be also exported in the reverse order
                            // Let's ignore it
                            // TODO: if someday an CommonDBRelation itemtype links 2 objects
                            // belonging to different forms, then we must ignore the relation
                            // only when the 2 linked objects belong to the same form.
                            // This will needs an extra check here.
                            continue;
                        }
                    }
                    $list[] = $subItem->export($remove_uuid);
                }
                if (!is_array($subItems[$key])) {
                    $export[$key] = $list;
                } else {
                    $export[$key][$itemtype] = $list;
                }
            }
        }

        return $export;
    }

    /**
     * Import children objects
     *
     * @param array PluginFormcreatorExportableInterface $item
     * @param PluginFormcreatorLinker $linker
     * @param array $input
     * @return void
     */
    public function importChildrenObjects($item, $linker, $subItems, $input) {
        $itemId = $item->getID();
        foreach ($subItems as $key => $itemtypes) {
            if (!is_array($itemtypes)) {
               if (!isset($input[$key])) {
                  $input[$key] = [];
               }
               $input[$key] = [$itemtypes => $input[$key]];
               $itemtypes = [$itemtypes];
            }
            foreach ($itemtypes as $itemtype) {
               $importedItems = [];
               if (!isset($input[$key][$itemtype])) {
                  continue;
               }
               foreach ($input[$key][$itemtype] as $subInput) {
                  $importedItem = $itemtype::import(
                     $linker,
                     $subInput,
                     $itemId
                  );

                  // If $importedItem === false the item import is postponed
                  if ($importedItem !== false) {
                     $importedItems[] = $importedItem;
                  }
               }
               // Delete all other restrictions
               $subItem = new $itemtype();
               $subItem->deleteObsoleteItems($item, $importedItems);
            }
        }
    }
}