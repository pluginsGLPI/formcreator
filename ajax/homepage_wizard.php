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
   echo '<table class="tab_cadrehov">';
   echo '<tr><th>' . __('FormCreator assistant', 'formcreator') . '</th></tr>';
   echo '<tr><td><div id="plugin_formcreator_wizard_categories" class="slinky-menu">';
   echo PluginFormcreatorCategory::getHtmlCategoryTree();   
   echo '</div></td></tr>';
   echo '</table>';

}

function plugin_formcreator_showWizardForms() {
   // TODO : select forms from the current category (or all if none clicked)
   echo '<div>';
   echo 'Forms area';
   echo '</div>';
}