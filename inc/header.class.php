<?php

class PluginFormcreatorHeader extends CommonDropdown
{
   static function getTypeName($nb=1) {
      return _n('Header', 'Headers', $nb, 'formcreator');
   }

   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL auto_increment,
                     `entities_id` int(11) NOT NULL DEFAULT '0',
                     `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
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
      switch ($item->getType()) {
         case "PluginFormcreatorConfig":
            $env = new self;
            $found_env = $env->find();
            $nb = count($found_env);
            return self::createTabEntry(self::getTypeName($nb), $nb);
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      echo '<div class="tab_cadre_pager" style="padding: 2px; margin: 5px 0">
         <h3 class="tab_bg_2" style="padding: 5px">
           <a href="' . Toolbox::getItemTypeFormURL(__CLASS__). '" class="submit">
                <img src="'.$CFG_GLPI['root_doc'].'/pics/menu_add.png" alt="+" align="absmiddle" />
                '.__('Add an header', 'formcreator').'
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

   function showForm($ID, $options = array()) {
      global $CFG_GLPI;

      if (!$this->isNewID($ID)) {
         $this->check($ID,'r');
      } else {
         $this->check(-1,'w');
      }
      $options['colspan'] = 2;
      $options['target']  = Toolbox::getItemTypeFormURL(__CLASS__);
      $this->showTabs($options);
      $this->showFormHeader($options);
      // echo '<form method="post" action="' . Toolbox::getItemTypeFormURL(__CLASS__) . '">';
      echo '<table class="tab_cadre_fixe">';
      //echo '<tr><th colspan="2">' . __('Headers', 'formcreator') . '</th></tr>';

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
      $this->addDivForTabs();

      return true;
   }
}
