<?php
include ("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $target = new PluginFormcreatorTarget();

   // Add a new target
   if(isset($_POST["add"]) && !empty($_POST['plugin_formcreator_forms_id'])) {
      $target->check(-1,'w',$_POST);
      if($target->add($_POST)) {
         switch ($_POST['itemtype']) {
            case 'PluginFormcreatorTargetTicket':
               Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/targetticket.form.php?id=' . $target->fields['items_id']);
               break;

            default :
               Html::back();
               break;
         }
      } else {
         Html::back();
      }

   // Delete a target
   } elseif(isset($_POST["delete"])) {
      $target->check($_POST['id'], 'd');
      $target->delete($_POST);
      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);

   } else {
      Html::back();
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
