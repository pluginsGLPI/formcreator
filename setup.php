<?php
/**
 * Define the plugin's version and informations
 *
 * @return Array [name, version, author, homepage, license, minGlpiVersion]
 */
function plugin_version_formcreator ()
{
   return array('name'       => _n('Form', 'Forms', 2, 'formcreator'),
            'version'        => '0.85-1.2',
            'author'         => '<a href="mailto:jmoreau@teclib.com">Jérémy MOREAU</a>
                                  - <a href="http://www.teclib.com">Teclib\'</a>',
            'homepage'       => 'https://github.com/TECLIB/formcreator',
            'license'        => '<a href="../plugins/formcreator/LICENSE" target="_blank">GPLv2</a>',
            'minGlpiVersion' => "0.85");
}

/**
 * Check plugin's prerequisites before installation
 *
 * @return boolean
 */
function plugin_formcreator_check_prerequisites ()
{
   if (version_compare(GLPI_VERSION,'0.85','lt') || version_compare(GLPI_VERSION,'0.86','ge')) {
      echo __('This plugin requires GLPI >= 0.85 and GLPI < 0.86', 'formcreator');
   } else {
      return true;
   }
   return false;
}

/**
 * Check plugin's config before activation (if needed)
 *
 * @param string $verbose Set true to show all messages (false by default)
 * @return boolean
 */
function plugin_formcreator_check_config($verbose=false)
{
   return true;
}

/**
 * Initialize all classes and generic variables of the plugin
 */
function plugin_init_formcreator ()
{
   global $PLUGIN_HOOKS;

   // Set the plugin CSRF compliance (required since GLPI 0.84)
   $PLUGIN_HOOKS['csrf_compliant']['formcreator'] = true;

   // Massive Action definition
   $PLUGIN_HOOKS['use_massive_action']['formcreator'] = 1;

   // Add specific CSS
   $PLUGIN_HOOKS['add_css']['formcreator'][] = "css/styles.css";

   $PLUGIN_HOOKS['menu_toadd']['formcreator'] = array(
      'admin'    => 'PluginFormcreatorForm',
      'helpdesk' => 'PluginFormcreatorFormlist',
   );

   // Add specific JavaScript
   $PLUGIN_HOOKS['add_javascript']['formcreator'][] = 'scripts/forms-validation.js.php';
   $PLUGIN_HOOKS['add_javascript']['formcreator'][] = 'scripts/scripts.js.php';

   // Add a link in the main menu plugins for technician and admin panel
   $PLUGIN_HOOKS['menu_entry']['formcreator'] = 'front/formlist.php';

   // Config page
   $plugin = new Plugin();
   $links  = array();
   if (Session::haveRight('entity', UPDATE) && $plugin->isActivated("formcreator")) {
      $PLUGIN_HOOKS['config_page']['formcreator'] = 'front/form.php';
      $links['config'] = '/plugins/formcreator/front/form.php';
      $links['add']    = '/plugins/formcreator/front/form.form.php';
   }
   $img = '<img  src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/check.png"
               title="' . __('Forms waiting for validation', 'formcreator') . '" alt="Waiting forms list" />';

   $links[$img] = '/plugins/formcreator/front/formanswer.php';

   // Set options for pages (title, links, buttons...)
   $links['search'] = '/plugins/formcreator/front/formlist.php';
   $PLUGIN_HOOKS['submenu_entry']['formcreator']['options'] = array(
      'config'       => array('title'  => __('Setup'),
                              'page'   => '/plugins/formcreator/front/form.php',
                              'links'  => $links),
      'options'      => array('title'  => _n('Form', 'Forms', 2, 'formcreator'),
                              'links'  => $links),
   );

   // Load field class and all its method to manage fields
   Plugin::registerClass('PluginFormcreatorFields');

   // Notification
   Plugin::registerClass('PluginFormcreatorFormanswer', array(
      'notificationtemplates_types' => true
   ));

   if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE && isset($_SESSION['glpimenu'])) {
      unset($_SESSION['glpimenu']);
   }
}
