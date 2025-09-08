<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator v3.0.0 - Migration Only (End of Life)
 * ---------------------------------------------------------------------
 * This is a minimal stub for the Install class to support migration
 * functionality only. All form creation features have been removed.
 * ---------------------------------------------------------------------
 */

/**
 * Minimal install/uninstall stub for EOL version
 */
class PluginFormcreatorInstall {

   /**
    * Install the plugin - EOL version only supports basic migration
    * 
    * @param Migration $migration
    * @return bool
    */
   public function install(Migration $migration): bool {
      // EOL version - minimal installation
      $migration->displayMessage("Formcreator v3.0.0 (End-of-Life) - Migration support only");
      
      // Just show EOL warning - no database operations needed
      $migration->displayMessage("NOTICE: This is an End-of-Life version for migration to GLPI 11 native forms only.");
      $migration->displayMessage("All form functionality has been moved to GLPI 11 core features.");
      
      return true;
   }
   
   /**
    * Uninstall the plugin
    * 
    * @param Migration $migration
    * @return bool
    */
   public function uninstall(Migration $migration): bool {      
      // EOL version - clean uninstall
      $migration->displayMessage("Uninstalling Formcreator v3.0.0 (End-of-Life)");
      $migration->displayMessage("No database cleanup needed for EOL version.");
      
      return true;
   }
   
   /**
    * Check if migration is needed (always false for EOL)
    * 
    * @return bool
    */
   public function isUpgrade(): bool {
      return false;
   }
   
   /**
    * Upgrade method (disabled for EOL)
    * 
    * @param Migration $migration
    * @return bool
    */
   public function upgrade(Migration $migration): bool {
      $migration->displayMessage("Upgrade not available in EOL version");
      return false;
   }
}
