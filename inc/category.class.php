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
    * @param $rootId id of the subtree root
    * @return array Tree of form categories as nested array
    */
   public static function getCategoryTree($rootId = 0) {
      $cat_table  = getTableForItemType('PluginFormcreatorCategory');
      $form_table = getTableForItemType('PluginFormcreatorForm');
      $table_fp   = getTableForItemType('PluginFormcreatorFormprofiles');
      
      // Selects categories containing forms or sub-categories
      $where      = "0 < (
      SELECT COUNT($form_table.id)
      FROM $form_table
      WHERE $form_table.`plugin_formcreator_categories_id` = $cat_table.`id`
      AND $form_table.`is_active` = 1
      AND $form_table.`is_deleted` = 0
      AND $form_table.`helpdesk_home` = 1
      AND ($form_table.`language` = '{$_SESSION['glpilanguage']}' OR $form_table.`language` = '')
      AND " . getEntitiesRestrictRequest("", $form_table, "", "", true, false) . "
      AND ($form_table.`access_rights` != " . PluginFormcreatorForm::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
      SELECT plugin_formcreator_forms_id
      FROM $table_fp
      WHERE plugin_formcreator_profiles_id = " . (int) $_SESSION['glpiactiveprofile']['id'] . "))
      ) OR 0 < (SELECT COUNT(*) FROM `$cat_table` AS `cat2` WHERE `cat2`.`plugin_formcreator_categories_id`=`$cat_table`.`id`)";
      
      $formCategory = new self();
      if ($rootId == 0) {
         $items = $formCategory->find("`level`='1' AND ($where)");
      } else {
         $items = $formCategory->find("`plugin_formcreator_categories_id`='$rootId' AND ($where)");
      }

      // No sub-categories, then return
      if (count($items) == 0) {
         return array();
      }
      
      // Generate UL / LI for sub categories
      $children = array();
      foreach($items as $categoryId => $categoryItem) {
         $children[$categoryId] = self::getCategoryTree($categoryId);
      }
      return $children;
   }
   
   /**
    * Prints form categories in a HTML slinky component
    */
   public static function slinkyView() {
      $categoryTree = array(0 => self::getCategoryTree());
      echo '<table class="tab_cadrehov">';
      echo '<tr><th>' . __('Form categories', 'formcreator') . '</th></tr>';
      echo '<tr><td><div id="plugin_formcreator_wizard_categories" class="slinky-menu">';
      echo self::HtmlCategoryTree($categoryTree);
      echo '</div></td></tr>';
      echo '</table>';
   }
   
   /**
    * Build nested UL / LI tags for category tree  
    * @param array $categoryRoot
    */
   protected static function HtmlCategoryTree(array $categoryRoot) {
      reset($categoryRoot);
      $categoryId = key($categoryRoot);
      $subCategories = $categoryRoot[$categoryId];
      
      if ($categoryId != 0) {
         $formCategory = new self();
         $formCategory->getFromDB($categoryId);
         $parentId = $formCategory->getField('plugin_formcreator_categories_id');
         $html = '<a href="#" data-parent-category-id="' . $parentId . '" data-category-id="' . $categoryId . '" onclick="updateWizardFormsView(' . $categoryId . ')">' . $formCategory->getField('name') . '</a>';
      } else {
         $html = '';
      }
      if (count($subCategories) == 0) {
         return $html;
      }
      $html .= '<ul>';
      foreach($subCategories as $subCategoryId => $subCategoryChildren) {
         $html .= '<li>';
         $html .= self::HtmlCategoryTree(array($subCategoryId => $subCategoryChildren)); 
         $html .= '</li>';
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
