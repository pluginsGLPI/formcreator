<?php

include ('../../../inc/includes.php');

if(isset($_SESSION['is_popup'])) unset($_SESSION['is_popup']);

Session::checkRight("config", "w");

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $config = new PluginFormcreatorConfig();

   if (isset($_POST["update"])) {
      Session::checkRight("config", "w");

      $config->update($_POST);

      Html::back();
   } else {
      Html::header(
         __('Form Creator', 'formcreator'),
         $_SERVER['PHP_SELF'],
         'plugins',
         'formcreator',
         'options'
      );

      $config->show($_GET);

      Html::footer();
   }

// Or display a "Not found" error
}else{
   Html::displayNotFoundError();
}
