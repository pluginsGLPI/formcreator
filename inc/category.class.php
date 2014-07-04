<?php

class PluginFormcreatorCategory extends CommonDropdown
{
   static function getTypeName($nb=1) {
      return _n('Form category', 'Form categories', $nb, 'formcreator');
   }

   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL auto_increment,
                     `name` varchar(255) NOT NULL DEFAULT '',
                     `comment` text collate utf8_unicode_ci,
                     PRIMARY KEY (`id`),
                     KEY `name` (`name`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query) or die($DB->error());
      }

      return true;
      }

   static function uninstall() {
      global $DB;

      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      return $DB->query($query) or die($DB->error());
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      $env = new self;
      $found_env = $env->find();
      $nb = count($found_env);
      return self::createTabEntry(self::getTypeName($nb), $nb);
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      echo '<div class="tab_cadre_pager" style="padding: 2px; margin: 5px 0">
         <h3 class="tab_bg_2" style="padding: 5px">
           <a href="' . Toolbox::getItemTypeFormURL(__CLASS__). '" class="submit">
                <img src="'.$CFG_GLPI['root_doc'].'/pics/menu_add.png" alt="+" align="absmiddle" />
                '.__('Add a form category', 'formcreator').'
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
}
