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
      PluginFormcreatorForm::getTypeName(2),
      '',
      'admin',
      'PluginFormcreatorForm'
   );

   Search::show('PluginFormcreatorForm');

   Html::footer();
} else {
   Html::displayRightError();
}
