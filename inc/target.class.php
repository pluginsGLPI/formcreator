<?php
class PluginFormcreatorTarget extends CommonDBTM
{
   public static function getTypeName($nb = 1)
   {
      return _n('Target', 'Targets', $nb, 'formcreator');
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      switch ($item->getType()) {
         case "PluginFormcreatorForm":
            $env       = new self;
            $found_env = $env->find();
            $nb        = count($found_env);
            return self::createTabEntry(self::getTypeName($nb), $nb);
      }
      return '';
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      echo '<div class="tab_cadre_pager" style="padding: 2px; margin: 5px 0">
         <h3 class="tab_bg_2" style="padding: 5px">
           <a href="' . Toolbox::getItemTypeFormURL(__CLASS__) . '" class="submit">
                <img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/pics/menu_add.png" alt="+" align="absmiddle" />
                ' . __('Add a target', 'formcreator') . '
            </a>
         </h3>
      </div>';

      $params['sort']  = (!empty($_POST['sort'])) ? (int) $_POST['sort'] : 0;
      $params['order'] = (!empty($_POST['order']) && in_array($_POST['order'], array('ASC', 'DESC')))
                           ? $_POST['order'] : 'ASC';
      $params['start'] = (!empty($_POST['start'])) ? (int) $_POST['start'] : 0;
      Search::manageGetValues(__CLASS__);
      //Search::showGenericSearch(__CLASS__, $_GET);
      Search::showList(__CLASS__, $params);
   }

   public static function install(Migration $migration)
   {
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `plugin_formcreator_forms_id` tinyint(1) NOT NULL,
                     `itemtype` varchar(100) NOT NULL DEFAULT 'Ticket',
                     `items_id`  int(11) NOT NULL  DEFAULT 0,
                     `name` varchar(255) NOT NULL DEFAULT '',
                     `comment` text collate utf8_unicode_ci
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
