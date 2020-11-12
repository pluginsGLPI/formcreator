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

class PluginFormcreatorWizard {

   const MENU_CATALOG      = 1;
   const MENU_LAST_FORMS   = 2;
   const MENU_RESERVATIONS = 3;
   const MENU_FEEDS        = 4;
   const MENU_BOOKMARKS    = 5;
   const MENU_HELP         = 6;

   public static function header($title) {
      global $CFG_GLPI, $HEADER_LOADED;

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

      if (method_exists(Html::class, 'displayImpersonateBanner')) {
         Html::displayImpersonateBanner();
      }

      // menu toggle (responsive mode)
      echo "<input type='checkbox' id='formcreator-toggle-nav-responsive'>";
      echo "<label for='formcreator-toggle-nav-responsive' class='formcreator-nav-button'></label>";

      echo '<div id="header" class ="plugin_formcreator_leftHeader">';

      // menu toggle (desktop mode)
      echo "<input type='checkbox' id='formcreator-toggle-nav-desktop'>";
      echo "<label for='formcreator-toggle-nav-desktop' class='formcreator-nav-button'></label>";

      self::showTicketSummary();

      echo '<div id="header_top">';
      echo '<div id="c_logo"></div>';
      echo '</div>';

      // Left vertical menu
      echo '<div id="c_menu" class="plugin_formcreator_leftMenu">';

      $activeMenuItem = self::findActiveMenuItem();
      echo '<ul class="plugin_formcreator_services">';
      echo '<li class="' . ($activeMenuItem == self::MENU_CATALOG ? 'plugin_formcreator_selectedMenuItem' : '') . '">';
      echo '<a href="' . FORMCREATOR_ROOTDOC.'/front/wizard.php' . '">';
      echo '<span class="fa fa-paper-plane fc_list_icon" title="'.__('Seek assistance', 'formcreator').'"></span>';
      echo '<span class="label">'.__('Seek assistance', 'formcreator').'</span>';
      echo '</a></li>';

      echo '<li class="' . ($activeMenuItem == self::MENU_LAST_FORMS ? 'plugin_formcreator_selectedMenuItem' : '') . '">';
      echo '<a href="' . FORMCREATOR_ROOTDOC.'/front/issue.php?reset=reset' . '">';
      echo '<span class="fa fa-list fc_list_icon" title="'.__('My requests for assistance', 'formcreator').'"></span>';
      echo '<span class="label">'.__('My requests for assistance', 'formcreator').'</span>';
      echo '</a></li>';

      if (Session::haveRight("reservation", ReservationItem::RESERVEANITEM)) {
         echo '<li class="' . ($activeMenuItem == self::MENU_RESERVATIONS ? 'plugin_formcreator_selectedMenuItem' : '') . '">';
         echo '<a href="' . FORMCREATOR_ROOTDOC.'/front/reservationitem.php?reset=reset' . '">';
         echo '<span class="fa fa-calendar-check fa-calendar-check-o fc_list_icon" title="'.__('Book an asset', 'formcreator').'"></span>';
         echo '<span class="label">'.__('Book an asset', 'formcreator').'</span>';
         echo '</a></li>';
      }

      if (RSSFeed::canView()) {
         echo '<li class="' . ($activeMenuItem == self::MENU_FEEDS ? 'plugin_formcreator_selectedMenuItem' : '') . '">';
         echo '<a href="' . FORMCREATOR_ROOTDOC.'/front/wizardfeeds.php' . '">';
         echo '<span class="fa fa-rss fc_list_icon" title="'.__('Consult feeds', 'formcreator').'"></span>';
         echo '<span class="label">'.__('Consult feeds', 'formcreator').'</span>';
         echo '</a></li>';
      }

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

      echo '<main id="page" class="plugin_formcreator_page">';

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
      // preferences
      echo '<li id="plugin_formcreator_preferences_icon">';
      echo '<a href="'.$CFG_GLPI["root_doc"].'/front/preference.php" class="fa fa-cog" title="'.
            __s('My settings').'"><span id="preferences_icon" title="'.__s('My settings').'" alt="'.__s('My settings').'" class="button-icon"></span>';
      echo '</a></li>';
      // Logout
      echo '<li id="plugin_formcreator_logoutIcon" ><a href="'.$CFG_GLPI["root_doc"].'/front/logout.php';      /// logout without noAuto login for extauth
      if (isset($_SESSION['glpiextauth']) && $_SESSION['glpiextauth']) {
         echo '?noAUTO=1';
      }
      echo '" class="fa fa-sign-out fa-sign-out-alt" title="'.__s('Logout').'">';
      echo '<span id="logout_icon" title="'.__s('Logout').'" alt="'.__s('Logout').'" class="button-icon"></span></a>';
      echo '</li>';

      echo '</ul>';

      // avatar
      echo '<span id="plugin_formcreator_avatar">';
      $user = new User;
      $user->getFromDB($_SESSION['glpiID']);
      echo '<a href="'.$CFG_GLPI["root_doc"].'/front/preference.php"
               title="'.formatUserName (0, $_SESSION["glpiname"],
                                           $_SESSION["glpirealname"],
                                           $_SESSION["glpifirstname"], 0, 20).'">
            <img src="'.User::getThumbnailURLForPicture($user->fields['picture']).'"/>
            </a>
            </span>';

      // Profile and entity selection
      echo '<ul class="plugin_formcreator_entityProfile">';
      if (Session::getLoginUserID()) {
         Html::showProfileSelecter($CFG_GLPI["root_doc"]."/front/helpdesk.public.php");
      }
      echo "</ul>";
   }

   public static function showTicketSummary() {
      // show ticket summary
      echo "<span id='formcreator_servicecatalogue_ticket_summary'>";
      $status_count = PluginFormcreatorIssue::getTicketSummary();

      $link = PluginFormcreatorIssue::getSearchURL();
      echo "<span class='status status_incoming'>
            <a href='".$link."?".
                     Toolbox::append_params(PluginFormcreatorIssue::getProcessingCriteria(), '&amp;')."'>
            <span class='status_number'>".
            $status_count[Ticket::INCOMING]."
            </span>
            <label class='status_label'>".__('Processing')."</label>
            </a>
            </span>";

      echo "<span class='status status_waiting'>
            <a href='".$link."?".
                     Toolbox::append_params(PluginFormcreatorIssue::getWaitingCriteria(), '&amp;')."'>
            <span class='status_number'>".
            $status_count[Ticket::WAITING]."
            </span>
            <label class='status_label'>".__('Pending')."</label>
            </a>
            </span>";

      echo "<span class='status status_validate'>
            <a href='".$link."?".
                     Toolbox::append_params(PluginFormcreatorIssue::getValidateCriteria(), '&amp;')."'>
            <span class='status_number'>".
            $status_count['to_validate']."
            </span>
            <label class='status_label'>".__('To validate', 'formcreator')."</label>
            </a>
            </span>";

      echo "<span class='status status_solved'>
            <a href='".$link."?".
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
