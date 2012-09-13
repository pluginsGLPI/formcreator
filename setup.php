<?php

function plugin_init_formcreator() {
   global $PLUGIN_HOOKS,$LANG,$CFG_GLPI;

   Plugin::registerClass('PluginFormcreatorForm');

   $PLUGIN_HOOKS['csrf_compliant']['formcreator'] = true;

   if (Session::haveRight("config", "w")) {
      $PLUGIN_HOOKS['config_page']['formcreator'] = 'front/form.php';
      $PLUGIN_HOOKS['submenu_entry']['formcreator']['options']['form']['links']['add']
                                                   = '/plugins/formcreator/front/form.form.php';
      $PLUGIN_HOOKS['submenu_entry']['formcreator']['options']['form']['links']['config']
                                                   = '/plugins/formcreator/front/form.php';
   }

   $PLUGIN_HOOKS['menu_entry']['formcreator'] = 'front/formlist.php';
   $PLUGIN_HOOKS["helpdesk_menu_entry"]['formcreator'] = '/front/formlist.php';

   $PLUGIN_HOOKS['add_javascript']['formcreator'] = 'js/script.php';
   $PLUGIN_HOOKS['add_css']['formcreator']        = 'style.css';

}

function plugin_version_formcreator() {

   return array('name'           => 'Form Creator',
                'version'        => '1.3.2',
                'author'         => 'Gonéri Le Bouder, Nicolas Manceau, Dimitri Mouillard',
                'homepage'       => 'http://www.teclib.com',
                'minGlpiVersion' => '0.83.3',
                'license'        => 'GPLv2');
}

function plugin_formcreator_check_prerequisites() {

   if (GLPI_VERSION >= 0.83) {
      return true;
   } else {
      echo "GLPI version not compatible, need 0.83";
   }
}

function plugin_formcreator_check_config($verbose=false) {
   global $LANG;

   if (true) { // Your configuration check
      return true;
   }
   if ($verbose) {
      echo $LANG['plugins'][2];
   }
   return false;
}

?>
