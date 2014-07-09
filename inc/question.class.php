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
      return _n('Question', 'Queestions', $nb, 'formcreator');
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
            $object  = new self;
            $founded = $object->find();
            $number  = count($founded);
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
      $tab_sections      = array();
      foreach($founded_sections as $section) {
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
         foreach($founded_questions as $question) {
            echo '<tr class="line1">';
            echo '<td>';
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/ui-text-field.png" alt="" title="" /> ';
            echo $question['name'];
            echo '</td>';

            echo '<td align="center">';
            if($question['required']) {
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/required.png"
                        alt="*" title="' . __('Required', 'formcreator') . '"
                        onclick="setRequired(' . 0 . ', this)" align="absmiddle" style="cursor: pointer" /> ';
            } else {
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/not-required.png"
                        alt="*" title="' . __('Required', 'formcreator') . '"
                        onclick="setRequired(' . 0 . ', this)" align="absmiddle" style="cursor: pointer" /> ';
            }
            echo '</td>';
            echo '<td align="center">';
            if($question['order'] != 1) {
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/up.png"
                        alt="*" title="' . __('Edit', 'formcreator') . '"
                        onclick="editField(' . 0 . ', this)" align="absmiddle" style="cursor: pointer" /> ';
            } else {
               echo '&nbsp;';
            }
            echo '</td>';
            echo '<td align="center">';
            if($question['order'] != $question_number) {
               echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/down.png"
                        alt="*" title="' . __('Edit', 'formcreator') . '"
                        onclick="editField(' . 0 . ', this)" align="absmiddle" style="cursor: pointer" /> ';
            } else {
               echo '&nbsp;';
            }
            echo '</td>';
            echo '<td align="center">';
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/pencil.png"
                     alt="*" title="' . __('Edit', 'formcreator') . '"
                     onclick="editField(' . 0 . ', this)" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';
            echo '<td align="center">';
            echo '<img src="' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/pics/delete.png"
                     alt="*" title="' . __('Delete', 'formcreator') . '"
                     onclick="removeField(' . 0 . ', this)" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';
            echo '</tr>';
         }

         echo '<tr class="line0">';
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

      $js_tab_values = "";
      foreach($tab_sections as $key) {
         $js_tab_values .= "tab_sections[$key] = document.getElementById('section_row_$key').innerHTML;".PHP_EOL;
      }

      echo '<script type="text/javascript">
               var add_section_link = document.getElementById("add_section_th").innerHTML;

               var tab_sections = [];
               ' . $js_tab_values . '

               function addQuestion(section) {
                  Ext.get("add_question_td_" + section).load({
                     url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/ajax/question.php",
                     scripts: false,
                     params: "section_id=" + section + "&form_id=" + ' . $item->getId() . '
                  });
               }

               function editQuestion(question) {
               }

               function addSection() {
                  Ext.get("add_section_th").load({
                     url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/ajax/section.php",
                     scripts: false,
                     params: "form_id=' . $item->getId() . '"
                  });
               }

               function editSection(section) {
                  resetAll();
                  document.getElementById("section_row_" + section).innerHTML = "<th colspan=\"6\"></th>";
                  Ext.get("section_row_" + section + "").child("th").load({
                     url: "' . $GLOBALS['CFG_GLPI']['root_doc'] . '/plugins/formcreator/ajax/section.php",
                     scripts: false,
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
                  for(section_id in tab_sections) {
                     if(parseInt(section_id))
                        document.getElementById("section_row_" + section_id).innerHTML = tab_sections[section_id];
                  }
               }

            </script>';

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

      $obj = new self();
      $table = $obj->getTable();

      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         // Create questions table
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     `plugin_formcreator_sections_id` tinyint(1) NOT NULL,
                     `plugin_formcreator_fieldtypes_id` tinyint(1) NOT NULL DEFAULT '0',
                     `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                     `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                     `required` boolean NOT NULL DEFAULT FALSE,
                     `displayed` tinyint(1) NOT NULL DEFAULT '0',
                     `plugin_formcreator_questions_id` int(11) NOT NULL,
                     `value_to_displayed` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                     `save_to_target` boolean NOT NULL DEFAULT FALSE,
                     `target_itemtype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                     `target_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                     `order` int(11) NOT NULL DEFAULT '0'
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
