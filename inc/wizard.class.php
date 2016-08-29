<?php
class PluginFormcreatorWizard {

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
      echo '<div id="header">';
      echo '<div id="header_top">';
      echo '<div id="c_logo">';
      echo '<a href="'.$CFG_GLPI["root_doc"].'/front/central.php" accesskey="1" title="home"></a>';
      echo '</div>';
      echo '<div id="c_preference">';
      echo '<ul><li id="deconnexion"><a href="' . $CFG_GLPI["root_doc"] . '/front/logout.php"></a></li></ul>';
      echo '</div>';
      echo '</div>';
      echo '<div id="c_menu">';
      echo '</div>';
      echo '<div id="c_ssmenu2">';
      echo '<ul>';
      // check user id : header used for display messages when session logout
      if (Session::getLoginUserID()) {
         html::showProfileSelecter($CFG_GLPI["root_doc"]."/front/helpdesk.public.php");
      }
      echo '</ul>';
      echo '</div>';
      echo '</div>';
      echo '</div>';

      echo '<div id="page">';

      // Left vertical menu
      echo '<div class="plugin_formcreator_card plugin_formcreator_leftMenu"><ul>';
      echo '<li><a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/reservationitem.php' . '">' . __('My last forms', 'formcreator') . '</a></li>';
      echo '<li><a href="' . $CFG_GLPI["root_doc"].'/plugins/formcreator/front/reservationitem.php' . '">' . _n('Reservation', 'Reservations', 2) . '</a></li>';
      echo '<li><a href="'.$CFG_GLPI["root_doc"].'/front/preference.php" title="'.
            __s('My settings').'">' . __s('My settings') . '</a></li>';

      // Profile and entity selection
      // check user id : header used for display messages when session logout
      if (Session::getLoginUserID()) {
         self::showProfileSelecter($CFG_GLPI["root_doc"]."/front/helpdesk.public.php");
      }

      // Logout
      echo '<li class="plugin_formcreator_leftMenuItem_separator"><a href="'.$CFG_GLPI["root_doc"].'/front/logout.php';
      /// logout witout noAuto login for extauth
      if (isset($_SESSION['glpiextauth']) && $_SESSION['glpiextauth']) {
         echo '?noAUTO=1';
      }
      echo '" title="'.__s('Logout').'">';
      echo __s('Logout');
      echo '</li>';

      echo '</ul></div>';

      // call static function callcron() every 5min
      CronTask::callCron();

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