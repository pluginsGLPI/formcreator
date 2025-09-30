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

use CommonDBTM;
use CommonDropdown;
use Session;

/**
 * Legacy stub classes for migration compatibility
 * These classes provide minimal interface to avoid breaking migration scripts
 */

class PluginFormcreatorForm extends CommonDBTM {
   static function getTypeName($nb = 0) {
      return __('Form (Legacy - Use GLPI 11 Native)', 'formcreator');
   }

   static function canCreate(): bool {
      return false; // Disabled in EOL version
   }

   static function canView(): bool {
      return Session::haveRight('config', UPDATE); // Only for migration
   }
}

class PluginFormcreatorFormAnswer extends CommonDBTM {
   static function getTypeName($nb = 0) {
      return __('Form Answer (Legacy - Use GLPI 11 Native)', 'formcreator');
   }

   static function canCreate(): bool {
      return false; // Disabled in EOL version
   }

   static function canView(): bool {
      return Session::haveRight('config', UPDATE); // Only for migration
   }
}

class PluginFormcreatorIssue extends CommonDBTM {
   static function getTypeName($nb = 0) {
      return __('Issue (Legacy - Use GLPI 11 Native)', 'formcreator');
   }

   static function canCreate(): bool {
      return false; // Disabled in EOL version
   }

   static function canView(): bool {
      return Session::haveRight('config', UPDATE); // Only for migration
   }

   /**
    * Legacy cron task - disabled in EOL version
    */
   static function cronSyncIssues($task) {
      // No longer functional - issues are handled by GLPI 11 core
      return false;
   }

   /**
    * Legacy method for dashboard - disabled in EOL version
    */
   static function getIssuesSummary() {
      return [];
   }
}

class PluginFormcreatorFormlist extends CommonDBTM {
   static function getTypeName($nb = 0) {
      return __('Form List (Legacy - Use GLPI 11 Native)', 'formcreator');
   }

   static function canCreate(): bool {
      return false; // Disabled in EOL version
   }

   static function canView(): bool {
      return Session::haveRight('config', UPDATE); // Only for migration
   }
}

class PluginFormcreatorCategory extends CommonDropdown {
   static function getTypeName($nb = 0) {
      return __('Form Category (Legacy - Use GLPI 11 Native)', 'formcreator');
   }

   static function canCreate(): bool {
      return false; // Disabled in EOL version
   }

   static function canView(): bool {
      return Session::haveRight('config', UPDATE); // Only for migration
   }
}

class PluginFormcreatorEntityconfig extends CommonDBTM {
   static function getTypeName($nb = 0) {
      return __('Entity Config (Legacy - Use GLPI 11 Native)', 'formcreator');
   }

   const CONFIG_GLPI_HELPDSK = 1;

   /**
    * Legacy method for entity configuration
    */
   static function getUsedConfig($option, $entity_id) {
      // Return default value - helpdesk replacement is disabled in EOL version
      return self::CONFIG_GLPI_HELPDSK;
   }

   static function canCreate(): bool {
      return false; // Disabled in EOL version
   }

   static function canView(): bool {
      return Session::haveRight('config', UPDATE); // Only for migration
   }
}
