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
 * @copyright Copyright © 2011 - 2018-2025 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

use Glpi\Plugin\Formcreator\Install;

/**
 * Plugin install process for Formcreator v3.0.0 (End-of-Life)
 *
 * @return boolean
 */
function plugin_formcreator_install() {
   spl_autoload_register('plugin_formcreator_autoload');

   $migration = new Migration(PLUGIN_FORMCREATOR_SCHEMA_VERSION);

   // Display EOL installation message
   $migration->displayMessage("Installing Formcreator v3.0.0 (End-of-Life - Migration Only)");

   // Use the unified Install class
   $install = new Install();

   if (!$install->install($migration)) {
      $migration->displayMessage("ERROR: Formcreator installation failed");
      return false;
   }

   // Check if migration to GLPI 11 native forms is needed
   $migration->displayMessage("Migration to GLPI 11 native forms may be available. Check Administration > Plugins > Formcreator for migration status.");

   $migration->displayMessage("Formcreator v3.0.0 installed successfully. This is an End-of-Life version for migration purposes only.");
   return true;
}

/**
 * Plugin uninstall process for Formcreator v3.0.0 (End-of-Life)
 *
 * @return boolean
 */
function plugin_formcreator_uninstall() {
   $migration = new Migration(PLUGIN_FORMCREATOR_SCHEMA_VERSION);

   // Display EOL uninstall message
   $migration->displayMessage("Uninstalling Formcreator v3.0.0 (End-of-Life)");

   // Use the unified Install class
   $install = new Install();

   if (!$install->uninstall()) {
      $migration->displayMessage("ERROR: Formcreator uninstallation failed");
      return false;
   }

   $migration->displayMessage("Formcreator v3.0.0 uninstalled successfully.");
   return true;
}

/**
 * Legacy function stub - Display preferences
 * This function is preserved for migration compatibility only
 */
function plugin_formcreator_display_preference() {
   // EOL: No preferences to display
   echo "<div class='center'>";
   echo "<h3>" . __('Formcreator End-of-Life', 'formcreator') . "</h3>";
   echo "<p>" . __('This plugin is End-of-Life. Please use GLPI 11 native forms.', 'formcreator') . "</p>";
   echo "</div>";
}

/**
 * Legacy function stub - Get types
 * This function is preserved for migration compatibility only
 *
 * @return array Empty array (EOL)
 */
function plugin_formcreator_getTypes() {
   // EOL: No types provided
   return [];
}

/**
 * Legacy function stub - Get rights
 * This function is preserved for migration compatibility only
 *
 * @return array Empty array (EOL)
 */
function plugin_formcreator_getRights() {
   // EOL: No specific rights
   return [];
}

/**
 * Legacy function stub - Get addtabon
 * This function is preserved for migration compatibility only
 *
 * @param array $types
 * @return array Empty array (EOL)
 */
function plugin_formcreator_getAddtabon($types = []) {
   // EOL: No tabs to add
   return [];
}

/**
 * Legacy function stub - Check configuration
 * This function is preserved for migration compatibility only
 *
 * @return boolean Always true for migration purposes
 */
function plugin_formcreator_check_config() {
   return true;
}

/**
 * Legacy function stub - MassiveAction hook
 * This function is preserved for migration compatibility only
 *
 * @param string $type
 * @return array Empty array (EOL)
 */
function plugin_formcreator_MassiveActions($type) {
   // EOL: No massive actions
   return [];
}

/**
 * Legacy function stub - Add default where
 * This function is preserved for migration compatibility only
 *
 * @param string $type
 * @return string Empty string (EOL)
 */
function plugin_formcreator_addDefaultWhere($type) {
   // EOL: No default where clause
   return '';
}

/**
 * Legacy function stub - Get search option
 * This function is preserved for migration compatibility only
 *
 * @param string $itemtype
 * @return array Empty array (EOL)
 */
function plugin_formcreator_getSearchOption($itemtype) {
   // EOL: No search options
   return [];
}

/**
 * Legacy function stub - Getsearchoptions
 * This function is preserved for migration compatibility only
 *
 * @param string $itemtype
 * @return array Empty array (EOL)
 */
function plugin_formcreator_getsearchoptions($itemtype) {
   // EOL: No search options
   return [];
}

/**
 * Legacy function stub - Get dropdowns
 * This function is preserved for migration compatibility only
 *
 * @return array Empty array (EOL)
 */
function plugin_formcreator_getDropdown() {
   // EOL: No dropdowns
   return [];
}

/**
 * Legacy function stub - Get database relations
 * This function is preserved for migration compatibility only
 *
 * @return array Empty array (EOL)
 */
function plugin_formcreator_getDatabaseRelations() {
   // EOL: No database relations to define
   return [];
}

/**
 * Legacy function stub - Define dropdown relations
 * This function is preserved for migration compatibility only
 *
 * @return array Empty array (EOL)
 */
function plugin_formcreator_getDropdownRelations() {
   // EOL: No dropdown relations
   return [];
}

/**
 * Get the plugin version for migration tracking
 *
 * @return string Plugin version
 */
function plugin_formcreator_getVersion() {
   return PLUGIN_FORMCREATOR_VERSION;
}
