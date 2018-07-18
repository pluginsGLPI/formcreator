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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorCommon {
   public static function getEnumValues($table, $field) {
      global $DB;

      $enum = [];
      if ($res = $DB->query( "SHOW COLUMNS FROM `$table` WHERE Field = '$field'" )) {
         $data = $DB->fetch_array($res);
         $type = $data['Type'];
         $matches = null;
         preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
         $enum = explode("','", $matches[1]);
      }

      return $enum;
   }

   public static function isNotificationEnabled() {
      global $CFG_GLPI;
      $notification = $CFG_GLPI['use_notifications'];

      return ($notification == '1');
   }

   public static function setNotification($enable) {
      global $CFG_GLPI;

      $CFG_GLPI['use_notifications'] = $enable ? '1' : '0';
   }

   public static function getGlpiVersion() {
      return defined('GLPI_PREVER')
             ? GLPI_PREVER
             : GLPI_VERSION;
   }

   /**
    * Get Link Name
    *
    * @param integer $value    Current value
    * @param boolean $inverted Whether to invert label
    *
    * @return string
    **/
   public static function getLinkName($value, $inverted = false) {
      $tmp = [];

      if (!$inverted) {
         $tmp[Ticket_Ticket::LINK_TO]        = __('Linked to');
         $tmp[Ticket_Ticket::DUPLICATE_WITH] = __('Duplicates');
         $tmp[Ticket_Ticket::SON_OF]         = __('Son of');
         $tmp[Ticket_Ticket::PARENT_OF]      = __('Parent of');
      } else {
         $tmp[Ticket_Ticket::LINK_TO]        = __('Linked to');
         $tmp[Ticket_Ticket::DUPLICATE_WITH] = __('Duplicated by');
         $tmp[Ticket_Ticket::SON_OF]         = __('Parent of');
         $tmp[Ticket_Ticket::PARENT_OF]      = __('Son of');
      }

      if (isset($tmp[$value])) {
         return $tmp[$value];
      }
      return NOT_AVAILABLE;
   }

   /**
    * Gets the ID of Formcreator request type
    */
   public static function getFormcreatorRequestTypeId() {
      global $DB;

      $requesttypes_id = 0;
      $request = $DB->request(
         RequestType::getTable(),
         ['name' => ['LIKE', 'Formcreator']]
      );
      if (count($request) === 1) {
         $row = $request->next();
         $requesttypes_id = $row['id'];
      }

      return $requesttypes_id;
   }

   /**
    * Get the maximum value of a column for a given itemtype
    * @param CommonDBTM $item
    * @param string $condition
    * @param string $fieldName
    * @return NULL|integer
    */
   public static function getMax(CommonDBTM $item, $condition, $fieldName) {
      $rows = $item->find($condition, "`$fieldName` DESC", '1');
      $line = array_pop($rows);
      if ($line === null) {
         return null;
      }
      return (int) $line[$fieldName];
   }
}
