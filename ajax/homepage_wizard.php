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
} else if ($_REQUEST['wizard'] == 'mostPopular') {
   plugin_formcreator_showMostPopularForms();
}

function plugin_formcreator_showWizardCategories() {
//    echo '<div id="plugin_formcreator_wizard_categories">';
//    PluginFormcreatorCategory::slinkyView(true);
//    echo '</div>';
   $tree = PluginFormcreatorCategory::getCategoryTree(0, true);
   echo json_encode($tree, JSON_UNESCAPED_SLASHES);
}

function plugin_formcreator_showWizardForms($rootCategory = 0, $keywords) {
   $form = new PluginFormcreatorForm();
   $form->showFormListView($rootCategory, $keywords, true);
}

function plugin_formcreator_showMostPopularForms() {
   $form = new PluginFormcreatorForm();
   $form->showMostPopular();
}
