<?php
require_once ('../../../inc/includes.php');

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

if (PluginFormcreatorIssue::canView()) {
   if (plugin_formcreator_replaceHelpdesk()) {
      PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));
   } else {
      if ($_SESSION['glpiactiveprofile']['interface'] == 'helpdesk') {
         Html::helpHeader(
            __('Form Creator', 'formcreator'),
            $_SERVER['PHP_SELF']
         );
      } else {
         Html::header(
            __('Form Creator', 'formcreator'),
            $_SERVER['PHP_SELF'],
            'helpdesk',
            'PluginFormcreatorFormlist'
         );
      }
   }

   Search::show('PluginFormcreatorIssue');

   if (plugin_formcreator_replaceHelpdesk()) {
      PluginFormcreatorWizard::footer();
   } else {
      Html::footer();
   }
} else {
   Html::displayRightError();
}
