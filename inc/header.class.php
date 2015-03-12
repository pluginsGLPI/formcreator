<?php
class PluginFormcreatorHeader extends CommonDropdown
{

   public static function getTypeName($nb=1)
   {
      return _n('Header', 'Headers', $nb, 'formcreator');
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      switch ($item->getType()) {
         case "PluginFormcreatorConfig":
            $env       = new self;
            $found_env = $env->find();
            $nb        = count($found_env);
            return self::createTabEntry(self::getTypeName($nb), $nb);
      }
      return '';
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {

      $header  = new self();
      $found = $header->find('entities_id = ' . $_SESSION['glpiactive_entity']);
      if (count($found) > 0) {
         echo '<div class="tab_cadre_pager" style="padding: 2px; margin: 5px 0">
            <h3 class="tab_bg_2" style="padding: 5px">
                <img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/pics/menu_add_off.png" alt="+" align="absmiddle" />
                ' . __('Add an header', 'formcreator') . '<br /><br />
               <em><i><img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/pics/warning.png" alt="/!\" align="absmiddle" height="16" />&nbsp;
               ' . __('An header already exists for this entity! You can have only one header per entity.', 'formcreator') . '</i></em>
            </h3>
         </div>';
      } else {

         $table   = getTableForItemType('PluginFormcreatorHeader');
         $where   = getEntitiesRestrictRequest( "", $table, "", "", true, false);
         $found = $header->find($where);

         if (count($found) > 0) {
            echo '<div class="tab_cadre_pager" style="padding: 2px; margin: 5px 0">
               <h3 class="tab_bg_2" style="padding: 5px">
              <a href="' . Toolbox::getItemTypeFormURL(__CLASS__) .  '" class="submit">
                   <img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/pics/menu_add.png" alt="+" align="absmiddle" />
                   ' . __('Add an header', 'formcreator') . '
               </a><br /><br />
                  <em><i><img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/pics/warning.png" alt="/!\" align="absmiddle" height="16" />&nbsp;
                  ' . __('An header exists for a parent entity! Another header will overwrite the previous one.', 'formcreator') . '</i></em>
               </h3>
            </div>';
         } else {
            echo '<div class="tab_cadre_pager" style="padding: 2px; margin: 5px 0">
               <h3 class="tab_bg_2" style="padding: 5px">
                 <a href="' . Toolbox::getItemTypeFormURL(__CLASS__) .  '" class="submit">
                      <img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/pics/menu_add.png" alt="+" align="absmiddle" />
                      ' . __('Add an header', 'formcreator') . '
                  </a>
               </h3>
            </div>';
         }
      }
      $params['sort']  = (!empty($_POST['sort'])) ? (int) $_POST['sort'] : 0;
      $params['order'] = (!empty($_POST['order']) && in_array($_POST['order'], array('ASC', 'DESC')))
                           ? $_POST['order'] : 'ASC';
      $params['start'] = (!empty($_POST['start'])) ? (int) $_POST['start'] : 0;
      Search::manageGetValues(__CLASS__);
      //Search::showGenericSearch(__CLASS__, $_GET);
      Search::showList(__CLASS__, $params);
   }

   public function showForm($ID, $options = array())
   {
      if (!$this->isNewID($ID)) {
         $this->check($ID, READ);
      } else {
         $this->check(-1, UPDATE);
      }
      $options['colspan'] = 2;
      $options['target']  = Toolbox::getItemTypeFormURL(__CLASS__);
      $this->showFormHeader($options);
      echo '<table class="tab_cadre_fixe">';

      echo "<tr class='line0'><td>" . __('Name') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "</tr>";

      echo "<tr class='line1'><td>" . __('Content') . "</td>";
      echo "<td>";
      echo "<textarea name='comment' id ='comment' >" . $this->fields['comment'] . "</textarea>";
      Html::initEditorSystem('comment');
      echo "</td>";
      echo "</tr>";


      $this->showFormButtons($options);

      return true;
   }

   public function prepareInputForAdd($input)
   {
      $header = new self();
      $found = $header->find('entities_id = ' . $input['entities_id']);
      if (count($found) > 0) {
         Session::addMessageAfterRedirect(__('An header already exists for this entity! You can have only one header per entity.', 'formcreator'), false, ERROR);
         return array();
      }

      return $input;
   }

   public static function install(Migration $migration)
   {
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL auto_increment,
                     `entities_id` int(11) NOT NULL DEFAULT '0',
                     `is_recursive` tinyint(1) NOT NULL DEFAULT '1',
                     `name` varchar(255) NOT NULL DEFAULT '',
                     `comment` text collate utf8_unicode_ci,
                     PRIMARY KEY (`id`),
                     KEY `name` (`name`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
      }

      // Migration from previous version
      if (TableExists('glpi_plugin_formcreator_titles')) {
         $query = "INSERT INTO `$table` (`id`, `name`, `comment`)
                     SELECT `id`, CONCAT('Header ', `id`) AS name, `name` AS comment FROM glpi_plugin_formcreator_titles";
         $GLOBALS['DB']->query($query);
         $GLOBALS['DB']->query("DROP TABLE glpi_plugin_formcreator_titles");
      }


      return true;
      }

   public static function uninstall()
   {
      $query = "DROP TABLE IF EXISTS `" . getTableForItemType(__CLASS__) . "`";
      return $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
   }
}
