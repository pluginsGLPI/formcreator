<?php
class PluginFormcreatorForm extends CommonDBTM
{
   static $rightname = 'entity';

   public $dohistory         = true;

   const ACCESS_PUBLIC       = 0;
   const ACCESS_PRIVATE      = 1;
   const ACCESS_RESTRICTED   = 2;

   /**
    * Check if current user have the right to create and modify requests
    *
    * @return boolean True if he can create and modify requests
    */
   public static function canCreate()
   {
      return Session::haveRight("entity", UPDATE);
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canView()
   {
      return Session::haveRight("entity", UPDATE);
   }

   /**
    * Check if current user have the right to read requests
    *
    * @return boolean True if he can read requests
    */
   public static function canDelete()
   {
      return Session::haveRight("entity", UPDATE);
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

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu  = parent::getMenuContent();
      $validation_image = '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/check.png"
                                title="' . __('Forms waiting for validation', 'formcreator') . '">';
      $import_image     = '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/import.png"
                                title="' . __('Import forms', 'formcreator') . '">';
      $menu['links']['search']          = PluginFormcreatorFormList::getSearchURL(false);
      $menu['links']['config']          = PluginFormcreatorForm::getSearchURL(false);
      $menu['links'][$validation_image] = PluginFormcreatorForm_Answer::getSearchURL(false);
      $menu['links'][$import_image]     = PluginFormcreatorForm::getFormURL(false)."?import_form=1";

      return $menu;
   }

   /**
    * Define search options for forms
    *
    * @return Array Array of fields to show in search engine and options for each fields
    */
   public function getSearchOptions()
   {
      $tab = array(
         '2' => array(
            'table'         => self::getTable(),
            'field'         => 'id',
            'name'          => __('ID'),
            'searchtype'    => 'contains',
            'massiveaction' => false,
         ),
         '1' => array(
            'table'         => self::getTable(),
            'field'         => 'name',
            'name'          => __('Name'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
         ),
         '4' => array(
            'table'         => self::getTable(),
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
            'table'         => self::getTable(),
            'field'         => 'is_recursive',
            'name'          => __('Recursive'),
            'datatype'      => 'bool',
            'massiveaction' => false,
         ),
         '7' => array(
            'table'         => self::getTable(),
            'field'         => 'language',
            'name'          => __('Language'),
            'datatype'      => 'specific',
            'searchtype'    => array('equals'),
            'massiveaction' => false,
         ),
         '8' => array(
            'table'         => self::getTable(),
            'field'         => 'helpdesk_home',
            'name'          => __('Homepage', 'formcreator'),
            'datatype'      => 'bool',
            'searchtype'    => array('equals', 'notequals'),
            'massiveaction' => true,
         ),
         '9' => array(
            'table'         => self::getTable(),
            'field'         => 'access_rights',
            'name'          => __('Access', 'formcreator'),
            'datatype'      => 'specific',
            'searchtype'    => array('equals', 'notequals'),
            'massiveaction' => true,
         ),
         '10' => array(
            'table'         => getTableForItemType('PluginFormcreatorCategory'),
            'field'         => 'name',
            'name'          => PluginFormcreatorCategory::getTypeName(1),
            'datatype'      => 'dropdown',
            'massiveaction' => true,

         ),
         '30' => array(
            'table'         => self::getTable(),
            'field'         => 'is_active',
            'name'          => __('Active'),
            'datatype'      => 'specific',
            'searchtype'    => array('equals', 'notequals'),
            'massiveaction' => true,
         ),
      );
      return $tab;
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
            return Dropdown::showFromArray('criteria[0][value]', array(
               '0' => __('Inactive'),
               '1' => __('Active'),
            ), array(
               'value'               => $values[$field],
               'display_emptychoice' => true,
               'display'             => false
            ));
            break;
         case 'access_rights' :
            return Dropdown::showFromArray('criteria[0][value]', array(
               Dropdown::EMPTY_VALUE => '--- ' . __('All langages', 'formcreator') . ' ---',
               self::ACCESS_PUBLIC => __('Public access', 'formcreator'),
               self::ACCESS_PRIVATE => __('Private access', 'formcreator'),
               self::ACCESS_RESTRICTED => __('Restricted access', 'formcreator'),
            ), array(
               'value'               => $values[$field],
               'display_emptychoice' => true,
               'display'             => false
            ));
            break;
         case 'language' :
            return Dropdown::showLanguages('criteria[0][value]', array(
               'value'               => $values[$field],
               'display_emptychoice' => true,
               'emptylabel'          => '--- ' . __('All langages', 'formcreator') . ' ---',
               'display'             => false
            ));
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
      global $CFG_GLPI;
      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'is_active':
            if($values[$field] == 0) {
               $output = '<div style="text-align: center"><img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/inactive.png"
                           height="16" width="16"
                           alt="' . __('Inactive') . '"
                           title="' . __('Inactive') . '" /></div>';
            } else {
               $output = '<div style="text-align: center"><img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/active.png"
                           height="16" width="16"
                           alt="' . __('Active') . '"
                           title="' . __('Active') . '" /></div>';
            }
            return $output;
            break;
         case 'access_rights':
            switch ($values[$field]) {
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
         case 'language' :
            if (empty($values[$field])) {
               return __('All langages', 'formcreator');
            } else {
               return Dropdown::getLanguageName($values[$field]);
            }
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
   public function showForm($ID, $options=array())
   {
      global $DB;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo '<tr class="tab_bg_1">';
      echo '<td width="20%"><strong>' . __('Name') . ' <span class="red">*</span></strong></td>';
      echo '<td width="30%"><input type="text" name="name" value="' . $this->fields["name"] . '" size="35"/></td>';
      echo '<td width="20%"><strong>' . __('Active') . ' <span class="red">*</span></strong></td>';
      echo '<td width="30%">';
      Dropdown::showYesNo("is_active", $this->fields["is_active"]);
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_2">';
      echo '<td>' . __('Category') . '</td>';
      echo '<td>';
      PluginFormcreatorCategory::dropdown(array(
         'name'  => 'plugin_formcreator_categories_id',
         'value' => ($ID != 0) ? $this->fields["plugin_formcreator_categories_id"] : 0,
      ));
      echo '</td>';
      echo '<td>' . __('Direct access on homepage', 'formcreator') . '</td>';
      echo '<td>';
      Dropdown::showYesNo("helpdesk_home", $this->fields["helpdesk_home"]);
      echo '</td>';

      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td>' . __('Description') . '</td>';
      echo '<td><input type="text" name="description" value="' . $this->fields['description'] . '" size="35" /></td>';
      echo '<td>' . __('Language') . '</td>';
      echo '<td>';
      Dropdown::showLanguages('language', array(
         'value'               => ($ID != 0) ? $this->fields['language'] : $_SESSION['glpilanguage'],
         'display_emptychoice' => true,
         'emptylabel'          => '--- ' . __('All langages', 'formcreator') . ' ---',
      ));
      echo '</td>';
      echo '</tr>';

      echo '<tr class="tab_bg_1">';
      echo '<td>' . _n('Header', 'Headers', 1, 'formcreator') . '</td>';
      echo '<td colspan="3"><textarea name="content" cols="124" rows="10">' . $this->fields["content"] . '</textarea></td>';
      Html::initEditorSystem('content');
      echo '</tr>';

      echo '<tr class="tab_bg_2">';
      echo '<td>' . __('Need to be validate?', 'formcreator') . '</td>';
      echo '<td colspan="3" class="validators_bloc">';

      Dropdown::showFromArray('validation_required', array(
         0 => Dropdown::EMPTY_VALUE,
         1 => _n('User', 'Users', 1),
         2 => _n('Group', 'Groups', 1),
      ), array(
         'value'     =>  $this->fields["validation_required"],
         'on_change' => 'changeValidators(this.value)'
      ));

      // Validators users
      $validators = array();
      $formId = $this->getID();
      $form_validator = new PluginFormcreatorForm_Validator();
      $rows = $form_validator->find("`plugin_formcreator_forms_id` = '$formId'");
      foreach($rows as $id => $row) {
         $validators[] = $row['items_id'];
      }

      // Si le formulaire est récursif, on authorise les validateurs des sous-entités
      // Sinon uniquement les validateurs de l'entité du formulaire
      if ($this->isRecursive()) {
         $entites = getSonsOf('glpi_entities', $this->getEntityID());
      } else {
         $entites = $this->getEntityID();
      }
      $subentities = getEntitiesRestrictRequest("", 'pu', "", $entites, true, true);

      // Select all users with ticket validation right and there groups
      $query = "SELECT DISTINCT u.`id`, u.`name`, u.`realname`, u.`firstname`, g.`id` AS groups_id, g.`completename` AS groups_name
                FROM `glpi_users` u
                INNER JOIN `glpi_profiles_users` pu ON u.`id` = pu.`users_id`
                INNER JOIN `glpi_profiles` p ON p.`id` = pu.`profiles_id`
                INNER JOIN `glpi_profilerights` pr ON p.`id` = pr.`profiles_id`
                LEFT JOIN `glpi_groups_users` gu ON u.`id` = gu.`users_id`
                LEFT JOIN `glpi_groups` g ON g.`id` = gu.`groups_id`
                WHERE pr.`name` = 'ticketvalidation'
                AND (
                  pr.`rights` & " . TicketValidation::VALIDATEREQUEST . " = " . TicketValidation::VALIDATEREQUEST . "
                  OR pr.`rights` & " . TicketValidation::VALIDATEINCIDENT . " = " . TicketValidation::VALIDATEINCIDENT . ")
                AND $subentities
                AND u.`is_active` = '1'
                GROUP BY u.`id`
                ORDER BY u.`name`";
      $result = $DB->query($query);
      $groups_users = array();

      echo '<div id="validators_users" style="width: 100%">';
      echo '<select name="_validator_users[]" size="4" style="width: 100%" multiple id="validator_users">';
      while($user = $DB->fetch_assoc($result)) {
         $groups_users[] = $user['id'];
         if (!empty($user['realname']) && !empty($user['firstname'])) {
            $displayName = $user['realname'] . ' ' .$user['firstname'];
         } else {
            $displayName = $user['name'];
         }
         echo '<option value="' . $user['id'] . '"';
         if (in_array($user['id'], $validators)) echo ' selected="selected"';
         echo '>' . $displayName . '</option>';
      }
      echo '</select>';
      echo '</div>';

      // Validators groups
      echo '<div id="validators_groups" style="width: 100%">';
      echo '<select name="_validator_groups[]" size="4" style="width: 100%" multiple id="validator_groups">';
      if (!empty($groups_users)) {
         $query = "SELECT DISTINCT g.`id`, g.`completename`
                   FROM `glpi_groups` g
                   INNER JOIN `glpi_groups_users` gu
                     ON g.`id` = gu.`groups_id`
                     AND gu.`users_id` IN (" . implode(',', $groups_users) . ")
                   ORDER BY g.`completename`";
         $result = $DB->query($query);
         while($group = $DB->fetch_assoc($result)) {
            echo '<option value="' . $group['id'] . '"';
            if (in_array($group['id'], $validators)) echo ' selected="selected"';
            echo '>' . $group['completename'] . '</option>';
         }
      }
      echo '</select>';
      echo '</div>';

      echo '<script type="text/javascript">
               function changeValidators(value) {
                  if (value == 1) {
                     document.getElementById("validators_users").style.display  = "block";
                     document.getElementById("validators_groups").style.display = "none";
                  } else if (value == 2) {
                     document.getElementById("validators_users").style.display  = "none";
                     document.getElementById("validators_groups").style.display = "block";
                  } else {
                     document.getElementById("validators_users").style.display  = "none";
                     document.getElementById("validators_groups").style.display = "none";
                  }
                  fcInitMultiSelect();
               }
               changeValidators(' . $this->fields["validation_required"] . ');
            </script>';
      echo '</td>';
      echo '</tr>';

      echo '<td>'.__('Default form in service catalog', 'formcreator').'</td>';
      echo '<td>';
      Dropdown::showYesNo("is_default", $this->fields["is_default"]);
      echo '</td>';
      echo '</tr>';

      $this->showFormButtons($options);
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
            $found = $object->find();
            $number  = count($found);
            return self::createTabEntry(self::getTypeName($number), $number);
            break;
         case "PluginFormcreatorForm":
            return __('Preview');
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
      $uri = strrchr($_SERVER['HTTP_REFERER'], '/');
      if(strpos($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));
      $uri = trim($uri, '/');

      switch ($uri) {
         case "form.form.php":
            echo '<div style="text-align: left">';
            $item->displayUserForm($item);
            echo '</div>';
            break;
      }
   }


   public function defineTabs($options=array())
   {
      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginFormcreatorQuestion', $ong, $options);
      $this->addStandardTab('PluginFormcreatorForm_Profile', $ong, $options);
      $this->addStandardTab('PluginFormcreatorTarget', $ong, $options);
      $this->addStandardTab(__CLASS__, $ong, $options);
      return $ong;
   }

   /**
    * Show the list of forms to be displayed to the end-user
    */
   public function showList() {
      global $CFG_GLPI, $DB;

      echo '<div class="center" id="plugin_formcreator_wizard">';

      echo '<div class="plugin_formcreator_marginRight plugin_formcreator_card">';
      $this->showWizard();
      // echo '<hr style="clear:both; height:0; background: transparent; border:none" />';
      echo '</div>';

      echo '<div id="plugin_formcreator_lastForms">';
      $this->showMyLastForms();
      echo '</div>';

      echo '</div>';
   }

   public function showServiceCatalog() {
      global $CFG_GLPI;

      echo "<div id='formcreator_servicecatalogue'>";

      // show wizard
      echo '<div id="plugin_formcreator_wizard" class="plugin_formcreator_menuplaceholder">';
      $this->showWizard(true);
      echo '</div>';

      echo '</div>'; #formcreator_servicecatalogue
   }

   public function showWizard($service_catalog = false) {

      echo '<div id="plugin_formcreator_wizard_categories">';
      echo '<div><h2>'._n("Category", "Categories", 2, 'formcreator').'</h2></div>';
      echo '<div><a href="#" id="wizard_seeall">' . __('see all', 'formcreator') . '</a></div>';
      echo '</div>';

      echo '<div id="plugin_formcreator_wizard_right">';

      // hook display central (for alert plugin)
      if ($service_catalog) {
         echo "<div id='plugin_formcreator_display_central'>";
         Plugin::doHook('display_central');
         echo "</div>";
      }

      echo '<div id="plugin_formcreator_searchBar">';
      $this->showSearchBar();
      echo '</div>';
      echo '<div class="plugin_formcreator_sort">';
      echo '<span class="formcreator_radios">';
      echo '<input type="radio" class="form-control" id="plugin_formcreator_mostPopular" name="sort" value="mostPopularSort" />';
      echo '<label for="plugin_formcreator_mostPopular">'.__('Popularity sort', 'formcreator').'</label>';
      echo '</span>';
      echo '<span class="formcreator_radios">';
      echo '<input type="radio" class="form-control" id="plugin_formcreator_alphabetic" name="sort" value="alphabeticSort" />';
      echo '<label for="plugin_formcreator_alphabetic">'.__('Alphabetic sort', 'formcreator').'</label>';
      echo '</span>';
      echo '</div>';
      echo '<div id="plugin_formcreator_wizard_forms">';
      echo '</div>';
      echo '</div>';
   }

   /**
    * Show form and FAQ items
    * @param number $rootCategory Items of this subtree only. 0 = no filtering
    * @param string $keywords Filter items with keywords
    * @param boolean $popularity If true : popularity sort; if false alphabetic sort
    * @param string $helpdeskHome show items for helpdesk only
    */
   public function showFormList($rootCategory = 0, $keywords = '', $helpdeskHome = false) {
      global $DB;

      $table_cat     = getTableForItemType('PluginFormcreatorCategory');
      $table_form    = getTableForItemType('PluginFormcreatorForm');
      $table_fp      = getTableForItemType('PluginFormcreatorForm_Profile');
      $table_target  = getTableForItemType('PluginFormcreatorTargets');
      $table_section = getTableForItemType('PluginFormcreatorSections');
      $table_question= getTableForItemType('PluginFormcreatorQuestions');
      $where         = getEntitiesRestrictRequest( "", $table_form, "", "", true, false);

      $order         = "$table_form.name ASC";

      $where_form    = "$table_form.`is_active` = 1 AND $table_form.`is_deleted` = 0 ";
      $where_form    .= getEntitiesRestrictRequest("AND", $table_form, "", "", true, false);
      $where_form    .= " AND $table_form.`language` IN ('".$_SESSION['glpilanguage']."', '', NULL, '0')";

      if ($helpdeskHome) {
         $where_form    .= "AND $table_form.`helpdesk_home` = '1'";
      }

      if ($rootCategory != 0) {
         $selectedCategories = getSonsOf($table_cat, $rootCategory);
         $selectedCategories = implode(', ', array_keys($selectedCategories));
         $where_form .= " AND $table_form.`plugin_formcreator_categories_id` IN ($selectedCategories)";
      } else {
         $selectedCategories = '';
      }

      // Find forms accessible by the current user
      $keywords = preg_replace("/[^A-Za-z0-9 ]/", '', $keywords);
      if (!empty($keywords)) {
         // Determine the optimal search mode
         $searchMode = "BOOLEAN MODE";
         $query = "SHOW TABLE STATUS WHERE `Name` = '$table_form'";
         $result = $DB->query($query);
         if ($result) {
            $row = $DB->fetch_assoc($result);
            if ($row['Rows'] > 20) {
               $searchMode = "NATURAL LANGUAGE MODE";
            }
         }
         $keywords = $DB->escape($keywords);
         $highWeightedMatch = " MATCH($table_form.`name`, $table_form.`description`)
               AGAINST('$keywords*' IN $searchMode)";
         $lowWeightedMatch = " MATCH($table_question.`name`, $table_question.`description`)
               AGAINST('$keywords*' IN $searchMode)";
         $where_form .= " AND ($highWeightedMatch OR $lowWeightedMatch)";
      }
      $query_forms = "SELECT
         $table_form.id,
         $table_form.name,
         $table_form.description,
         $table_form.usage_count,
         $table_form.is_default
      FROM $table_form
      LEFT JOIN $table_cat ON ($table_cat.id = $table_form.`plugin_formcreator_categories_id`)
      LEFT JOIN $table_target ON ($table_target.`plugin_formcreator_forms_id` = $table_form.`id`)
      LEFT JOIN $table_section ON ($table_section.`plugin_formcreator_forms_id` = $table_form.`id`)
      LEFT JOIN $table_question ON ($table_question.`plugin_formcreator_sections_id` = $table_section.`id`)
      WHERE $where_form
      AND (`access_rights` != ".PluginFormcreatorForm::ACCESS_RESTRICTED." OR $table_form.`id` IN (
         SELECT plugin_formcreator_forms_id
         FROM $table_fp
         WHERE `profiles_id` = ".$_SESSION['glpiactiveprofile']['id']."))
      GROUP BY `$table_target`.`plugin_formcreator_forms_id`,
               $table_form.id,
               $table_form.name,
               $table_form.description,
               $table_form.usage_count,
               $table_form.is_default
      HAVING COUNT(`$table_target`.`plugin_formcreator_forms_id`) > 0
      ORDER BY $order";
      $result_forms = $DB->query($query_forms);

      $formList = array();
      if ($DB->numrows($result_forms) > 0) {
         while ($form = $DB->fetch_array($result_forms)) {
            $formDescription = plugin_formcreator_encode($form['description']);
            $formList[] = [
                  'id'           => $form['id'],
                  'name'         => $form['name'],
                  'description'  => $formDescription,
                  'type'         => 'form',
                  'usage_count'  => $form['usage_count'],
                  'is_default'   => $form['is_default']?"true":"false"
            ];
         }
      }

      // Find FAQ entries
      $query_faqs = KnowbaseItem::getListRequest([
            'faq'      => '1',
            'contains' => $keywords
      ]);
      if ($selectedCategories != '') {
         $query_faqs = "SELECT `faqs`.* FROM ($query_faqs)  AS `faqs`
         WHERE `faqs`.`knowbaseitemcategories_id` IN (SELECT `knowbaseitemcategories_id` FROM `$table_cat` WHERE `id` IN ($selectedCategories) AND `knowbaseitemcategories_id` <> '0')";
      } else {
         $query_faqs = "SELECT `faqs`.* FROM ($query_faqs)  AS `faqs`
         INNER JOIN `$table_cat` ON (`faqs`.`knowbaseitemcategories_id` = `$table_cat`.`knowbaseitemcategories_id`)
         WHERE `faqs`.`knowbaseitemcategories_id` <> '0'";
      }
      $result_faqs = $DB->query($query_faqs);
      if ($DB->numrows($result_faqs) > 0) {
         while ($faq = $DB->fetch_array($result_faqs)) {
            $formList[] = [
                  'id'           => $faq['id'],
                  'name'         => $faq['name'],
                  'description'  => '',
                  'type'         => 'faq',
                  'usage_count'  => $faq['view'],
                  'is_default'   => false
            ];
         }
      }

      if (count($formList) == 0) {
         $defaultForms = true;
         // No form nor FAQ have been selected
         // Fallback to default forms
         $where_form       = "$table_form.`is_active` = 1 AND $table_form.`is_deleted` = 0 ";
         $where_form       .= getEntitiesRestrictRequest("AND", $table_form, "", "", true, false);
         $where_form       .= " AND $table_form.`language` IN ('".$_SESSION['glpilanguage']."', '', NULL, '0')";
         $where_form       .= " AND `is_default` <> '0'";
         $query_forms = "SELECT $table_form.id, $table_form.name, $table_form.description, $table_form.usage_count
         FROM $table_form
         LEFT JOIN $table_cat ON ($table_cat.id = $table_form.`plugin_formcreator_categories_id`)
         WHERE $where_form
         AND (`access_rights` != ".PluginFormcreatorForm::ACCESS_RESTRICTED." OR $table_form.`id` IN (
         SELECT plugin_formcreator_forms_id
         FROM $table_fp
         WHERE profiles_id = ".$_SESSION['glpiactiveprofile']['id']."))
         ORDER BY $order";
         $result_forms = $DB->query($query_forms);

         if ($DB->numrows($result_forms) > 0) {
            while ($form = $DB->fetch_array($result_forms)) {
               $formDescription = plugin_formcreator_encode($form['description']);
               $formList[] = [
                     'id'           => $form['id'],
                     'name'         => $form['name'],
                     'description'  => $formDescription,
                     'type'         => 'form',
                     'usage_count'  => $form['usage_count'],
                     'is_default'   => true
               ];
            }
         }
      } else {
         $defaultForms = false;
      }
      return array('default' => $defaultForms, 'forms' => $formList);
   }

   protected function showSearchBar() {
      echo '<form name="formcreator_search" onsubmit="javascript: return false;" >';
      echo '<input type="text" name="words" id="formcreator_search_input" required/>';
      echo '<span id="formcreator_search_input_bar"></span>';
      echo '<label for="formcreator_search_input">'.__('Please, describe your need here', 'formcreator').'</label>';
      echo '</form>';
   }

   protected function showMyLastForms() {
      global $DB, $CFG_GLPI;

      $userId = $_SESSION['glpiID'];
      echo '<div class="plugin_formcreator_card">';
      echo '<div class="plugin_formcreator_heading">'.__('My last forms (requester)', 'formcreator').'</div>';
      $query = "SELECT fa.`id`, f.`name`, fa.`status`, fa.`request_date`
                      FROM glpi_plugin_formcreator_forms f
                      INNER JOIN glpi_plugin_formcreator_forms_answers fa ON f.`id` = fa.`plugin_formcreator_forms_id`
                      WHERE fa.`requester_id` = '$userId'
                      AND f.is_deleted = 0
                      ORDER BY fa.`status` ASC, fa.`request_date` DESC
                      LIMIT 0, 5";
      $result = $DB->query($query);
      if ($DB->numrows($result) == 0) {
         echo '<div class="line1" align="center">'.__('No form posted yet', 'formcreator').'</div>';
         echo "<ul>";
      } else {
         while ($form = $DB->fetch_assoc($result)) {
               echo '<li class="plugin_formcreator_answer">';
               echo ' <a class="plugin_formcreator_'.$form['status'].'" href="form_answer.form.php?id='.$form['id'].'">'.$form['name'].'</a>';
               echo '<span class="plugin_formcreator_date">'.Html::convDateTime($form['request_date']).'</span>';
               echo '</li>';
         }
         echo "</ul>";
         echo '<div align="center">';
         echo '<a href="form_answer.php?criteria[0][field]=4&criteria[0][searchtype]=equals&criteria[0][value]='.$userId.'">';
         echo __('All my forms (requester)', 'formcreator');
         echo '</a>';
         echo '</div>';
      }
      echo '</div>';

      if (Session::haveRight('ticketvalidation', TicketValidation::VALIDATEINCIDENT)
            || Session::haveRight('ticketvalidation', TicketValidation::VALIDATEREQUEST)) {

         echo '<div class="plugin_formcreator_card">';
         echo '<div class="plugin_formcreator_heading">'.__('My last forms (validator)', 'formcreator').'</div>';
         $groupList = Group_User::getUserGroups($userId);
         $groupIdList = array();
         foreach ($groupList as $group) {
            $groupIdList[] = $group['id'];
         }
         $groupIdList = $groupIdList + array('NULL', '0', '');
         $groupIdList = "'" . implode("', '", $groupIdList) . "'";
         $query = "SELECT fa.`id`, f.`name`, fa.`status`, fa.`request_date`
                FROM glpi_plugin_formcreator_forms f
                INNER JOIN glpi_plugin_formcreator_forms_validators fv ON fv.`plugin_formcreator_forms_id`=f.`id`
                INNER JOIN glpi_plugin_formcreator_forms_answers fa ON f.`id` = fa.`plugin_formcreator_forms_id`
                WHERE (f.`validation_required` = 1 AND fv.`items_id` = '$userId' AND fv.`itemtype` = 'User'
                   OR f.`validation_required` = 2 AND fv.`items_id` IN ($groupIdList) AND fv.`itemtype` = 'Group'
                )
                AND f.is_deleted = 0
                ORDER BY fa.`status` ASC, fa.`request_date` DESC
                LIMIT 0, 5";
         $result = $DB->query($query);
         if ($DB->numrows($result) == 0) {
            echo '<div class="line1" align="center">'.__('No form waiting for validation', 'formcreator').'</div>';
         } else {
            echo "<ul>";
            while ($form = $DB->fetch_assoc($result)) {
               echo '<li class="plugin_formcreator_answer">';
               echo ' <a class="plugin_formcreator_'.$form['status'].'" href="form_answer.form.php?id='.$form['id'].'">'.$form['name'].'</a>';
               echo '<span class="plugin_formcreator_date">'.Html::convDateTime($form['request_date']).'</span>';
               echo '</li>';
            }
            echo "</ul>";
            echo '<div align="center">';
            echo '<a href="form_answer.php?criteria[0][field]=5&criteria[0][searchtype]=equals&criteria[0][value]='.$_SESSION['glpiID'].'">';
            echo __('All my forms (validator)', 'formcreator');
            echo '</a>';
            echo '</div>';
         }
         echo '</div>';
      }
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
      global $CFG_GLPI, $DB;

      if(isset($_SESSION['formcreator']['datas'])) {
         $datas = $_SESSION['formcreator']['datas'];
         unset($_SESSION['formcreator']['datas']);
      } else {
         $datas = null;
      }

      // Print css media
      echo Html::css(FORMCREATOR_ROOTDOC."/css/print.css", array('media' => 'print'));

      // Display form
      echo "<form name='formcreator_form'".$item->getID()."' method='post' role='form' enctype='multipart/form-data'
               action='". $CFG_GLPI['root_doc']."/plugins/formcreator/front/form.form.php'
               class='formcreator_form form_horizontal' onsubmit='return validateForm(this);'>";
      echo "<h1 class='form-title'>";
      echo $item->fields['name']."&nbsp;";
      echo "<img src='".FORMCREATOR_ROOTDOC."/pics/print.png' class='pointer print_button'
                 title='".__("Print this form", 'formcreator')."' onclick='window.print();'>";
      echo '</h1>';

      // Form Header
      if (!empty($item->fields['content'])) {
         echo '<div class="form_header">';
         echo html_entity_decode($item->fields['content']);
         echo '</div>';
      }
      // Get and display sections of the form
      $question      = new PluginFormcreatorQuestion();
      $questions     = array();

      $section_class = new PluginFormcreatorSection();
      $find_sections = $section_class->find('plugin_formcreator_forms_id = ' . $item->getID(), '`order` ASC');
      echo '<div class="form_section">';
      foreach ($find_sections as $section_line) {
         echo '<h2>' . $section_line['name'] . '</h2>';

         // Display all fields of the section
         $questions = $question->find('plugin_formcreator_sections_id = ' . $section_line['id'], '`order` ASC');
         foreach ($questions as $question_line) {
            if (isset($datas[$question_line['id']])) {
               // multiple choice question are saved as JSON and needs to be decoded
               $answer = (in_array($question_line['fieldtype'], array('checkboxes', 'multiselect')))
                           ? json_decode($datas[$question_line['id']])
                           : $datas[$question_line['id']];
            } else {
               $answer = null;
            }
            PluginFormcreatorFields::showField($question_line, $answer);
         }
      }
      echo '<script type="text/javascript">formcreatorShowFields();</script>';

      // Show validator selector
      if ($item->fields['validation_required'] > 0) {
         $table_form_validator = PluginFormcreatorForm_Validator::getTable();
         $validators = array(0 => Dropdown::EMPTY_VALUE);

         // Groups
         if ($item->fields['validation_required'] == 2) {
            $query = "SELECT g.`id`, g.`completename`
                      FROM `glpi_groups` g
                      LEFT JOIN `$table_form_validator` fv ON fv.`items_id` = g.`id` AND fv.itemtype = 'Group'
                      WHERE fv.`plugin_formcreator_forms_id` = " . $this->getID();
            $result = $DB->query($query);
            while($validator = $DB->fetch_assoc($result)) {
               $validators[$validator['id']] = $validator['completename'];
            }

         // Users
         } else {
            $query = "SELECT u.`id`, u.`name`, u.`realname`, u.`firstname`
                      FROM `glpi_users` u
                      LEFT JOIN `$table_form_validator` fv ON fv.`items_id` = u.`id` AND fv.itemtype = 'User'
                      WHERE fv.`plugin_formcreator_forms_id` = " . $this->getID();
            $result = $DB->query($query);
            while($validator = $DB->fetch_assoc($result)) {
               $validators[$validator['id']] = formatUserName($validator['id'], $validator['name'], $validator['realname'], $validator['firstname']);
            }
         }

         echo '<div class="form-group required liste line' . (count($questions) + 1) % 2 . '" id="form-validator">';
         echo '<label>' . __('Choose a validator', 'formcreator') . ' <span class="red">*</span></label>';
         Dropdown::showFromArray('formcreator_validator', $validators);
         echo '</div>';
      }

      echo '</div>';

      // Display submit button
      echo '<div class="center">';
      echo '<input type="submit" name="submit_formcreator" class="submit_button" value="' . __('Send') . '" />';
      echo '</div>';

      echo '<input type="hidden" name="formcreator_form" value="' . $item->getID() . '">';
      echo '<input type="hidden" name="_glpi_csrf_token" value="' . Session::getNewCSRFToken() . '">';
      echo '<input type="hidden" name="uuid" value="' .$item->fields['uuid'] . '">';
      echo '</form>';
   }

   /**
    * Prepare input datas for adding the form
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForAdd($input)
   {
      // Decode (if already encoded) and encode strings to avoid problems with quotes
      foreach ($input as $key => $value) {
         if (!is_array($value)) {
            $input[$key] = plugin_formcreator_encode($value);
         }
      }

      // generate a uniq id
      if (!isset($input['uuid'])
          || empty($input['uuid'])) {
         $input['uuid'] = plugin_formcreator_getUuid();
      }

      // Control fields values :
      // - name is required
      if(isset($input['name'])
         && empty($input['name'])) {
         Session::addMessageAfterRedirect(__('The name cannot be empty!', 'formcreator'), false, ERROR);
         return array();
      }

      return $input;
   }

   /**
    * Actions done after the ADD of the item in the database
    *
    * @return nothing
   **/
   public function post_addItem()
   {
      $this->updateValidators();
      return true;
   }

   /**
    * Prepare input datas for updating the form
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForUpdate($input)
   {
      if (isset($input['access_rights'])
            || isset($_POST['massiveaction'])
            || isset($input['usage_count'])) {
         return $input;
      } else {
         $this->updateValidators();
         return $this->prepareInputForAdd($input);
      }
   }

   /**
    * Actions done after the PURGE of the item in the database
    *
    * @return nothing
   **/
   public function post_purgeItem() {
      global $DB;

      $target = new PluginFormcreatorTarget();
      $target->deleteByCriteria(array('plugin_formcreator_forms_id' => $this->getID()));

      $section = new PluginFormcreatorSection();
      $section->deleteByCriteria(array('plugin_formcreator_forms_id' => $this->getID()));

      $form_validator = new PluginFormcreatorForm_Validator();
      $form_validator->deleteByCriteria(array('plugin_formcreator_forms_id' => $this->getID()));
   }

   /**
    * Save form validators
    *
    * @return void
    */
   private function updateValidators()
   {
      global $DB;

      if (!isset($this->input['validation_required'])) {
         return true;
      }

      $form_validator = new PluginFormcreatorForm_Validator();
      $form_validator->deleteByCriteria(array('plugin_formcreator_forms_id' => $this->getID()));

      if ($this->input['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_USER
          && !empty($this->input['_validator_users'])
          || $this->input['validation_required'] == PluginFormcreatorForm_Validator::VALIDATION_GROUP
          && !empty($this->input['_validator_groups'])) {

         switch ($this->input['validation_required']) {
            case PluginFormcreatorForm_Validator::VALIDATION_USER:
               $validators = $this->input['_validator_users'];
               $validatorItemtype = 'User';
               break;
            case PluginFormcreatorForm_Validator::VALIDATION_GROUP:
               $validators = $this->input['_validator_groups'];
               $validatorItemtype = 'Group';
               break;
         }
         foreach ($validators as $itemId) {
            $form_validator = new PluginFormcreatorForm_Validator();
            $form_validator->add(array(
                  'plugin_formcreator_forms_id' => $this->getID(),
                  'itemtype'                    => $validatorItemtype,
                  'items_id'                    => $itemId
            ));
         }
      }
   }

   public function saveForm()
   {
      $valid = true;

      $tab_section    = array();
      $datas          = array();
      $sections       = new PluginFormcreatorSection();
      $found_sections = $sections->find('`plugin_formcreator_forms_id` = ' . $this->getID());
      foreach ($found_sections as $id => $fields) $tab_section[] = $id;

      if (count($tab_section) < 1) {
         $found_questions = array();
      } else {
         $questions         = new PluginFormcreatorQuestion();
         $found_questions = $questions->find('`plugin_formcreator_sections_id` IN (' . implode(',', $tab_section) .')');
      }
      // Validate form fields
      foreach ($found_questions as $id => $fields) {
         // If field was not post, it's value is empty
         if (isset($_POST['formcreator_field_' . $id])) {
            $datas[$id] = is_array($_POST['formcreator_field_' . $id])
                           ? json_encode($_POST['formcreator_field_' . $id])
                           : $_POST['formcreator_field_' . $id];

            // Replace "," by "." if field is a float field and remove spaces
            if ($fields['fieldtype'] == 'float') {
               $datas[$id] = str_replace(',', '.', $datas[$id]);
               $datas[$id] = str_replace(' ', '', $datas[$id]);
            }
            unset($_POST['formcreator_field_' . $id]);
         } else {
            $datas[$id] = '';
         }

         $className = 'PluginFormcreator' . ucfirst($fields['fieldtype']) . 'Field';
         $filePath  = dirname(__FILE__) . '/fields/' . $fields['fieldtype'] . '-field.class.php';

         if (class_exists($className)) {
            $obj = new $className($fields, $datas);
            if (PluginFormcreatorFields::isVisible($id, $datas) && !$obj->isValid($datas[$id])) {
               $valid = false;
            }
         } else {
            $valid = false;
         }
      }
      if (isset($_POST) && is_array($_POST)) {
         $datas = $datas + $_POST;
      }

      // Check required_validator
      if ($this->fields['validation_required'] && empty($datas['formcreator_validator'])) {
         Session::addMessageAfterRedirect(__('You must select validator !','formcreator'), false, ERROR);
         $valid = false;
      }

      // If not valid back to form
      if (!$valid) {
         foreach($datas as $key => $value) {
            if (is_array($value)) {
               foreach($value as $key2 => $value2) {
                  $datas[$key][$key2] = plugin_formcreator_encode($value2);
               }
            } elseif(is_array(json_decode($value))) {
               $value = json_decode($value);
               foreach($value as $key2 => $value2) {
                  $value[$key2] = plugin_formcreator_encode($value2);
               }
               $datas[$key] = json_encode($value);
            } else {
               $datas[$key] = plugin_formcreator_encode($value);
            }
         }

         $_SESSION['formcreator']['datas'] = $datas;
      // Save form
      } else {
         $formanswer = new PluginFormcreatorForm_Answer();
         $formanswer->saveAnswers($datas);
      }

      return $valid;
   }

   public function increaseUsageCount() {
      // Increase usage count of the form
      $this->update([
            'id' => $this->getID(),
            'usage_count' => $this->getField('usage_count') + 1,
      ]);
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

      $table = self::getTable();

      // Create default request type
      $query  = "SELECT id FROM `glpi_requesttypes` WHERE `name` LIKE 'Formcreator';";
      $result = $DB->query($query) or die ($DB->error());
      if ($DB->numrows($result) > 0) {
         list($requesttype) = $DB->fetch_array($result);
      } else {
         $query = "INSERT INTO `glpi_requesttypes` SET `name` = 'Formcreator';";
         $DB->query($query) or die ($DB->error());
         $requesttype = $DB->insert_id();
      }

      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

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
                     `plugin_formcreator_categories_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
                     `is_active` tinyint(1) NOT NULL DEFAULT '0',
                     `language` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
                     `helpdesk_home` tinyint(1) NOT NULL DEFAULT '0',
                     `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
                     `validation_required` tinyint(1) NOT NULL DEFAULT '0',
                     `usage_count` int(11) NOT NULL DEFAULT '0',
                     `is_default` tinyint(1) NOT NULL DEFAULT '0',
                     `uuid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }

      // Migration from previous version
      if (FieldExists($table, 'cat', false)
          || !FieldExists($table, 'plugin_formcreator_categories_id', false)) {
         $migration->addField($table, 'plugin_formcreator_categories_id',
                              'integer', array('value' => '0'));
      }

      // Migration from previous version
      if (!FieldExists($table, 'validation_required', false)) {
         $migration->addField($table, 'validation_required', 'bool', array('value' => '0'));
      }

      // Migration from previous version
      if (!FieldExists($table, 'requesttype', false)) {
         $migration->addField($table, 'access_rights', 'bool', array('value' => '1'));
         $migration->addField($table, 'requesttype', 'integer', array('value' => '1'));
         $migration->addField($table, 'description', 'string');
         $migration->addField($table, 'helpdesk_home', 'bool', array('value' => '0'));
         $migration->addField($table, 'is_deleted', 'bool', array('value' => '0'));
      }

      /**
       * Migration of special chars from previous versions
       *
       * @since 0.85-1.2.3
       */
      $query  = "SELECT `id`, `name`, `description`, `content`
                 FROM `$table`";
      $result = $DB->query($query);
      while ($line = $DB->fetch_array($result)) {
         $query_update = "UPDATE `$table` SET
                            `name`        = '" . plugin_formcreator_encode($line['name']) . "',
                            `description` = '" . plugin_formcreator_encode($line['description']) . "',
                            `content`     = '" . plugin_formcreator_encode($line['content']) . "'
                          WHERE `id` = " . (int) $line['id'];
         $DB->query($query_update) or die ($DB->error());
      }

      /**
       * Add natural language search
       * Add form usage counter
       *
       * @since 0.90-1.4
       */
      // An error may occur if the Search index does not exists
      // This is not critical as we need to (re) create it
      If (isIndex('glpi_plugin_formcreator_forms', 'Search')) {
         $query = "ALTER TABLE `glpi_plugin_formcreator_forms` DROP INDEX `Search`";
         $DB->query($query);
      }

      // Re-add FULLTEXT index
      $query = "ALTER TABLE `glpi_plugin_formcreator_forms` ADD FULLTEXT INDEX `Search` (`name`, `description`)";
      $DB->query($query) or die ($DB->error());

      $migration->addField($table, 'usage_count', 'integer', array('after' => 'validation_required',
                                                                   'value' => '0'));
      $migration->addField($table, 'is_default', 'bool', array('after' => 'usage_count',
                                                               'value' => '0'));


      // Create standard search options
      $displayPreference = new DisplayPreference();
      $displayPreference->deleteByCriteria(array('itemtype' => 'PluginFormcreatorForm'));

      $query = "INSERT IGNORE INTO `glpi_displaypreferences`
                  (`id`, `itemtype`, `num`, `rank`, `users_id`)
               VALUES
                  (NULL, '" . __CLASS__ . "', 30, 1, 0),
                  (NULL, '" . __CLASS__ . "', 3, 2, 0),
                  (NULL, '" . __CLASS__ . "', 10, 3, 0),
                  (NULL, '" . __CLASS__ . "', 7, 4, 0),
                  (NULL, '" . __CLASS__ . "', 8, 5, 0),
                  (NULL, '" . __CLASS__ . "', 9, 6, 0);";
      $DB->query($query) or die ($DB->error());


      // add uuid to forms
      if (!FieldExists($table, 'uuid', false)) {
         $migration->addField($table, 'uuid', 'string', array('after' => 'is_default'));
      }

      $migration->migrationOneTable($table);

      // fill missing uuid (force update of forms, see self::prepareInputForUpdate)
      $obj = new self();
      $all_forms = $obj->find("uuid IS NULL");
      foreach($all_forms as $forms_id => $form) {
         $obj->update(array('id' => $forms_id));
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

      $DB->query('DROP TABLE IF EXISTS `'.self::getTable().'`');
      $DB->query('DROP TABLE IF EXISTS `glpi_plugin_formcreator_questions_conditions`');

      // Delete logs of the plugin
      $log = new Log();
      $log->deleteByCriteria(array('itemtype' => __CLASS__));

      $displayPreference = new DisplayPreference();
      $displayPreference->deleteByCriteria(array('itemtype' => 'PluginFormcreatorForm'));

      return true;
   }

   /**
    * Duplicate a from. Execute duplicate action for massive action.
    *
    * NB: Queries are made directly in SQL without GLPI's API to avoid controls made by Add(), prepareInputForAdd(), etc.
    *
    * @return Boolean true if success, false toherwize.
    */
   public function Duplicate()
   {
      global $DB;

      $section             = new PluginFormcreatorSection();
      $question            = new PluginFormcreatorQuestion();
      $target              = new PluginFormcreatorTarget();
      $target_ticket       = new PluginFormcreatorTargetTicket();
      $target_ticket_actor = new PluginFormcreatorTargetTicket_Actor();
      $form_section        = new PluginFormcreatorSection();
      $section_question    = new PluginFormcreatorQuestion();
      $question_condition  = new PluginFormcreatorQuestion_Condition();
      $form_validator      = new PluginFormcreatorForm_Validator();
      $form_profile        = new PluginFormcreatorForm_Profile();
      $tab_questions       = array();

      // From datas
      $form_datas              = $this->fields;
      $form_datas['name']     .= ' [' . __('Duplicate', 'formcreator') . ']';
      $form_datas['is_active'] = 0;

      unset($form_datas['id']);

      $old_form_id             = $this->getID();
      $new_form_id             = $this->add($form_datas);
      if ($new_form_id === false) return false;

      // Form profiles
      $rows = $form_profile->find("`plugin_formcreator_forms_id` = '$old_form_id'");
      foreach($rows as $row) {
         unset($row['id'],
               $row['uuid']);
         $row['plugin_formcreator_forms_id'] = $new_form_id;
         if (!$form_validator->add($row)) {
            return false;
         }
      }

      // Form validators
      $rows = $form_validator->find("`plugin_formcreator_forms_id` = '$old_form_id'");
      foreach($rows as $row) {
         unset($row['id'],
               $row['uuid']);
         $row['plugin_formcreator_forms_id'] = $new_form_id;
         if (!$form_validator->add($row)) {
            return false;
         }
      }

      // Form sections
      $rows = $form_section->find("`plugin_formcreator_forms_id` = '$old_form_id'");
      foreach($rows as $sections_id => $row) {
         unset($row['id'],
               $row['uuid']);
         $row['plugin_formcreator_forms_id'] = $new_form_id;
         if (!$new_sections_id = $form_section->add($row)) {
            return false;
         }

         // Form questions
         $rows = $section_question->find("`plugin_formcreator_sections_id` = '$sections_id'");
         foreach($rows as $questions_id => $row) {
            unset($row['id'],
                  $row['uuid']);
            $row['plugin_formcreator_sections_id'] = $new_sections_id;
            if (!$new_questions_id = $section_question->add($row)) {
               return false;
            }

            $tab_questions[$questions_id] = $new_questions_id;

            // Form questions conditions
            $rows = $question_condition->find("`plugin_formcreator_questions_id` = '$questions_id'");
            foreach($rows as $conditions_id => $row) {
               unset($row['id'],
                     $row['uuid']);
               $row['plugin_formcreator_questions_id'] = $new_questions_id;
               if (!$new_conditions_id = $question_condition->add($row)) {
                  return false;
               }
            }
         }
      }

      // Form targets
      $rows = $target->find("`plugin_formcreator_forms_id` = '$old_form_id'");
      foreach($rows as $targets_id => $target_values) {
         unset($target_values['id'],
               $target_values['uuid']);
         $target_values['plugin_formcreator_forms_id'] = $new_form_id;
         if (!$target->add($target_values)) {
            return false;
         }

         if (!$target_ticket->getFromDB($target_values['items_id'])) {
            return false;
         }

         $update_target_ticket = $target_ticket->fields;
         unset($update_target_ticket['id'], $update_target_ticket['uuid']);
         foreach ($tab_questions as $id => $value) {
            $update_target_ticket['name']    = str_replace('##question_' . $id . '##', '##question_' . $value . '##', $update_target_ticket['name']);
            $update_target_ticket['name']    = str_replace('##answer_' . $id . '##', '##answer_' . $value . '##', $update_target_ticket['name']);
            $update_target_ticket['comment'] = str_replace('##question_' . $id . '##', '##question_' . $value . '##', $update_target_ticket['comment']);
            $update_target_ticket['comment'] = str_replace('##answer_' . $id . '##', '##answer_' . $value . '##', $update_target_ticket['comment']);
         }

         $new_target_ticket = new PluginFormcreatorTargetTicket();
         $new_target_ticket->add($update_target_ticket);
         $new_target_ticket_id = $new_target_ticket->getID();
         if (!$new_target_ticket_id) return false;

         $target->update(array(
               'id'        => $target->getID(),
               'items_id'  => $new_target_ticket_id,
         ));

         // Form target tickets actors
         $rows = $target_ticket_actor->find("`plugin_formcreator_targettickets_id` = '{$target_values['items_id']}'");
         foreach($rows as $actors_id => $row) {
            unset($row['id'],
                  $row['uuid']);
            $row['plugin_formcreator_targettickets_id'] = $new_target_ticket_id;
            if (!$new_actors_id = $target_ticket_actor->add($row)) {
               return false;
            }
         }
      }

      return true;
   }

   /**
    * Transfer a form to another entity. Execute transfert action for massive action.
    *
    * @return Boolean true if success, false otherwize.
    */
   public function Transfer($entity)
   {
      global $DB;

      $query = "UPDATE `glpi_plugin_formcreator_forms` SET
                   `entities_id` = " . $entity . "
                WHERE `id` = " . $this->fields['id'];
      $DB->query($query);
      return true;
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   public static function showMassiveActionsSubForm(MassiveAction $ma) {
      switch ($ma->getAction()) {
         case 'Transfert':
            Entity::dropdown(array(
               'name' => 'entities_id',
            ));
            echo '<br /><br />' . Html::submit(_x('button','Post'), array('name' => 'massiveaction'));
            return true;
    }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * Execute massive action for PluginFormcreatorFrom
    *
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,  array $ids)
   {
      global $DB;

      switch ($ma->getAction()) {
         case 'Duplicate' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id) && $item->Duplicate()) {
                  Session::addMessageAfterRedirect(sprintf(__('Form duplicated: %s', 'formcreator'), $item->getName()));
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            return;
         case 'Transfert' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id) && $item->Transfer($ma->POST['entities_id'])) {
                  Session::addMessageAfterRedirect(sprintf(__('Form Transfered: %s', 'formcreator'), $item->getName()));
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            return;
         case 'Export' :
            foreach ($ids as $id) {
               if ($item->getFromDB($id)) {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  // Example of ko count
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
            }
            echo "<br>";
            echo "<div class='center'>";
            echo "<a href='#' onclick='window.history.back()'>".__("Back")."</a>";
            echo "</div>";

            $listOfId = array('plugin_formcreator_forms_id' => array_values($ids));
            Html::redirect(FORMCREATOR_ROOTDOC."/front/export.php?".Toolbox::append_params($listOfId));
            header("Content-disposition:attachment filename=\"test\"");
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

   public static function countAvailableForm() {
      global $DB;

      $form_table = getTableForItemType('PluginFormcreatorForm');
      $table_fp   = getTableForItemType('PluginFormcreatorForm_Profile');
      $nb         = 0;

      if (TableExists($form_table)
          && TableExists($table_fp)
          && isset($_SESSION['glpiactiveprofile']['id'])) {
         $where      = getEntitiesRestrictRequest( "", $form_table, "", "", true, false);
         $query      = "SELECT COUNT($form_table.id)
                        FROM $form_table
                        WHERE $form_table.`is_active` = 1
                        AND $form_table.`is_deleted` = 0
                        AND ($form_table.`language` = '{$_SESSION['glpilanguage']}'
                             OR $form_table.`language` IN ('0', '', NULL))
                        AND $where
                        AND ($form_table.`access_rights` != " . PluginFormcreatorForm::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
                           SELECT plugin_formcreator_forms_id
                           FROM $table_fp
                           WHERE profiles_id = " . $_SESSION['glpiactiveprofile']['id']."))";
         if ($result = $DB->query($query)) {
            list($nb) = $DB->fetch_array($result);
         }
      }

      return $nb;
   }

   /**
    * Export in an array all the data of the current instanciated form
    * @return array the array with all data (with sub tables)
    */
   function export() {
      if (!$this->getID()) {
         return false;
      }

      $form           = $this->fields;
      $form_section   = new PluginFormcreatorSection;
      $form_target    = new PluginFormcreatorTarget;
      $form_validator = new PluginFormcreatorForm_Validator;
      $form_profile   = new PluginFormcreatorForm_Profile;

      // replace dropdown ids
      if ($form['plugin_formcreator_categories_id'] > 0) {
         $form['_plugin_formcreator_category']
            = Dropdown::getDropdownName('glpi_plugin_formcreator_categories',
                                        $form['plugin_formcreator_categories_id']);
      }
      if ($form['entities_id'] > 0) {
         $form['_entity']
            = Dropdown::getDropdownName('glpi_entities',
                                        $form['entities_id']);
      }

      // remove uneeded keys
      unset($form['id'],
            $form['plugin_formcreator_categories_id'],
            $form['entities_id'],
            $form['usage_count']);

      // get sections
      $form['_sections'] = [];
      $all_sections = $form_section->find("plugin_formcreator_forms_id = ".$this->getID());
      foreach($all_sections as $sections_id => $section) {
         $form_section->getFromDB($sections_id);
         $form['_sections'][] = $form_section->export();
      }

      // get validators
      $form['_validators'] = [];
      $all_validators = $form_validator->find("plugin_formcreator_forms_id = ".$this->getID());
      foreach($all_validators as $validators_id => $validator) {
         $form_validator->getFromDB($validators_id);
         $form['_validators'][] = $form_validator->export();
      }

      // get targets
      $form['_targets'] = [];
      $all_target = $form_target->find("plugin_formcreator_forms_id = ".$this->getID());
      foreach($all_target as $targets_id => $target) {
         $form_target->getFromDB($targets_id);
         $form['_targets'][] = $form_target->export();
      }

      // get profiles
      $form['_profiles'] = [];
      $all_profiles = $form_profile->find("plugin_formcreator_forms_id = ".$this->getID());
      foreach($all_profiles as $profiles_id => $profile) {
         $form_profile->getFromDB($profiles_id);
         $form['_profiles'][] = $form_profile->export();
      }

      return $form;
   }

   /**
    * Display an html form to upload a json with forms data
    */
   public function showImportForm() {
      global $CFG_GLPI;

      echo "<form name='form' method='post' action='".
            PluginFormcreatorForm::getFormURL().
            "?import_send=1' enctype=\"multipart/form-data\">";

      echo "<div class='spaced' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe' id='mainformtable'>";
      echo "<tr class='headerRow'>";
      echo "<th>";
      echo __("Import forms");
      echo "</th>";
      echo "</tr>";
      echo "<tr>";
      echo "<td>";
      echo Html::file(array('name' => 'json_file'));
      echo "</td>";
      echo "</tr>";
      echo "<td class='center'>";
      echo Html::submit(_x('button','Send'), array('name' => 'import_send'));
      echo "</td>";
      echo "</tr>";
      echo "<tr>";
      echo "</table>";
      echo "</div>";

      Html::closeForm();
   }

   /**
    * Process import of json file(s) sended by the submit of self::showImportForm
    * @param  array  $params GET/POST data who need to contains the filename(s) in _json_file key
    */
   public function importJson($params = array()) {
      // parse json file(s)
      foreach($params['_json_file'] as $filename) {
         if (!$json = file_get_contents(GLPI_TMP_DIR."/".$filename)) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file is empty"));
            continue;
         }
         if (!$forms_toimport = json_decode($json, true)) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file seems corrupt"));
            continue;
         }
         if (!isset($forms_toimport['forms'])) {
            Session::addMessageAfterRedirect(__("Forms import impossible, the file seems corrupt"));
            continue;
         }

         foreach($forms_toimport['forms'] as $form) {
            self::import($form);
         }

         Session::addMessageAfterRedirect(sprintf(__("Forms successfully imported from %s", "formcreator"),
                                                  $filename));
      }
   }

   /**
    * Import a form into the db
    * @see PluginFormcreatorForm::importJson
    *
    * @param  array   $form the form data (match the form table)
    * @return integer the form's id
    */
   public static function import($form = array()) {
      $form_obj = new self;
      $entity   = new Entity;
      $form_cat = new PluginFormcreatorCategory;

      $item = new self;

      // retrieve foreign keys
      if (!isset($form['_entity'])
          || !$form['entities_id']
                  = plugin_formcreator_getFromDBByField($entity,
                                                        'completename',
                                                        $form['_entity'])) {
         $form['entities_id'] = $_SESSION['glpiactive_entity'];
      }
      if (!isset($form['_plugin_formcreator_categories_id'])
          || !$form['_plugin_formcreator_categories_id']
            = plugin_formcreator_getFromDBByField($form_cat,
                                                  'completename',
                                                  $form['_plugin_formcreator_category'])) {
         $form['plugin_formcreator_categories_id'] = 0;
      }

      // retrieve form by its uuid
      if ($forms_id = plugin_formcreator_getFromDBByField($form_obj,
                                                          'uuid',
                                                          $form['uuid'])) {
         // add id key
         $form['id'] = $forms_id;

         // update existing form
         $form_obj->update($form);
      } else {
         // create new form
         $forms_id = $form_obj->add($form);
      }


      // import form's sections
      if ($forms_id
          && isset($form['_sections'])) {
         foreach($form['_sections'] as $section) {
            PluginFormcreatorSection::import($forms_id, $section);
         }
      }

      // import form's validators
      if ($forms_id
          && isset($form['_validators'])) {
         foreach($form['_validators'] as $validator) {
            PluginFormcreatorForm_Validator::import($forms_id, $validator);
         }
      }

      // import form's targets
      if ($forms_id
          && isset($form['_targets'])) {
         foreach($form['_targets'] as $target) {
            PluginFormcreatorTarget::import($forms_id, $target);
         }
      }

      return $forms_id;
   }
}
