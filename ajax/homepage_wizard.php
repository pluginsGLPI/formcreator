<?php
include ('../../../inc/includes.php');

if (!isset($_SESSION['glpiactiveprofile']['id'])) {
   // Session is not valid then exit
   exit;
}

if ($_REQUEST['wizard'] == 'categories') {
   plugin_formcreator_showWizardCategories();
} else if ($_REQUEST['wizard'] == 'forms') {
   if (isset($_REQUEST['categoriesId'])) {
      $categoriesId = (int) $_REQUEST['categoriesId'];
   } else {
      $categoriesId = 0;
   }
   plugin_formcreator_showWizardForms($categoriesId);
}

function plugin_formcreator_showWizardCategories() {
   echo '<div id="plugin_formcreator_wizard_categories" style="width: 275px; float: left;">';
   PluginFormcreatorCategory::slinkyView();
   echo '</div>';
}

function plugin_formcreator_showWizardForms($rootCategory = 0) {
   $form = new PluginFormcreatorForm();
   $form->showFormListView($rootCategory);
}