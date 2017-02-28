<?php
class PluginFormcreatorTarget extends CommonDBTM
{
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

   public static function getTypeName($nb = 1)
   {
      return _n('Destination', 'Destinations', $nb, 'formcreator');
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      switch ($item->getType()) {
         case "PluginFormcreatorForm":
            $env       = new self;
            $found_env = $env->find('plugin_formcreator_forms_id = '.$item->getID());
            $nb        = count($found_env);
            return self::createTabEntry(self::getTypeName($nb), $nb);
      }
      return '';
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      global $CFG_GLPI;

      echo '<table class="tab_cadre_fixe">';

      echo '<tr>';
      echo '<th colspan="3">'._n('Destinations', 'Destinations', 2, 'formcreator').'</th>';
      echo '</tr>';

      $target_class    = new PluginFormcreatorTarget();
      $found_targets = $target_class->find('plugin_formcreator_forms_id = '.$item->getID());
      $target_number   = count($found_targets);
      $token           = Session::getNewCSRFToken();
      $i = 0;
      foreach ($found_targets as $target) {
         $i++;
         echo '<tr class="line'.($i % 2).'">';

         echo '<td onclick="document.location=\'../front/targetticket.form.php?id='.$target['items_id'].'\'" style="cursor: pointer">';
         echo $target['name'];
         echo '</td>';

         echo '<td align="center" width="32">';
         echo '<img src="'.$CFG_GLPI['root_doc'].'/plugins/formcreator/pics/pencil.png"
                  alt="*" title="'.__('Edit').'"
                  onclick="document.location=\'../front/targetticket.form.php?id='.$target['items_id'].'\'" align="absmiddle" style="cursor: pointer" /> ';
         echo '</td>';

         echo '<td align="center" width="32">';
         echo '<img src="'.$CFG_GLPI['root_doc'].'/plugins/formcreator/pics/delete.png"
                  alt="*" title="'.__('Delete', 'formcreator').'"
                  onclick="deleteTarget('.$item->getID().', \''.$token.'\', '.$target['id'].')" align="absmiddle" style="cursor: pointer" /> ';
         echo '</td>';

         echo '</tr>';
      }


      // Display add target link...
      echo '<tr class="line'.(($i + 1) % 2).'" id="add_target_row">';
      echo '<td colspan="3">';
      echo '<a href="javascript:addTarget('.$item->getID().', \''.$token.'\');">
                <img src="'.$CFG_GLPI['root_doc'].'/pics/menu_add.png" alt="+" align="absmiddle" />
                '.__('Add a destination', 'formcreator').'
            </a>';
      echo '</td>';
      echo '</tr>';

      // OR display add target form
      echo '<tr class="line'.(($i + 1) % 2).'" id="add_target_form" style="display: none;">';
      echo '<td colspan="3" id="add_target_form_td"></td>';
      echo '</tr>';

      echo "</table>";
   }

