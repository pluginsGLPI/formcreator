<?php
/**
 * Install all necessary elements for the plugin
 *
 * @return boolean True if success
 */
function plugin_formcreator_install()
{
   $version   = plugin_version_formcreator();
   $migration = new Migration($version['version']);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginFormcreator' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'install')) {
            $classname::install($migration);
         }
      }
   }

   return true;
}

/**
 * Uninstall previously installed elements of the plugin
 *
 * @return boolean True if success
 */
function plugin_formcreator_uninstall()
{
   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginFormcreator' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'uninstall')) {
            $classname::uninstall();
         }
      }
   }
   return true ;
}

/**
 * Define Dropdown tables to be manage in GLPI :
 */
function plugin_formcreator_getDropdown()
{
   return array(
       'PluginFormcreatorHeader'   => _n('Header', 'Headers', 2, 'formcreator'),
       'PluginFormcreatorCategory' => _n('Form category', 'Form categories', 2, 'formcreator'),
   );
}

/**
 * Define specific search request
 *
 * @param  String $type    Itemtype for the search engine
 * @return String          Specific search request
 */
function plugin_formcreator_addDefaultWhere($type)
{
   switch ($type) {
      case "PluginFormcreatorFormanswer" :
         if (Session::haveRight('config', UPDATE)
            || Session::haveRight('ticketvalidation', TicketValidation::VALIDATEINCIDENT)
            || Session::haveRight('ticketvalidation', TicketValidation::VALIDATEREQUEST)) {
            return " 1=1 ";

         } else {
            return " `glpi_plugin_formcreator_formanswers`.`requester_id` = " . $_SESSION['glpiID'];
         }
         break;
      default:
         return '';
   }
}

function plugin_formcreator_AssignToTicket($types)
{
   $types['PluginFormcreatorFormanswer'] = PluginFormcreatorFormanswer::getTypeName();

   return $types;
}

// Define actions :
function plugin_formcreator_MassiveActions($type) {

   switch ($type) {
      // New action for core and other plugin types : name = plugin_PLUGINNAME_actionname
      case 'PluginFormcreatorForm' :
         return array(
            'PluginFormcreatorForm' . MassiveAction::CLASS_ACTION_SEPARATOR . 'Duplicate' => _x('button', 'Duplicate'),
            'PluginFormcreatorForm' . MassiveAction::CLASS_ACTION_SEPARATOR . 'Transfert' => __('Transfer'),
         );
   }
   return array();
}

function plugin_formcreator_pre_ticket_purge(CommonDBTM $item) {
   $answerTicket = new PluginFormcreatorFormanswer_Ticket();
   return $answerTicket->deleteByCriteria(array(
      'tickets_id'      => $item->getID()
   ));
}