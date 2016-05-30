<?php
include ('../../../inc/includes.php');

if (!isset($_SESSION['glpiactiveprofile']['id'])) {
   // Session is not valid then exit
   exit;
}

if ($_REQUEST['wizard'] == 'categories') {
   plugin_formcreator_showWizardCategories();
} else if ($_REQUEST['wizard'] == 'forms') {
   plugin_formcreator_showWizardForms();
}

function plugin_formcreator_showWizardCategories() {
   // Define tables
   $cat_table  = getTableForItemType('PluginFormcreatorCategory');
   $form_table = getTableForItemType('PluginFormcreatorForm');
   $table_fp   = getTableForItemType('PluginFormcreatorFormprofiles');
   $where      = getEntitiesRestrictRequest( "", $form_table, "", "", true, false);
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
   
    if ($GLOBALS['DB']->numrows($result) > 0) {
       echo '<div id="plugin_formcreator_wizard_categories" class="slinky-menu"><ul>';
       while ($category = $GLOBALS['DB']->fetch_array($result)) {
          echo '<li><a href="#" onclick="updateWizardFormsView(' . $category['id'] . ')">' . $category['name'] . '</a></li>';
       }
       echo '</ul></div>';
    }
   

}

function plugin_formcreator_showWizardForms() {
   // TODO : select forms from the current category (or all if none clicked)
   echo '<div>';
   echo 'Forms area';
   echo '</div>';
}