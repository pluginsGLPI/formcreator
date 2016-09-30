<?php

include ("../../../inc/includes.php");

if (RSSFeed::canView()) {
   PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));
   RSSFeed::showListForCentral(false);
   PluginFormcreatorWizard::footer();
}

?>