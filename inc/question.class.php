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
            $founded     = $section->find('plugin_formcreator_forms_id = ' . $item->getID());
            $tab_section = array();
            foreach($founded as $section_item) {
               $tab_section[] = $section_item['id'];
            }

            if(!empty($tab_section)) {
               $object  = new self;
               $founded = $object->find('plugin_formcreator_sections_id IN (' . implode(', ', $tab_section) . ')');
               $number  = count($founded);
            }
            return self::createTabEntry(self::getTypeName($number), $number);
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
      echo '<table class="tab_cadre_fixe">';

      // Get sections
      $section          = new PluginFormcreatorSection();
      $founded_sections = $section->find('plugin_formcreator_forms_id = ' . $item->getId(), '`order`');
      $section_number   = count($founded_sections);
      $tab_sections     = array();
      $tab_questions    = array();
      foreach ($founded_sections as $section) {
         $tab_sections[] = $section['id'];
         echo '<tr id="section_row_' . $section['id'] . '">';
         echo '<th>' . $section['name'] . '</th>';
         echo '<th align="center" width="32">&nbsp;</th>';

         echo '<th align="center" width="32">';
         if($section['order'] != 1) {
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/up2.png"
                     alt="*" title="' . __('Edit', 'formcreator') . '"
                     onclick="moveSection(' . $section['id'] . ', \'up\');" align="absmiddle" style="cursor: pointer" /> ';
         } else {
            echo '&nbsp;';
         }
         echo '</th>';
         echo '<th align="center" width="32">';
         if($section['order'] != $section_number) {
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/down2.png"
                     alt="*" title="' . __('Edit', 'formcreator') . '"
                     onclick="moveSection(' . $section['id'] . ', \'down\');" align="absmiddle" style="cursor: pointer" /> ';
         } else {
            echo '&nbsp;';
         }
         echo '</th>';

         echo '<th align="center" width="32">';
         echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/pencil.png"
                  alt="*" title="' . __('Edit', 'formcreator') . '"
                  onclick="editSection(' . $section['id'] . ')" align="absmiddle" style="cursor: pointer" /> ';
         echo '</th>';

         echo '<th align="center" width="32">';
         echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/delete.png"
                  alt="*" title="' . __('Delete', 'formcreator') . '"
                  onclick="deleteSection(' . $section['id'] . ', \'' . addslashes($section['name']) . '\')"
                  align="absmiddle" style="cursor: pointer" /> ';
         echo '</th>';
         echo '</tr>';


         // Get questions
         $question          = new PluginFormcreatorQuestion();
         $founded_questions = $question->find('plugin_formcreator_sections_id = ' . $section['id'], '`order`');
         $question_number   = count($founded_questions);
         $i = 0;
         foreach ($founded_questions as $question) {
            $i++;
            $tab_questions[] = $question['id'];
            echo '<tr class="line' . ($i % 2) . '" id="question_row_' . $question['id'] . '">';
            echo '<td onclick="editQuestion(' . $question['id'] . ', ' . $section['id'] . ')" style="cursor: pointer">';
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/ui-' . $question['fieldtype'] . '-field.png" alt="" title="" /> ';
            echo $question['name'];
            echo '</td>';

            echo '<td align="center">';
            if($question['required']) {
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/required.png"
                        alt="*" title="' . __('Required', 'formcreator') . '"
                        onclick="setRequired(' . $question['id'] . ', 0)" align="absmiddle" style="cursor: pointer" /> ';
            } else {
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/not-required.png"
                        alt="*" title="' . __('Required', 'formcreator') . '"
                        onclick="setRequired(' . $question['id'] . ', 1)" align="absmiddle" style="cursor: pointer" /> ';
            }
            echo '</td>';
            echo '<td align="center">';
            if($question['order'] != 1) {
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/up.png"
                        alt="*" title="' . __('Edit', 'formcreator') . '"
                        onclick="moveQuestion(' . $question['id'] . ', \'up\');" align="absmiddle" style="cursor: pointer" /> ';
            } else {
               echo '&nbsp;';
            }
            echo '</td>';
            echo '<td align="center">';
            if($question['order'] != $question_number) {
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/down.png"
                        alt="*" title="' . __('Edit', 'formcreator') . '"
                        onclick="moveQuestion(' . $question['id'] . ', \'down\');" align="absmiddle" style="cursor: pointer" /> ';
            } else {
               echo '&nbsp;';
            }
            echo '</td>';
            echo '<td align="center">';
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/pencil.png"
                     alt="*" title="' . __('Edit', 'formcreator') . '"
                     onclick="editQuestion(' . $question['id'] . ', ' . $section['id'] . ')" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';
            echo '<td align="center">';
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/delete.png"
                     alt="*" title="' . __('Delete', 'formcreator') . '"
                     onclick="deleteQuestion(' . $question['id'] . ', \'' . addslashes($question['name']) . '\')" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';
            echo '</tr>';
         }


         echo '<tr class="line' . (($i + 1) % 2) . '">';
         echo '<td colspan="6" id="add_question_td_' . $section['id'] . '" class="add_question_tds">';
         echo '<a href="javascript:addQuestion(' . $section['id'] . ');">
                   <img src="'.$GLOBALS['CFG_GLPI']['root_doc'].'/pics/menu_add.png" alt="+" align="absmiddle" />
                   '.__('Add a question', 'formcreator').'
               </a>';
         echo '</td>';
         echo '</tr>';
      }

      echo '<tr class="line1">';
      echo '<th colspan="6" id="add_section_th">';
      echo '<a href="javascript:addSection();" class="submit">
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

      echo '<script type="text/javascript">
               // === QUESTIONS ===
               var tab_questions = [];
               ' . $js_tab_questions . '
               var line_questions = [];
               ' . $js_line_questions . '

               function addQuestion(section) {
                  resetAll();
                  Ext.get("add_question_td_" + section).load({
                     url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/ajax/question.php",
                     scripts: true,
                     params: "section_id=" + section + "&form_id=" + ' . $item->getId() . '
                  });
               }

               function editQuestion(question, section) {
                  resetAll();
                  document.getElementById("question_row_" + question).innerHTML = "<td colspan=\"6\"></td>";
                  Ext.get("question_row_" + question + "").child("td").load({
                     url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/ajax/question.php",
                     scripts: true,
                     params: "question_id=" + question + "&section_id=" + section + "&form_id=' . $item->getId() . '"
                  });
               }

               function setRequired(question_id, value) {
                  Ext.Ajax.request({
                     url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/question.form.php",
                     success: reloadTab,
                     params: {
                        set_required: 1,
                        id: question_id,
                        value: value,
                        _glpi_csrf_token: "' . Session::getNewCSRFToken() . '"
                     }
                  });
               }

               function moveQuestion(question_id, way) {
                  Ext.Ajax.request({
                     url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/question.form.php",
                     success: reloadTab,
                     params: {
                        move: 1,
                        id: question_id,
                        way: way,
                        _glpi_csrf_token: "' . Session::getNewCSRFToken() . '"
                     }
                  });
               }

               function deleteQuestion(question_id, question_name) {
                  if(confirm("' . __('Are you sure you want to delete this question:', 'formcreator') . ' " + question_name)) {
                     Ext.Ajax.request({
                        url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/question.form.php",
                        success: reloadTab,
                        params: {
                           delete: 1,
                           id: question_id,
                           plugin_formcreator_forms_id: ' . $item->getId() . ',
                           _glpi_csrf_token: "' . Session::getNewCSRFToken() . '"
                        }
                     });
                  }
               }

               // === SECTIONS ===
               var add_section_link = document.getElementById("add_section_th").innerHTML;

               var tab_sections = [];
               ' . $js_tab_sections . '

               function addSection() {
                  resetAll();
                  Ext.get("add_section_th").load({
                     url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/ajax/section.php",
                     scripts: true,
                     params: "form_id=' . $item->getId() . '"
                  });
               }

               function editSection(section) {
                  resetAll();
                  document.getElementById("section_row_" + section).innerHTML = "<th colspan=\"6\"></th>";
                  Ext.get("section_row_" + section + "").child("th").load({
                     url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/ajax/section.php",
                     scripts: true,
                     params: "section_id=" + section + "&form_id=' . $item->getId() . '"
                  });
               }

               function deleteSection(section_id, section_name) {
                  if(confirm("' . __('Are you sure you want to delete this section:', 'formcreator') . ' " + section_name)) {
                     Ext.Ajax.request({
                        url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/section.form.php",
                        success: reloadTab,
                        params: {
                           delete: 1,
                           id: section_id,
                           plugin_formcreator_forms_id: ' . $item->getId() . ',
                           _glpi_csrf_token: "' . Session::getNewCSRFToken() . '"
                        }
                     });
                  }
               }

               function moveSection(section_id, way) {
                  Ext.Ajax.request({
                     url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/front/section.form.php",
                     success: reloadTab,
                     params: {
                        move: 1,
                        id: section_id,
                        way: way,
                        _glpi_csrf_token: "' . Session::getNewCSRFToken() . '"
                     }
                  });
               }

               function resetAll() {
                  document.getElementById("add_section_th").innerHTML = add_section_link;
                  for (section_id in tab_sections) {
                     if(parseInt(section_id)) {
                        document.getElementById("section_row_" + section_id).innerHTML = tab_sections[section_id];
                        document.getElementById("add_question_td_" + section_id).innerHTML = tab_questions[section_id];
                     }
                  }
                  for (question_id in line_questions) {
                     if(parseInt(question_id)) {
                        document.getElementById("question_row_" + question_id).innerHTML = line_questions[question_id];
                     }
                  }
               }

            </script>';

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
      // Control fields values :
      // - name is required
      if(empty($input['name'])) {
         Session::addMessageAfterRedirect(__('The title is required', 'formcreator'), false, ERROR);
         return array();
      }
      // - field type is required
      if(empty($input['fieldtype'])) {
         Session::addMessageAfterRedirect(__('The field type is required', 'formcreator'), false, ERROR);
         return array();
      }
      // - section is required
      if(empty($input['plugin_formcreator_sections_id'])) {
         Session::addMessageAfterRedirect(__('The section is required', 'formcreator'), false, ERROR);
         return array();
      }

      // Fields are differents for dropdown lists, so we need to replace these values into the good ones
      if(!empty($input['ldap_auth'])) {
         $input['values'] = json_encode(array(
            'ldap_auth'      => $input['ldap_auth'],
            'ldap_filter'    => $input['ldap_filter'],
            'ldap_attribute' => strtolower($input['ldap_attribute']),
         ));
      }

      // Get next order
      $obj    = new self();
      $query  = "SELECT MAX(`order`) AS `order`
                 FROM `{$obj->getTable()}`
                 WHERE `plugin_formcreator_sections_id` = {$input['plugin_formcreator_sections_id']}";
      $result = $GLOBALS['DB']->query($query);
      $line   = $GLOBALS['DB']->fetch_array($result);
      $input['order'] = $line['order'] + 1;

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
      // Control fields values :
      // - name is required
      if(empty($input['name'])) {
         Session::addMessageAfterRedirect(__('The title is required', 'formcreator'), false, ERROR);
         return array();
      }
      // - field type is required
      if(empty($input['fieldtype'])) {
         Session::addMessageAfterRedirect(__('The field type is required', 'formcreator'), false, ERROR);
         return array();
      }
      // - section is required
      if(empty($input['plugin_formcreator_sections_id'])) {
         Session::addMessageAfterRedirect(__('The section is required', 'formcreator'), false, ERROR);
         return array();
      }

      // Fields are differents for dropdown lists, so we need to replace these values into the good ones
      if($input['fieldtype'] == 'dropdown') {
         $input['default_values'] = $input['dropdown_default_value'];
         $input['values']         = $input['dropdown_values'];
      }

      // Fields are differents for dropdown lists, so we need to replace these values into the good ones
      if(!empty($input['ldap_auth'])) {
         $input['values'] = json_encode(array(
            'ldap_auth'      => $input['ldap_auth'],
            'ldap_filter'    => $input['ldap_filter'],
            'ldap_attribute' => strtolower($input['ldap_attribute']),
         ));
      }

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

      return $input;
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
                     `plugin_formcreator_sections_id` tinyint(1) NOT NULL,
                     `fieldtype` varchar(30) NOT NULL DEFAULT 'text',
                     `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                     `show_type` enum ('show', 'hide') NOT NULL DEFAULT 'show',
                     `show_field` int(11) NULL DEFAULT NULL,
                     `show_condition` enum('equal', 'notequal', 'lower', 'greater') NULL DEFAULT NULL,
                     `show_value` varchar(255) NULL DEFAULT NULL,
                     `required` boolean NOT NULL DEFAULT FALSE,
                     `show_empty` boolean NOT NULL DEFAULT FALSE,
                     `default_values` text NULL DEFAULT NULL,
                     `values` text NULL DEFAULT NULL,
                     `range_min` varchar(10) NULL DEFAULT NULL,
                     `range_max` varchar(10) NULL DEFAULT NULL,
                     `description` text NOT NULL DEFAULT '',
                     `regex` varchar(255) NULL DEFAULT NULL,
                     `order` int(11) NOT NULL DEFAULT '0'
                  )
                  ENGINE = MyISAM
                  DEFAULT CHARACTER SET = utf8
                  COLLATE = utf8_unicode_ci";
         $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
      } elseif(!FieldExists($table, 'fieldtype')) {
         // Migration from previous version
         $query = "ALTER TABLE `$table`
                   ADD `fieldtype` varchar(30) NOT NULL DEFAULT 'text',
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

            foreach($datas->value as $value) {
               $values .= urldecode($value) . PHP_EOL;
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

            $query_udate = "UPDATE $table SET
                               `fieldtype` = $fieldtype,
                               `values`    = $values,
                               `default`   = $default,
                               `regex`     = $regex,
                               `required`  = $required
                            WHERE `id` = {$line['id']}";
            $GLOBALS['DB']->query($query_udate) or die ($GLOBALS['DB']->error());
         }

         $query = "ALTER TABLE `$table`
                   DROP `type`,
                   DROP `data`,
                   DROP `option`;";
         $GLOBALS['DB']->query($query) or die ($GLOBALS['DB']->error());
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
      $GLOBALS['DB']->query('DROP TABLE IF EXISTS `'.$obj->getTable().'`');

      // Delete logs of the plugin
      $GLOBALS['DB']->query('DELETE FROM `glpi_logs` WHERE itemtype = "' . __CLASS__ . '"');

      return true;
   }
}
