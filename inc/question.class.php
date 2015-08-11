<?php
class PluginFormcreatorQuestion extends CommonDBChild
{
   static public $itemtype = "PluginFormcreatorSection";
   static public $items_id = "plugin_formcreator_sections_id";

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
      return _n('Question', 'Questions', $nb, 'formcreator');
   }


   function addMessageOnAddAction() {}
   function addMessageOnUpdateAction() {}
   function addMessageOnDeleteAction() {}
   function addMessageOnPurgeAction() {}

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
         case "PluginFormcreatorForm":
            $number      = 0;
            $section     = new PluginFormcreatorSection();
            $found     = $section->find('plugin_formcreator_forms_id = ' . $item->getID());
            $tab_section = array();
            foreach($found as $section_item) {
               $tab_section[] = $section_item['id'];
            }

            if(!empty($tab_section)) {
               $object  = new self;
               $found = $object->find('plugin_formcreator_sections_id IN (' . implode(', ', $tab_section) . ')');
               $number  = count($found);
            }
            return self::createTabEntry(self::getTypeName($number), $number);
      }
      return '';
   }

   /**
    * Display a list of all form sections and questions
    *
    * @param  CommonGLPI $item         Instance of a CommonGLPI Item (The Form Item)
    * @param  integer    $tabnum       Number of the current tab
    * @param  integer    $withtemplate
    *
    * @see CommonDBTM::displayTabContentForItem
    *
    * @return null                     Nothing, just display the list
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0)
   {
      echo '<table class="tab_cadre_fixe">';

      // Get sections
      $section          = new PluginFormcreatorSection();
      $found_sections = $section->find('plugin_formcreator_forms_id = ' . $item->getId(), '`order`');
      $section_number   = count($found_sections);
      $tab_sections     = array();
      $tab_questions    = array();
      $token            = Session::getNewCSRFToken();
      foreach ($found_sections as $section) {
         $tab_sections[] = $section['id'];
         echo '<tr id="section_row_' . $section['id'] . '">';
         echo '<th>' . $section['name'] . '</th>';
         echo '<th align="center" width="32">&nbsp;</th>';

         echo '<th align="center" width="32">';
         if($section['order'] != 1) {
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/up2.png"
                     alt="*" title="' . __('Edit') . '"
                     onclick="moveSection(\'' . $token . '\', ' . $section['id'] . ', \'up\');" align="absmiddle" style="cursor: pointer" /> ';
         } else {
            echo '&nbsp;';
         }
         echo '</th>';
         echo '<th align="center" width="32">';
         if($section['order'] != $section_number) {
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/down2.png"
                     alt="*" title="' . __('Edit') . '"
                     onclick="moveSection(\'' . $token . '\', ' . $section['id'] . ', \'down\');" align="absmiddle" style="cursor: pointer" /> ';
         } else {
            echo '&nbsp;';
         }
         echo '</th>';

         echo '<th align="center" width="32">';
         echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/pencil.png"
                  alt="*" title="' . __('Edit') . '"
                  onclick="editSection(' . $item->getId() . ', \'' . $token . '\', ' . $section['id'] . ')" align="absmiddle" style="cursor: pointer" /> ';
         echo '</th>';

         echo '<th align="center" width="32">';
         echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/delete.png"
                  alt="*" title="' . __('Delete', 'formcreator') . '"
                  onclick="deleteSection(' . $item->getId() . ', \'' . $token . '\', ' . $section['id'] . ')"
                  align="absmiddle" style="cursor: pointer" /> ';
         echo '</th>';
         echo '</tr>';


         // Get questions
         $question          = new PluginFormcreatorQuestion();
         $found_questions = $question->find('plugin_formcreator_sections_id = ' . $section['id'], '`order`');
         $question_number   = count($found_questions);
         $i = 0;
         foreach ($found_questions as $question) {
            $i++;
            $tab_questions[] = $question['id'];
            echo '<tr class="line' . ($i % 2) . '" id="question_row_' . $question['id'] . '">';
            echo '<td onclick="editQuestion(' . $item->getId() . ', \'' . $token . '\', ' . $question['id'] . ', ' . $section['id'] . ')" style="cursor: pointer">';
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/ui-' . $question['fieldtype'] . '-field.png" alt="" title="" /> ';
            echo $question['name'];
            echo '</td>';

            echo '<td align="center">';

            $question_type = $question['fieldtype'] . 'Field';


            $question_types = PluginFormcreatorFields::getTypes();
            $classname = $question['fieldtype'] . 'Field';
            $fields = $classname::getPrefs();

            // avoid quote js error
            $question['name'] = htmlspecialchars_decode($question['name'], ENT_QUOTES);

            if ($fields['required'] == 0) {
               echo '&nbsp;';
            } elseif($question['required']) {
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/required.png"
                        alt="*" title="' . __('Required', 'formcreator') . '"
                        onclick="setRequired(\'' . $token . '\', ' . $question['id'] . ', 0)" align="absmiddle" style="cursor: pointer" /> ';
            } else {
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/not-required.png"
                        alt="*" title="' . __('Required', 'formcreator') . '"
                        onclick="setRequired(\'' . $token . '\', ' . $question['id'] . ', 1)" align="absmiddle" style="cursor: pointer" /> ';
            }
            echo '</td>';
            echo '<td align="center">';
            if($question['order'] != 1) {
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/up.png"
                        alt="*" title="' . __('Edit') . '"
                        onclick="moveQuestion(\'' . $token . '\', ' . $question['id'] . ', \'up\');" align="absmiddle" style="cursor: pointer" /> ';
            } else {
               echo '&nbsp;';
            }
            echo '</td>';
            echo '<td align="center">';
            if($question['order'] != $question_number) {
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/down.png"
                        alt="*" title="' . __('Edit') . '"
                        onclick="moveQuestion(\'' . $token . '\', ' . $question['id'] . ', \'down\');" align="absmiddle" style="cursor: pointer" /> ';
            } else {
               echo '&nbsp;';
            }
            echo '</td>';
            echo '<td align="center">';
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/pencil.png"
                     alt="*" title="' . __('Edit') . '"
                     onclick="editQuestion(' . $item->getId() . ', \'' . $token . '\', ' . $question['id'] . ', ' . $section['id'] . ')" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';
            echo '<td align="center">';
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/delete.png"
                     alt="*" title="' . __('Delete', 'formcreator') . '"
                     onclick="deleteQuestion(' . $item->getId() . ', \'' . $token . '\', ' . $question['id'] . ')" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';
            echo '</tr>';
         }


         echo '<tr class="line' . (($i + 1) % 2) . '">';
         echo '<td colspan="6" id="add_question_td_' . $section['id'] . '" class="add_question_tds">';
         echo '<a href="javascript:addQuestion(' . $item->getId() . ', \'' . $token . '\', ' . $section['id'] . ');">
                   <img src="'.$GLOBALS['CFG_GLPI']['root_doc'].'/pics/menu_add.png" alt="+" align="absmiddle" />
                   '.__('Add a question', 'formcreator').'
               </a>';
         echo '</td>';
         echo '</tr>';
      }

      echo '<tr class="line1">';
      echo '<th colspan="6" id="add_section_th">';
      echo '<a href="javascript:addSection(' . $item->getId() . ', \'' . $token . '\');">
                <img src="'.$GLOBALS['CFG_GLPI']['root_doc'].'/pics/menu_add.png" alt="+" align="absmiddle" />
                '.__('Add a section', 'formcreator').'
            </a>';
      echo '</th>';
      echo '</tr>';

      echo "</table>";

      $js_tab_sections   = "";
      $js_tab_questions  = "";
      $js_line_questions = "";
      foreach ($tab_sections as $key) {
         $js_tab_sections  .= "tab_sections[$key] = document.getElementById('section_row_$key').innerHTML;".PHP_EOL;
         $js_tab_questions .= "tab_questions[$key] = document.getElementById('add_question_td_$key').innerHTML;".PHP_EOL;
      }
      foreach ($tab_questions as $key) {
         $js_line_questions .= "line_questions[$key] = document.getElementById('question_row_$key').innerHTML;".PHP_EOL;
      }
   }

   /**
    * Validate form fields before add or update a question
    *
    * @param  Array $input Datas used to add the item
    *
    * @return Array        The modified $input array
    *
    * @param  [type] $input [description]
    * @return [type]        [description]
    */
   private function checkBeforeSave($input)
   {
      // Control fields values :
      // - name is required
      if (empty($input['name'])) {
         Session::addMessageAfterRedirect(__('The title is required', 'formcreator'), false, ERROR);
         return array();
      }

      // - field type is required
      if (empty($input['fieldtype'])) {
         Session::addMessageAfterRedirect(__('The field type is required', 'formcreator'), false, ERROR);
         return array();
      }

      // - section is required
      if (empty($input['plugin_formcreator_sections_id'])) {
         Session::addMessageAfterRedirect(__('The section is required', 'formcreator'), false, ERROR);
         return array();
      }

      // Values are required for GLPI dropdowns, dropdowns, multiple dropdowns, checkboxes, radios, LDAP
      $itemtypes = array('select', 'multiselect', 'checkboxes', 'radios', 'ldap');
      if (empty($input['values']) && in_array($input['fieldtype'], $itemtypes)) {
         Session::addMessageAfterRedirect(
            __('The field value is required:', 'formcreator') . ' ' . $input['name'],
            false,
            ERROR);
         return array();
      }

      // Fields are differents for dropdown lists, so we need to replace these values into the good ones
      if ($input['fieldtype'] == 'dropdown') {
         if (empty($input['dropdown_values'])) {
            Session::addMessageAfterRedirect(
               __('The field value is required:', 'formcreator') . ' ' . $input['name'],
               false,
               ERROR);
            return array();
         }
         $input['values']         = $input['dropdown_values'];
         $input['default_values'] = isset($input['dropdown_default_value']) ? $input['dropdown_default_value'] : '';
      }

      // Fields are differents for GLPI object lists, so we need to replace these values into the good ones
      if ($input['fieldtype'] == 'glpiselect') {
         if (empty($input['glpi_objects'])) {
            Session::addMessageAfterRedirect(
               __('The field value is required:', 'formcreator') . ' ' . $input['name'],
               false,
               ERROR);
            return array();
         }
         $input['values']         = $input['glpi_objects'];
         $input['default_values'] = isset($input['dropdown_default_value']) ? $input['dropdown_default_value'] : '';
      }

      // A description field should have a description
      if (($input['fieldtype'] == 'description') && empty($input['description'])) {
            Session::addMessageAfterRedirect(
               __('A description field should have a description:', 'formcreator') . ' ' . $input['name'],
               false,
               ERROR);
            return array();
      }

      // format values for numbers
      if (($input['fieldtype'] == 'integer') || ($input['fieldtype'] == 'float')) {
         $input['default_values'] = !empty($input['default_values'])
                                       ? (float) str_replace(',', '.', $input['default_values'])
                                       : null;
         $input['range_min']      = !empty($input['range_min'])
                                       ? (float) str_replace(',', '.', $input['range_min'])
                                       : null;
         $input['range_max']      = !empty($input['range_max'])
                                       ? (float) str_replace(',', '.', $input['range_max'])
                                       : null;
      }

      // LDAP fields validation
      if ($input['fieldtype'] == 'ldapselect') {
         // Fields are differents for dropdown lists, so we need to replace these values into the good ones
         if(!empty($input['ldap_auth'])) {

            $config_ldap = new AuthLDAP();
            $config_ldap->getFromDB($input['ldap_auth']);

            if (!empty($input['ldap_attribute'])) {
               $ldap_dropdown = new RuleRightParameter();
               $ldap_dropdown->getFromDB($input['ldap_attribute']);
               $attribute     = array($ldap_dropdown->fields['value']);
            } else {
               $attribute     = array();
            }

            // Set specific error handler too catch LDAP errors
            if (!function_exists('warning_handler')) {
               function warning_handler($errno, $errstr, $errfile, $errline, array $errcontext) {
                  if (0 === error_reporting()) return false;
                  throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
               }
            }
            set_error_handler("warning_handler", E_WARNING);

            try {
               $ds            = $config_ldap->connect();
               ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
               ldap_control_paged_result($ds, 1);
               $sn            = ldap_search($ds, $config_ldap->fields['basedn'], $input['ldap_filter'], $attribute);
               $entries       = ldap_get_entries($ds, $sn);
            } catch(Exception $e) {
               Session::addMessageAfterRedirect(__('Cannot recover LDAP informations!', 'formcreator'), false, ERROR);
            }

            restore_error_handler();

            $input['values'] = json_encode(array(
               'ldap_auth'      => $input['ldap_auth'],
               'ldap_filter'    => $input['ldap_filter'],
               'ldap_attribute' => strtolower($input['ldap_attribute']),
            ));
         }
      }

      // Add leading and trailing regex marker automaticaly
      if (!empty($input['regex'])) {
         if (substr($input['regex'], 0, 1)  != '/')
            if (substr($input['regex'], 0, 1)  != '^')   $input['regex'] = '/^' . $input['regex'];
            else                                         $input['regex'] = '/' . $input['regex'];
         if (substr($input['regex'], -1, 1) != '/')
            if (substr($input['regex'], -1, 1)  != '$')  $input['regex'] = $input['regex'] . '$/';
            else                                         $input['regex'] = $input['regex'] . '/';
      }

      return $input;
   }

   /**
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForAdd($input)
   {
      $input = $this->checkBeforeSave($input);

      // Decode (if already encoded) and encode strings to avoid problems with quotes
      foreach ($input as $key => $value) {
         $input[$key] = plugin_formcreator_encode($value);
      }

      if (!empty($input)) {
         // Get next order
         $obj    = new self();
         $query  = "SELECT MAX(`order`) AS `order`
                    FROM `{$obj->getTable()}`
                    WHERE `plugin_formcreator_sections_id` = {$input['plugin_formcreator_sections_id']}";
         $result = $GLOBALS['DB']->query($query);
         $line   = $GLOBALS['DB']->fetch_array($result);
         $input['order'] = $line['order'] + 1;
      }
      return $input;
   }

   /**
    * Prepare input datas for adding the question
    * Check fields values and get the order for the new question
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
   **/
   public function prepareInputForUpdate($input)
   {
      $input = $this->checkBeforeSave($input);

      // Decode (if already encoded) and encode strings to avoid problems with quotes
      foreach ($input as $key => $value) {
         $input[$key] = plugin_formcreator_encode($value);
      }

      if (!empty($input)) {
         // If change section, reorder questions
         if($input['plugin_formcreator_sections_id'] != $this->fields['plugin_formcreator_sections_id']) {
            // Reorder other questions from the old section
            $query = "UPDATE `{$this->getTable()}` SET
                `order` = `order` - 1
                WHERE `order` > {$this->fields['order']}
                AND plugin_formcreator_sections_id = {$this->fields['plugin_formcreator_sections_id']}";
            $GLOBALS['DB']->query($query);

            // Get the order for the new section
            $obj    = new self();
            $query  = "SELECT MAX(`order`) AS `order`
                       FROM `{$obj->getTable()}`
                       WHERE `plugin_formcreator_sections_id` = {$input['plugin_formcreator_sections_id']}";
            $result = $GLOBALS['DB']->query($query);
            $line   = $GLOBALS['DB']->fetch_array($result);
            $input['order'] = $line['order'] + 1;
         }
      }

      return $input;
   }

   public function updateConditions($input) {
      $query = "DELETE FROM `glpi_plugin_formcreator_questions_conditions`
                WHERE `plugin_formcreator_questions_id` = {$input['id']}";
      $GLOBALS['DB']->query($query);

      // ===============================================================
      // TODO : Mettre en place l'interface multi-conditions
      // Ci-dessous une solution temporaire qui affiche uniquement la 1ere condition
      $show_field = isset($input['show_field']) ? $input['show_field'] : 'NULL';
      $value = plugin_formcreator_encode($input['show_value']);
      $query = "INSERT INTO `glpi_plugin_formcreator_questions_conditions` SET
                  `plugin_formcreator_questions_id` = {$input['id']},
                  `show_field`     = $show_field,
                  `show_condition` = \"{$input['show_condition']}\",
                  `show_value`     = \"{$value}\"";
      $GLOBALS['DB']->query($query);
      // ===============================================================
   }

   /**
    * Actions done after the PURGE of the item in the database
    * Reorder other questions
    *
    * @return nothing
   **/
   public function post_purgeItem()
   {
      $query = "UPDATE `{$this->getTable()}` SET
                `order` = `order` - 1
                WHERE `order` > {$this->fields['order']}
                AND plugin_formcreator_sections_id = {$this->fields['plugin_formcreator_sections_id']}";
      $GLOBALS['DB']->query($query);
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

      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         // Create questions table
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `plugin_formcreator_sections_id` int(11) NOT NULL,
                     `fieldtype` varchar(30) NOT NULL DEFAULT 'text',
                     `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                     `required` boolean NOT NULL DEFAULT FALSE,
                     `show_empty` boolean NOT NULL DEFAULT FALSE,
                     `default_values` text NULL,
                     `values` text NULL,
                     `range_min` varchar(10) NULL DEFAULT NULL,
                     `range_max` varchar(10) NULL DEFAULT NULL,
                     `description` text NOT NULL,
                     `regex` varchar(255) NULL DEFAULT NULL,
                     `order` int(11) NOT NULL DEFAULT '0',
                     `show_rule` enum('always','hidden','shown') NOT NULL DEFAULT 'always'
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci";
         $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());

         // Create questions conditions table (since 0.85-1.1)
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_questions_conditions` (
                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `plugin_formcreator_questions_id` int(11) NOT NULL,
                    `show_field` int(11) DEFAULT NULL,
                    `show_condition` enum('==','!=','<','>','<=','>=') DEFAULT NULL,
                    `show_value` varchar(255) DEFAULT NULL,
                    `show_logic` enum('AND','OR','XOR') DEFAULT NULL
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci";
         $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());

      } else {
         // Migration 0.83-1.0 => 0.85-1.0
         if(!FieldExists($table, 'fieldtype', false)) {
            // Migration from previous version
            $query = "ALTER TABLE `$table`
                      ADD `fieldtype` varchar(30) NOT NULL DEFAULT 'text',
                      ADD `show_type` enum ('show', 'hide') NOT NULL DEFAULT 'show',
                      ADD `show_field` int(11) DEFAULT NULL,
                      ADD `show_condition` enum('equal','notequal','lower','greater') COLLATE utf8_unicode_ci DEFAULT NULL,
                      ADD `show_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                      ADD `required` tinyint(1) NOT NULL DEFAULT '0',
                      ADD `show_empty` tinyint(1) NOT NULL DEFAULT '0',
                      ADD `default_values` text COLLATE utf8_unicode_ci,
                      ADD `values` text COLLATE utf8_unicode_ci,
                      ADD `range_min` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
                      ADD `range_max` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
                      ADD `regex` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                      CHANGE `content` `description` text COLLATE utf8_unicode_ci NOT NULL,
                      CHANGE `position` `order` int(11) NOT NULL DEFAULT '0';";
            $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());

            // order start from 1 instead of 0
            $GLOBALS['DB']->query("UPDATE `$table` SET `order` = `order` + 1;") or die ($GLOBALS['DB']->error());

            // Match new type
            $query  = "SELECT `id`, `type`, `data`, `option`
                       FROM $table";
            $result = $GLOBALS['DB']->query($query);
            while ($line = $GLOBALS['DB']->fetch_array($result)) {
               $datas    = json_decode($line['data']);
               $options  = json_decode($line['option']);

               $fieldtype = 'text';
               $values    = '';
               $default   = '';
               $regex     = '';
               $required  = 0;

               if (isset($datas->value) && !empty($datas->value)) {
                  if(is_object($datas->value)) {
                     foreach($datas->value as $value) {
                        if (!empty($value)) $values .= urldecode($value) . "\r\n";
                     }
                  } else {
                     $values .= urldecode($datas->value);
                  }
               }

               switch ($line['type']) {
                  case '1':
                     $fieldtype = 'text';

                     if (isset($options->type)) {
                        switch ($options->type) {
                           case '2':
                              $required  = 1;
                              break;
                           case '3':
                              $regex = '[[:alpha:]]';
                              break;
                           case '4':
                              $fieldtype = 'float';
                              break;
                           case '5':
                              $regex = urldecode($options->value);
                              // Add leading and trailing regex marker (automaticaly added in V1)
                              if (substr($regex, 0, 1)  != '/') $regex = '/' . $regex;
                              if (substr($regex, -1, 1) != '/') $regex = $regex . '/';
                              break;
                           case '6':
                              $fieldtype = 'email';
                              break;
                           case '7':
                              $fieldtype = 'date';
                              break;
                        }
                     }
                     $default_values = $values;
                     $values = '';
                     break;

                  case '2':
                     $fieldtype = 'select';
                     break;

                  case '3':
                     $fieldtype = 'checkboxes';
                     break;

                  case '4':
                     $fieldtype = 'textarea';
                     if (isset($options->type) && ($options->type == 2)) {
                        $required = 1;
                     }
                     $default_values = $values;
                     $values = '';
                     break;

                  case '5':
                     $fieldtype = 'file';
                     break;

                  case '8':
                     $fieldtype = 'select';
                     break;

                  case '9':
                     $fieldtype = 'select';
                     break;

                  case '10':
                     $fieldtype = 'dropdown';
                     break;

                  default :
                     $data = null;
                     break;
               }

               $query_udate = 'UPDATE ' . $table . ' SET
                                  `fieldtype`      = "' . $fieldtype . '",
                                  `values`         = "' . htmlspecialchars($values) . '",
                                  `default_values` = "' . htmlspecialchars($default) . '",
                                  `regex`          = "' . $regex . '",
                                  `required`       = "' . $required .' "
                               WHERE `id` = ' . $line['id'];
               $GLOBALS['DB']->query($query_udate) or die ($GLOBALS['DB']->error());
            }

            $query = "ALTER TABLE `$table`
                      DROP `type`,
                      DROP `data`,
                      DROP `option`,
                      DROP `plugin_formcreator_forms_id`;";
            $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
         }

         // Migration 0.85-1.0 => 0.85-1.1
         if (FieldExists($table, 'show_type', false)) {

            // Fix type of section ID
            if (!FieldExists('glpi_plugin_formcreator_questions', 'show_rule')) {
               $query = "ALTER TABLE  `glpi_plugin_formcreator_questions`
                         CHANGE `plugin_formcreator_sections_id` `plugin_formcreator_sections_id` INT NOT NULL,
                         ADD `show_rule` enum('always','hidden','shown') NOT NULL DEFAULT 'always'";
               $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
            }

            // Create new table for conditionnal show of questions
            $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_questions_conditions` (
                       `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                       `plugin_formcreator_questions_id` int(11) NOT NULL,
                       `show_field` int(11) DEFAULT NULL,
                       `show_condition` enum('==','!=','<','>','<=','>=') DEFAULT NULL,
                       `show_value` varchar(255) DEFAULT NULL,
                       `show_logic` enum('AND','OR','XOR') DEFAULT NULL
                     )
                     ENGINE = MyISAM
                     DEFAULT CHARACTER SET = utf8
                     COLLATE = utf8_unicode_ci";
            $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());

            // Migrate date from "questions" table to "questions_conditions" table
            $query  = "SELECT `id`, `show_type`, `show_field`, `show_condition`, `show_value`
                       FROM $table";
            $result = $GLOBALS['DB']->query($query);
            while ($line = $GLOBALS['DB']->fetch_array($result)) {
               switch ($line['show_type']) {
                  case 'hide' :
                     $show_rule = 'hidden';
                     break;
                  default:
                     $show_rule = 'always';
               }
               switch ($line['show_condition']) {
                  case 'notequal' :
                     $show_condition = '!=';
                     break;
                  case 'lower' :
                     $show_condition = '<';
                     break;
                  case 'greater' :
                     $show_condition = '>';
                     break;
                  default:
                     $show_condition = '==';
               }

               $line['show_value'] = addslashes($line['show_value']);

               $query_udate = "UPDATE `glpi_plugin_formcreator_questions` SET
                                 `show_rule` = '$show_rule'
                               WHERE `id` = {$line['id']}";
               $GLOBALS['DB']->query($query_udate) or die ($GLOBALS['DB']->error());

               $query_udate = "INSERT INTO `glpi_plugin_formcreator_questions_conditions` SET
                                  `plugin_formcreator_questions_id` = {$line['id']},
                                  `show_field`     = '{$line['show_field']}',
                                  `show_condition` = '{$show_condition}',
                                  `show_value`     = '{$line['show_value']}'";
               $GLOBALS['DB']->query($query_udate) or die ($GLOBALS['DB']->error());
            }

            // Delete old fields
            $query = "ALTER TABLE `$table`
                      DROP `show_type`,
                      DROP `show_field`,
                      DROP `show_condition`,
                      DROP `show_value`;";
            $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
         }

         /**
          * Migration of special chars from previous versions
          *
          * @since 0.85-1.2.3
          */
         // Migrate "questions" table
         $query  = "SELECT `id`, `name`, `values`, `default_values`, `description`
                    FROM `glpi_plugin_formcreator_questions`";
         $result = $GLOBALS['DB']->query($query);
         while ($line = $GLOBALS['DB']->fetch_array($result)) {
            $query_update = 'UPDATE `glpi_plugin_formcreator_questions` SET
                               `name`           = "' . plugin_formcreator_encode($line['name']) . '",
                               `values`         = "' . plugin_formcreator_encode($line['values']) . '",
                               `default_values` = "' . plugin_formcreator_encode($line['default_values']) . '",
                               `description`    = "' . plugin_formcreator_encode($line['description']) . '"
                             WHERE `id` = ' . $line['id'];
            $GLOBALS['DB']->query($query_update) or die ($GLOBALS['DB']->error());
         }

         // Migrate "question_conditions" table
         $query  = "SELECT `id`, `show_value`
                    FROM `glpi_plugin_formcreator_questions_conditions`";
         $result = $GLOBALS['DB']->query($query);
         while ($line = $GLOBALS['DB']->fetch_array($result)) {
            $query_update = 'UPDATE `glpi_plugin_formcreator_questions_conditions` SET
                               `show_value` = "' . plugin_formcreator_encode($line['show_value']) . '"
                             WHERE `id` = ' . $line['id'];
            $GLOBALS['DB']->query($query_update) or die ($GLOBALS['DB']->error());
         }
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
      $obj = new self();
      $GLOBALS['DB']->query('DROP TABLE IF EXISTS `' . $obj->getTable() . '`');

      // Delete logs of the plugin
      $GLOBALS['DB']->query('DELETE FROM `glpi_logs` WHERE itemtype = "' . __CLASS__ . '"');

      return true;
   }
}
