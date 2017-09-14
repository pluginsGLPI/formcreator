<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

if (! plugin_formcreator_replaceHelpdesk()) {
   Html::redirect($CFG_GLPI["root_doc"]."/plugins/formcreator/front/formlist.php");
}

PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));

$form = new PluginFormcreatorForm();
$form->showServiceCatalog();

PluginFormcreatorWizard::footer();
