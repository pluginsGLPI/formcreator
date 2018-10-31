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
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 *
 * @since 0.1.0
 */
class PluginFormcreatorEntityconfig extends CommonDBTM {

   const CONFIG_PARENT = -2;
   const CONFIG_PARENT_STRING = '-/-';
   const CONFIG_SIMPLIFIED_SERVICE_CATALOG = 1;
   const CONFIG_EXTENDED_SERVICE_CATALOG = 2;

   /**
    * @var bool $dohistory maintain history
    */
   public $dohistory                   = true;

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      $tabNames = [];
      if (!$withtemplate) {
         if ($item->getType() == 'Entity') {
            $tabNames[1] = _n('Form', 'Forms', 2, 'formcreator');
         }
      }
      return $tabNames;
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'Entity') {
         $config = new self();
         $config->showFormForEntity($item);
      }
   }

   public function showFormForEntity(Entity $entity) {
      $ID = $entity->getField('id');
      if (!$entity->can($ID, READ)
            || !Notification::canView()) {
         return false;
      }

      if (!$this->getFromDB($ID)) {
         $this->add([
               'id'                 => $ID,
               'replace_helpdesk'   => self::CONFIG_PARENT
         ]);
      }

      $canedit = $entity->canUpdateItem();
      echo "<div class='spaced'>";
      if ($canedit) {
         echo "<form method='post' name=form action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      }

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".__('Helpdesk', 'formcreator')."</th></tr>";

      if ($ID != 0) {
         $elements = [
            self::CONFIG_PARENT => __('Inheritance of the parent entity')
         ];
      } else {
         $elements = [];
      }
      $elements[0] = __('GLPi\'s helpdesk', 'formcreator');
      $elements[1] = __('Service catalog simplified', 'formcreator');
      $elements[2] = __('Service catalog extended', 'formcreator');

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Helpdesk mode', 'formcreator')."</td>";
      echo "<td>";
      $value = $this->fields["replace_helpdesk"];
      $inheritedValue = self::getUsedConfig('replace_helpdesk', $ID, self::CONFIG_PARENT);
      if ($value == self::CONFIG_PARENT) {
         Dropdown::showFromArray('replace_helpdesk', $elements, ['value' => $inheritedValue]);
         echo '<div class="green">' . __('Inheritance of the parent entity') . '</div>';
      } else {
         Dropdown::showFromArray('replace_helpdesk', $elements, ['value' => $value]);
      }
      echo '</td></tr>';

      // External links configuration
      echo "<tr><th colspan='2'>" . __('External links configuration', 'formcreator') . "</th></tr>";
      echo "<tr>";
      echo "<td >" . __('Prefix for external links', 'formcreator') . "</td>";
      echo "<td>";
      $value = $this->fields["external_links_prefix"];
      $inheritedValue = self::getUsedConfig('external_links_prefix', $ID, self::CONFIG_PARENT_STRING);
      if ($value == self::CONFIG_PARENT_STRING) {
         echo '<input type="text" name="external_links_prefix" value="' . $inheritedValue . '" />';
         echo '<div class="green">' . __('Inheritance of the parent entity') . '</div>';
      } else {
         echo '<input type="text" name="external_links_prefix" value="' . $value . '" />';
      }
      echo "</td></tr>";
      echo "<tr><td colspan='2'>";
      echo "<em>" . __('All the Glpi external links which name starts with this prefix will be displayed in the helpdesk menu.', 'formcreator') . "</em>";
      echo "</td></tr>";

      echo "<tr>";
      echo "<td >" . __('Prefix for icon name', 'Icon:') . "</td>";
      echo "<td>";
      $value = $this->fields["external_links_icon"];
      $inheritedValue = self::getUsedConfig('external_links_icon', $ID, self::CONFIG_PARENT_STRING);
      if ($value == self::CONFIG_PARENT_STRING) {
         echo '<input type="text" name="external_links_icon" value="' . $inheritedValue . '" />';
         echo '<div class="green">' . __('Inheritance of the parent entity') . '</div>';
      } else {
         echo '<input type="text" name="external_links_icon" value="' . $value . '" />';
      }
      echo "</td></tr>";
      echo "<tr><td colspan='2'>";
      echo "<em>" . __('A line of text in the link description that starts with this prefix is supposed to contain the name of the icon to be used in the menu.', 'formcreator') . "</em>";
      echo "</td></tr>";
      echo "<tr>";
      echo "<td >" . __('Prefix for link title', 'Title:') . "</td>";
      echo "<td>";
      $value = $this->fields["external_links_title"];
      $inheritedValue = self::getUsedConfig('external_links_title', $ID, self::CONFIG_PARENT_STRING);
      if ($value == self::CONFIG_PARENT_STRING) {
         echo '<input type="text" name="external_links_title" value="' . $inheritedValue . '" />';
         echo '<div class="green">' . __('Inheritance of the parent entity') . '</div>';
      } else {
         echo '<input type="text" name="external_links_title" value="' . $value . '" />';
      }
      echo "</td></tr>";
      echo "<tr><td colspan='2'>";
      echo "<em>" . __('A line of text in the link description that starts with this prefix is supposed to contain the title used when hovering the link in the menu.', 'formcreator') . "</em>";
      echo "</td></tr>";

      // Header bar configuration
      echo "<tr><th colspan='2'>" . __('Header bar configuration', 'formcreator') . "</th></tr>";
      echo "<tr>";
      echo "<td >" . __('Display the tickets summary', 'formcreator') . "</td>";
      echo "<td>";
      $value = $this->fields["tickets_summary"];
      $inheritedValue = self::getUsedConfig('tickets_summary', $ID, self::CONFIG_PARENT);
      if ($value == self::CONFIG_PARENT) {
         Dropdown::showYesNo("tickets_summary", $inheritedValue);
         echo '<div class="green">' . __('Inheritance of the parent entity') . '</div>';
      } else {
         Dropdown::showYesNo("tickets_summary", $value);
      }
      echo "</td></tr>";

      echo "<tr>";
      echo "<td >" . __('Allow to change user preferences', 'formcreator') . "</td>";
      echo "<td>";
      $value = $this->fields["user_preferences"];
      $inheritedValue = self::getUsedConfig('user_preferences', $ID, self::CONFIG_PARENT);
      if ($value == self::CONFIG_PARENT) {
         Dropdown::showYesNo("user_preferences", $inheritedValue);
         echo '<div class="green">' . __('Inheritance of the parent entity') . '</div>';
      } else {
         Dropdown::showYesNo("user_preferences", $value);
      }
      echo "</td></tr>";

      echo "<tr>";
      echo "<td >" . __('Display user avatar', 'formcreator') . "</td>";
      echo "<td>";
      $value = $this->fields["avatar"];
      $inheritedValue = self::getUsedConfig('avatar', $ID, self::CONFIG_PARENT);
      if ($value == self::CONFIG_PARENT) {
         Dropdown::showYesNo("avatar", $inheritedValue);
         echo '<div class="green">' . __('Inheritance of the parent entity') . '</div>';
      } else {
         Dropdown::showYesNo("avatar", $value);
      }
      echo "</td></tr>";

      echo "<tr>";
      echo "<td >" . __('Display user name', 'formcreator') . "</td>";
      echo "<td>";
      $value = $this->fields["user_name"];
      $inheritedValue = self::getUsedConfig('user_name', $ID, self::CONFIG_PARENT);
      if ($value == self::CONFIG_PARENT) {
         Dropdown::showYesNo("user_name", $inheritedValue);
         echo '<div class="green">' . __('Inheritance of the parent entity') . '</div>';
      } else {
         Dropdown::showYesNo("user_name", $value);
      }
      echo "</td></tr>";

      echo "<tr>";
      echo "<td >" . __('Display profile selector', 'formcreator') . "</td>";
      echo "<td>";
      $value = $this->fields["profile_selector"];
      $inheritedValue = self::getUsedConfig('profile_selector', $ID, self::CONFIG_PARENT);
      if ($value == self::CONFIG_PARENT) {
         Dropdown::showYesNo("profile_selector", $inheritedValue);
         echo '<div class="green">' . __('Inheritance of the parent entity') . '</div>';
      } else {
         Dropdown::showYesNo("profile_selector", $value);
      }
      echo "</td></tr>";

      if ($canedit) {
         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='4'>";
         echo "<input type='hidden' name='id' value='".$entity->fields["id"]."'>";
         echo "<input type='submit' name='update' value=\""._sx('button', 'Save')."\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();

      } else {
         echo "</table>";
      }

      echo "</div>";
   }

   /**
    * Retrieve data of current entity or parent entity
    *
    * @param $fieldref        string   name of the referent field to know if we look at parent entity
    * @param $entities_id
    * @param $default_value   integer/string  value to return (default -2 or -/-)
    *
    * @return string value
    */
   static function getUsedConfig($fieldref, $entities_id, $default_value = -2) {

      $entity = new Entity();
      $entityConfig = new self();
      // Search in entity data of the current entity
      if ($entity->getFromDB($entities_id)) {
         // Value is defined : use it
         if ($entityConfig->getFromDB($entities_id)) {
            if (is_numeric($default_value)
                  && ($entityConfig->fields[$fieldref] != self::CONFIG_PARENT)) {
               return $entityConfig->fields[$fieldref];
            }
            if (!is_numeric($default_value)
                  && ($entityConfig->fields[$fieldref] != self::CONFIG_PARENT_STRING)) {
               return $entityConfig->fields[$fieldref];
            }

         }
      }

      // Entity data not found or not defined : search in parent one
      if ($entities_id > 0) {
         return self::getUsedConfig($fieldref, $entity->fields['entities_id'], $default_value);
      }

      return $default_value;
   }

   /**
    * Load the plugin configuration in a global variable $_SESSION['plugin_formcretor']
    *
    * @global array $_SESSION['plugin_formcretor']
    */
   static function loadConfiguration() {
      // Protect if session is not opened
      if (! isset($_SESSION['glpiactive_entity'])) {
         return;
      }

      // Get global configuration parameters
      $_SESSION['plugin_formcretor']['tickets_summary'] = self::getUsedConfig('tickets_summary',
         $_SESSION['glpiactive_entity']);
      $_SESSION['plugin_formcretor']['user_preferences'] = self::getUsedConfig('user_preferences',
         $_SESSION['glpiactive_entity']);
      $_SESSION['plugin_formcretor']['avatar'] = self::getUsedConfig('avatar',
         $_SESSION['glpiactive_entity']);
      $_SESSION['plugin_formcretor']['user_name'] = self::getUsedConfig('user_name',
         $_SESSION['glpiactive_entity']);
      $_SESSION['plugin_formcretor']['profile_selector'] = self::getUsedConfig('profile_selector',
         $_SESSION['glpiactive_entity']);
      $_SESSION['plugin_formcretor']['external_links_prefix'] = self::getUsedConfig('external_links_prefix',
         $_SESSION['glpiactive_entity'], self::CONFIG_PARENT_STRING);
      $_SESSION['plugin_formcretor']['external_links_icon'] = self::getUsedConfig('external_links_icon',
         $_SESSION['glpiactive_entity'], self::CONFIG_PARENT_STRING);
      $_SESSION['plugin_formcretor']['external_links_title'] = self::getUsedConfig('external_links_title',
         $_SESSION['glpiactive_entity'], self::CONFIG_PARENT_STRING);
   }
}