   /**
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
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
         Session::addMessageAfterRedirect(__('The name cannot be empty!', 'formcreator'), false, ERROR);
         return array();
      }
      // - field type is required
      if(isset($input['itemtype'])) {
         if (empty($input['itemtype'])) {
            Session::addMessageAfterRedirect(__('The type cannot be empty!', 'formcreator'), false, ERROR);
            return array();
         }

         switch ($input['itemtype']) {
            case 'PluginFormcreatorTargetTicket':
               $targetticket      = new PluginFormcreatorTargetTicket();
               $id_targetticket   = $targetticket->add(array(
                  'name'    => $input['name'],
                  'comment' => '##FULLFORM##'
               ));
               $input['items_id'] = $id_targetticket;

               if (!isset($input['_skip_create_actors'])
                   || !$input['_skip_create_actors']) {
                  $targetTicket_actor = new PluginFormcreatorTargetTicket_Actor();
                  $targetTicket_actor->add(array(
                        'plugin_formcreator_targettickets_id'  => $id_targetticket,
                        'actor_role'                           => 'requester',
                        'actor_type'                           => 'creator',
                        'use_notification'                     => '1'
                  ));
                  $targetTicket_actor = new PluginFormcreatorTargetTicket_Actor();
                  $targetTicket_actor->add(array(
                        'plugin_formcreator_targettickets_id'  => $id_targetticket,
                        'actor_role'                           => 'observer',
                        'actor_type'                           => 'validator',
                        'use_notification'                     => '1'
                  ));
               }
               break;
         }
      }

      // generate a uniq id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

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
      // Decode (if already encoded) and encode strings to avoid problems with quotes
      foreach ($input as $key => $value) {
         $input[$key] = plugin_formcreator_encode($value);
      }

      // generate a uniq id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      return $input;
   }

   public function pre_deleteItem() {
      $itemtype = $this->getField('itemtype');
      $item = new $itemtype();
      return $item->delete(array('id' => $this->getField('items_id')));
   }

   public static function install(Migration $migration)
   {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `plugin_formcreator_forms_id` int(11) NOT NULL,
                     `itemtype` varchar(100) NOT NULL DEFAULT 'PluginFormcreatorTargetTicket',
                     `items_id` int(11) NOT NULL DEFAULT 0,
                     `name` varchar(255) NOT NULL DEFAULT '',
                     `uuid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query) or die($DB->error());

      // Migration from previous version
      } else{
         // Migration to 0.85-1.2.5
         if (FieldExists($table, 'plugin_formcreator_forms_id', false)) {
            $query = "ALTER TABLE `glpi_plugin_formcreator_targets`
                       CHANGE `plugin_formcreator_forms_id` `plugin_formcreator_forms_id` INT NOT NULL;";
            $DB->query($query);
         }

         if(!FieldExists($table, 'itemtype', false)) {
            // Migration from version 1.5 to 1.6
            if (!FieldExists($table, 'type', false)) {
               $query = "ALTER TABLE `$table`
                         ADD `type` tinyint(1) NOT NULL default '2';";
               $DB->query($query);
            }

            // Add new column for link with target items
            $query = "ALTER TABLE `$table`
                        ADD `itemtype` varchar(100) NOT NULL DEFAULT 'PluginFormcreatorTargetTicket',
                        ADD `items_id` int(11) NOT NULL DEFAULT 0;";
            $DB->query($query);

            // Create ticket template for each configuration in DB
            $query = "SELECT t.`urgency`, t.`priority`, t.`itilcategories_id`, t.`type`, f.`entities_id`
                      FROM `glpi_plugin_formcreator_targets` t, `glpi_plugin_formcreator_forms` f
                      WHERE f.`id` = t.`plugin_formcreator_forms_id`
                      GROUP BY t.`urgency`, t.`priority`, t.`itilcategories_id`, t.`type`, f.`entities_id`";
            $result = $DB->query($query) or die($DB->error());

            $i = 0;
            while ($ligne = $DB->fetch_array($result)) {
               $i++;
               $id = $ligne['urgency'].$ligne['priority'].$ligne['itilcategories_id'].$ligne['type'];

               $template    = new TicketTemplate();
               $template_id = $template->add(array(
                  'name'         => 'Template Formcreator '.$i,
                  'entities_id'  => $ligne['entities_id'],
                  'is_recursive' => 1,
               ));

               $predefinedField = new TicketTemplatePredefinedField();

               // Urgency
               if(!empty($ligne['urgency'])) {
                  $predefinedField->add(array(
                     'tickettemplates_id' => $template_id,
                     'num'                => 10,
                     'value'              => $ligne['urgency'],
                  ));
               }

               // Priority
               if(!empty($ligne['priority'])) {
                  $predefinedField->add(array(
                     'tickettemplates_id' => $template_id,
                     'num'                => 3,
                     'value'              => $ligne['priority'],
                  ));
               }

               // Category
               if(!empty($ligne['itilcategories_id'])) {
                  $predefinedField->add(array(
                     'tickettemplates_id' => $template_id,
                     'num'                => 7,
                     'value'              => $ligne['itilcategories_id'],
                  ));
               }

               // Type
               if(!empty($ligne['type'])) {
                  $predefinedField->add(array(
                     'tickettemplates_id' => $template_id,
                     'num'                => 14,
                     'value'              => $ligne['type'],
                  ));
               }

               $_SESSION["formcreator_tmp"]["ticket_template"]["$id"] = $template_id;
            }

            // Install or upgrade of TargetTicket is a prerequisite
            $version   = plugin_version_formcreator();
            $migration = new Migration($version['version']);
            require_once ('targetticket.class.php');
            PluginFormcreatorTargetTicket::install($migration);
            $table_targetticket = getTableForItemType('PluginFormcreatorTargetTicket');

            // Convert targets to ticket templates only if at least one target extsis
            if ($i > 0) {
               // Prepare Mysql CASE For each ticket template
               $mysql_case_template  = "CASE CONCAT(`urgency`, `priority`, `itilcategories_id`, `type`)";
               foreach ($_SESSION["formcreator_tmp"]["ticket_template"] as $id => $value) {
                  $mysql_case_template .= " WHEN $id THEN $value ";
               }
               $mysql_case_template .= "END AS `tickettemplates_id`";

               // Create Target ticket
               $query  = "SELECT `id`, `name`, $mysql_case_template, `content` FROM `$table`;";
               $result = $DB->query($query);
               while ($line = $DB->fetch_array($result)) {
                  // Insert target ticket
                  $query_insert = "INSERT INTO `$table_targetticket` SET
                                    `name` = '".htmlspecialchars($line['name'])."',
                                    `tickettemplates_id` = ".$line['tickettemplates_id'].",
                                    `comment` = '".htmlspecialchars($line['content'])."'";
                  $DB->query($query_insert);
                  $targetticket_id = $DB->insert_id();

                  // Update target with target ticket id
                  $query_update = "UPDATE `$table`
                                   SET `items_id` = ".$targetticket_id."
                                   WHERE `id` = ".$line['id'];
                  $DB->query($query_update);
               }
            }
            // Remove useless column content
            $DB->query("ALTER TABLE `$table` DROP `content`;");


            /**
             * Migration of special chars from previous versions
             *
             * @since 0.85-1.2.3
             */
            if (FieldExists($table_targetticket, 'comment')) {
               $query  = "SELECT `id`, `comment`
                          FROM `$table_targetticket`";
               $result = $DB->query($query);
               while ($line = $DB->fetch_array($result)) {
                  $query_update = "UPDATE `$table_targetticket` SET
                                     `comment` = '".plugin_formcreator_encode($line['comment'])."'
                                   WHERE `id` = ".$line['id'];
                  $DB->query($query_update) or die ($DB->error());
               }
            }
         }

         // add uuid to targets
         if (!FieldExists($table, 'uuid', false)) {
            $migration->addField($table, 'uuid', 'string');
            $migration->migrationOneTable($table);
         }

         // fill missing uuid (force update of targets, see self::prepareInputForUpdate)
         $obj   = new self();
         $all_targets = $obj->find("uuid IS NULL");
         foreach($all_targets as $targets_id => $target) {
            $obj->update(array('id' => $targets_id));
         }
      }

      return true;
   }

   public static function uninstall()
   {
      global $DB;

      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      return $DB->query($query) or die($DB->error());
   }

   /**
    * Import a form's target into the db
    * @see PluginFormcreatorForm::importJson
    *
    * @param  integer $forms_id  id of the parent form
    * @param  array   $target the target data (match the target table)
    * @return integer the target's id
    */
   public static function import($forms_id = 0, $target = array()) {
      $item = new self;

      $target['plugin_formcreator_forms_id'] = $forms_id;
      $target['_skip_checks']                = true;
      $target['_skip_create_actors']         = true;

      if ($targets_id = plugin_formcreator_getFromDBByField($item, 'uuid', $target['uuid'])) {
         // add id key
         $target['id'] = $targets_id;

         // update target
         $item->update($target);
      } else {
         //create target
         $targets_id = $item->add($target);
         $item->getFromDB('$targets_id');
      }

      // import sub table
      $target['itemtype']::import($item->fields['items_id'], $target['_data']);

      return $targets_id;
   }


   /**
    * Export in an array all the data of the current instanciated target
    * @return array the array with all data (with sub tables)
    */
   public function export() {
      if (!$this->getID()) {
         return false;
      }

      $form_target_actor = new PluginFormcreatorTargetTicket_Actor;
      $target            = $this->fields;
      $targetId = $this->getID();

      // get data from subclass (ex PluginFormcreatorTargetTicket)
      $target_item = new $target['itemtype'];
      if ($target_item->getFromDB($target['items_id'])) {
         $target['_data'] = $target_item->export();
      }

      // remove key and fk
      unset($target['id'],
            $target['items_id'],
            $target['plugin_formcreator_forms_id'],
            $target['tickettemplates_id']);


      // get target actors
      $target['_data']['_actors'] = [];
      $all_target_actors = $form_target_actor->find("`plugin_formcreator_targettickets_id` = '$targetId'");
      foreach($all_target_actors as $target_actors_id => $target_actor) {
         if ($form_target_actor->getFromDB($target_actors_id)) {
            $target['_data']['_actors'][] = $form_target_actor->export();
         }
      }

      return $target;
   }

   /**
    * get all targets of a form
    * @param PluginFormcreatorForm $form
    */
   public function getTargetsForForm(PluginFormcreatorForm $form) {
      $targets = array();
      $formId = $form->getID();
      $foundTargets = $this->find("plugin_formcreator_forms_id = '$formId'");
      foreach ($foundTargets as $id => $row) {
         $target = getItemForItemtype($row['itemtype']);
         $target->getFromDB($row['items_id']);
         $targets[] = $target;
      }

      return $targets;
   }
}
