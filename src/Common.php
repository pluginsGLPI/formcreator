<?php

/**
 *
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
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace Glpi\Plugin\Formcreator;

use CommonITILObject;
use Session;

/**
 * Legacy stub class for migration compatibility
 */
class Common {

   /**
    * Show EOL warning when legacy methods are called
    */
   private static function showEolWarning($method) {
      if (!defined('PLUGIN_FORMCREATOR_LEGACY_WARNING_SHOWN')) {
         $message = sprintf(
            __('Class method %s is deprecated in Formcreator v%s (EOL). Use GLPI 11 native forms instead.', 'formcreator'),
            $method,
            PLUGIN_FORMCREATOR_VERSION
         );

         if (isCommandLine()) {
            echo "WARNING: " . $message . PHP_EOL;
         } else {
            Session::addMessageAfterRedirect($message, true, WARNING);
         }
         define('PLUGIN_FORMCREATOR_LEGACY_WARNING_SHOWN', true);
      }
   }

   /**
    * Legacy method - no longer functional
    */
   public static function getCssFilename() {
      self::showEolWarning(__METHOD__);
      return '';
   }

   /**
    * Legacy method - no longer functional
    */
   public static function hookPreShowTab($params) {
      self::showEolWarning(__METHOD__);
      return false;
   }

   /**
    * Legacy method - no longer functional
    */
   public static function hookPostShowTab($params) {
      self::showEolWarning(__METHOD__);
      return false;
   }

   /**
    * Legacy method - no longer functional
    */
   public static function hookRedefineMenu($menu) {
      self::showEolWarning(__METHOD__);
      return $menu;
   }

   /**
    * Legacy method for migration compatibility
    */
   public static function getTicketStatusForIssue($ticket) {
      // This method might be called during migration
      // Return a basic status based on ticket state
      if ($ticket && isset($ticket->fields['status'])) {
         return $ticket->fields['status'];
      }
      return CommonITILObject::INCOMING;
   }
}
