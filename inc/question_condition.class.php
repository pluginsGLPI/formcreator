<?php
class PluginFormcreatorQuestion_Condition extends CommonDBChild
{
   static public $itemtype = "PluginFormcreatorQuestion";
   static public $items_id = "plugin_formcreator_questions_id";

   /**
    * Import a question's condition into the db
    * @see PluginFormcreatorQuestion::import
    *
    * @param  integer $questions_id  id of the parent question
    * @param  array   $condition the condition data (match the condition table)
    * @return integer the condition's id
    */
   public static function import($questions_id = 0, $condition = array()) {
      $item = new self;

      $condition['plugin_formcreator_questions_id'] = $questions_id;

      if ($conditions_id = plugin_formcreator_getFromDBByField($item, 'uuid', $condition['uuid'])) {
         // add id key
         $condition['id'] = $conditions_id;

         // update condition
         $item->update($condition);
      } else {
         //create condition
         $conditions_id = $item->add($condition);
      }

      return $conditions_id;
   }

   /**
    * Export in an array all the data of the current instanciated condition
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
   public function export($remove_uuid = false) {
      if (!$this->getID()) {
         return false;
      }

      $condition = $this->fields;

      unset($condition['id'],
            $condition['plugin_formcreator_questions_id']);

      if ($remove_uuid) {
         $condition['uuid'] = '';
      }

      return $condition;
   }

   public static function install(Migration $migration)
   {
      global $DB;

      $table = self::getTable();
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                  `id`                                int(11)                NOT NULL AUTO_INCREMENT,
                  `plugin_formcreator_questions_id`   int(11)                NOT NULL DEFAULT '0',
                  `show_field`                        int(11)                NULL     DEFAULT NULL,
                  `show_condition`                    enum('==','!=','<','>','<=','>=') NULL DEFAULT NULL,
                  `show_value`                        varchar(255)           NULL     DEFAULT NULL ,
                  `show_logic`                        enum('AND','OR','XOR') NULL     DEFAULT NULL,
                  `uuid`                              varchar(255)           NULL     DEFAULT NULL
                  PRIMARY KEY (`id`)
                  )
                  ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }

      // Migration 0.85-1.0 => 0.85-1.1
      $question_table = 'glpi_plugin_formcreator_questions';
      if (FieldExists($question_table, 'show_type', false)) {
         // Migrate date from "questions" table to "questions_conditions" table
         $query  = "SELECT `id`, `show_type`, `show_field`, `show_condition`, `show_value`
         FROM $question_table";
         $result = $DB->query($query);
         while ($line = $DB->fetch_array($result)) {
            switch ($line['show_type']) {
               case 'hide' :
                  $show_rule = 'hidden';
                  break;
               default:
                  $show_rule = 'always';
            }
            switch ($line['show_condition']) {
               case 'notequal' :
                  $show_condition = '!=';
                  break;
               case 'lower' :
                  $show_condition = '<';
                  break;
               case 'greater' :
                  $show_condition = '>';
                  break;
               default:
                  $show_condition = '==';
            }

            $show_field = empty($line['show_field']) ? 'NULL' : $line['show_field'];

            $query_udate = "UPDATE `$question_table` SET
            `show_rule` = '$show_rule'
            WHERE `id` = " . $line['id'];
            $DB->query($query_udate) or die ($DB->error());

            $query_udate = "INSERT INTO `$table` SET
            `plugin_formcreator_questions_id` = {$line['id']},
            `show_field`     = $show_field,
            `show_condition` = '$show_condition',
            `show_value`     = '" . Toolbox::addslashes_deep($line['show_value']) . "'";
            $DB->query($query_udate) or die ($DB->error());
         }

         // Delete old fields
         $query = "ALTER TABLE `$table`
         DROP `show_type`,
         DROP `show_field`,
         DROP `show_condition`,
         DROP `show_value`;";
         $DB->query($query) or die ($DB->error());
      }

      // Migrate "question_conditions" table
      $query  = "SELECT `id`, `show_value`
      FROM `$table`";
      $result = $DB->query($query);
      while ($line = $DB->fetch_array($result)) {
         $query_update = "UPDATE `$table` SET
         `show_value` = '" . plugin_formcreator_encode($line['show_value']) . "'
                           WHERE `id` = " . $line['id'];
         $DB->query($query_update) or die ($DB->error());
      }

      // add uuid to questions conditions
      if (!FieldExists($table, 'uuid', false)) {
         $migration->addField($table, 'uuid', 'string');
         $migration->migrationOneTable($table);
      }

      // fill missing uuid (force update of questions, see self::prepareInputForUpdate)
      $condition_obj = new self();
      $all_conditions = $condition_obj->find("uuid IS NULL");
      foreach($all_conditions as $conditions_id => $condition) {
         $condition_obj->update(array('id'   => $conditions_id,
               'uuid' => plugin_formcreator_getUuid()));
      }

      return true;
   }

   public static function uninstall()
   {
      global $DB;

      $table = self::getTable();
      $query = "DROP TABLE IF EXISTS `$table`";
      $DB->query($query) or die($DB->error());

      return true;
   }

}