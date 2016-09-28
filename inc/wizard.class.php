<?php
class PluginFormcreatorWizard {

   const MENU_CATALOG      = 1;
   const MENU_LAST_FORMS   = 2;
   const MENU_RESERVATIONS = 3;
   const MENU_FEEDS        = 4;

   public static function header($title) {
      global $CFG_GLPI, $HEADER_LOADED, $PLUGIN_HOOKS;

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

      echo '<div class="plugin_formcreator_container">';
      echo '<div id="header" class ="plugin_formcreator_leftHeader">';
      echo '<div id="header_top">';
      echo '<div id="c_logo">';
      echo '</div>';
      echo '</div>';

      // Left vertical menu
      $activeMenuItem = self::findActiveMenuItem();
      echo '<div id="c_menu" class="plugin_formcreator_leftMenu"><ul class="plugin_formcreator_services">';
      echo '<li class="' . ($activeMenuItem == self::MENU_CATALOG ? 'plugin_formcreator_selectedMenuItem' : '') . ' plugin_formcreator_serviceCatalogIcon">';
      echo '<span></span><a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/wizard.php' . '">' . __('Create an issue', 'formcreator') . '</a></li>';

      echo '<li class="' . ($activeMenuItem == self::MENU_LAST_FORMS ? 'plugin_formcreator_selectedMenuItem' : '')  . ' plugin_formcreator_myRequestsIcon">';
      echo '<span></span><a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/issue.php' . '">' . __('My issues', 'formcreator') . '</a></li>';

//       TODO : find the best way to reuse the code from GLPi for reservations and feeds
//       echo '<li class="' . ($activeMenuItem == self::MENU_RESERVATIONS ? 'plugin_formcreator_selectedMenuItem' : '')  . ' plugin_formcreator_reservationsIcon">';
//       echo '<span></span><a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/reservationitem.php' . '">' . __('Book an asset', 'formcreator', 2) . '</a></li>';

//       echo '<li class="' . ($activeMenuItem == self::MENU_FEEDS ? 'plugin_formcreator_selectedMenuItem' : '')  . ' plugin_formcreator_feedsIcon">';
//       echo '<span></span><a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/wizardfeeds.php' . '">' . __('Consult feeds', 'formcreator') . '</a></li>';

      echo '</ul>';
      echo '<div class="plugin_formcreator_leftMenuBottom">';

      // Profile and entity selection
      // check user id : header used for display messages when session logout
      echo '<ul class="plugin_formcreator_entityProfile">';
      if (Session::getLoginUserID()) {
         self::showProfileSelecter($CFG_GLPI["root_doc"]."/front/helpdesk.public.php");
      }
      echo '</ul>';

      echo '<div class="plugin_formcreator_userMenuCell">';
      echo '<span id="plugin_formcreator_avatar"></span>';
      echo '<div id="myname" class="plugin_formcreator_myname">' . formatUserName (0, $_SESSION["glpiname"], $_SESSION["glpirealname"],
                              $_SESSION["glpifirstname"], 0, 20) . '</div>';
      //echo '<div>';
      echo '<ul class="plugin_formcreator_userMenu_icons">';
      echo '<li id="plugin_formcreator_preferences_icon">';
      echo '<a href="'.$CFG_GLPI["root_doc"].'/front/preference.php" title="'.
            __s('My settings').'"><span id="preferences_icon" title="'.__s('My settings').'" alt="'.__s('My settings').'" class="button-icon"></span>';
      echo '</a></li>';

      // Bookmark
      echo '<li id="plugin_formcreator_bookmarkIcon">';
      Ajax::createIframeModalWindow('loadbookmark',
            $CFG_GLPI["root_doc"]."/front/bookmark.php?action=load",
            array('title'         => __('Load a bookmark'),
                  'reloadonclose' => true));
      echo '<a href="#" onclick="$(\'#loadbookmark\').dialog(\'open\');">';
      echo '<span id="bookmark_icon" title="' . __('Load a bookmark') . '" alt="' . __('Load a bookmark') . '" class="button-icon"></span>';
      echo '</a>';
      echo '</li>';

      // Logout
      echo '<li id="plugin_formcreator_logoutIcon" ><a href="'.$CFG_GLPI["root_doc"].'/front/logout.php';      /// logout witout noAuto login for extauth
      if (isset($_SESSION['glpiextauth']) && $_SESSION['glpiextauth']) {
         echo '?noAUTO=1';
      }
      echo '" title="'.__s('Logout').'">';
      echo '<span id="logout_icon" title="'.__s('Logout').'" alt="'.__s('Logout').'" class="button-icon"></span></a>';
      echo '</li>';

      echo '</ul></div></div></div>';
      echo '</div>';

      echo '<div id="page" class="plugin_formcreator_page">';

      // call static function callcron() every 5min
      CronTask::callCron();

   }

   public static function footer() {
      global $CFG_GLPI, $FOOTER_LOADED;

      // Print foot for help page
      if ($FOOTER_LOADED) {
         return;
      }
      $FOOTER_LOADED = true;

      echo "<div id='footer' class='plugin_formcreator_footer'>";
      echo "<table width='100%'><tr><td class='right'>";
      echo "<a href='http://glpi-project.org/'>";
      echo "<span class='copyright'>GLPI ".$CFG_GLPI["version"].
           " Copyright (C) ".
           "2015-".
           //date("Y"). // TODO, decomment this in 2016
           " by Teclib'".
           " - Copyright (C) 2003-2015 INDEPNET Development Team".
           "</span>";
      echo "</a></td></tr></table></div>";

      echo "</div>"; // fin de la div id ='page' initiée dans la fonction header

      if ($_SESSION['glpi_use_mode'] == Session::TRANSLATION_MODE) { // debug mode traduction
         echo "<div id='debug-float'>";
         echo "<a href='#see_debug'>GLPI TRANSLATION MODE</a>";
         echo "</div>";
      }

      if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) { // mode debug
         echo "<div id='debug-float'>";
         echo "<a href='#see_debug'>GLPI DEBUG MODE</a>";
         echo "</div>";
      }
      Html::displayDebugInfos();
      echo "</body></html>";
      closeDBConnections();
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

   protected static function showProfileSelecter($target) {
      global $CFG_GLPI;

      if (count($_SESSION["glpiprofiles"]) > 1) {
         echo '<li><form name="form" method="post" action="'.$target.'">';
         $values = array();
         foreach ($_SESSION["glpiprofiles"] as $key => $val) {
            $values[$key] = $val['name'];
         }

         Dropdown::showFromArray('newprofile',$values,
               array('value'     => $_SESSION["glpiactiveprofile"]["id"],
                     'width'     => '150px',
                     'on_change' => 'submit()'));
         Html::closeForm();
         echo '</li>';
      }

      if (Session::isMultiEntitiesMode()) {
         echo '<li>';
         Ajax::createModalWindow('entity_window', $CFG_GLPI['root_doc']."/ajax/entitytree.php",
               array('title'       => __('Select the desired entity'),
                     'extraparams' => array('target' => $target)));
         echo "<a onclick='entity_window.dialog(\"open\");' href='#modal_entity_content' title=\"".
               addslashes($_SESSION["glpiactive_entity_name"]).
               "\" class='entity_select' id='global_entity_select'>".
               $_SESSION["glpiactive_entity_shortname"]."</a>";

         echo "</li>";
      }
   }

}