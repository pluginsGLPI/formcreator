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
            $found_env = $env->find('plugin_formcreator_forms_id = ' . $item->getID());
            $nb        = count($found_env);
            return self::createTabEntry(self::getTypeName($nb), $nb);
      }
      return '';
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      echo '<table class="tab_cadre_fixe">';

      echo '<tr>';
      echo '<th colspan="3">' . __('Destinations', 'formcreator') . '</th>';
      echo '</tr>';

      $target_class    = new PluginFormcreatorTarget();
      $founded_targets = $target_class->find('plugin_formcreator_forms_id = ' . $item->getID());
      $target_number   = count($founded_targets);
      $i = 0;
      foreach ($founded_targets as $target) {
         $i++;
         echo '<tr class="line' . ($i % 2) . '">';

         echo '<td onclick="document.location=\'../front/targetticket.form.php?id=' . $target['items_id'] . '\'" style="cursor: pointer">';
         echo $target['name'];
         echo '</td>';

         echo '<td align="center" width="32">';
         echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/pencil.png"
                  alt="*" title="' . __('Edit', 'formcreator') . '"
                  onclick="document.location=\'../front/targetticket.form.php?id=' . $target['items_id'] . '\'" align="absmiddle" style="cursor: pointer" /> ';
         echo '</td>';

         echo '<td align="center" width="32">';
         echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/delete.png"
                  alt="*" title="' . __('Delete', 'formcreator') . '"
                  onclick="deleteTarget(' . $target['id'] . ', \'' . addslashes($target['name']) . '\')" align="absmiddle" style="cursor: pointer" /> ';
         echo '</td>';

         echo '</tr>';
      }


      // Display add target link...
      echo '<tr class="line' . (($i + 1) % 2) . '" id="add_target_row">';
      echo '<td colspan="3">';
      echo '<a href="javascript:addTarget(' . $item->fields['id'] . ');">
                <img src="'.$GLOBALS['CFG_GLPI']['root_doc'].'/pics/menu_add.png" alt="+" align="absmiddle" />
                '.__('Add a destination', 'formcreator').'
            </a>';
      echo '</td>';
      echo '</tr>';

      // OR display add target form
      echo '<tr class="line' . (($i + 1) % 2) . '" id="add_target_form" style="display: none;">';
      echo '<td colspan="3" id="add_target_form_td"></td>';
      echo '</tr>';

      echo "</table>";


      echo '<script type="text/javascript">
               function addTarget(form) {
                  document.getElementById("add_target_form").style.display = "table-row";
                  document.getElementById("add_target_row").style.display = "none";
                  Ext.get("add_target_form_td").load({
                     url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/ajax/target.php",
                     scripts: true,
                     params: "form_id=" + ' . $item->getId() . '
                  });
               }

               function cancelAddTarget() {
                  document.getElementById("add_target_row").style.display = "table-row";
                  document.getElementById("add_target_form").style.display = "none";
               }

               function deleteTarget(target_id, target_name) {
                  if(confirm("' . __('Are you sure you want to delete this destination:', 'formcreator') . ' " + target_name)) {
                     Ext.Ajax.request({
                        url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/target.form.php",
                        success: reloadTab,
                        params: {
                           delete: 1,
                           id: target_id,
                           plugin_formcreator_forms_id: ' . $item->getId() . ',
                           _glpi_csrf_token: "' . Session::getNewCSRFToken() . '"
                        }
                     });
                  }
               }
            </script>';
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
      // Control fields values :
      // - name is required
      if(empty($input['name'])) {
         Session::addMessageAfterRedirect(__('The name cannot be empty!', 'formcreator'), false, ERROR);
         return array();
      }
      // - field type is required
      if(empty($input['itemtype'])) {
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
            break;
      }

      return $input;
   }

   public static function install(Migration $migration)
   {
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `plugin_formcreator_forms_id` tinyint(1) NOT NULL,
                     `itemtype` varchar(100) NOT NULL DEFAULT 'PluginFormcreatorTargetTicket',
                     `items_id` int(11) NOT NULL DEFAULT 0,
                     `name` varchar(255) NOT NULL DEFAULT ''
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());

      // Migration from previous version
      } elseif(!FieldExists($table, 'itemtype')) {
         // Migration from version 1.5 to 1.6
         if (!FieldExists($table, 'type')) {
            $query = "ALTER TABLE `$table`
                      ADD `type` tinyint(1) NOT NULL default '2';";
            $GLOBALS['DB']->query($query);
         }

         // Add new column for link with target items
         $query = "ALTER TABLE `$table`
                     ADD `itemtype` varchar(100) NOT NULL DEFAULT 'PluginFormcreatorTargetTicket',
                     ADD `items_id` int(11) NOT NULL DEFAULT 0;";
         $GLOBALS['DB']->query($query);

         // Create ticket template for each configuration in DB
         $query = "SELECT `urgency`, `priority`, `itilcategories_id`, `type`
                   FROM `glpi_plugin_formcreator_targets`
                   GROUP BY `urgency`, `priority`, `itilcategories_id`, `type`";
         $result = $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());

         $i = 0;
         while ($ligne = $GLOBALS['DB']->fetch_array($result)) {
            $i++;
            $id = $ligne['urgency'] . $ligne['priority'] . $ligne['itilcategories_id'] . $ligne['type'];

            $template    = new TicketTemplate();
            $template_id = $template->add(array(
               'name'         => 'Template Formcreator ' . $i,
               'entities_id'  => 0,
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

         // Prepare Mysql CASE For each ticket template
         $mysql_case_template  = "CASE CONCAT(`urgency`, `priority`, `itilcategories_id`, `type`)";
         foreach ($_SESSION["formcreator_tmp"]["ticket_template"] as $id => $value) {
            $mysql_case_template .= "WHEN $id THEN $value";
         }
         $mysql_case_template .= "END AS `tickettemplates_id`";

         // Create Target ticket
         $version   = plugin_version_formcreator();
         $migration = new Migration($version['version']);
         PluginFormcreatorTargetTicket::install($migration);
         $table_targetticket = getTableForItemType('PluginFormcreatorTargetTicket');
         $query = "SELECT `id`, `name`, $mysql_case_template, `content` FROM `$table`;";
         $result = $GLOBALS['DB']->query($query);
         while ($line = $GLOBALS['DB']->fetch_array($result)) {
            // Insert target ticket
            $query_insert = "INSERT INTO $table_targetticket SET
                              `name` = {$line['name']},
                              `tickettemplates_id` = {$line['tickettemplates_id']},
                              `comment` = {$line['content']}";
            $GLOBALS['DB']->query($query_insert);
            $targetticket_id = $GLOBALS['DB']->insert_id();

            // Update target with target ticket id
            $query_update = "UPDATE `$table` SET `items_id` = $targetticket_id WHERE `id` = {$line['id']}";
            $GLOBALS['DB']->query($query_update);
         }

         // Remove useless column content
         $GLOBALS['DB']->query("ALTER TABLE `$table` DROP `content`;");
      }

      return true;
   }

   public static function uninstall()
   {
      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      return $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
   }
}
