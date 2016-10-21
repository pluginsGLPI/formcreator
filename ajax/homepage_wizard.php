<?php
include ('../../../inc/includes.php');

if (!isset($_SESSION['glpiactiveprofile']['id'])) {
   // Session is not valid then exit
   exit;
}

if ($_REQUEST['wizard'] == 'categories') {
   plugin_formcreator_showWizardCategories(plugin_formcreator_replaceHelpdesk());
} else if ($_REQUEST['wizard'] == 'forms') {
   if (isset($_REQUEST['categoriesId'])) {
      $categoriesId = intval($_REQUEST['categoriesId']);
   } else {
      $categoriesId = 0;
   }
   $keywords = isset($_REQUEST['keywords']) ? $_REQUEST['keywords'] : '';
   $helpdeskHome = isset($_REQUEST['helpdeskHome']) ? $_REQUEST['helpdeskHome'] != '0' : false;
   plugin_formcreator_showWizardForms($categoriesId, $keywords, $helpdeskHome);
} elseif ($_REQUEST['wizard'] == 'toggle_menu') {
   $_SESSION['plugin_formcreator_toggle_menu'] = isset($_SESSION['plugin_formcreator_toggle_menu'])
                                                   ? !$_SESSION['plugin_formcreator_toggle_menu']
                                                   : true;
}

function plugin_formcreator_showWizardCategories($helpdesk = true) {
   $tree = PluginFormcreatorCategory::getCategoryTree(0, $helpdesk);
   echo json_encode($tree, JSON_UNESCAPED_SLASHES);
}

function plugin_formcreator_showWizardForms($rootCategory = 0, $keywords, $helpdeskHome = false) {
   $form = new PluginFormcreatorForm();
   $formList = $form->showFormList($rootCategory, $keywords, $helpdeskHome);
   echo json_encode($formList, JSON_UNESCAPED_SLASHES);
}
