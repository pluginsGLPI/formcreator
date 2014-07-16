<?php

class PluginFormcreatorSection extends CommonDBChild
{
   static public $itemtype = "PluginFormcreatorForm";
   static public $items_id = "plugin_formcreator_forms_id";

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

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0)
   {
      return _n('Question', 'Queestions', $nb, 'formcreator');
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

         // Create questions table
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `plugin_formcreator_forms_id` tinyint(1) NOT NULL,
                     `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                     `order` int(11) NOT NULL DEFAULT '0'
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

   /**
    * Prepare input datas for adding the section
    * Check fields values and get the order for the new section
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForAdd($input)
   {
      global $DB;

      // Control fields values :
      // - name is required
      if(empty($input['name'])) {
         Session::addMessageAfterRedirect(__('The title is required', 'formcreato'), false, ERROR);
         return array();
      }

      // Get next order
      $obj = new self();
      $query = "SELECT MAX(`order`) AS `order`
                FROM `{$obj->getTable()}`
                WHERE `plugin_formcreator_forms_id` = {$input['plugin_formcreator_forms_id']}";
      $result = $DB->query($query);
      $line = $DB->fetch_array($result);
      $input['order'] = $line['order'] + 1;

      return $input;
   }


   /**
    * Actions done after the PURGE of the item in the database
    * Reorder other sections
    *
    * @return nothing
   **/
   public function post_purgeItem()
   {
      global $DB;

      $query = "UPDATE `{$this->getTable()}` SET
                `order` = `order` - 1
                WHERE `order` > {$this->fields['order']}
                AND plugin_formcreator_forms_id = {$this->fields['plugin_formcreator_forms_id']}";
      $DB->query($query);
   }
}
