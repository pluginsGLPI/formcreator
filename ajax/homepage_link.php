<?php
define('GLPI_ROOT', '../../../');
include (GLPI_ROOT . "/inc/autoload.function.php");
include (GLPI_ROOT . "/config/config.php");

/**
 * from /inc/db.function.php
 */
function importArrayFromDB($DATA) {

   $TAB = json_decode($DATA,true);

   // Use old scheme to decode
   if (!is_array($TAB)) {
      $TAB = array();

      foreach (explode(" ", $DATA) as $ITEM) {
         $A = explode("=>", $ITEM);

         if ((strlen($A[0]) > 0)
         && isset($A[1])) {
            $TAB[urldecode($A[0])] = urldecode($A[1]);
         }
      }
   }
   return $TAB;
}

function getName() {
   // Load Language file
   Session::loadLanguage(); //slow environnement without gettext cache (ex : in dev environement)
   return _n('Form','Forms', 2, 'formcreator');
}

echo '<li id="menu5"><a href="' . $GLOBALS['CFG_GLPI']['root_doc'];
echo '/plugins/formcreator/front/formlist.php" class="itemP">';
echo getName() . '</a></li>';