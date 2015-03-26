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
      return _n('Section', 'Sections', $nb, 'formcreator');
   }

   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean True on success
    */
   public static function install(Migration $migration)
   {
      $obj   = new self();
      $table = $obj->getTable();

      // Create new table
      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         // Create questions table
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `plugin_formcreator_forms_id` int(11) NOT NULL,
                     `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                     `order` int(11) NOT NULL DEFAULT '0'
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci;";
         $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
      } else {
         /**
          * Migration of special chars from previous versions
          *
          * @since 0.85-1.2.3
          */
         $query  = "SELECT `id`, `name`
                    FROM `$table`";
         $result = $GLOBALS['DB']->query($query);
         while ($line = $GLOBALS['DB']->fetch_array($result)) {
            $query_update = 'UPDATE `' . $table . '` SET
                               `name` = "' . plugin_formcreator_encode($line['name']) . '"
                             WHERE `id` = ' . $line['id'];
            $GLOBALS['DB']->query($query_update) or die ($GLOBALS['DB']->error());
         }
      }

      // Migration from previous version => Remove useless target field
      if(FieldExists($table, 'plugin_formcreator_targets_id', false)) {
         $GLOBALS['DB']->query("ALTER TABLE `$table` DROP `plugin_formcreator_targets_id`;");
      }

      // Migration from previous version => Rename "position" into "order" and start order from 1 instead of 0
      if(FieldExists($table, 'position', false)) {
         $GLOBALS['DB']->query("ALTER TABLE `$table` CHANGE `position` `order` INT(11) NOT NULL DEFAULT '0';");
         $GLOBALS['DB']->query("UPDATE `$table` SET `order` = `order` + 1;");
      }

      // Migration from previous version => Update Question table, then create a "description" question from content
      if(FieldExists($table, 'content', false)) {
         $version   = plugin_version_formcreator();
         $migration = new Migration($version['version']);
         PluginFormcreatorQuestion::install($migration);
         $table_questions = getTableForItemType('PluginFormcreatorQuestion');

         // Increment the order of questions which are in a section with a description
         $query = "UPDATE `$table_questions`
                   SET `order` = `order` + 1
                   WHERE `plugin_formcreator_sections_id` IN (
                     SELECT `id`
                     FROM $table
                     WHERE `content` != ''
                  );";
         $GLOBALS['DB']->query($query);

         // Create description from content
         $query = "INSERT INTO `$table_questions` (`plugin_formcreator_sections_id`, `fieldtype`, `name`, `description`, `order`)
                     SELECT `id`, 'description' AS fieldtype, CONCAT('Description ', `id`) AS name,  `content`, 1 AS `order`
                     FROM $table
                     WHERE `content` != ''";
         $GLOBALS['DB']->query($query);

         // Delete content column
         $GLOBALS['DB']->query("ALTER TABLE `$table` DROP `content`;");
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
      $obj = new self();
      $GLOBALS['DB']->query('DROP TABLE IF EXISTS `'.$obj->getTable().'`');

      // Delete logs of the plugin
      $GLOBALS['DB']->query('DELETE FROM `glpi_logs` WHERE itemtype = "' . __CLASS__ . '"');

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
      // Decode (if already encoded) and encode strings to avoid problems with quotes
      foreach ($input as $key => $value) {
         $input[$key] = plugin_formcreator_encode($value);
      }

      // Control fields values :
      // - name is required
      if(empty($input['name'])) {
         Session::addMessageAfterRedirect(__('The title is required', 'formcreato'), false, ERROR);
         return array();
      }

      // Get next order
      $obj    = new self();
      $query  = "SELECT MAX(`order`) AS `order`
                 FROM `{$obj->getTable()}`
                 WHERE `plugin_formcreator_forms_id` = {$input['plugin_formcreator_forms_id']}";
      $result = $GLOBALS['DB']->query($query);
      $line   = $GLOBALS['DB']->fetch_array($result);
      $input['order'] = $line['order'] + 1;

      return $input;
   }

   /**
    * Prepare input datas for updating the form
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForUpdate($input)
   {
      return $this->prepareInputForAdd($input);
   }


   /**
    * Actions done after the PURGE of the item in the database
    * Reorder other sections
    *
    * @return nothing
   **/
   public function post_purgeItem()
   {
      $query = "UPDATE `{$this->getTable()}` SET
                  `order` = `order` - 1
                WHERE `order` > {$this->fields['order']}
                AND plugin_formcreator_forms_id = {$this->fields['plugin_formcreator_forms_id']}";
      $GLOBALS['DB']->query($query);
   }
}
