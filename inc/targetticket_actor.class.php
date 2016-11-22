<?php
class PluginFormcreatorTargetTicket_Actor extends CommonDBTM
{

   public static function install(Migration $migration)
   {
      global $DB;

      $table = self::getTable();

      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `plugin_formcreator_targettickets_id` int(11) NOT NULL,
                    `actor_role` enum('requester','observer','assigned') NOT NULL,
                    `actor_type` enum('creator','validator','person','question_person','group','question_group','supplier','question_supplier') NOT NULL,
                    `actor_value` int(11) DEFAULT NULL,
                    `use_notification` BOOLEAN NOT NULL DEFAULT TRUE,
                    `uuid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    KEY `plugin_formcreator_targettickets_id` (`plugin_formcreator_targettickets_id`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci";
         $DB->query($query) or die($DB->error());
      }

      // add uuid to actor
      if (!FieldExists($table, 'uuid', false)) {
         $migration->addField($table, 'uuid', 'string');
         $migration->migrationOneTable($table);
      }

      // fill missing uuid
      $obj = new self();
      $all_actor = $obj->find("uuid IS NULL");
      foreach($all_actor as $actors_id => $actor) {
         $obj->update(array('id'   => $actors_id,
                            'uuid' => plugin_formcreator_getUuid()));
      }

   }

   public static function uninstall()
   {
      global $DB;

      $table = self::getTable();
      $query = "DROP TABLE IF EXISTS `$table`";
      return $DB->query($query) or die($DB->error());
   }

   /**
    * Import a form's targetticket's actor into the db
    * @see PluginFormcreatorTargetTicket::import
    *
    * @param  integer $targettickets_id  id of the parent targetticket
    * @param  array   $actor the actor data (match the actor table)
    * @return integer the actor's id
    */
   public static function import($targettickets_id = 0, $actor = array()) {
      $item = new self;

      $actor['plugin_formcreator_targettickets_id'] = $targettickets_id;

      // retrieve FK
      if (isset($actor['_question'])) {
         $section = new PluginFormcreatorSection;
         $question = new PluginFormcreatorQuestion;
         $exploded = explode('##$$##', $actor['_question']);

         if (plugin_formcreator_getFromDBByField($section, 'name', $exploded[0])
             && $questions_id = plugin_formcreator_getFromDBByField($question, 'name', $exploded[1])) {
            $actor['actor_value'] = $questions_id;
         } else{
            return false;
         }

      } else if (isset($actor['_user'])) {
         $user = new User;
         if ($users_id = plugin_formcreator_getFromDBByField($user, 'name', $actor['_user'])) {
            $actor['actor_value'] = $users_id;
         } else {
            return false;
         }
      } else if (isset($actor['_group'])) {
         $group = new Group;
         if ($groups_id = plugin_formcreator_getFromDBByField($group, 'completename', $actor['_user'])) {
            $actor['actor_value'] = $groups_id;
         } else {
            return false;
         }
      } else if (isset($actor['_supplier'])) {
         $supplier = new Supplier;
         if ($suppliers_id = plugin_formcreator_getFromDBByField($supplier, 'name', $actor['_user'])) {
            $actor['actor_value'] = $suppliers_id;
         } else {
            return false;
         }
      }

      if ($actors_id = plugin_formcreator_getFromDBByField($item, 'uuid', $actor['uuid'])) {
         // add id key
         $actor['id'] = $actors_id;

         // update actor
         $item->update($actor);
      } else {
         //create actor
         $actors_id = $item->add($actor);
      }

      return $actors_id;
   }

   /**
    * Export in an array all the data of the current instanciated actor
    * @return array the array with all data (with sub tables)
    */
   public function export() {
      if (!$this->getID()) {
         return false;
      }

      $target_actor = $this->fields;

      unset($target_actor['id'],
            $target_actor['plugin_formcreator_targettickets_id']);

      // export FK
      switch ($target_actor['actor_type']) {
         case 'question_person':
         case 'question_group':
         case 'question_supplier':
            $question = new PluginFormcreatorQuestion;
            $section = new PluginFormcreatorSection;
            if ($question->getFromDB($target_actor['actor_value'])
                && $section->getFromDB($question->fields['plugin_formcreator_sections_id'])) {
               $target_actor['_question'] = $section->fields['name'].
                                            "##$$##".
                                            $question->fields['name'];
               unset($target_actor['actor_value']);
            }
            break;
         case 'person':
            $user = new User;
            if ($user->getFromDB($target_actor['actor_value'])) {
               $target_actor['_user'] = $user->fields['name'];
               unset($target_actor['actor_value']);
            }
            break;
         case 'group':
            $group = new Group;
            if ($group->getFromDB($target_actor['actor_value'])) {
               $target_actor['_group'] = $group->fields['completename'];
               unset($target_actor['actor_value']);
            }
            break;
         case 'supplier':
            $supplier = new Supplier;
            if ($supplier->getFromDB($target_actor['actor_value'])) {
               $target_actor['_supplier'] = $supplier->fields['name'];
               unset($target_actor['actor_value']);
            }
            break;
      }

      return $target_actor;
   }

}