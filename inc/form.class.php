<?php

class PluginFormcreatorForm extends CommonDBTM
{
   public $dohistory = true;

   /**
    * Check if current user have the right to create and modify requests
    *
    * @return boolean True if he can create and modify requests
    */
   public static function canCreate()
   {
      return true;
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView()
   {
      return true;
   }

   public function getSearchOptions()
   {
      $tab = array(
         '1' => array(
            'table'         => $this->getTable(),
            'field'         => 'id',
            'name'          => __('ID', 'formcreator'),
            'datatype'      => 'number',
            'searchtype'    => 'equals',
            'massiveaction' => false
         ),
         '2' => array(
            'table'         => $this->getTable(),
            'field'         => 'name',
            'name'          => __('Name', 'formcreator'),
            'datatype'      => 'itemlink',
            'massiveaction' => false
         ),
         '3' => array(
            'table'         => $this->getTable(),
            'field'         => 'is_active',
            'name'          => __('Status', 'formcreator'),
            'datatype'      => 'specific',
            'searchtype'    => array('equals', 'notequals'),
            'massiveaction' => false
         ),
      );
      return $tab;
   }

   static function getDefaultSearchRequest() {

      $search = array('field'      => array(0 => 3),
                      'searchtype' => array(0 => 'equals'),
                      'contains'   => array(0 => 1),
                      'sort'       => 2,
                      'order'      => 'ASC');

     return $search;
   }


   static function getSpecificValueToDisplay($field, $values, array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'is_active':
            return ($values[$field] == 0) ? __('Inactive', 'formcreator') : __('Active', 'formcreator');
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   /**
    * @since version 0.84
    *
    * @param $field
    * @param $name            (default '')
    * @param $values          (default '')
    * @param $options   array
    **/
   static function getSpecificValueToSelect($field, $name='', $values='', array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      $options['display'] = false;

      switch ($field) {
         case 'is_active' :
            $output  = "<select name='".$name."'>";
            $output .=  "<option value='0' ".(($values[$field] == 0)?" selected ":"").">"
                        . __('Inactive', 'formcreator')
                        . "</option>";
            $output .=  "<option value='1' ".(($values[$field] == 1)?" selected ":"").">"
                        . __('Active', 'formcreator')
                        . "</option>";
            $output .=  "</select>";

            return $output;
            break;
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }


   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0)
   {
      return _n('Form', 'Forms', $nb, 'formcreator');
   }

   public function showList()
   {
      global $DB;

      echo '<div class="center">';

      // Get entities we can access
      $entities_table = getTableForItemType('Entity');
      $where          = getEntitiesRestrictRequest( "", $entities_table, "", "", true, true);

      // Show header for the current entity or it's first parent header
      $table  = getTableForItemType('PluginFormcreatorHeader');
      $query  = "SELECT $table.`comment`
                 FROM $table, $entities_table
                 WHERE $where
                 ORDER BY $entities_table.`completename` DESC";
      $result = $DB->query($query);
      if(!empty($result)) {
         list($description) = $DB->fetch_array($result);
         echo '<table class="tab_cadre_fixe">';
         echo '<tr><td>' . html_entity_decode($description) . '</td></tr>';
         echo '</table>';
         echo '<br />';
      }

      // Show categories
      $cat_table  = getTableForItemType('PluginFormcreatorCategory');
      $form_table = getTableForItemType('PluginFormcreatorCategory');
      $query  = "SELECT $cat_table.`name`
                 FROM $cat_table
                 WHERE 0 < (
                     SELECT COUNT(id)
                     FROM $form_table
                     WHERE $form_table.`formcreator_categories_id` = $cat_table.`id
                     AND $form_table.`is_active` = 1
                     AND $where
                     AND $form_table.`language` = {$_SESSION['glpilanguage']}
                  )
                 ORDER BY $cat_table.`name` ASC";
      $result = $DB->query($query);
      if(!empty($result)) {
         echo '<table class="tab_cadre_fixe">';
         while(list($category_name) = $DB->fetch_array($result)) {
            echo '<tr><th>' . $category_name . '</t></tr>';
         }
         echo '</table>';
      }

      echo '</div>';
   }



   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      switch ($item->getType()) {
         case "PluginFormcreatorConfig":
            $object  = new self;
            $founded = $object->find();
            $number  = count($founded);
            return self::createTabEntry(self::getTypeName($number), $number);
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      switch ($item->getType()) {
         case "PluginFormcreatorConfig":
            $params = $_GET;
            Search::manageGetValues(__CLASS__);
            Search::showGenericSearch(__CLASS__, $_GET);
            Search::showList(__CLASS__, $params);
      }
      return false;
   }

   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean True on success
    */
   public static function install(Migration $migration)
   {
      global $DB;

      $obj = new self();
      $table = $obj->getTable();

      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         // Create default request type
         $query  = "SELECT id FROM `glpi_requesttypes` WHERE `name` LIKE 'Formcreator';";
         $result = $DB->query($query) or die ($DB->error());
         if( !empty($result)) {
            list($requesttype) = $DB->fetch_array($result);
         } else {
            $query = "INSERT IGNORE INTO `glpi_requesttypes` SET `name` = 'Formcreator';";
            $DB->query($query) or die ($DB->error());
            $requesttype = $DB->insert_id();
         }

         // Create Forms table
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `entities_id` int(11) NOT NULL DEFAULT '0',
                     `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
                     `requesttype` int(11) NOT NULL DEFAULT '$requesttype',
                     `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                     `description` varchar(255) COLLATE utf8_unicode_ci,
                     `content` longtext COLLATE utf8_unicode_ci,
                     `formcreator_categories_id` tinyint(3) UNSIGNED NOT NULL,
                     `is_active` tinyint(1) NOT NULL DEFAULT '0',
                     `language` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
                     `helpdesk_home` tinyint(1) NOT NULL DEFAULT '0'
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }

      return true;
   }

   /**
    * Database table uninstallation for the item type
    *
    * @return boolean True on success
    */
   public static function uninstall()
   {
      global $DB;

      $obj = new self();
      $DB->query('DROP TABLE IF EXISTS `'.$obj->getTable().'`');

      // Delete logs of the plugin
      $DB->query('DELETE FROM `glpi_logs` WHERE itemtype = "' . __CLASS__ . '"');

      return true;
   }
}
