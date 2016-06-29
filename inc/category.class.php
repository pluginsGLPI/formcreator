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
    * {@inheritDoc}
    * @see CommonTreeDropdown::getAdditionalFields()
    */
   public function getAdditionalFields()
   {
      return [
            [
                  'name'      => 'knowbaseitemcategories_id',
                  'type'      => 'dropdownValue',
                  'label'     => __('Knowbase category','formcreator'),
                  'list'      => false
            ]
      ];
   }

   /**
    * @param $rootId id of the subtree root
    * @return array Tree of form categories as nested array
    */
   public static function getCategoryTree($rootId = 0, $helpdeskHome = false) {
      $cat_table  = getTableForItemType('PluginFormcreatorCategory');
      $form_table = getTableForItemType('PluginFormcreatorForm');
      $table_fp   = getTableForItemType('PluginFormcreatorFormprofiles');
      if ($helpdeskHome) {
         $helpdesk   ="AND $form_table.`helpdesk_home` = 1";
      } else {
         $helpdesk   = '';
      }

      $query_faqs = KnowbaseItem::getListRequest([
            'faq'      => '1',
            'contains' => ''
      ]);

      // Selects categories containing forms or sub-categories
      $where      = "(SELECT COUNT($form_table.id)
         FROM $form_table
         WHERE $form_table.`plugin_formcreator_categories_id` = $cat_table.`id`
         AND $form_table.`is_active` = 1
         AND $form_table.`is_deleted` = 0
         $helpdesk
         AND $form_table.`language` IN ('".$_SESSION['glpilanguage']."', '', NULL, '0')
         AND ".getEntitiesRestrictRequest("", $form_table, "", "", true, false)."
         AND ($form_table.`access_rights` != ".PluginFormcreatorForm::ACCESS_RESTRICTED." OR $form_table.`id` IN (
         SELECT plugin_formcreator_forms_id
         FROM $table_fp
         WHERE plugin_formcreator_profiles_id = ".$_SESSION['glpiactiveprofile']['id']."))
      ) > 0
      OR (SELECT COUNT(*)
         FROM `$cat_table` AS `cat2`
         WHERE `cat2`.`plugin_formcreator_categories_id`=`$cat_table`.`id`
      ) > 0
      OR (SELECT COUNT(*)
         FROM ($query_faqs) AS `faqs`
         WHERE `faqs`.`knowbaseitemcategories_id` = `$cat_table`.`knowbaseitemcategories_id`
         AND `faqs`.`knowbaseitemcategories_id` <> '0'
      ) > 0";

      $formCategory = new self();
      if ($rootId == 0) {
         $items = $formCategory->find("`level`='1' AND ($where)");
         $name = '';
         $parent = 0;
      } else {
         $items = $formCategory->find("`plugin_formcreator_categories_id`='$rootId' AND ($where)");
         $formCategory = new self();
         $formCategory->getFromDB($rootId);
         $name = $formCategory->getField('name');
         $parent = $formCategory->getField('plugin_formcreator_categories_id');
      }

      // No sub-categories, then return
      if (count($items) == 0) {
         return array(
               'name'            => $name,
               'parent'          => $parent,
               'id'              => $rootId,
               'subcategories'   => new stdClass()
         );
      }

      // Generate sub categories
      $children = array(
            'name'            => $name,
            'parent'          => $parent,
            'id'              => $rootId,
            'subcategories'   => array()
      );
      foreach($items as $categoryId => $categoryItem) {
         $children['subcategories'][$categoryId] = self::getCategoryTree($categoryId);
      }
      return $children;
   }

   public static function install(Migration $migration)
   {
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

      // Migration from previous version
      if (TableExists('glpi_plugin_formcreator_cats')) {
         $query = "INSERT IGNORE INTO `$table` (`id`, `name`)
                     SELECT `id`,`name` FROM glpi_plugin_formcreator_cats";
         $DB->query($query);
         $DB->query("DROP TABLE glpi_plugin_formcreator_cats");
      }

      /**
       * Migration of special chars from previous versions
       *
       * @since 0.85-1.2.3
       */
      $query  = "SELECT `id`, `name`, `comment`
                 FROM `$table`";
      $result = $DB->query($query);
      while ($line = $DB->fetch_array($result)) {
         $query_update = "UPDATE `$table` SET
                            `name`    = '".plugin_formcreator_encode($line['name'])."',
                            `comment` = '".plugin_formcreator_encode($line['comment'])."'
                          WHERE `id` = ".$line['id'];
         $DB->query($query_update) or die ($DB->error());
      }

      /**
       * Migrate categories to tree structure
       *
       * @since 0.90-1.5
       */
      $migration->addField($table, 'completename', 'string', array('after' => 'comment'));
      $migration->addField($table, 'plugin_formcreator_categories_id', 'integer', array('after' => 'completename'));
      $migration->addField($table, 'level', 'integer', array('value' => 1,
                                                             'after' => 'plugin_formcreator_categories_id'));
      $migration->addField($table, 'sons_cache', 'longtext', array('after' => 'level'));
      $migration->addField($table, 'ancestors_cache', 'longtext', array('after' => 'sons_cache'));
      $migration->addField($table, 'knowbaseitemcategories_id', 'integer', array('after' => 'ancestors_cache'));
      $migration->migrationOneTable($table);
      $query  = "UPDATE $table SET `completename`=`name` WHERE `completename`=''";
      $DB->query($query);

      return true;
   }

   public static function uninstall()
   {
      global $DB;

      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      return $DB->query($query) or die($DB->error());
   }
}
