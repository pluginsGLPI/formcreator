<?php
include ('../../../inc/includes.php');

$plugin = new Plugin();
if(!$plugin->isInstalled('formcreator') || !$plugin->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

Session::checkLoginUser();

Html::header(
   __('Form list', 'formcreator'),
   $_SERVER['PHP_SELF'],
   'plugins',
   'formcreator',
   'options'
);


$form = new PluginFormcreatorForm();
$form->showList();

Html::footer();
