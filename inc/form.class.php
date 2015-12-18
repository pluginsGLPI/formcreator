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
      $image = '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/check.png"
                  title="' . __('Forms waiting for validation', 'formcreator') . '"
                  alt="' . __('Forms waiting for validation', 'formcreator') . '">';
      $menu['links']['search'] = PluginFormcreatorFormList::getSearchURL(false);
      $menu['links']['config'] = PluginFormcreatorForm::getSearchURL(false);
      $menu['links'][$image]   = PluginFormcreatorFormanswer::getSearchURL(false);

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
            'table'         => $this->getTable(),
            'field'         => 'id',
            'name'          => __('ID'),
            'searchtype'    => 'contains',
            'massiveaction' => false,
         ),
         '1' => array(
            'table'         => $this->getTable(),
            'field'         => 'name',
            'name'          => __('Name'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
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
            'name'          => __('Recursive'),
            'datatype'      => 'bool',
            'massiveaction' => false,
         ),
         '7' => array(
            'table'         => $this->getTable(),
            'field'         => 'language',
            'name'          => __('Language'),
            'datatype'      => 'specific',
            'searchtype'    => array('equals'),
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
         '10' => array(
            'table'         => getTableForItemType('PluginFormcreatorCategory'),
            'field'         => 'name',
            'name'          => PluginFormcreatorCategory::getTypeName(1),
            'datatype'      => 'dropdown',
            'massiveaction' => true,

         ),
         '30' => array(
            'table'         => $this->getTable(),
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
      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'is_active':
            if($values[$field] == 0) {
               $output = '<div style="text-align: center"><img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/inactive.png"
                           height="16" width="16"
                           alt="' . __('Inactive') . '"
                           title="' . __('Inactive') . '" /></div>';
            } else {
               $output = '<div style="text-align: center"><img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/active.png"
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
      $query = "SELECT `users_id`
                FROM `glpi_plugin_formcreator_formvalidators`
                WHERE `forms_id` = '" . $this->getID(). "'";
      $result = $GLOBALS['DB']->query($query);
      while(list($id) = $GLOBALS['DB']->fetch_array($result)) {
         $validators[] = $id;
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
      $query = "SELECT u.`id`, u.`name`, u.`realname`, g.`id` AS groups_id, g.`completename` AS groups_name
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
                GROUP BY u.`id`
                ORDER BY u.`name`";
      $result = $GLOBALS['DB']->query($query);
      $groups = array();

      echo '<div id="validators_users" style="width: 100%">';
      echo '<select name="_validator_users[]" size="4" style="width: 100%" multiple id="validator_users">';
      while($user = $GLOBALS['DB']->fetch_assoc($result)) {
         $groups[$user['groups_id']] = $user['groups_name'];
         echo '<option value="' . $user['id'] . '"';
         if (in_array($user['id'], $validators)) echo ' selected="selected"';
         echo '>' . $user['name'] . '</option>';
      }
      echo '</select>';
      echo '</div>';

      // Validators groups
      echo '<div id="validators_groups" style="width: 100%">';
      echo '<select name="_validator_groups[]" size="4" style="width: 100%" multiple id="validator_groups">';
      foreach ($groups AS $groups_id => $groups_name) {
         echo '<option value="' . $groups_id . '"';
         if (in_array($groups_id, $validators)) echo ' selected="selected"';
         echo '>' . $groups_name . '</option>';
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
               }
               changeValidators(' . $this->fields["validation_required"] . ');
            </script>';
      echo '</td>';
      echo '</tr>';

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
      $this->addStandardTab('PluginFormcreatorFormprofiles', $ong, $options);
      $this->addStandardTab('PluginFormcreatorTarget', $ong, $options);
      $this->addStandardTab(__CLASS__, $ong, $options);
      return $ong;
   }

   /**
    * Show the list of forms to be displayed to the end-user
    */
   public function showList()
   {
      echo '<div class="center">';

      // Show header for the current entity or it's first parent header
      $table  = getTableForItemType('PluginFormcreatorHeader');
      $where  = getEntitiesRestrictRequest( "", $table, "", "", true, false);
      $query  = "SELECT $table.`comment`
                 FROM $table
                 WHERE $where";
      $result = $GLOBALS['DB']->query($query);
      if (!empty($result)) {
         list($description) = $GLOBALS['DB']->fetch_array($result);
         if (!empty($description)) {
            echo '<table class="tab_cadre_fixe">';
            echo '<tr><td>' . html_entity_decode($description) . '</td></tr>';
            echo '</table>';
            echo '<br />';
         }
      }

      echo '<div style="width: 950px; margin: 0 auto;">';
      echo '<div style="float:right; width: 375px;">';
         echo '<table class="tab_cadrehov" style="width: 375px">';
            echo '<tr><th colspan="2">' . __('My last forms (requester)', 'formcreator') . '</th></tr>';
            $query = "SELECT fa.`id`, f.`name`, fa.`status`, fa.`request_date`
                      FROM glpi_plugin_formcreator_forms f
                      INNER JOIN glpi_plugin_formcreator_formanswers fa ON f.`id` = fa.`plugin_formcreator_forms_id`
                      WHERE fa.`requester_id` = '" . $_SESSION['glpiID'] . "'
                      AND f.is_deleted = 0
                      ORDER BY fa.`status` ASC, fa.`request_date` DESC
                      LIMIT 0, 5";
            $result = $GLOBALS['DB']->query($query);
            if ($GLOBALS['DB']->numrows($result) == 0) {
               echo '<tr><td colspan="2" class="line1" align="center">' . __('No form posted yet', 'formcreator') . '</td></tr>';
            } else {
               while ($form = $GLOBALS['DB']->fetch_assoc($result)) {
                  $img_dir = $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/';
                  $img = '<img src="' . $img_dir . '' . $form['status'] . '.png" align="absmiddle"
                              alt="' . __($form['status'], 'formcreator') . '"
                              title="' . __($form['status'], 'formcreator') . '" />';
                  echo '<tr>';
                  echo '<td>' . $img . ' <a href="formanswer.form.php?id=' . $form['id'] . '">' . $form['name'] . '</a></td>';
                  echo '<td align="center" width="35%">' . Html::convDateTime($form['request_date']) . '</td>';
                  echo '</tr>';
               }
               echo '<tr>';
               echo '<th colspan="2" align="center">';
               echo '<a href="formanswer.php?criteria[0][field]=4&criteria[0][searchtype]=equals&criteria[0][value]=2">';
               echo __('All my forms (requester)', 'formcreator');
               echo '</a>';
               echo '</th>';
               echo '</tr>';
            }
         echo '</table>';

         echo '<br />';

         if (Session::haveRight('ticketvalidation', TicketValidation::VALIDATEINCIDENT)
            || Session::haveRight('ticketvalidation', TicketValidation::VALIDATEREQUEST)) {
            echo '<table class="tab_cadrehov" style="width: 375px">';
            echo '<tr><th colspan="2">' . __('My last forms (validator)', 'formcreator') . '</t></tr>';
            $query = "SELECT fa.`id`, f.`name`, fa.`status`, fa.`request_date`
                      FROM glpi_plugin_formcreator_forms f
                      INNER JOIN glpi_plugin_formcreator_formanswers fa ON f.`id` = fa.`plugin_formcreator_forms_id`
                      WHERE fa.`validator_id` = '" . $_SESSION['glpiID'] . "'
                      AND f.is_deleted = 0
                      ORDER BY fa.`status` ASC, fa.`request_date` DESC
                      LIMIT 0, 5";
            $result = $GLOBALS['DB']->query($query);
            if ($GLOBALS['DB']->numrows($result) == 0) {
               echo '<tr><td colspan="2" class="line1" align="center">' . __('No form waiting for validation', 'formcreator') . '</td></tr>';
            } else {
               while ($form = $GLOBALS['DB']->fetch_assoc($result)) {
                  $img_dir = $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/';
                  $img = '<img src="' . $img_dir . '' . $form['status'] . '.png" align="absmiddle"
                              alt="' . __($form['status'], 'formcreator') . '"
                              title="' . __($form['status'], 'formcreator') . '" />';
                  echo '<tr>';
                  echo '<td>' . $img . ' <a href="formanswer.form.php?id=' . $form['id'] . '">' . $form['name'] . '</a></td>';
                  echo '<td align="center" width="35%">' . Html::convDateTime($form['request_date']) . '</td>';
                  echo '</tr>';
               }
               echo '<tr>';
               echo '<th colspan="2" align="center">';
               echo '<a href="formanswer.php?criteria[0][field]=5&criteria[0][searchtype]=equals&criteria[0][value]=2">';
               echo __('All my forms (validator)', 'formcreator');
               echo '</a>';
               echo '</th>';
               echo '</tr>';
            }
            echo '</table>';
         }

      echo '</div>';
      echo '<div style="width: 550px; float: left;">';

      // Define tables
      $cat_table  = getTableForItemType('PluginFormcreatorCategory');
      $form_table = getTableForItemType('PluginFormcreatorForm');
      $table_fp   = getTableForItemType('PluginFormcreatorFormprofiles');
      $where      = getEntitiesRestrictRequest( "", $form_table, "", "", true, false);

      // Show form without category
            $where       = getEntitiesRestrictRequest( "", $form_table, "", "", true, false);
            $table_fp    = getTableForItemType('PluginFormcreatorFormprofiles');
            $query_forms = "SELECT $form_table.id, $form_table.name, $form_table.description
                            FROM $form_table
                            WHERE $form_table.`plugin_formcreator_categories_id` = 0
                            AND $form_table.`is_active` = 1
                            AND $form_table.`is_deleted` = 0
                            AND ($form_table.`language` = '{$_SESSION['glpilanguage']}' OR $form_table.`language` = '')
                            AND $where
                            AND (`access_rights` != " . self::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
                               SELECT plugin_formcreator_forms_id
                               FROM $table_fp
                               WHERE plugin_formcreator_profiles_id = " . (int) $_SESSION['glpiactiveprofile']['id'] . "))
                            ORDER BY $form_table.name ASC";
            $result_forms = $GLOBALS['DB']->query($query_forms);
            $i = 0;
            if ($GLOBALS['DB']->numrows($result_forms) > 0) {
               echo '<table class="tab_cadrehov" style="width: 550px">';
               echo '<tr><th>' . __('Forms without category', 'formcreator') . '</th></tr>';
               while ($form = $GLOBALS['DB']->fetch_array($result_forms)) {
                  $i++;
                  echo '<tr class="line' . ($i % 2) . '">';
                  echo '<td>';
                  echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/pics/plus.png" alt="+" title=""
                            onclick="showDescription(' . $form['id'] . ', this)" align="absmiddle" style="cursor: pointer">';
                  echo '&nbsp;';
                  echo '<a href="' . $GLOBALS['CFG_GLPI']['root_doc']
                           . '/plugins/formcreator/front/formdisplay.php?id=' . $form['id'] . '"
                           title="' . plugin_formcreator_encode($form['description']) . '">'
                           . $form['name']
                           . '</a></td>';
                  echo '</tr>';
                  echo '<tr id="desc' . $form['id'] . '" class="line' . ($i % 2) . ' form_description">';
                  echo '<td><div>' . $form['description'] . '&nbsp;</div></td>';
                  echo '</tr>';
               }

            echo '</table>';
            echo '<br />';
         }

      // Show categories wicth have at least one form user can access
      $query  = "SELECT $cat_table.`name`, $cat_table.`id`
                 FROM $cat_table
                 WHERE 0 < (
                     SELECT COUNT($form_table.id)
                     FROM $form_table
                     WHERE $form_table.`plugin_formcreator_categories_id` = $cat_table.`id`
                     AND $form_table.`is_active` = 1
                     AND $form_table.`is_deleted` = 0
                     AND ($form_table.`language` = '{$_SESSION['glpilanguage']}' OR $form_table.`language` = '')
                     AND $where
                     AND ($form_table.`access_rights` != " . self::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
                        SELECT plugin_formcreator_forms_id
                        FROM $table_fp
                        WHERE plugin_formcreator_profiles_id = " . (int) $_SESSION['glpiactiveprofile']['id'] . "))
                  )
                 ORDER BY $cat_table.`name` ASC";
      $result = $GLOBALS['DB']->query($query);
      if (!empty($result)) {

         // For each categories, show the list of forms the user can fill
         while ($category = $GLOBALS['DB']->fetch_array($result)) {
         echo '<table class="tab_cadrehov" style="width: 550px">';
            echo '<tr><th>' . $category['name'] . '</th></tr>';

            $where       = getEntitiesRestrictRequest( "", $form_table, "", "", true, false);
            $table_fp    = getTableForItemType('PluginFormcreatorFormprofiles');
            $query_forms = "SELECT $form_table.id, $form_table.name, $form_table.description
                            FROM $form_table
                            WHERE $form_table.`plugin_formcreator_categories_id` = {$category['id']}
                            AND $form_table.`is_active` = 1
                            AND $form_table.`is_deleted` = 0
                            AND ($form_table.`language` = '{$_SESSION['glpilanguage']}' OR $form_table.`language` = '')
                            AND $where
                            AND (`access_rights` != " . self::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
                               SELECT plugin_formcreator_forms_id
                               FROM $table_fp
                               WHERE plugin_formcreator_profiles_id = " . (int) $_SESSION['glpiactiveprofile']['id'] . "))
                            ORDER BY $form_table.name ASC";
            $result_forms = $GLOBALS['DB']->query($query_forms);
            $i = 0;
            while ($form = $GLOBALS['DB']->fetch_array($result_forms)) {
               $i++;
               echo '<tr class="line' . ($i % 2) . '">';
               echo '<td>';
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/pics/plus.png" alt="+" title=""
                         onclick="showDescription(' . $form['id'] . ', this)" align="absmiddle" style="cursor: pointer">';
               echo '&nbsp;';
               echo '<a href="' . $GLOBALS['CFG_GLPI']['root_doc']
                        . '/plugins/formcreator/front/formdisplay.php?id=' . $form['id'] . '"
                        title="' . plugin_formcreator_encode($form['description']) . '">'
                        . $form['name']
                        . '</a></td>';
               echo '</tr>';
               echo '<tr id="desc' . $form['id'] . '" class="line' . ($i % 2) . ' form_description">';
               echo '<td><div>' . $form['description'] . '&nbsp;</div></td>';
               echo '</tr>';
            }

         echo '</table>';
         echo '<br />';
         }
      }
      echo '</div>';

      echo '</div>';
      echo '</div>';
      echo '<hr style="clear:both; height:0; background: transparent; border:none" />';
      echo '<script type="text/javascript">
               function showDescription(id, img){
                  if(img.alt == "+") {
                    img.alt = "-";
                    img.src = "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/pics/moins.png";
                    document.getElementById("desc" + id).style.display = "table-row";
                  } else {
                    img.alt = "+";
                    img.src = "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/pics/plus.png";
                    document.getElementById("desc" + id).style.display = "none";
                  }
               }
            </script>';
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
      if(isset($_SESSION['formcreator']['datas'])) {
         $datas = $_SESSION['formcreator']['datas'];
         unset($_SESSION['formcreator']['datas']);
      } else {
         $datas = null;
      }

      echo '<form name="formcreator_form' . $item->getID() . '" method="post" role="form" enctype="multipart/form-data"
               action="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/form.form.php"
               class="formcreator_form form_horizontal" onsubmit="return validateForm(this);">';
      echo '<h1 class="form-title">' . $item->fields['name'] . '</h1>';

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
         $validators = array(0 => Dropdown::EMPTY_VALUE);

         // Groups
         if ($item->fields['validation_required'] == 2) {
            $query = 'SELECT g.`id`, g.`completename`
                      FROM `glpi_groups` g
                      LEFT JOIN `glpi_plugin_formcreator_formvalidators` fv ON fv.`users_id` = g.`id`
                      WHERE fv.`forms_id` = "' . $this->getID(). '"';
            $result = $GLOBALS['DB']->query($query);
            while($validator = $GLOBALS['DB']->fetch_assoc($result)) {
               $validators[$validator['id']] = $validator['completename'];
            }

         // Users
         } else {
            $query = 'SELECT u.`id`, u.`name`, u.`realname`, u.`firstname`
                      FROM `glpi_users` u
                      LEFT JOIN `glpi_plugin_formcreator_formvalidators` fv ON fv.`users_id` = u.`id`
                      WHERE fv.`forms_id` = "' . $this->getID(). '"';
            $result = $GLOBALS['DB']->query($query);
            while($validator = $GLOBALS['DB']->fetch_assoc($result)) {
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

      // Control fields values :
      // - name is required
      if(empty($input['name'])) {
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
      // Save form validators
      $query = 'DELETE FROM `glpi_plugin_formcreator_formvalidators` WHERE `forms_id` = "' . $this->getID() . '"';
      $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
      if ( (($this->fields['validation_required'] == 1) && (!empty($this->input['_validator_users'])))
            || (($this->fields['validation_required'] == 2) && (!empty($this->input['_validator_groups']))) ) {

         $validators = ($this->fields['validation_required'] == 1)
                        ? $this->input['_validator_users']
                        : $this->input['_validator_groups'];

         foreach ($validators as $user) {
            $query = 'INSERT INTO `glpi_plugin_formcreator_formvalidators` SET
                      `forms_id` = "' . $this->getID() . '",
                      `users_id` = "' . $user . '"';
            $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
         }
      }

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
      if (isset($input['access_rights']) || isset($_POST['massiveaction'])) {
         return $input;
      } else {
         // Save form validators
         $query = 'DELETE FROM `glpi_plugin_formcreator_formvalidators` WHERE `forms_id` = "' . $this->getID() . '"';
         $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());


         if ( (($this->fields['validation_required'] == 1) && (!empty($this->input['_validator_users'])))
               || (($this->fields['validation_required'] == 2) && (!empty($this->input['_validator_groups']))) ) {

            $validators = ($this->fields['validation_required'] == 1)
                           ? $this->input['_validator_users']
                           : $this->input['_validator_groups'];
            foreach ($validators as $user) {
               $query = 'INSERT INTO `glpi_plugin_formcreator_formvalidators` SET
                         `forms_id` = "' . $this->getID() . '",
                         `users_id` = "' . $user . '"';
               $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
            }
         }

         return $this->prepareInputForAdd($input);
      }
   }

   public function saveForm()
   {
      $valid = true;

      $tab_section       = array();
      $sections          = new PluginFormcreatorSection();
      $found_sections  = $sections->find('`plugin_formcreator_forms_id` = ' . $this->getID());
      foreach ($found_sections as $id => $fields) $tab_section[] = $id;

      $questions         = new PluginFormcreatorQuestion();
      $found_questions = $questions->find('`plugin_formcreator_sections_id` IN (' . implode(',', $tab_section) .')');

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

         $className = $fields['fieldtype'] . 'Field';
         $filePath  = dirname(__FILE__) . '/fields/' . $fields['fieldtype'] . '-field.class.php';

         if(is_file($filePath)) {
            include_once ($filePath);
            if (class_exists($className)) {
               $obj = new $className($fields, $datas);
               if (!$obj->isValid($datas[$id])) {
                  $valid = false;
               }
            }
         } else {
            $valid = false;
         }
      }
      $datas = $datas + $_POST;

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
         Html::back();

      // Save form
      } else {
         $formanswer = new PluginFormcreatorFormanswer();
         $formanswer->saveAnswers($datas);
      }
   }

   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean True on success
    */
   public static function install(Migration $migration)
   {
      $obj   = new self();
      $table = $obj->getTable();

      // Create default request type
      $query  = "SELECT id FROM `glpi_requesttypes` WHERE `name` LIKE 'Formcreator';";
      $result = $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
      if ($GLOBALS['DB']->numrows($result) > 0) {
         list($requesttype) = $GLOBALS['DB']->fetch_array($result);
      } else {
         $query = "INSERT INTO `glpi_requesttypes` SET `name` = 'Formcreator';";
         $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
         $requesttype = $GLOBALS['DB']->insert_id();
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
                     `plugin_formcreator_categories_id` tinyint(3) UNSIGNED NOT NULL,
                     `is_active` tinyint(1) NOT NULL DEFAULT '0',
                     `language` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
                     `helpdesk_home` tinyint(1) NOT NULL DEFAULT '0',
                     `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
                     `validation_required` tinyint(1) NOT NULL DEFAULT '0'
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci;";
         $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
      } else {
         // Migration from previous version
         if (FieldExists($table, 'cat', false)) {
            $query = "ALTER TABLE `$table`
                      CHANGE `cat` `plugin_formcreator_categories_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '0';";
            $GLOBALS['DB']->query($query);
         }

         // Migration from previous version
         if (!FieldExists($table, 'validation_required', false)) {
            $query = "ALTER TABLE `$table`
                      ADD `validation_required` tinyint(1) NOT NULL DEFAULT '0';";
            $GLOBALS['DB']->query($query);
         }

         // Migration from previous version
         if (!FieldExists($table, 'plugin_formcreator_categories_id', false)) {
            $query = "ALTER TABLE `$table`
                      ADD `plugin_formcreator_categories_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '1';";
            $GLOBALS['DB']->query($query);
         }

         // Migration from previous version
         if (!FieldExists($table, 'requesttype', false)) {
            $query = "ALTER TABLE `$table`
                      ADD `access_rights` tinyint(1) NOT NULL DEFAULT '1',
                      ADD `requesttype` int(11) NOT NULL DEFAULT '$requesttype',
                      ADD `description` varchar(255) COLLATE utf8_unicode_ci,
                      ADD `helpdesk_home` tinyint(1) NOT NULL DEFAULT '0',
                      ADD `is_deleted` tinyint(1) NOT NULL DEFAULT '0';";
            $GLOBALS['DB']->query($query);
         }

         /**
          * Migration of special chars from previous versions
          *
          * @since 0.85-1.2.3
          */
         $query  = "SELECT `id`, `name`, `description`, `content`
                    FROM `$table`";
         $result = $GLOBALS['DB']->query($query);
         while ($line = $GLOBALS['DB']->fetch_array($result)) {
            $query_update = 'UPDATE `' . $table . '` SET
                               `name`        = "' . plugin_formcreator_encode($line['name']) . '",
                               `description` = "' . plugin_formcreator_encode($line['description']) . '",
                               `content`     = "' . plugin_formcreator_encode($line['content']) . '"
                             WHERE `id` = ' . $line['id'];
            $GLOBALS['DB']->query($query_update) or die ($GLOBALS['DB']->error());
         }
      }

      if (!TableExists('glpi_plugin_formcreator_formvalidators')) {
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_formvalidators` (
                     `forms_id` int(11) NOT NULL,
                     `users_id` int(11) NOT NULL,
                     PRIMARY KEY (`forms_id`, `users_id`)
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci;";
         $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
      }

      // Create standard search options
      $query = 'DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = "PluginFormcreatorForm"';
      $GLOBALS['DB']->query($query) or die("error deleting glpi_displaypreferences ". $GLOBALS['DB']->error());

      $query = "INSERT IGNORE INTO `glpi_displaypreferences` (`id`, `itemtype`, `num`, `rank`, `users_id`) VALUES
               (NULL, '" . __CLASS__ . "', 30, 1, 0),
               (NULL, '" . __CLASS__ . "', 3, 2, 0),
               (NULL, '" . __CLASS__ . "', 10, 3, 0),
               (NULL, '" . __CLASS__ . "', 7, 4, 0),
               (NULL, '" . __CLASS__ . "', 8, 5, 0),
               (NULL, '" . __CLASS__ . "', 9, 6, 0);";
      $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());

      return true;
   }

   /**
    * Database table uninstallation for the item type
    *
    * @return boolean True on success
    */
   public static function uninstall()
   {
      $obj = new self();
      $GLOBALS['DB']->query('DROP TABLE IF EXISTS `'.$obj->getTable().'`');

      // Delete logs of the plugin
      $GLOBALS['DB']->query('DELETE FROM `glpi_logs` WHERE itemtype = "' . __CLASS__ . '"');

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
      $section       = new PluginFormcreatorSection();
      $question      = new PluginFormcreatorQuestion();
      $target        = new PluginFormcreatorTarget();
      $target_ticket = new PluginFormcreatorTargetTicket();
      $tab_questions = array();

      // From datas
      $form_datas              = $this->fields;
      $form_datas['name']     .= ' [' . __('Duplicate', 'formcreator') . ']';
      $form_datas['is_active'] = 0;

      unset($form_datas['id']);

      $old_form_id             = $this->getID();
      $new_form_id             = $this->add($form_datas);
      if ($new_form_id === false) return false;

      // Form profiles
      $query = "INSERT INTO glpi_plugin_formcreator_formprofiles
                (plugin_formcreator_forms_id, plugin_formcreator_profiles_id)
                (SELECT $new_form_id, plugin_formcreator_profiles_id
                  FROM glpi_plugin_formcreator_formprofiles
                  WHERE plugin_formcreator_forms_id = $old_form_id)";
      if (!$GLOBALS['DB']->query($query)) return false;

      // Form validators
      $query = "INSERT INTO glpi_plugin_formcreator_formvalidators
                (forms_id, users_id)
                (SELECT $new_form_id, users_id
                  FROM glpi_plugin_formcreator_formvalidators
                  WHERE forms_id = $old_form_id)";
      if (!$GLOBALS['DB']->query($query)) return false;

      // Form sections
      $query_sections = "SELECT `id`, `plugin_formcreator_forms_id`, `name`, `order`
                         FROM glpi_plugin_formcreator_sections
                         WHERE plugin_formcreator_forms_id = $old_form_id";
      $result_sections = $GLOBALS['DB']->query($query_sections);
      if (!$result_sections) return false;

      while ($section_values = $GLOBALS['DB']->fetch_array($result_sections)) {
         $section_id = $section_values['id'];

         $insert_section = "INSERT INTO `glpi_plugin_formcreator_sections` SET
                      `plugin_formcreator_forms_id` = $new_form_id,
                      `name`                        = \"{$section_values['name']}\",
                      `order`                       = {$section_values['order']}";
         $GLOBALS['DB']->query($insert_section);
         $new_section_id = $GLOBALS['DB']->insert_id();
         if ($new_section_id === false) return false;

         // Form questions
         $query_questions = "SELECT `id`, `plugin_formcreator_sections_id`, `fieldtype`, `name`, `required`,
                              `show_empty`, `default_values`, `values`, `range_min`, `range_max`,
                              `description`, `regex`, `order`, `show_rule`
                            FROM glpi_plugin_formcreator_questions
                            WHERE plugin_formcreator_sections_id = $section_id";
         $result_questions = $GLOBALS['DB']->query($query_questions);
         if (!$result_questions) return false;

         while ($question_values = $GLOBALS['DB']->fetch_array($result_questions)) {
            $question_id = $question_values['id'];

            $insert_question = 'INSERT INTO `glpi_plugin_formcreator_questions` SET
                         `plugin_formcreator_sections_id` = ' . (int) $new_section_id . ',
                         `fieldtype`                      = "' . addslashes($question_values['fieldtype']) . '",
                         `name`                           = "' . addslashes($question_values['name']) . '",
                         `required`                       = ' . (int) $question_values['required'] . ',
                         `show_empty`                     = ' . (int) $question_values['show_empty'] . ',
                         `default_values`                 = "' . addslashes($question_values['default_values']) . '",
                         `values`                         = "' . addslashes($question_values['values']) . '",
                         `range_min`                      = "' . addslashes($question_values['range_min']) . '",
                         `range_max`                      = "' . addslashes($question_values['range_max']) . '",
                         `description`                    = "' . addslashes($question_values['description']) . '",
                         `regex`                          = "' . addslashes($question_values['regex']) . '",
                         `order`                          = ' . (int) $question_values['order'] . ',
                         `show_rule`                      = "' . addslashes($question_values['show_rule']) . '"';
            $GLOBALS['DB']->query($insert_question);
            $new_question_id = $GLOBALS['DB']->insert_id();
            if ($new_question_id === false) return false;
            $tab_questions[$question_id] = $new_question_id;

            // Form questions conditions
            $insert_condition = "INSERT INTO glpi_plugin_formcreator_questions_conditions
                      (plugin_formcreator_questions_id, show_field, show_condition, show_value, show_logic)
                      (SELECT $new_question_id, show_field, show_condition, show_value, show_logic
                        FROM glpi_plugin_formcreator_questions_conditions
                        WHERE plugin_formcreator_questions_id = $question_id)";
            if (!$GLOBALS['DB']->query($insert_condition)) return false;
         }
      }

      // Form targets
      $query_targets = "SELECT `id`, `plugin_formcreator_forms_id`, `itemtype`, `items_id`, `name`
                         FROM glpi_plugin_formcreator_targets
                         WHERE plugin_formcreator_forms_id = $old_form_id";
      $result_targets = $GLOBALS['DB']->query($query_targets);
      if (!$result_targets) return false;

      while ($target_values = $GLOBALS['DB']->fetch_array($result_targets)) {
         $target_id = $target_values['id'];

         $insert_target = 'INSERT INTO `glpi_plugin_formcreator_targets` SET
                            `plugin_formcreator_forms_id` = ' . (int) $new_form_id . ',
                            `itemtype`                    = "' . addslashes($target_values['itemtype']) . '",
                            `items_id`                    = ' . (int) $target_values['items_id'] . ',
                            `name`                        = "' . addslashes($target_values['name']) . '"';
         $GLOBALS['DB']->query($insert_target);
         $new_target_id = $GLOBALS['DB']->insert_id();
         if ($new_target_id === false) return false;

         $query_ttickets = "SELECT `id`, `name`, `tickettemplates_id`, `comment`, `due_date_rule`,
                               `due_date_question`, `due_date_value`, `due_date_period`
                            FROM `glpi_plugin_formcreator_targettickets`
                            WHERE `id` = {$target_values['items_id']}";
         $result_ttickets = $GLOBALS['DB']->query($query_ttickets);
         $result_ttickets = $GLOBALS['DB']->fetch_array($result_ttickets);
         if (!$result_ttickets) return false;

         foreach ($tab_questions as $id => $value) {
            $result_ttickets['name']    = str_replace('##question_' . $id . '##', '##question_' . $value . '##', $result_ttickets['name']);
            $result_ttickets['name']    = str_replace('##answer_' . $id . '##', '##answer_' . $value . '##', $result_ttickets['name']);
            $result_ttickets['comment'] = str_replace('##question_' . $id . '##', '##question_' . $value . '##', $result_ttickets['comment']);
            $result_ttickets['comment'] = str_replace('##answer_' . $id . '##', '##answer_' . $value . '##', $result_ttickets['comment']);
         }

         $insert_ttickets = 'INSERT INTO `glpi_plugin_formcreator_targettickets` SET
                               `name`               = "' . addslashes($result_ttickets['name']) . '",
                               `tickettemplates_id` = ' . (int) $result_ttickets['tickettemplates_id'] . ',
                               `comment`            = "' . addslashes($result_ttickets['comment']) . '",
                               `due_date_rule`      = "' . addslashes($result_ttickets['due_date_rule']) . '",
                               `due_date_question`  = ' . (int) $result_ttickets['due_date_question'] . ',
                               `due_date_value`     = ' . (int) $result_ttickets['due_date_value'] . ',
                               `due_date_period`    = "' . addslashes($result_ttickets['due_date_period']) . '"';
         $GLOBALS['DB']->query($insert_ttickets);
         $new_target_ticket_id = $GLOBALS['DB']->insert_id();
         if (!$new_target_ticket_id) return false;

         $update_target = 'UPDATE `glpi_plugin_formcreator_targets` SET
                              `items_id` = ' . $new_target_ticket_id . '
                           WHERE `id` = ' . $new_target_id;
         $GLOBALS['DB']->query($update_target);

         // Form target tickets actors
         $query = "INSERT INTO glpi_plugin_formcreator_targettickets_actors
                   (plugin_formcreator_targettickets_id, actor_role, actor_type, actor_value, use_notification)
                   (SELECT $new_target_ticket_id, actor_role, actor_type, actor_value, use_notification
                     FROM glpi_plugin_formcreator_targettickets_actors
                     WHERE plugin_formcreator_targettickets_id = {$target_values['items_id']})";
         $GLOBALS['DB']->query($query);
      }

      return true;
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
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

}
