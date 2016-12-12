<?php

include ("../../../inc/includes.php");

$kb = new KnowbaseItem();

if (isset($_GET["id"])) {
   $kb->check($_GET["id"], READ);

   PluginFormcreatorWizard::header(__('Service catalog', 'formcreator'));

   $available_options = array('item_itemtype', 'item_items_id', 'id');
   $options           = array();
   foreach ($available_options as $key) {
      if (isset($_GET[$key])) {
         $options[$key] = $_GET[$key];
      }
   }
   $_SESSION['glpilisturl']['KnowbaseItem'] = FORMCREATOR_ROOTDOC."/front/wizard.php";
   $kb->display($options);

   PluginFormcreatorWizard::footer();

}
