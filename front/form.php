<?php

include ('../../../inc/includes.php');

//anonyme or not ?
Session::checkLoginUser();

if (Session::getLoginUserID()) {
   Html::header($LANG['plugin_formcreator']['name'],
               $_SERVER['PHP_SELF'],
               "plugins",
               "formcreator",
               "form"
               );
} else {
        //$_SESSION["glpilanguage"] = $CFG_GLPI['language'];
        Html::simpleHeader($LANG['plugin_formcreator']['name2'],array(__('Authentication') => "../../../index.php?co=1",
                                                   __('FAQ')  => "../../../front/helpdesk.faq.php",
                                                   $LANG['plugin_formcreator']['name2'] => "./formlist.php"));
}

Search::show('PluginFormcreatorForm');

Html::footer();
?>