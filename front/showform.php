<?php
include ("../../../inc/includes.php");

if(!isset($_SESSION['glpiID'])) {
   $_SESSION['glpiID'] = 2;
   $_SESSION['glpi_active_profile'] = 2;
}

// Check if plugin is activated...
$plugin = new Plugin();

if($plugin->isActivated("formcreator") && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
   $form = new PluginFormcreatorForm();
   if($form->getFromDB((int) $_REQUEST['id'])) {
      Html::header(
         __('Form Creator', 'formcreator'),
         $_SERVER['PHP_SELF'],
         'plugins',
         'formcreator',
         'options'
      );

      $form->displayUserForm($form);

      Html::footer();
   } else {
      Html::displayNotFoundError();
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
