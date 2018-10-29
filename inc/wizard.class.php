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

class PluginFormcreatorWizard {

   const MENU_CATALOG      = 1;
   const MENU_LAST_FORMS   = 2;
   const MENU_RESERVATIONS = 3;
   const MENU_FEEDS        = 4;
   const MENU_BOOKMARKS    = 5;
   const MENU_HELP         = 6;
   const MENU_LINKS        = 7;

   public static function header($title) {
      global $CFG_GLPI, $HEADER_LOADED, $DB;

      // Print a nice HTML-head for help page
      if ($HEADER_LOADED) {
         return;
      }
      $HEADER_LOADED = true;

      Html::includeHeader($title);

      $body_class = "layout_".$_SESSION['glpilayout'];
      if ((strpos($_SERVER['REQUEST_URI'], "form.php") !== false)
            && isset($_GET['id']) && ($_GET['id'] > 0)) {
         if (!CommonGLPI::isLayoutExcludedPage()) {
            $body_class.= " form";
         } else {
            $body_class = "";
         }
      }
      echo "<body class='$body_class' id='plugin_formcreator_serviceCatalog'>";

      $toggle_menu = '';
      if (isset($_SESSION['plugin_formcreator_toggle_menu'])
          && $_SESSION['plugin_formcreator_toggle_menu']) {
         $toggle_menu = "toggle_menu";
      }
      echo '<div class="plugin_formcreator_container '.$toggle_menu.'">';

      // menu toggle (responsive mode)
      echo "<input type='checkbox' id='formcreator-toggle-nav-responsive'>";
      echo "<label for='formcreator-toggle-nav-responsive' class='formcreator-nav-button'></label>";

      echo '<div id="header" class ="plugin_formcreator_leftHeader">';

      // menu toggle (desktop mode)
      echo "<input type='checkbox' id='formcreator-toggle-nav-desktop'>";
      echo "<label for='formcreator-toggle-nav-desktop' class='formcreator-nav-button'></label>";

      if ($_SESSION['plugin_formcretor']['tickets_summary']) {
         self::showTicketSummary();
      }

      echo '<div id="header_top">';
      echo '<div id="c_logo"></div>';
      echo '</div>';

      // Left vertical menu
      echo '<div id="c_menu" class="plugin_formcreator_leftMenu">';

      $activeMenuItem = self::findActiveMenuItem();
      echo '<ul class="plugin_formcreator_services">';
      echo '<li class="' . ($activeMenuItem == self::MENU_CATALOG ? 'plugin_formcreator_selectedMenuItem' : '') . '">';
      echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/wizard.php' . '">';
      echo '<span class="fa fa-paper-plane-o fc_list_icon" title="'.__('Seek assistance', 'formcreator').'"></span>';
      echo '<span class="label">'.__('Seek assistance', 'formcreator').'</span>';
      echo '</a></li>';

      echo '<li class="' . ($activeMenuItem == self::MENU_LAST_FORMS ? 'plugin_formcreator_selectedMenuItem' : '') . '">';
      echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/issue.php?reset=reset' . '">';
      echo '<span class="fa fa-list fc_list_icon" title="'.__('My requests for assistance', 'formcreator').'"></span>';
      echo '<span class="label">'.__('My requests for assistance', 'formcreator').'</span>';
      echo '</a></li>';

      if (Session::haveRight("reservation", ReservationItem::RESERVEANITEM)) {
         $reservation_item = new reservationitem;
         $entity_filter = getEntitiesRestrictRequest("", 'glpi_reservationitems', 'entities_id',
                                                     $_SESSION['glpiactiveentities']);
         $found_available_res = $reservation_item->find($entity_filter);
         if (count($found_available_res)) {
            echo '<li class="' . ($activeMenuItem == self::MENU_RESERVATIONS ? 'plugin_formcreator_selectedMenuItem' : '') . '">';
            echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/reservationitem.php' . '">';
            echo '<span class="fa fa-calendar-check-o fc_list_icon" title="'.__('Book an asset', 'formcreator').'"></span>';
            echo '<span class="label">'.__('Book an asset', 'formcreator').'</span>';
            echo '</a></li>';
         }
      }

      if (RSSFeed::canView()) {
         echo '<li class="' . ($activeMenuItem == self::MENU_FEEDS ? 'plugin_formcreator_selectedMenuItem' : '') . '">';
         echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/wizardfeeds.php' . '">';
         echo '<span class="fa fa-rss fc_list_icon" title="'.__('Consult feeds', 'formcreator').'"></span>';
         echo '<span class="label">'.__('Consult feeds', 'formcreator').'</span>';
         echo '</a></li>';
      }

      $query = "SELECT `glpi_savedsearches`.*,
                       `glpi_savedsearches_users`.`id` AS IS_DEFAULT
                FROM `glpi_savedsearches`
                LEFT JOIN `glpi_savedsearches_users`
                  ON (`glpi_savedsearches`.`itemtype` = `glpi_savedsearches_users`.`itemtype`
                      AND `glpi_savedsearches`.`id` = `glpi_savedsearches_users`.`savedsearches_id`
                      AND `glpi_savedsearches_users`.`users_id` = '".Session::getLoginUserID()."')
                WHERE `glpi_savedsearches`.`is_private`='1'
                  AND `glpi_savedsearches`.`users_id`='".Session::getLoginUserID()."'
                  OR `glpi_savedsearches`.`is_private`='0' ".
                     getEntitiesRestrictRequest("AND", "glpi_savedsearches", "", "", true);

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result)) {
            Ajax::createSlidePanel(
                  'showSavedSearches',
                  [
                     'title'     => __('Saved searches'),
                     'url'       => $CFG_GLPI['root_doc'] . '/ajax/savedsearch.php?action=show',
                     'icon'      => '/pics/menu_config.png',
                     'icon_url'  => SavedSearch::getSearchURL(),
                     'icon_txt'  => __('Manage saved searches')
                  ]
                  );
            echo '<li class="' . ($activeMenuItem == self::MENU_BOOKMARKS ? 'plugin_formcreator_selectedMenuItem' : '') . '">';
            Ajax::createIframeModalWindow('loadbookmark',
                  $CFG_GLPI["root_doc"]."/front/savedsearch.php?action=load",
                  ['title'         => __('Saved searches'),
                   'reloadonclose' => true]);
            echo '<a href="#" id="showSavedSearchesLink">';
            echo '<span class="fa fa-star fc_list_icon" title="'.__('Saved searches').'"></span>';
            echo '<span class="label">'.__('Saved searches').'</span>';
            echo '</a>';
            echo '</li>';
         }
      }

      $link = new Link;
      $ar_links = $link->find("name LIKE '". $_SESSION['plugin_formcretor']['external_links_prefix'] ."%'");
      foreach ($ar_links as $id => $a_link) {
         /*
          * The link name is filtered and trimmed to remove the 'Helpdesk' string and extra whitespaces:
          * Helpdesk 1 - the first link
          * will display: the first link.
          */
         $label = trim(str_replace($_SESSION['plugin_formcretor']['external_links_prefix'], '', $a_link['name']));

         /*
          * If the link data field contains a line starting with Icon:, this line is used as a class icon
          * for the link. As an example, this line:
          * Icon: fa fa-dashboard
          * will use the font awesome fa-dashboard icon for the current link.
          * As default, the fa-globe icon is used.
          */
         preg_match_all("/^". $_SESSION['plugin_formcretor']['external_links_icon'] ."(.*)$/m", $a_link['data'], $found);
         $icon = "fa fa-globe";
         if ((count($found) > 0) and (count($found[0]) > 0)) {
            $icon = trim(str_replace($_SESSION['plugin_formcretor']['external_links_icon'], '', $found[0][0]));
         }

         /*
          * If the link data field contains a line starting with Title:, this line is used to find the title used
          * for the link. As an example, this line:
          * Title: this is my title
          * will define a new title for the current link.
          * As default, the link name is used.
          */
         preg_match_all("/^". $_SESSION['plugin_formcretor']['external_links_title'] ."(.*)$/m", $a_link['data'], $found);
         $title = $a_link['name'];
         if ((count($found) > 0) and (count($found[0]) > 0)) {
            $title = trim(str_replace($_SESSION['plugin_formcretor']['external_links_title'], '', $found[0][0]));
         }
         /*
          * If the external link uses a new window...
          */
         $target = "";
         if ($a_link['open_window'] == "1") {
            $target = 'target="_blank"';
         }
         echo '<li class="' . ($activeMenuItem == self::MENU_LINKS ? 'plugin_formcreator_selectedMenuItem' : '') . '">';
         echo '<a href="' . $a_link['link'] . '" '. $target .'>';
         echo '<span class="'. $icon .' fc_list_icon" title="' . $title . '"></span>';
         echo '<span class="label">'. $label .'</span>';
         echo '</a>';
         echo '</li>';
      }

      if (isset($CFG_GLPI["helpdesk_doc_url"]) && !empty($CFG_GLPI["helpdesk_doc_url"])) {
         echo '<li class="' . ($activeMenuItem == self::MENU_HELP ? 'plugin_formcreator_selectedMenuItem' : '') . 'plugin_formcreator_helpIcon">';
         echo '<a href="' . $CFG_GLPI["helpdesk_doc_url"] . '" target="_blank">';
         echo '<span class="fa fa-question fc_list_icon" title="' . __s('Help') . '"></span>';
         echo '<span class="label">'.__('Help').'</span>';
         echo '</a>';
         echo '</li>';
      }

      echo '</ul>';

      echo '</div>';
      echo '</div>';

      echo '<div id="header_top" class="formcreator_header_top">';
      self::showHeaderTopContent();
      echo '</div>'; //.formcreator_header_top

      echo '<div id="page" class="plugin_formcreator_page">';

      // call static function callcron() every 5min
      CronTask::callCron();
      Html::displayMessageAfterRedirect();
   }

   public static function footer() {
      return Html::helpFooter();
   }

   public static function showHeaderTopContent() {
      global $CFG_GLPI;

      // icons
      echo '</ul>';
      echo '<ul class="plugin_formcreator_userMenu_icons">';
      if ($_SESSION['plugin_formcretor']['user_preferences']) {
         // preferences
         echo '<li id="plugin_formcreator_preferences_icon">';
         echo '<a href="' . $CFG_GLPI["root_doc"] . '/front/preference.php" class="fa fa-cog" title="' .
            __s('My settings') . '"><span id="preferences_icon" title="' . __s('My settings') . '" alt="' . __s('My settings') . '" class="button-icon"></span>';
         echo '</a></li>';
      }
      // Logout
      echo '<li id="plugin_formcreator_logoutIcon" ><a href="'.$CFG_GLPI["root_doc"].'/front/logout.php';      /// logout without noAuto login for extauth
      if (isset($_SESSION['glpiextauth']) && $_SESSION['glpiextauth']) {
         echo '?noAUTO=1';
      }
      echo '" class="fa fa-sign-out" title="'.__s('Logout').'">';
      echo '<span id="logout_icon" title="'.__s('Logout').'" alt="'.__s('Logout').'" class="button-icon"></span></a>';
      echo '</li>';

      echo '</ul>';

      // avatar
      // Get formatted user name
      $user = new User;
      $user->getFromDB($_SESSION['glpiID']);
      $dbu = new DbUtils();
      $the_user_name = $dbu->formatUserName(0, $_SESSION["glpiname"],
         $_SESSION["glpirealname"], $_SESSION["glpifirstname"], 0, 20);

      if ($_SESSION['plugin_formcretor']['avatar']) {
         // avatar
         echo '<span id="plugin_formcreator_avatar">';
         if ($_SESSION['plugin_formcretor']['user_preferences']) {
            echo '<a href="' . $CFG_GLPI["root_doc"] . '/front/preference.php">';
         }
         echo '<img title="'. $the_user_name. '" src="' . User::getThumbnailURLForPicture($user->fields['picture']) . '"/>';
         if ($_SESSION['plugin_formcretor']['user_preferences']) {
            echo '</a>';
         }
         echo '</span>';
      }

      // Profile and entity selection
      if ($_SESSION['plugin_formcretor']['profile_selector'] or $_SESSION['plugin_formcretor']['user_name']) {
         echo '<ul class="plugin_formcreator_entityProfile">';
         if ($_SESSION['plugin_formcretor']['user_name']) {
            echo '<li>';
            echo '<span class="plugin_formcreator_user_name">';
            echo $the_user_name;
            echo '</span>';
            echo '</li>';
         }

         if ($_SESSION['plugin_formcretor']['profile_selector']) {
            echo '<li>';
            if (Session::getLoginUserID()) {
               Html::showProfileSelecter($CFG_GLPI["root_doc"] . "/front/helpdesk.public.php");
            }
            echo '</li>';
         }
         echo "</ul>";
      }
   }

   public static function showTicketSummary() {
      // show ticket summary
      echo "<span id='formcreator_servicecatalogue_ticket_summary'>";
      $status_count = PluginFormcreatorIssue::getTicketSummary();

      echo "<span class='status status_incoming'>
            <a href='".FORMCREATOR_ROOTDOC."/front/issue.php?".
                     Toolbox::append_params(PluginFormcreatorIssue::getIncomingCriteria(), '&amp;')."'>
            <span class='status_number'>".
            $status_count[Ticket::INCOMING]."
            </span>
            <label class='status_label'>".__('Processing')."</label>
            </a>
            </span>";

      echo "<span class='status status_waiting'>
            <a href='".FORMCREATOR_ROOTDOC."/front/issue.php?".
                     Toolbox::append_params(PluginFormcreatorIssue::getWaitingCriteria(), '&amp;')."'>
            <span class='status_number'>".
            $status_count[Ticket::WAITING]."
            </span>
            <label class='status_label'>".__('Pending')."</label>
            </a>
            </span>";

      echo "<span class='status status_validate'>
            <a href='".FORMCREATOR_ROOTDOC."/front/issue.php?".
                     Toolbox::append_params(PluginFormcreatorIssue::getValidateCriteria(), '&amp;')."'>
            <span class='status_number'>".
            $status_count['to_validate']."
            </span>
            <label class='status_label'>".__('To validate', 'formcreator')."</label>
            </a>
            </span>";

      echo "<span class='status status_solved'>
            <a href='".FORMCREATOR_ROOTDOC."/front/issue.php?".
                     Toolbox::append_params(PluginFormcreatorIssue::getSolvedCriteria(), '&amp;')."'>
            <span class='status_number'>".
            $status_count[Ticket::SOLVED]."
            </span>
            <label class='status_label'>".__('Closed', 'formcreator')."</label>
            </a>
            </span>";

      echo '</span>'; // formcreator_servicecatalogue_ticket_summary
   }

   protected static function findActiveMenuItem() {
      if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/wizard.php") !== false
          || strpos($_SERVER['REQUEST_URI'], "formcreator/front/formdisplay.php") !== false
          || strpos($_SERVER['REQUEST_URI'], "formcreator/front/knowbaseitem.form.php") !== false) {
         return self::MENU_CATALOG;
      }
      if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/issue.php") !== false
          || strpos($_SERVER['REQUEST_URI'], "formcreator/front/issue.form.php") !== false) {
         return self::MENU_LAST_FORMS;
      }
      if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/reservationitem.php") !== false) {
         return self::MENU_RESERVATIONS;
      }
      if (strpos($_SERVER['REQUEST_URI'], "formcreator/front/wizardfeeds.php") !== false) {
         return self::MENU_FEEDS;
      }
      return false;
   }
}
