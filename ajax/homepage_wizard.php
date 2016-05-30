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
//        echo '<div id="plugin_formcreator_wizard_categories" class="slinky-menu"><ul>';
//        while ($category = $GLOBALS['DB']->fetch_array($result)) {
//           echo '<li><a href="#" onclick="updateWizardFormsView(' . $category['id'] . ')">' . $category['name'] . '</a></li>';
//        }
//        echo '</ul></div>';
   echo PluginFormcreatorCategory::getHtmlCategoryTree();   

}

function plugin_formcreator_showWizardForms() {
   // TODO : select forms from the current category (or all if none clicked)
   echo '<div>';
   echo 'Forms area';
   echo '</div>';
}