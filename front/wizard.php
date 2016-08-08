<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();

if($plugin->isActivated("formcreator")) {
   Html::header(__('Service catalog', 'formcreator'), '');

   $form = new PluginFormcreatorForm();
   $form->showWizard();


   Html::footer();
} else {
   Html::displayNotFoundError();
}
