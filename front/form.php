<?php
require_once ('../../../inc/includes.php');

// Check if current user have config right
Session::checkRight("config", "w");

// Check if plugin is activated...
$plugin = new Plugin();
if(!$plugin->isInstalled('formcreator') || !$plugin->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

if(PluginFormcreatorForm::canView()) {
   Html::header(
      __('Forms', 'formcreator'),
      $_SERVER['PHP_SELF'],
      'plugins',
      'formcreator',
      'config'
   );

   Search::show('PluginFormcreatorForm');

   Html::footer();
} else {
   Html::displayRightError();
}
