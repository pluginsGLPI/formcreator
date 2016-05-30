<?php
class PluginFormcreatorCategory extends CommonTreeDropdown
{
   // Activate translation on GLPI 0.85
   var $can_be_translated = true;

   public static function getTypeName($nb = 1)
   {
      return _n('Form category', 'Form categories', $nb, 'formcreator');
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      $env       = new self;
      $found_env = $env->find();
      $nb        = count($found_env);
      return self::createTabEntry(self::getTypeName($nb), $nb);
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      global $CFG_GLPI;

      if ($item->getType()==__CLASS__) {
         $item->showChildren();
      }
   }
   
   /**
    * Recursively build an HTML category tree with UL / LI tags
    * @param integer $rootId ID of the root item
    */
   public static function getHtmlCategoryTree($rootId = 0) {
      $formCategory = new self();
      if ($rootId == 0) {
         $items = $formCategory->find("`level`='1'");
      } else {
         $items = $formCategory->find("`plugin_formcreator_categories_id`='$rootId'");
      }

      if ($rootId !=0) {
         $formCategory->getFromDB($rootId);
         $html = '<a href="#" onclick="updateWizardFormsView(' . $rootId . ')">' . $formCategory->getField('name') . '</a>';
      } else {
         $html = '';
      }
      

      // No item, then return
      if (count($items) == 0) {
         return $html;
      }
      
      $html .= '<ul>';
      foreach($items as $categoryId => $categoryItem) {
         $html .= '<li>' . self::getHtmlCategoryTree($categoryId) . '</li>';
      }
      $html .= '</ul>';
      return $html;
   }

   public static function install(Migration $migration)
   {
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL auto_increment,
                     `name` varchar(255) NOT NULL DEFAULT '',
                     `comment` text collate utf8_unicode_ci,
                     PRIMARY KEY (`id`),
                     KEY `name` (`name`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
      }

      // Migration from previous version
      if (TableExists('glpi_plugin_formcreator_cats')) {
         $query = "INSERT IGNORE INTO `$table` (`id`, `name`)
                     SELECT `id`,`name` FROM glpi_plugin_formcreator_cats";
         $GLOBALS['DB']->query($query);
         $GLOBALS['DB']->query("DROP TABLE glpi_plugin_formcreator_cats");
      }

      /**
       * Migration of special chars from previous versions
       *
       * @since 0.85-1.2.3
       */
      $query  = "SELECT `id`, `name`, `comment`
                 FROM `$table`";
      $result = $GLOBALS['DB']->query($query);
      while ($line = $GLOBALS['DB']->fetch_array($result)) {
         $query_update = "UPDATE `$table` SET
                            `name`    = '" . plugin_formcreator_encode($line['name']) . "',
                            `comment` = '" . plugin_formcreator_encode($line['comment']) . "'
                          WHERE `id` = " . (int) $line['id'];
         $GLOBALS['DB']->query($query_update) or die ($GLOBALS['DB']->error());
      }
      
      /**
       * Migrate categories to tree structure
       * 
       * @since 0.85-1.2.4
       */
      $migration->addField($table, 'completename', 'string', array('after' => 'comment'));
      $migration->addField($table, 'plugin_formcreator_categories_id', 'integer', array('after' => completename));
      $migration->addField($table, 'level', 'integer', array('value' => 1, 'after' => plugin_formcreator_categories_id));
      $migration->addField($table, 'sons_cache', 'longtext', array('after' => 'level'));
      $migration->addField($table, 'ancestors_cache', 'longtext', array('after' => 'sons_cache'));
      $migration->migrationOneTable($table);
      $query  = "UPDATE $table SET `completename`=`name` WHERE `completename`=''";
      $GLOBALS['DB']->query($query);
      
      return true;
   }

   public static function uninstall()
   {
      $query = "DROP TABLE IF EXISTS `" . getTableForItemType(__CLASS__) . "`";
      return $GLOBALS['DB']->query($query) or die($GLOBALS['DB']->error());
   }
}
