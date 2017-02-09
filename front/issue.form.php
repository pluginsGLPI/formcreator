<?php
require_once ('../../../inc/includes.php');

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated('formcreator')) {
   if (!isset($_REQUEST['sub_itemtype'])) {
      Html::displayNotFoundError();
   }

   $issue = new PluginFormcreatorIssue();
   if (isset($_POST['save_formanswer'])) {
      $_POST['plugin_formcreator_forms_id'] = intval($_POST['formcreator_form']);
      $_POST['status']                      = 'waiting';
      $issue->saveAnswers($_POST);
      Html::back();
   } else {

      if (plugin_formcreator_replaceHelpdesk()) {
         PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));
      } else {
         Html::redirect($CFG_GLPI["root_doc"]."/front/helpdesk.public.php");
      }

      $issue->display($_REQUEST);

      if (plugin_formcreator_replaceHelpdesk()) {
         PluginFormcreatorWizard::footer();
      } else {
         Html::footer();
      }
   }
} else {
   Html::displayNotFoundError();
}
