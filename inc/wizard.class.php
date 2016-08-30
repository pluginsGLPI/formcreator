<?php
class PluginFormcreatorWizard {

   const MENU_CATALOG      = 1;
   const MENU_LAST_FORMS   = 2;
   const MENU_RESERVATIONS = 3;
   const MENU_SETTINGS     = 4;

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
         echo "<body class='$body_class'>";
//       echo '<div id="header">';
//       echo '<div id="header_top">';
//       echo '<div id="c_logo">';
//       echo '<a href="'.$CFG_GLPI["root_doc"].'/front/central.php" accesskey="1" title="home"></a>';
//       echo '</div>';
//       echo '<div id="c_preference">';
//       echo '<ul><li id="deconnexion"><a href="' . $CFG_GLPI["root_doc"] . '/front/logout.php"></a></li></ul>';
//       echo '</div>';
//       echo '</div>';
//       echo '<div id="c_menu">';
//       echo '</div>';
//       echo '<div id="c_ssmenu2">';
//       echo '<ul>';
//       // check user id : header used for display messages when session logout
//       if (Session::getLoginUserID()) {
//          html::showProfileSelecter($CFG_GLPI["root_doc"]."/front/helpdesk.public.php");
//       }
//       echo '</ul>';
//       echo '</div>';
//       echo '</div>';
//      echo '</div>';

      echo '<div class="plugin_formcreator_container">';
      echo '<div id="header" class ="plugin_formcreator_leftHeader">';
      echo '<div id="header_top">';
      echo '<div id="c_logo">';
      echo '</div>';
      echo '</div>';

      // Left vertical menu
      $activeMenuItem = self::findActiveMenuItem();
      echo '<div id="c_menu" class="plugin_formcreator_leftMenu"><ul>';
      echo '<li class="' . ($activeMenuItem == self::MENU_CATALOG ? 'plugin_formcreator_selectedMenuItem' : '') . '">';
      echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/wizard.php' . '">' . __('Service catalog', 'formcreator') . '</a></li>';

      echo '<li class="' . ($activeMenuItem == self::MENU_LAST_FORMS ? 'plugin_formcreator_selectedMenuItem' : '')  . '">';
      echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/reservationitem.php' . '">' . __('My last forms', 'formcreator') . '</a></li>';

      echo '<li class="' . ($activeMenuItem == self::MENU_RESERVATIONS ? 'plugin_formcreator_selectedMenuItem' : '')  . '">';
      echo '<a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/reservationitem.php' . '">' . _n('Reservation', 'Reservations', 2) . '</a></li>';

      // Profile and entity selection
      // check user id : header used for display messages when session logout
      if (Session::getLoginUserID()) {
         self::showProfileSelecter($CFG_GLPI["root_doc"]."/front/helpdesk.public.php");
      }

      echo '<li id="plugin_formcreator_preferences_icon" class="' . ($activeMenuItem == self::MENU_SETTINGS ? 'plugin_formcreator_selectedMenuItem' : '')  . '">';
      echo '<a href="'.$CFG_GLPI["root_doc"].'/front/preference.php" title="'.
            __s('My settings').'"><span id="preferences_icon" title="'.__s('My settings').'" alt="'.__s('My settings').'" class="button-icon"></span>';
//      echo '<span id="myname">';
//       echo formatUserName (0, $_SESSION["glpiname"], $_SESSION["glpirealname"],
//             $_SESSION["glpifirstname"], 0, 20);
//      echo '</span>';
      echo '</a></li>';

      // Logout
      echo '<li id="plugin_formcreator_logoutIcon" ><a href="'.$CFG_GLPI["root_doc"].'/front/logout.php';      /// logout witout noAuto login for extauth
      if (isset($_SESSION['glpiextauth']) && $_SESSION['glpiextauth']) {
         echo '?noAUTO=1';
      }
      echo '" title="'.__s('Logout').'">';
      //echo __s('Logout');
      echo '<span id="logout_icon" title="'.__s('Logout').'" alt="'.__s('Logout').'" class="button-icon"></span>';
      echo '</li>';

      echo '</ul></div>';
//       echo '<div id="c_preference"><ul>';
//       echo '<li id="deconnexion"><a href="" title="'.__s('Logout').'">';
//       echo '<span id="logout_icon" title="'.__s('Logout').'" alt="'.__s('Logout').'" class="button-icon"></span>';
//       echo '</a></li>';
//       echo '</ul></div>';

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

      echo "</div>"; // fin de la div id ='page' initiée dans la fonction header

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
      self::displayDebugInfos();
      echo "</body></html>";
      closeDBConnections();
   }

   protected static function findActiveMenuItem() {
      if (strpos($_SERVER['REQUEST_URI'], "plugins/formcreator/front/wizard.php") !== false) {
         return self::MENU_CATALOG;
      }
      if (strpos($_SERVER['REQUEST_URI'], "plugins/formcreator/front/wizard.php") !== false) {
         return self::MENU_LAST_FORMS;
      }
      if (strpos($_SERVER['REQUEST_URI'], "plugins/formcreator/front/reservationitem.php") !== false) {
         return self::MENU_RESERVATIONS;
      }
      if (strpos($_SERVER['REQUEST_URI'], "plugins/formcreator/front/wizard.php") !== false) {
         return self::MENU_SETTINGS;
      }
      return false;
   }

   protected static function showProfileSelecter($target) {
      global $CFG_GLPI;

      if (count($_SESSION["glpiprofiles"]) > 1) {
         echo '<li class="plugin_formcreator_leftMenuItem_separator"><form name="form" method="post" action="'.$target.'">';
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
         if (count($_SESSION["glpiprofiles"]) > 1) {
            echo '<li>';
         } else {
            echo '<li class="plugin_formcreator_leftMenuItem_separator">';
         }
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