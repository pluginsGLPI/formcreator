<?php
class PluginFormcreatorWizard {

   const MENU_CATALOG      = 1;
   const MENU_LAST_FORMS   = 2;
   const MENU_RESERVATIONS = 3;
   const MENU_FEEDS        = 4;
   const MENU_BOOKMARKS    = 5;

   public static function header($title) {
      global $CFG_GLPI, $HEADER_LOADED, $PLUGIN_HOOKS, $DB;

      // Print a nice HTML-head for help page
      if ($HEADER_LOADED) {
         return;
      }
      $HEADER_LOADED = true;

      // force layout of glpi
      $_SESSION['glpilayout'] = "lefttab";

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
      echo '<div id="header_top">';
      echo '<div id="c_logo"></div>';
      echo '</div>';


      // Left vertical menu
      echo '<div id="c_menu" class="plugin_formcreator_leftMenu">';

      $activeMenuItem = self::findActiveMenuItem();
      echo '<ul class="plugin_formcreator_services">';
      echo '<li class="' . ($activeMenuItem == self::MENU_CATALOG ? 'plugin_formcreator_selectedMenuItem' : '') . ' plugin_formcreator_serviceCatalogIcon">';
      echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/wizard.php' . '">';
      echo '<span class="fc_list_icon" title="'.__('Seek assistance', 'formcreator').'"></span>';
      echo '<label>'.__('Seek assistance', 'formcreator').'</label>';
      echo '</a></li>';

      echo '<li class="' . ($activeMenuItem == self::MENU_LAST_FORMS ? 'plugin_formcreator_selectedMenuItem' : '')  . ' plugin_formcreator_myRequestsIcon">';
      echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/issue.php' . '">';
      echo '<span class="fc_list_icon" title="'.__('My requests for assistance', 'formcreator').'"></span>';
      echo '<label>'.__('My requests for assistance', 'formcreator').'</label>';
      echo '</a></li>';

      if (Session::haveRight("reservation", ReservationItem::RESERVEANITEM)) {
         $reservation_item = new reservationitem;
         $entity_filter = getEntitiesRestrictRequest("", 'glpi_reservationitems', 'entities_id',
                                                     $_SESSION['glpiactiveentities']);
         $found_available_res = $reservation_item->find($entity_filter);
         if (count($found_available_res)) {
            echo '<li class="' . ($activeMenuItem == self::MENU_RESERVATIONS ? 'plugin_formcreator_selectedMenuItem' : '')  . ' plugin_formcreator_reservationsIcon">';
            echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/reservationitem.php' . '">';
            echo '<span class="fc_list_icon" title="'.__('Book an asset', 'formcreator').'"></span>';
            echo '<label>'.__('Book an asset', 'formcreator').'</label>';
            echo '</a></li>';
         }
      }

      if (RSSFeed::canView()) {
         echo '<li class="' . ($activeMenuItem == self::MENU_FEEDS ? 'plugin_formcreator_selectedMenuItem' : '')  . ' plugin_formcreator_feedsIcon">';
         echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/wizardfeeds.php' . '">';
         echo '<span class="fc_list_icon" title="'.__('Consult feeds', 'formcreator').'"></span>';
         echo '<label>'.__('Consult feeds', 'formcreator').'</label>';
         echo '</a></li>';
      }


      $query = "SELECT `glpi_bookmarks`.*,
                       `glpi_bookmarks_users`.`id` AS IS_DEFAULT
                FROM `glpi_bookmarks`
                LEFT JOIN `glpi_bookmarks_users`
                  ON (`glpi_bookmarks`.`itemtype` = `glpi_bookmarks_users`.`itemtype`
                      AND `glpi_bookmarks`.`id` = `glpi_bookmarks_users`.`bookmarks_id`
                      AND `glpi_bookmarks_users`.`users_id` = '".Session::getLoginUserID()."')
                WHERE `glpi_bookmarks`.`is_private`='1'
                  AND `glpi_bookmarks`.`users_id`='".Session::getLoginUserID()."'
                  OR `glpi_bookmarks`.`is_private`='0' ".
                     getEntitiesRestrictRequest("AND", "glpi_bookmarks", "", "", true);

      if ($result = $DB->query($query)) {
         if($numrows = $DB->numrows($result)) {
            echo '<li class="' . ($activeMenuItem == self::MENU_BOOKMARKS ? 'plugin_formcreator_selectedMenuItem' : '') . 'plugin_formcreator_bookmarksIcon">';
            Ajax::createIframeModalWindow('loadbookmark',
                  $CFG_GLPI["root_doc"]."/front/bookmark.php?action=load",
                  array('title'         => __('Load a bookmark'),
                        'reloadonclose' => true));
            echo '<a href="#" onclick="$(\'#loadbookmark\').dialog(\'open\');">';
            echo '<span class="fc_list_icon" title="'.__('Load a bookmark').'"></span>';
            echo '<label>'.__('Load a bookmark').'</label>';
            echo '</a>';
            echo '</li>';
         }
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

   }

   public static function footer() {
      return Html::helpFooter();
   }

   public static function showHeaderTopContent() {
      global $CFG_GLPI;

      // menu toggle (desktop mode)
      echo "<input type='checkbox' id='formcreator-toggle-nav-desktop'>";
      echo "<label for='formcreator-toggle-nav-desktop' class='formcreator-nav-button'></label>";

      // show ticket summary
      $options = array('criteria' => array(array('field'      => 4,
                                                 'searchtype' => 'equals',
                                                 'value'      => 'process',
                                                 'link'       => 'AND',
                                                 'value'      => 'notold')),
                       'reset'    => 'reset');
      echo "<span id='formcreator_servicecatalogue_ticket_summary'>";
      $status_count = PluginFormcreatorIssue::getTicketSummary(false);

      if (count($status_count[Ticket::INCOMING]) > 0) {
      echo "<span class='status status_incoming'>
            <a href='".FORMCREATOR_ROOTDOC."/front/issue.php?".
                    Toolbox::append_params($options,'&amp;')."'>
            <span class='status_number'>".
            $status_count[Ticket::INCOMING]."
            </span>
            <label class='status_label'>".__('Processing')."</label>
            </a>
            </span>";
      }

      if (count($status_count[Ticket::WAITING]) > 0) {
         $options['criteria'][0]['value'] = Ticket::WAITING;
         echo "<span class='status status_waiting'>
               <a href='".FORMCREATOR_ROOTDOC."/front/issue.php?".
                       Toolbox::append_params($options,'&amp;')."'>
               <span class='status_number'>".
               $status_count[Ticket::WAITING]."
               </span>
               <label class='status_label'>".__('Pending')."</label>
               </a>
               </span>";
      }

      if (count($status_count['to_validate']) > 0) {
         $options['criteria'][0]['value'] = Ticket::WAITING;
         echo "<span class='status status_validate'>
               <a href='".FORMCREATOR_ROOTDOC."/front/issue.php?".
                       Toolbox::append_params($options,'&amp;')."'>
               <span class='status_number'>".
               $status_count['to_validate']."
               </span>
               <label class='status_label'>".__('To validate', 'formcreator')."</label>
               </a>
               </span>";
      }

      if (count($status_count[Ticket::SOLVED]) > 0) {
         $options['criteria'][0]['value'] = 'old';
         echo "<span class='status status_solved'>
               <a href='".FORMCREATOR_ROOTDOC."/front/issue.php?".
                       Toolbox::append_params($options,'&amp;')."'>
               <span class='status_number'>".
               $status_count[Ticket::SOLVED]."
               </span>
               <label class='status_label'>".__('Closed', 'formcreator')."</label>
               </a>
               </span>";
      }

      echo '</span>'; #formcreator_servicecatalogue_ticket_summary

      // icons
      echo '</ul>';
      echo '<ul class="plugin_formcreator_userMenu_icons">';
      // preferences
      echo '<li id="plugin_formcreator_preferences_icon">';
      echo '<a href="'.$CFG_GLPI["root_doc"].'/front/preference.php" title="'.
            __s('My settings').'"><span id="preferences_icon" title="'.__s('My settings').'" alt="'.__s('My settings').'" class="button-icon"></span>';
      echo '</a></li>';
      // Logout
      echo '<li id="plugin_formcreator_logoutIcon" ><a href="'.$CFG_GLPI["root_doc"].'/front/logout.php';      /// logout witout noAuto login for extauth
      if (isset($_SESSION['glpiextauth']) && $_SESSION['glpiextauth']) {
         echo '?noAUTO=1';
      }
      echo '" title="'.__s('Logout').'">';
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
