<?php
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * @since 0.90-1.5
 */
class PluginFormcreatorForm_Validator extends CommonDBRelation {

      // From CommonDBRelation
   static public $itemtype_1          = 'PluginFormcreatorForm';
   static public $items_id_1          = 'plugin_formcreator_forms_id';

   static public $itemtype_2          = 'itemtype';
   static public $items_id_2          = 'items_id';
   static public $checkItem_2_Rights  = self::HAVE_VIEW_RIGHT_ON_ITEM;

   const VALIDATION_USER  = 1;
   const VALIDATION_GROUP = 2;

   /**
    * @see  CommondDBTM::prepareInputForAdd
    */
   public function prepareInputForAdd($input) {
      global $DB;

      // generate a uniq id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }


   public static function install(Migration $migration)
   {
      global $DB;

      $obj   = new self();
      $table = self::getTable();
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id`                          int(11) NOT NULL AUTO_INCREMENT,
                     `plugin_formcreator_forms_id` int(11) NOT NULL,
                     `itemtype`                    varchar(255) NOT NULL DEFAULT '',
                     `items_id`                    int(11) NOT NULL,
                     `uuid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                     PRIMARY KEY (`id`),
                     UNIQUE KEY `unicity` (`plugin_formcreator_forms_id`, `itemtype`, `items_id`)
                  )
                  ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;";
         $DB->query($query) or plugin_formcrerator_upgrade_error($migration);
      }

      // Convert the old relation in glpi_plugin_formcreator_formvalidators table
      if (TableExists('glpi_plugin_formcreator_formvalidators')) {
         $table_form = PluginFormcreatorForm::getTable();
         $old_table = 'glpi_plugin_formcreator_formvalidators';
         $query = "INSERT INTO `$table` (`plugin_formcreator_forms_id`, `itemtype`, `items_id`)
               SELECT
                  `$old_table`.`forms_id`,
                  IF(`validation_required` = '".self::VALIDATION_USER."', 'User', 'Group'),
                  `$old_table`.`users_id`
               FROM `$old_table`
               LEFT JOIN `$table_form` ON (`$table_form`.`id` = `$old_table`.`forms_id`)
               WHERE `validation_required` > 1";
         $DB->query($query) or plugin_formcrerator_upgrade_error($migration);
         $migration->displayMessage('Backing up table glpi_plugin_formcreator_formvalidators');
         $migration->renameTable('glpi_plugin_formcreator_formvalidators', 'glpi_plugin_formcreator_formvalidators_backup');
      }

      // add uuid to validator
      if (!FieldExists($table, 'uuid', false)) {
         $migration->addField($table, 'uuid', 'string');
         $migration->migrationOneTable($table);
      }

      // fill missing uuid
      $all_validators = $obj->find("uuid IS NULL");
      foreach($all_validators as $validators_id => $validator) {
         $obj->update(array('id'   => $validators_id,
                            'uuid' => plugin_formcreator_getUuid()));
      }
   }

   public static function uninstall()
   {
      global $DB;

      $table = self::getTable();
      $query = "DROP TABLE IF EXISTS `$table`";
      return $DB->query($query) or plugin_formcrerator_upgrade_error($migration);
   }

   /**
    * Import a form's validator into the db
    * @see PluginFormcreatorForm::importJson
    *
    * @param  integer $forms_id  id of the parent form
    * @param  array   $validator the validator data (match the validator table)
    * @return integer the validator's id
    */
   public static function import($forms_id = 0, $validator = array()) {
      $item = new self;

      $validator['plugin_formcreator_forms_id'] = $forms_id;

      if ($validators_id = plugin_formcreator_getFromDBByField($item, 'uuid', $validator['uuid'])) {
         // add id key
         $validator['id'] = $validators_id;

         // update section
         $item->update($validator);
      } else {
         //create section
         $validators_id = $item->add($validator);
      }

      return $validators_id;
   }

   /**
    * Export in an array all the data of the current instanciated validator
    * @return array the array with all data (with sub tables)
    */
   public function export() {
      if (!$this->getID()) {
         return false;
      }

      $validator = $this->fields;

      // remove key and fk
      unset($validator['id'],
            $validator['plugin_formcreator_forms_id']);

      if (is_subclass_of($validator['itemtype'], 'CommonDBTM')) {
         $validator_obj = new $validator['itemtype'];
         if ($validator_obj->getFromDB($validator['items_id'])) {

            // replace id data
            $identifier_field = isset($validator_obj->fields['completename'])
                                 ? 'completename'
                                 : 'name';
            $validator['_item'] = $validator_obj->fields[$identifier_field];
         }
      }
      unset($validator['items_id']);

      return $validator;
   }
}
