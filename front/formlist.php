<?php
include ('../../../inc/includes.php');

$plugin = new Plugin();
if (!$plugin->isInstalled('formcreator') || !$plugin->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

Session::checkLoginUser();

if ($_SESSION['glpiactiveprofile']['interface'] == 'helpdesk') {
   if (plugin_formcreator_replaceHelpdesk()) {
      Html::redirect('issue.php');
   } else {
      Html::helpHeader(
         __('Form list', 'formcreator'),
         $_SERVER['PHP_SELF']
      );
   }
} else {
   Html::header(
      __('Form list', 'formcreator'),
      $_SERVER['PHP_SELF'],
      'helpdesk',
      'PluginFormcreatorFormlist'
   );
}

$form = new PluginFormcreatorForm();
$form->showList();

Html::footer();
