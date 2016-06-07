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
   $keywords = isset($_REQUEST['keywords']) ? $_REQUEST['keywords'] : '';
   plugin_formcreator_showWizardForms($categoriesId, $keywords);
}

function plugin_formcreator_showWizardCategories() {
   echo '<div id="plugin_formcreator_wizard_categories">';
   PluginFormcreatorCategory::slinkyView();
   echo '</div>';
}

function plugin_formcreator_showWizardForms($rootCategory = 0, $keywords) {
   $form = new PluginFormcreatorForm();
   $form->showFormListView($rootCategory, $keywords);
}