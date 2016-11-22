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
      global $DB;

      $table = self::getTable();

      // Create new table
      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         // Create questions table
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `plugin_formcreator_forms_id` int(11) NOT NULL,
                     `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                     `order` int(11) NOT NULL DEFAULT '0',
                     `uuid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      } else {
         /**
          * Migration of special chars from previous versions
          *
          * @since 0.85-1.2.3
          */
         $query  = "SELECT `id`, `name`
                    FROM `$table`";
         $result = $DB->query($query);
         while ($line = $DB->fetch_array($result)) {
            $query_update = "UPDATE `$table` SET
                               `name` = '".plugin_formcreator_encode($line['name'])."'
                             WHERE `id` = ".$line['id'];
            $DB->query($query_update) or die ($DB->error());
         }
      }

      // Migration from previous version => Remove useless target field
      if(FieldExists($table, 'plugin_formcreator_targets_id', false)) {
         $DB->query("ALTER TABLE `$table` DROP `plugin_formcreator_targets_id`;");
      }

      // Migration from previous version => Rename "position" into "order" and start order from 1 instead of 0
      if(FieldExists($table, 'position', false)) {
         $DB->query("ALTER TABLE `$table` CHANGE `position` `order` INT(11) NOT NULL DEFAULT '0';");
         $DB->query("UPDATE `$table` SET `order` = `order` + 1;");
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
         $DB->query($query);

         // Create description from content
         $query = "INSERT INTO `$table_questions` (`plugin_formcreator_sections_id`, `fieldtype`, `name`, `description`, `order`)
                     SELECT `id`, 'description' AS fieldtype, CONCAT('Description ', `id`) AS name,  `content`, 1 AS `order`
                     FROM $table
                     WHERE `content` != ''";
         $DB->query($query);

         // Delete content column
         $DB->query("ALTER TABLE `$table` DROP `content`;");
      }

      // add uuid to sections
      if (!FieldExists($table, 'uuid', false)) {
         $migration->addField($table, 'uuid', 'string');
         $migration->migrationOneTable($table);
      }

      // fill missing uuid (force update of sections, see self::prepareInputForUpdate)
      $obj = new self();
      $all_sections = $obj->find("uuid IS NULL");
      foreach($all_sections as $sections_id => $section) {
         $obj->update(array('id' => $sections_id));
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

      // Delete logs of the plugin
      $log = new Log;
      $log->deleteByCriteria(array('itemtype' => __CLASS__));

      $table = self::getTable();
      $DB->query("DROP TABLE IF EXISTS `$table`");

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

      // Decode (if already encoded) and encode strings to avoid problems with quotes
      foreach ($input as $key => $value) {
         $input[$key] = plugin_formcreator_encode($value);
      }

      // Control fields values :
      // - name is required
      if(isset($input['name'])
         && empty($input['name'])) {
         Session::addMessageAfterRedirect(__('The title is required', 'formcreato'), false, ERROR);
         return array();
      }

      // generate a uniq id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      // Get next order
      $table  = self::getTable();
      $query  = "SELECT MAX(`order`) AS `order`
                 FROM `$table`
                 WHERE `plugin_formcreator_forms_id` = {$input['plugin_formcreator_forms_id']}";
      $result = $DB->query($query);
      $line   = $DB->fetch_array($result);
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
      if (!isset($input['plugin_formcreator_forms_id'])) {
         $input['plugin_formcreator_forms_id'] = $this->fields['plugin_formcreator_forms_id'];
      }
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
      global $DB;

      $table = self::getTable();
      $query = "UPDATE `$table` SET
                  `order` = `order` - 1
                WHERE `order` > {$this->fields['order']}
                AND plugin_formcreator_forms_id = {$this->fields['plugin_formcreator_forms_id']}";
      $DB->query($query);

      $question = new PluginFormcreatorQuestion();
      $question->deleteByCriteria(array('plugin_formcreator_sections_id' => $this->getID()), 1);
   }

   /**
    * Import a form's section into the db
    * @see PluginFormcreatorForm::importJson
    *
    * @param  integer $forms_id  id of the parent form
    * @param  array   $section the section data (match the section table)
    * @return integer the section's id
    */
   public static function import($forms_id = 0, $section = array()) {
      $item = new self;

      $section['plugin_formcreator_forms_id'] = $forms_id;
      $section['_skip_checks']                = true;

      if ($sections_id = plugin_formcreator_getFromDBByField($item, 'uuid', $section['uuid'])) {
         // add id key
         $section['id'] = $sections_id;

         // update section
         $item->update($section);
      } else {
         //create section
         $sections_id = $item->add($section);
      }

      if ($sections_id
          && isset($section['_questions'])) {
         foreach($section['_questions'] as $question) {
            PluginFormcreatorQuestion::import($sections_id, $question);
         }
      }

      return $sections_id;
   }

   /**
    * Export in an array all the data of the current instanciated section
    * @return array the array with all data (with sub tables)
    */
   public function export() {
      if (!$this->getID()) {
         return false;
      }

      $form_question = new PluginFormcreatorQuestion;
      $section       = $this->fields;

      // remove key and fk
      unset($section['id'],
            $section['plugin_formcreator_forms_id']);

      // get questions
      $section['_questions'] = [];
      $all_questions = $form_question->find("plugin_formcreator_sections_id = ".$this->getID());
      foreach($all_questions as $questions_id => $question) {
         if ($form_question->getFromDB($questions_id)) {
            $section['_questions'][] = $form_question->export();
         }
      }

      return $section;
   }
}
