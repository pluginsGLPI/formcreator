<?php
require_once ('../../../inc/includes.php');

// Check if current user have config right
Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if(!$plugin->isInstalled('formcreator') || !$plugin->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

if(PluginFormcreatorForm::canView()) {
   Html::header(
      __('Form Creator', 'formcreator'),
      $_SERVER['PHP_SELF'],
      'admin',
      'PluginFormcreatorForm'
   );

   Search::show('PluginFormcreatorForm');

   Html::footer();
} else {
   Html::displayRightError();
}
