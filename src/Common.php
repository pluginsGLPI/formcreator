<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator v3.0.0 - Migration Only (End of Life)
 * ---------------------------------------------------------------------
 * This is a stub class that provides the minimal interface required
 * for migration scripts. All functionality has been moved to GLPI 11 core.
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
