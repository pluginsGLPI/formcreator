<?php
include ('../../../inc/includes.php');

// Define tables
$cat_table  = getTableForItemType('PluginFormcreatorCategory');
$form_table = getTableForItemType('PluginFormcreatorForm');
$table_fp   = getTableForItemType('PluginFormcreatorFormprofiles');
$where      = getEntitiesRestrictRequest( "", $form_table, "", "", true, false);

// Show form whithout table
$query_forms = "SELECT $form_table.id, $form_table.name, $form_table.description
                FROM $form_table
                WHERE $form_table.`plugin_formcreator_categories_id` = 0
                AND $form_table.`is_active` = 1
                AND $form_table.`is_deleted` = 0
                AND $form_table.`helpdesk_home` = 1
                AND ($form_table.`language` = '{$_SESSION['glpilanguage']}' OR $form_table.`language` = '')
                AND $where
                AND (`access_rights` != " . PluginFormcreatorForm::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
                   SELECT plugin_formcreator_forms_id
                   FROM $table_fp
                   WHERE plugin_formcreator_profiles_id = " . (int) $_SESSION['glpiactiveprofile']['id'] . "))
                ORDER BY $form_table.name ASC";
$result_forms = $GLOBALS['DB']->query($query_forms);

// Show categories wicth have at least one form user can access
$query  = "SELECT $cat_table.`name`, $cat_table.`id`
           FROM $cat_table
           WHERE 0 < (
               SELECT COUNT($form_table.id)
               FROM $form_table
               WHERE $form_table.`plugin_formcreator_categories_id` = $cat_table.`id`
               AND $form_table.`is_active` = 1
               AND $form_table.`is_deleted` = 0
               AND $form_table.`helpdesk_home` = 1
               AND ($form_table.`language` = '{$_SESSION['glpilanguage']}' OR $form_table.`language` = '')
               AND $where
               AND ($form_table.`access_rights` != " . PluginFormcreatorForm::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
                  SELECT plugin_formcreator_forms_id
                  FROM $table_fp
                  WHERE plugin_formcreator_profiles_id = " . (int) $_SESSION['glpiactiveprofile']['id'] . "))
            )
           ORDER BY $cat_table.`name` ASC";
$result = $GLOBALS['DB']->query($query);
if ($GLOBALS['DB']->numrows($result) > 0 || $GLOBALS['DB']->numrows($result_forms) > 0) {
   echo '<table class="tab_cadrehov">';
   echo '<tr class="noHover">';
   echo '<th><a href="../plugins/formcreator/front/formlist.php">' . _n('Form', 'Forms', 2, 'formcreator') . '</a></th>';
   echo '</tr>';

   if ($GLOBALS['DB']->numrows($result_forms) > 0) {
      echo '<tr class="noHover"><th>' . __('Forms without category', 'formcreator') . '</th></tr>';
      $i = 0;
      while ($form = $GLOBALS['DB']->fetch_array($result_forms)) {
         $i++;
         echo '<tr class="line' . ($i % 2) . ' tab_bg_' . ($i % 2 +1) . '">';
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
   }

   if ($GLOBALS['DB']->numrows($result) > 0) {
      // For each categories, show the list of forms the user can fill
      $i = 0;
      while ($category = $GLOBALS['DB']->fetch_array($result)) {
         echo '<tr class="noHover"><th>' . $category['name'] . '</th></tr>';
         $query_forms = "SELECT $form_table.id, $form_table.name, $form_table.description
                         FROM $form_table
                         WHERE $form_table.`plugin_formcreator_categories_id` = {$category['id']}
                         AND $form_table.`is_active` = 1
                         AND $form_table.`is_deleted` = 0
                         AND $form_table.`helpdesk_home` = 1
                         AND ($form_table.`language` = '{$_SESSION['glpilanguage']}' OR $form_table.`language` = '')
                         AND $where
                         AND (`access_rights` != " . PluginFormcreatorForm::ACCESS_RESTRICTED . " OR $form_table.`id` IN (
                            SELECT plugin_formcreator_forms_id
                            FROM $table_fp
                            WHERE plugin_formcreator_profiles_id = " . (int) $_SESSION['glpiactiveprofile']['id'] . "))
                         ORDER BY $form_table.name ASC";
         $result_forms = $GLOBALS['DB']->query($query_forms);
         $i = 0;
         while ($form = $GLOBALS['DB']->fetch_array($result_forms)) {
            $i++;
            echo '<tr class="line' . ($i % 2) . ' tab_bg_' . ($i % 2 +1) . '">';
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
      }
   }
   echo '</table>';
   echo '<br />';
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
