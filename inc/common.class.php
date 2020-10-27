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

class PluginFormcreatorCommon {
   public static function getEnumValues($table, $field) {
      global $DB;

      $enum = [];
      if ($res = $DB->query( "SHOW COLUMNS FROM `$table` WHERE Field = '$field'" )) {
         if (version_compare(GLPI_VERSION, '9.5') >= 0) {
            $fa = 'fetchArray';
         } else {
            $fa = 'fetch_array';
         }
         $data = $DB->$fa($res);
         $type = $data['Type'];
         $matches = null;
         preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
         if (!isset($matches[1])) {
            return [];
         }
         $enum = explode("','", $matches[1]);
      }

      return $enum;
   }

   /**
    * Get status of notifications
    *
    * @return boolean
    */
   public static function isNotificationEnabled() {
      global $CFG_GLPI;
      $notification = $CFG_GLPI['use_notifications'];

      return ($notification == '1');
   }

   /**
    * Enable or disable notifications
    *
    * @param boolean $enable
    * @return void
    */
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
    * @param array $condition
    * @param string $fieldName
    * @return null|integer
    */
   public static function getMax(CommonDBTM $item, array $condition, $fieldName) {
      global $DB;

      $line = $DB->request([
         'SELECT' => [$fieldName],
         'FROM'   => $item::getTable(),
         'WHERE'  => $condition,
         'ORDER'  => "$fieldName DESC",
         'LIMIT'  => 1
      ])->next();

      if (!isset($line[$fieldName])) {
         return null;
      }
      return (int) $line[$fieldName];
   }

   /**
    * Prepare keywords for a fulltext search in boolean mode
    * takes into account strings in double quotes
    *
    * @param string $keywords
    * @return string
    */
   public static function prepareBooleanKeywords($keywords) {
      // @see https://stackoverflow.com/questions/2202435/php-explode-the-string-but-treat-words-in-quotes-as-a-single-word
      preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $keywords, $matches);
      $matches = $matches[0];
      foreach ($matches as &$keyword) {
         if (strpos($keyword, '"') !== 0) {
            // keyword does not begins with a double quote (assume it does not ends with this char)
            $keyword = rtrim($keyword, '*');
            $keyword .= '*';
         }
      }

      return implode(' ', $matches);
   }

   /**
    * get the list of pictograms available for the current version of GLPI
    *
    * @return array
    */
   public static function getFontAwesomePictoNames() {
      $list = require_once(__DIR__ . '/../' . self::getPictoFilename(GLPI_VERSION));
      return $list;
   }

   /**
    * get the name of the php file containing the pictogram list depending on the version of GLPI
    *
    * @param $version string GLPI version
    * @return string
    */
   public static function getPictoFilename($version) {
      if (version_compare($version, '9.4') < 0) {
         return 'data/font-awesome_9.3.php';
      }
      if (version_compare($version, '9.5') < 0) {
         return 'data/font-awesome_9.4.php';
      }
      if (version_compare($version, '9.6') < 0) {
         return 'data/font-awesome_9.5.php';
      }

      return '';
   }

   /**
    * Show a dropdown with Font Awesome pictograms
    *
    * @param string $name name of the HTML input
    * @param array $options
    * @return mixed
    */
   public static function showFontAwesomeDropdown($name, $options = []) {
      $items = [];
      foreach (static::getFontAwesomePictoNames() as $key => $value) {
         $items[$key] = /* '<i class="' . $key . '"></i>' . */ $value;
      }

      $previewId = $name . '_preview';
      $options['on_change'] = 'plugin_formceator_showPictogram(this, "' . $previewId . '")';
      $options['display'] = false;
      $options['display_emptychoice'] = true;
      if (!isset($options['value'])) {
         $options['value'] = '';
      }
      $output = Dropdown::showFromArray($name, $items, $options);
      $output .= '<i id="' . $previewId . '" class="'. $options['value'] . '"></i>';
      echo $output;
   }

   /**
    * Cancel a new ticketn while it is still allowed
    *
    * In case of error, a message is added to the session
    *
    * @param integer $id
    * @return boolean true on success, false otherwise
    */
   public static function cancelMyTicket($id) {
      $ticket = new Ticket();
      $ticket->getFromDB($id);
      if (!$ticket->canRequesterUpdateItem()) {
         Session::addMessageAfterRedirect(__('You cannot delete this issue. Maybe it is taken into account.', 'formcreator'), true,  ERROR);
         return false;
      }

      if (!$ticket->delete($ticket->fields)) {
         Session::addMessageAfterRedirect(__('Failed to delete this issue. An internal error occured.', 'formcreator'), true, ERROR);
         return false;
      }

      return true;
   }

   /**
    * Get the status to set for an issue matching a ticket
    * Tightly related to SQL query in SyncIssues automatic actions
    *
    * Conversion matrix
    *
    *                               Validation Status
    *                +-------------+---------+---------+----------+
    *                |NULL or NONE | WAITING | REFUSED | ACCEPTED |
    *     + ---------+-------------+---------+---------+----------+
    * T S | INCOMING |     T            V          V         T
    * i t | ASSIGNED |     T            V          V         T
    * c a | PLANNED  |     T            V          V         T
    * k t | WAITING  |     T            V          V         T
    * e u | SOLVED   |     T            V          T         T
    * t s | CLOSED   |     T            V          T         T
    *
    * T = status picked from Ticket
    * V = status picked from Validation
    *
    * @param Ticket $item
    * @return integer
    */
   public static function getTicketStatusForIssue(Ticket $item) {
      $ticketValidations = (new TicketValidation())->find([
         'tickets_id' => $item->getID(),
      ], [
         'timeline_position ASC'
      ], 1);
      $ticketValidation = new TicketValidation();
      if (count($ticketValidations)) {
         $row = array_shift($ticketValidation);
         $ticketValidation->getFromDB($row['id']);
      }

      $status = $item->fields['status'];
      $user = 0;
      if (!$ticketValidation->isNewItem()) {
         $user = $ticketValidation->fields['users_id_validate'];
         switch ($ticketValidation->fields['status']) {
            case CommonITILValidation::WAITING:
               $status = PluginFormcreatorFormAnswer::STATUS_WAITING;
               break;

            case CommonITILValidation::REFUSED:
               if ($item->fields['status'] != Ticket::SOLVED && $item->fields['status'] != Ticket::CLOSED) {
                  $status = PluginFormcreatorFormAnswer::STATUS_REFUSED;
               }
               break;
         }
      }

      return ['status' => $status, 'user' => $user];
   }
}
