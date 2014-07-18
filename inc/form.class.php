<?php
class PluginFormcreatorForm extends CommonDBTM
{
   public $dohistory       = true;

   const ACCESS_PUBLIC     = 0;
   const ACCESS_PRIVATE    = 1;
   const ACCESS_RESTRICTED = 2;

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

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0)
   {
      return _n('Form', 'Forms', $nb, 'formcreator');
   }

   /**
    * Define search options for forms
    *
    * @return Array Array of fields to show in search engine and options for each fields
    */
   public function getSearchOptions()
   {
      $tab = array(
         '1' => array(
            'table'         => $this->getTable(),
            'field'         => 'id',
            'name'          => __('ID', 'formcreator'),
            'datatype'      => 'number',
            'searchtype'    => 'equals',
            'massiveaction' => false,
         ),
         '2' => array(
            'table'         => $this->getTable(),
            'field'         => 'name',
            'name'          => __('Name', 'formcreator'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
         ),
         '3' => array(
            'table'         => $this->getTable(),
            'field'         => 'is_active',
            'name'          => __('Status', 'formcreator'),
            'datatype'      => 'specific',
            'searchtype'    => array('equals', 'notequals'),
            'massiveaction' => true,
         ),
         '4' => array(
            'table'         => $this->getTable(),
            'field'         => 'description',
            'name'          => __('Description', 'formcreator'),
            'massiveaction' => false,
         ),
         '5' => array(
            'table'         => 'glpi_entities',
            'field'         => 'completename',
            'name'          => _n('Entity', 'Entities', 1),
            'datatype'      => 'dropdown',
            'massiveaction' => false,
         ),
         '6' => array(
            'table'         => $this->getTable(),
            'field'         => 'is_recursive',
            'name'          => __('Recursive', 'formcreator'),
            'datatype'      => 'bool',
            'massiveaction' => false,
         ),
         '7' => array(
            'table'         => $this->getTable(),
            'field'         => 'language',
            'name'          => __('Language'),
            'massiveaction' => false,
         ),
         '8' => array(
            'table'         => $this->getTable(),
            'field'         => 'helpdesk_home',
            'name'          => __('Homepage', 'formcreator'),
            'datatype'      => 'bool',
            'searchtype'    => array('equals', 'notequals'),
            'massiveaction' => true,
         ),
         '9' => array(
            'table'         => $this->getTable(),
            'field'         => 'access_rights',
            'name'          => __('Access', 'formcreator'),
            'datatype'      => 'specific',
            'searchtype'    => array('equals', 'notequals'),
            'massiveaction' => true,
         ),
      );
      return $tab;
   }

   /**
    * Define default search request
    *
    * @return Array Array of search options : [field, searchtype, contains, sort, order]
    */
   public static function getDefaultSearchRequest()
   {
      $search = array('field'      => array(0 => 3),
                      'searchtype' => array(0 => 'equals'),
                      'contains'   => array(0 => 1),
                      'sort'       => 2,
                      'order'      => 'ASC');
      return $search;
   }

   /**
    * Define how to display search field for a specific type
    *
    * @since version 0.84
    *
    * @param String $field           Name of the field as define in $this->getSearchOptions()
    * @param String $name            Name attribute for the field to be posted (default '')
    * @param Array  $values          Array of all values to display in search engine (default '')
    * @param Array  $options         Options (optional)
    *
    * @return String                 Html string to be displayed for the form field
    **/
   public static function getSpecificValueToSelect($field, $name='', $values='', array $options=array())
   {

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      $options['display'] = false;

      switch ($field) {
         case 'is_active' :
            $output  = "<select name='".$name."'>";
            $output .=  "<option value='0' ".(($values[$field] == 0)?" selected ":"").">"
                        . __('Inactive', 'formcreator')
                        . "</option>";
            $output .=  "<option value='1' ".(($values[$field] == 1)?" selected ":"").">"
                        . __('Active', 'formcreator')
                        . "</option>";
            $output .=  "</select>";

            return $output;
            break;
         case 'access_rights' :
            $output  = '<select name="' . $name . '">';
            $output .=  '<option value="' . self::ACCESS_PUBLIC . '" '
                           . (($values[$field] == 0) ? ' selected ' : '') . '>'
                        . __('Public access', 'formcreator')
                        . '</option>';
            $output .=  '<option value="' . self::ACCESS_PRIVATE . '" '
                           . (($values[$field] == 1) ? ' selected ' : '') . '>'
                        . __('Private access', 'formcreator')
                        . '</option>';
            $output .=  '<option value="' . self::ACCESS_RESTRICTED . '" '
                           . (($values[$field] == 1) ? ' selected ' : '') . '>'
                        . __('Restricted access', 'formcreator')
                        . '</option>';
            $output .=  '</select>';

            return $output;
            break;
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   /**
    * Define how to display a specific value in search result table
    *
    * @param  String $field   Name of the field as define in $this->getSearchOptions()
    * @param  Mixed  $values  The value as it is stored in DB
    * @param  Array  $options Options (optional)
    * @return Mixed           Value to be displayed
    */
   public static function getSpecificValueToDisplay($field, $values, array $options=array())
   {
      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'is_active':
            return ($values[$field] == 0) ? __('Inactive', 'formcreator') : __('Active', 'formcreator');
            break;
         case 'access_rights':
            switch($values[$field]) {
               case self::ACCESS_PUBLIC :
                  return __('Public access', 'formcreator');
                  break;
               case self::ACCESS_PRIVATE :
                  return __('Private access', 'formcreator');
                  break;
               case self::ACCESS_RESTRICTED :
                  return __('Restricted access', 'formcreator');
                  break;
            }
            return '';
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * Show the Form edit form the the adminsitrator in the config page
    *
    * @param  Array  $options Optional options
    *
    * @return NULL         Nothing, just display the form
    */
   public function showForm($options=array())
   {
      if (!empty($options['id'])) {
         $id = $options['id'];
         $this->canView();
      } else {
         $id = 0;
         $this->canCreate();
      }
      $this->initForm($id);
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo '<tr class="tab_bg_1">';
      echo '<td>' . __('Name') . ' <span class="red">*</span></td>';
      echo '<td><input type="text" name="name" value="' . $this->fields["name"] . '" size="54"/></td>';
      echo '<td>' . __('Active') . ' <span class="red">*</span></td>';
      echo '<td>';
      Dropdown::showYesNo("is_active", $this->fields["is_active"]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_2">';
      echo '<td>' . __('Description') . '</td>';
      echo '<td><input type="text" name="description" value="' . $this->fields['description'] . '" size="54" /></td>';
      echo '<td>' . __('Language') . ' <span class="red">*</span></td>';
      echo '<td>';
      Dropdown::showLanguages('language', array(
         'value' => ($id != 0) ? $this->fields['language'] : $_SESSION['glpilanguage'],
      ));
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td>' . __('Category') . ' <span class="red">*</span></td>';
      echo '<td>';
      PluginFormcreatorCategory::dropdown(array(
         'name'  => 'formcreator_categories_id',
         'value' => ($id != 0) ? $this->fields["formcreator_categories_id"] : 1,
      ));
      echo '</td>';
      echo '<td>' . __('Access') . ' <span class="red">*</span></td>';
      echo '<td>';
      Dropdown::showFromArray(
         'access_rights',
         array(
            self::ACCESS_PUBLIC     => __('Public access', 'formcreator'),
            self::ACCESS_PRIVATE    => __('Private access', 'formcreator'),
            self::ACCESS_RESTRICTED => __('Restricted access', 'formcreator'),
         ),
         array(
            'value' => ($id != 0) ? $this->fields["access_rights"] : 1,
         )
      );
      echo '</td></tr>';

      echo '<tr class="tab_bg_2">';
      echo '<td colspan="2">&nbsp;</td>';
      echo '<td>' . __('Direct access on homepage', 'formcreator') . '</td>';
      echo '<td>';
      Dropdown::showYesNo("helpdesk_home", $this->fields["helpdesk_home"]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td>' . __('Header') . '</td>';
      echo '<td colspan="3"><textarea name="content" cols="115" rows="10">' . $this->fields["content"] . '</textarea></td>';
      Html::initEditorSystem('content');
      echo '</tr>';

      $this->showFormButtons($options);
      $this->addDivForTabs();
   }


   /**
    * Return the name of the tab for item including forms like the config page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Config Item)
    * @param  integer    $withtemplate
    *
    * @return String                   Name to be displayed
    */
   public function getTabNameForItem(CommonGLPI $item, $withtemplate=0)
   {
      switch ($item->getType()) {
         case "PluginFormcreatorConfig":
            $object  = new self;
            $founded = $object->find();
            $number  = count($founded);
            return self::createTabEntry(self::getTypeName($number), $number);
            break;
         case "PluginFormcreatorForm":
            return __('Preview', 'formcreator');
            break;
      }
      return '';
   }

   /**
    * Display a list of all forms on the configuration page
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Config Item)
    * @param  integer    $tabnum       Number of the current tab
    * @param  integer    $withtemplate
    *
    * @see CommonDBTM::displayTabContentForItem
    *
    * @return null                     Nothing, just display the list
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0)
   {
      switch ($item->getType()) {
         case "PluginFormcreatorConfig":
            $params = $_REQUEST;
            $params += self::getDefaultSearchRequest();
            Search::manageGetValues(__CLASS__);
            Search::showGenericSearch(__CLASS__, $params);
            Search::showList(__CLASS__, $params);
            break;
         case "PluginFormcreatorForm":
            echo '<div style="text-align: left">';
            $item->displayUserForm($item);
            echo '</div>';
            break;
      }
   }


   public function defineTabs($options=array())
   {
      $ong = array();
      $this->addStandardTab('PluginFormcreatorQuestion', $ong, $options);
      $this->addStandardTab('PluginFormcreatorTarget', $ong, $options);
      $this->addStandardTab(__CLASS__, $ong, $options);
      return $ong;
   }

   /**
    * Show the list of forms to be displayed to the end-user
    */
   public function showList()
   {
      global $DB;

      echo '<div class="center">';

      // Get entities we can access
      $entities_table = getTableForItemType('Entity');
      $where          = getEntitiesRestrictRequest( "", $entities_table, "", "", true, true);

      // Show header for the current entity or it's first parent header
      $table  = getTableForItemType('PluginFormcreatorHeader');
      $query  = "SELECT $table.`comment`
                 FROM $table, $entities_table
                 WHERE $where
                 ORDER BY $entities_table.`completename` DESC";
      $result = $DB->query($query);
      if (!empty($result)) {
         list($description) = $DB->fetch_array($result);
         if (!empty($description)) {
            echo '<table class="tab_cadre_fixe">';
            echo '<tr><td>' . html_entity_decode($description) . '</td></tr>';
            echo '</table>';
            echo '<br />';
         }
      }

      // Show categories wicth have at least one form user can access
      $cat_table  = getTableForItemType('PluginFormcreatorCategory');
      $form_table = getTableForItemType('PluginFormcreatorForm');
      $query  = "SELECT $cat_table.`name`, $cat_table.`id`
                 FROM $cat_table
                 WHERE 0 < (
                     SELECT COUNT($form_table.id)
                     FROM $form_table
                     LEFT JOIN $entities_table ON $entities_table.`id` =  $form_table.`entities_id` AND $where
                     WHERE $form_table.`formcreator_categories_id` = $cat_table.`id`
                     AND $form_table.`is_active` = 1
                     AND $form_table.`language` = '{$_SESSION['glpilanguage']}'
                  )
                 ORDER BY $cat_table.`name` ASC";
      $result = $DB->query($query);
      if (!empty($result)) {
         echo '<table class="tab_cadre_fixe">';

         // For each categories, show the list of forms the user can fill
         while($category = $DB->fetch_array($result)) {
            echo '<tr><th colspan="2">' . $category['name'] . '</t></tr>';

            $query_forms = "SELECT $form_table.id, $form_table.name, $form_table.description
                            FROM $form_table
                            LEFT JOIN $entities_table ON $entities_table.`id` =  $form_table.`entities_id` AND $where
                            WHERE $form_table.`formcreator_categories_id` = {$category['id']}
                            AND $form_table.`is_active` = 1
                            AND $form_table.`language` = '{$_SESSION['glpilanguage']}'
                            ORDER BY $form_table.name ASC";
            $result_forms = $DB->query($query_forms);
            $i = 0;
            while($form = $DB->fetch_array($result_forms)) {
               $i++;
               echo '<tr class="line' . ($i % 2) . '" onclick="document.location = \'' . $GLOBALS['CFG_GLPI']['root_doc']
                        . '/plugins/formcreator/front/showform.php?id=' . $form['id'] . '\'" style="cursor:pointer">';
               echo '<td><a href="' . $GLOBALS['CFG_GLPI']['root_doc']
                        . '/plugins/formcreator/front/showform.php?id=' . $form['id'] . '">'
                        . $form['name']
                        . '</a></td>';
               echo '<td>' . $form['description'] . '</td>';
               echo '</tr>';
            }

         }
         echo '</table>';
      }

      echo '</div>';
   }

   /**
    * Display the Form end-user form to be filled
    *
    * @param  CommonGLPI   $item       Instance of the Form to be displayed
    *
    * @return Null                     Nothing, just display the form
    */
   public function displayUserForm(CommonGLPI $item)
   {
      echo '<form name="formcreator_form' . $item->getId() . '" method="post" action="" role="form" class="formcreator_form form_horizontal">';
      echo '<h1 class="form-title">' . $item->fields['name'] . '</h1>';

      // Form Header
      if (!empty($item->fields['content'])) {
         echo '<div class="form_header">';
         echo html_entity_decode($item->fields['content']);
         echo '</div>';
      }
      // Get and display sections of the form
      $section  = new PluginFormcreatorSection();
      $question = new PluginFormcreatorQuestion();
      $sections = $section->find('plugin_formcreator_forms_id = ' . $item->getID(), '`order` ASC');
      foreach($sections as $section_line) {
         echo '<div class="form_section">';
         echo '<h2>' . $section_line['name'] . '</h2>';

         // Display all fields of the section
         $questions = $question->find('plugin_formcreator_sections_id = ' . $section_line['id'], '`order` ASC');
         foreach($questions as $question_line) {
            PluginFormcreatorFields::showField($question_line);
         }

         echo '</div>';
      }

      // Display submit button
      echo '<div class="center">';
      echo '<input type="submit" class="submit_button" />';
      echo '</div>';

      echo '<input type="hidden" name="_glpi_csrf_token" value="' . Session::getNewCSRFToken() . '">';
      echo '</form>';
   }

   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean True on success
    */
   public static function install(Migration $migration)
   {
      global $DB;

      $obj   = new self();
      $table = $obj->getTable();

      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         // Create default request type
         $query  = "SELECT id FROM `glpi_requesttypes` WHERE `name` LIKE 'Formcreator';";
         $result = $DB->query($query) or die ($DB->error());
         if ( !empty($result)) {
            list($requesttype) = $DB->fetch_array($result);
         } else {
            $query = "INSERT IGNORE INTO `glpi_requesttypes` SET `name` = 'Formcreator';";
            $DB->query($query) or die ($DB->error());
            $requesttype = $DB->insert_id();
         }

         // Create Forms table
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `entities_id` int(11) NOT NULL DEFAULT '0',
                     `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
                     `access_rights` tinyint(1) NOT NULL DEFAULT '1',
                     `requesttype` int(11) NOT NULL DEFAULT '$requesttype',
                     `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                     `description` varchar(255) COLLATE utf8_unicode_ci,
                     `content` longtext COLLATE utf8_unicode_ci,
                     `formcreator_categories_id` tinyint(3) UNSIGNED NOT NULL,
                     `is_active` tinyint(1) NOT NULL DEFAULT '0',
                     `language` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
                     `helpdesk_home` tinyint(1) NOT NULL DEFAULT '0',
                     `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }

      return true;
   }

   /**
    * Database table uninstallation for the item type
    *
    * @return boolean True on success
    */
   public static function uninstall()
   {
      global $DB;

      $obj = new self();
      $DB->query('DROP TABLE IF EXISTS `'.$obj->getTable().'`');

      // Delete logs of the plugin
      $DB->query('DELETE FROM `glpi_logs` WHERE itemtype = "' . __CLASS__ . '"');

      return true;
   }
}
