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
      }

      return true;
      }

   public static function uninstall()
   {
      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      return $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
   }
}
