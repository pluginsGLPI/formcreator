<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isActivated('formcreator')) {
   Html::displayNotFoundError();
}

if (! plugin_formcreator_replaceHelpdesk()) {
   Html::redirect($CFG_GLPI["root_doc"]."/front/helpdesk.public.php");
}

if (RSSFeed::canView()) {
   PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));
   RSSFeed::showListForCentral(false);
   PluginFormcreatorWizard::footer();
}
