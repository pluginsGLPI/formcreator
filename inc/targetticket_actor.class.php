<?php
class PluginFormcreatorTargetTicket_Actor extends CommonDBTM
{

   static function getEnumActorType() {
      return array(
            'creator'            => __("Form requester", 'formcreator'),
            'validator'          => __("Form validator", 'formcreator'),
            'person'             => __("Specific person", 'formcreator'),
            'question_person'    => __("Person from the question", 'formcreator'),
            'group'              => __('Specific group', 'formcreator'),
            'question_group'     => __('Group from the question', 'formcreator'),
            'supplier'           => __('Specific entity', 'formcreator'),
            'question_supplier'  => __('Supplier from the question', 'formcreator'),
            'question_actors'    => __('Actors from the question', 'formcreator'),
      );
   }

   public static function install(Migration $migration)
   {
      global $DB;

      $enum_actor_type = "'".implode("', '", array_keys(self::getEnumActorType()))."'";

      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `plugin_formcreator_targettickets_id` int(11) NOT NULL,
                    `actor_role` enum('requester','observer','assigned') NOT NULL,
                    `actor_type` enum($enum_actor_type) NOT NULL,
                    `actor_value` int(11) DEFAULT NULL,
                    `use_notification` BOOLEAN NOT NULL DEFAULT TRUE,
                    KEY `plugin_formcreator_targettickets_id` (`plugin_formcreator_targettickets_id`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci";
         $DB->query($query) or die($DB->error());
      } else {
         $current_enum_actor_type = PluginFormcreatorCommon::getEnumValues($table, 'actor_type');
         if (count($current_enum_actor_type) != count(self::getEnumActorType())) {
            $query = "ALTER TABLE `$table`
            CHANGE COLUMN `actor_type` `actor_type`
            ENUM($enum_actor_type)
            NOT NULL";
            $DB->query($query) or die($DB->error());
         }
      }
   }

   public static function uninstall()
   {
      global $DB;

      $table = self::getTable();
      $query = "DROP TABLE IF EXISTS `$table`";
      return $DB->query($query) or die($DB->error());
   }

}