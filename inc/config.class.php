<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

class PluginFormcreatorConfig extends CommonDBTM {

   static protected $notable = true;

   /**
    * Load the plugin configuration in a global variable $PFC_CONFIG
    *
    * @global array $PFC_CONFIG
    */
   static function loadConfiguration() {
      global $PFC_CONFIG;

      // Get global configuration parameters
      $PFC_CONFIG = Config::getConfigurationValues('formcreator');
      if (! isset($PFC_CONFIG['user_preferences'])) {
         Config::setConfigurationValues('formcreator', ['user_preferences' => true]);
         $PFC_CONFIG['user_preferences'] = true;
      }
      if (! isset($PFC_CONFIG['avatar'])) {
         Config::setConfigurationValues('formcreator', ['avatar' => true]);
         $PFC_CONFIG['avatar'] = true;
      }
      if (! isset($PFC_CONFIG['user_name'])) {
         Config::setConfigurationValues('formcreator', ['user_name' => true]);
         $PFC_CONFIG['user_name'] = true;
      }
      if (! isset($PFC_CONFIG['profile_selector'])) {
         Config::setConfigurationValues('formcreator', ['profile_selector' => true]);
         $PFC_CONFIG['profile_selector'] = true;
      }
      if (! isset($PFC_CONFIG['external_links_prefix'])) {
         Config::setConfigurationValues('formcreator', ['external_links_prefix' => 'Helpdesk']);
         $PFC_CONFIG['external_links_prefix'] = 'Helpdesk';
      }
      if (! isset($PFC_CONFIG['external_links_icon'])) {
         Config::setConfigurationValues('formcreator', ['external_links_icon' => 'Icon:']);
         $PFC_CONFIG['external_links_icon'] = 'Icon:';
      }
      if (! isset($PFC_CONFIG['external_links_title'])) {
         Config::setConfigurationValues('formcreator', ['external_links_title' => 'Title:']);
         $PFC_CONFIG['external_links_title'] = 'Title:';
      }
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      $tabNames = [];
      if (!$withtemplate) {
         if ($item->getType() == 'Config') {
            $tabNames[1] = _n('Form', 'Forms', 2, 'formcreator');
         }
      }
      return $tabNames;
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'Config') {
         $config = new self();
         $config->showFormConfiguration();
      }
   }

   /*
    * Function called by the Glpi core configuration process to allow updating
    * submitted parameters.
    */
   static function configUpdate($input) {
      return $input;
   }

   function showFormConfiguration() {
      global $CFG_GLPI, $PFC_CONFIG;

      if (!Session::haveRight("config", UPDATE)) {
         return false;
      }

      // Show configuration form
      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL('Config')."\" method='post'>";
      echo "<input type='hidden' name='config_class' value='".__CLASS__."'>";
      echo "<input type='hidden' name='config_context' value='formcreator'>";

      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";

      // External links configuration
      echo "<tr><th colspan='4'>" . __('External links configuration', 'formcreator') . "</th></tr>";
      echo "<tr>";
      echo "<td >" . __('Prefix for external links', 'formcreator') . "</td>";
      echo "<td colspan='3'>";
      echo '<input type="text" name="external_links_prefix" value="' . $PFC_CONFIG['external_links_prefix'] . '" />';
      echo "</td></tr>";
      echo "<tr><td colspan='4'>";
      echo "<em>" . __('All the Glpi external links which name starts with this prefix will be displayed in the helpdesk menu.', 'formcreator') . "</em>";
      echo "</td></tr>";
      echo "<tr>";
      echo "<td >" . __('Prefix for icon name', 'Icon:') . "</td>";
      echo "<td colspan='3'>";
      echo '<input type="text" name="external_links_icon" value="' . $PFC_CONFIG['external_links_icon'] . '" size="40" />';
      echo "</td></tr>";
      echo "<tr><td colspan='4'>";
      echo "<em>" . __('A line of text in the link description that starts with this prefix is supposed to contain the name of the icon to be used in the menu.', 'formcreator') . "</em>";
      echo "</td></tr>";
      echo "<tr>";
      echo "<td >" . __('Prefix for link title', 'Title:') . "</td>";
      echo "<td colspan='3'>";
      echo '<input type="text" name="external_links_title" value="' . $PFC_CONFIG['external_links_title'] . '"size="40"  />';
      echo "</td></tr>";
      echo "<tr><td colspan='4'>";
      echo "<em>" . __('A line of text in the link description that starts with this prefix is supposed to contain the title used when hovering the link in the menu.', 'formcreator') . "</em>";
      echo "</td></tr>";

      // Header bar configuration
      echo "<tr><th colspan='4'>" . __('Header bar configuration', 'formcreator') . "</th></tr>";
      echo "<tr>";
      echo "<td >" . __('Allow to change user preferences', 'formcreator') . "</td>";
      echo "<td colspan='3'>";
      Dropdown::showYesNo("user_preferences", $PFC_CONFIG['user_preferences']);
      echo "</td></tr>";
      echo "<tr>";
      echo "<td >" . __('Display user avatar', 'formcreator') . "</td>";
      echo "<td colspan='3'>";
      Dropdown::showYesNo("avatar", $PFC_CONFIG['avatar']);
      echo "</td></tr>";
      echo "<tr>";
      echo "<td >" . __('Display user name', 'formcreator') . "</td>";
      echo "<td colspan='3'>";
      Dropdown::showYesNo("user_name", $PFC_CONFIG['user_name']);
      echo "</td></tr>";
      echo "<tr>";
      echo "<td >" . __('Show profile selector', 'formcreator') . "</td>";
      echo "<td colspan='3'>";
      Dropdown::showYesNo("profile_selector", $PFC_CONFIG['profile_selector']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='update' class='submit' value=\""._sx('button', 'Save')."\">";
      echo "</td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }

}
