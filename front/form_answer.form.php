<?php
include ("../../../inc/includes.php");

Session::redirectIfNotLoggedIn();

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $formanswer = new PluginFormcreatorForm_Answer();

   // Edit an existing target ticket
   if (isset($_POST['update'])) {
      $formanswer->update($_POST);
      Html::back();

   } else if (isset($_POST['refuse_formanswer'])) {

      $formanswer->getFromDB(intval($_POST['id']));
      $formanswer->refuseAnswers($_POST);
      $formanswer->redirectToList();

   } else if (isset($_POST['accept_formanswer'])) {

      $formanswer->getFromDB(intval($_POST['id']));
      $formanswer->acceptAnswers($_POST);
      $formanswer->redirectToList();

   } else if (isset($_POST['save_formanswer'])) {
      $_POST['plugin_formcreator_forms_id'] = intval($_POST['formcreator_form']);
      $_POST['status']                      = 'waiting';
      $formanswer->saveAnswers($_POST);
      $formanswer->redirectToList();

      // Show target ticket form
   } else {
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

      $formanswer->display($_REQUEST);

      if (plugin_formcreator_replaceHelpdesk()) {
         PluginFormcreatorWizard::footer();
      } else {
         Html::footer();
      }
   }

   // Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
