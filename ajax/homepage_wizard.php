<?php
include ('../../../inc/includes.php');

if (!isset($_SESSION['glpiactiveprofile']['id'])) {
   // Session is not valid then exit
   exit;
}

if ($_REQUEST['wizard'] == 'categories') {
   plugin_formcreator_showWizardCategories();
} else if ($_REQUEST['wizard'] == 'forms') {
   plugin_formcreator_showWizardForms($_REQUEST['categoriesId']);
}

function plugin_formcreator_showWizardCategories() {
   PluginFormcreatorCategory::slinkyView();
}

/**
 * Builds a category tree with UL / LI HTML  tags
 * @param array $categoryTree nested arrays of category IDs
 */
function plugin_formcreator_buildCategorySlinky(array $categoryTree) {
   $html = '<a href="#" data-parent-category-id="' . $parentId . '" data-category-id="' . $rootId . '" onclick="updateWizardFormsView(' . $rootId . ')">' . $formCategory->getField('name') . '</a>';
   foreach($categoryTree as $categoryId => $categorySubTree) {
   }
}

function plugin_formcreator_showWizardForms($rootCategory = 0) {
   $form = new PluginFormcreatorForm();
   $form->showFormListView($rootCategory);
}