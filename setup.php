<?php
/**
 * Define the plugin's version and informations
 *
 * @return Array [name, version, author, homepage, license, minGlpiVersion]
 */
function plugin_version_formcreator ()
{
   return array(
      'name'           => _n('Form', 'Forms', 2, 'formcreator'),
      'version'        => '0.90-1.3.3',
      'author'         => '<a href="mailto:contact@teclib.com">Jérémy MOREAU</a>
                           - <a href="http://www.teclib.com">Teclib\'</a>',
      'homepage'       => 'https://github.com/TECLIB/formcreator',
      'license'        => '<a href="../plugins/formcreator/LICENSE" target="_blank">GPLv2</a>',
      'minGlpiVersion' => "0.85"
   );
}

/**
 * Check plugin's prerequisites before installation
 *
 * @return boolean
 */
function plugin_formcreator_check_prerequisites ()
{
   if (version_compare(GLPI_VERSION,'0.85','lt')) {
      echo 'This plugin requires GLPI >= 0.85';
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
   global $PLUGIN_HOOKS, $CFG_GLPI;

   // Set the plugin CSRF compliance (required since GLPI 0.84)
   $PLUGIN_HOOKS['csrf_compliant']['formcreator'] = true;

   // Can assign FormAnswer to tickets
   $PLUGIN_HOOKS['assign_to_ticket']['formcreator'] = true;
   array_push($CFG_GLPI["ticket_types"], 'PluginFormcreatorFormanswer');

   $plugin = new Plugin();
   if ($plugin->isInstalled('formcreator') && $plugin->isActivated('formcreator')) {

      // Massive Action definition
      $PLUGIN_HOOKS['use_massive_action']['formcreator'] = 1;

      // Load menu entries if user is logged in and if he has access to at least one form
      if (isset($_SESSION['glpiID'])) {
         // If user have acces to one form or more, add link
         $form_table = getTableForItemType('PluginFormcreatorForm');
         $table_fp   = getTableForItemType('PluginFormcreatorFormprofiles');
         $where      = getEntitiesRestrictRequest( "", $form_table, "", "", true, false);
         $query      = "SELECT COUNT($form_table.id)
                        FROM $form_table
                        WHERE $form_table.`is_active` = 1
                        AND $form_table.`is_deleted` = 0
                        AND $form_table.`helpdesk_home` = 1
                        AND ($form_table.`language` = '{$_SESSION['glpilanguage']}' OR $form_table.`language` = '')
                        AND $where
                        AND ($form_table.`access_rights` != " . PluginFormcreatorForm::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
                           SELECT plugin_formcreator_forms_id
                           FROM $table_fp
                           WHERE plugin_formcreator_profiles_id = " . (int) $_SESSION['glpiactiveprofile']['id'] . "))";

         $result = $GLOBALS['DB']->query($query);
         list($nb) = $GLOBALS['DB']->fetch_array($result);
         if ($nb > 0) {
            $PLUGIN_HOOKS['menu_toadd']['formcreator']['helpdesk'] = 'PluginFormcreatorFormlist';
         }

         // Add a link in the main menu plugins for technician and admin panel
         $PLUGIN_HOOKS['menu_entry']['formcreator'] = 'front/formlist.php';

         // Config page
         $plugin = new Plugin();
         $links  = array();
         if (Session::haveRight('entity', UPDATE)) {
            $PLUGIN_HOOKS['config_page']['formcreator']         = 'front/form.php';
            $PLUGIN_HOOKS['menu_toadd']['formcreator']['admin'] = 'PluginFormcreatorForm';
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
      }

      // Load JS and CSS files if we are on a page witch need them
      if (strpos($_SERVER['REQUEST_URI'], "plugins/formcreator") !== false
          || strpos($_SERVER['REQUEST_URI'], "central.php") !== false
          || isset($_SESSION['glpiactiveprofile']) &&
             $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk') {

          // Add specific CSS
         $PLUGIN_HOOKS['add_css']['formcreator'][] = "css/styles.css";

         $PLUGIN_HOOKS['add_css']['formcreator'][]        = 'lib/pqselect/pqselect.min.css';
         $PLUGIN_HOOKS['add_javascript']['formcreator'][] = 'lib/pqselect/pqselect.min.js';

         // Add specific JavaScript
         $PLUGIN_HOOKS['add_javascript']['formcreator'][] = 'scripts/forms-validation.js.php';
         $PLUGIN_HOOKS['add_javascript']['formcreator'][] = 'scripts/scripts.js.php';
      }

      // Load field class and all its method to manage fields
      Plugin::registerClass('PluginFormcreatorFields');

      // Notification
      Plugin::registerClass('PluginFormcreatorFormanswer', array(
         'notificationtemplates_types' => true
      ));
   }
}

/**
 * Encode special chars
 *
 * @param  String    $string  The string to encode
 * @return String             The encoded string
 */
function plugin_formcreator_encode($string)
{
   $string = stripcslashes($string);
   $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
   $string = str_replace('&apos;', "'", $string);
   $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
   return $string;
}

/**
 * Encode special chars
 *
 * @param  String    $string  The string to encode
 * @return String             The encoded string
 */
function plugin_formcreator_decode($string)
{
   $string = stripcslashes($string);
   $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
   $string = str_replace('&apos;', "'", $string);
   return $string;
}
