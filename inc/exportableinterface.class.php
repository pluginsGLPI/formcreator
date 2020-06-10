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

interface PluginFormcreatorExportableInterface
{
   /**
    * Export in an array all the data of the current instanciated form
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false);

   /**
    * Import an itemtype into the db
    * @see PluginFormcreatorForm::importJson
    *
    * @param  PluginFormcreatorLinker $linker
    * @param  integer $containerId  id of the parent itemtype, 0 if not
    * @param  array   $input the target data (match the target table)
    * @return integer|false the id of the imported item or false on error
    */
   public static function import(PluginFormcreatorLinker $linker, $input = [], $containerId = 0);

   /**
    * Delete all items belonging to a container and not in the list of items to keep
    *
    * Used when importing objects. Items not matching imported objects are deleted
    * @param CommonDBTM $container instance of the object containing items of
    * @param array $exclude list of ID to keep
    *
    * @return boolean
    */
   public function deleteObsoleteItems(CommonDBTM $container, array $exclude);
}
