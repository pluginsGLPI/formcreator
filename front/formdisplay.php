<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();
$form   = new PluginFormcreatorForm();

PluginFormcreatorForm::header();

if($plugin->isActivated("formcreator")
   && isset($_REQUEST['id'])
   && is_numeric($_REQUEST['id'])) {

   if($form->getFromDB((int) $_REQUEST['id'])) {

      if($form->fields['access_rights'] != PluginFormcreatorForm::ACCESS_PUBLIC) {
         Session::checkLoginUser();
      }
      if($form->fields['access_rights'] == PluginFormcreatorForm::ACCESS_RESTRICTED) {
         $form_profile = new PluginFormcreatorForm_Profile();
         $formId = $form->getID();
         $activeProfileId = $_SESSION['glpiactiveprofile']['id'];
         $rows = $form_profile->find("profiles_id = '$activeProfileId'
                                      AND plugin_formcreator_forms_id = '$formId'", "", "1");
         if(count($rows) == 0) {
            Html::displayRightError();
            exit();
         }
      }
      if(($form->fields['access_rights'] == PluginFormcreatorForm::ACCESS_PUBLIC) && (!isset($_SESSION['glpiID']))) {
         // If user is not authenticated, create temporary user
         if(!isset($_SESSION['glpiname'])) {
            $_SESSION['formcreator_forms_id'] = $form->fields['id'];
            $_SESSION['glpiname'] = 'formcreator_temp_user';
            $_SESSION['valid_id'] = session_id();
            $_SESSION['glpiactiveentities'] = $form->fields['entities_id'];
            $subentities = getSonsOf('glpi_entities', $form->fields['entities_id']);
            $_SESSION['glpiactiveentities_string'] = (!empty($subentities))
                                                   ? "'" . implode("', '", $subentities) . "'"
                                                   : "'" . $form->fields['entities_id'] . "'";
         }
      }

      $form->displayUserForm($form);

   } else {
      Html::displayNotFoundError();
   }

   // If user was not authenticated, remove temporary user
   if($_SESSION['glpiname'] == 'formcreator_temp_user') {
      unset($_SESSION['glpiname']);
   }

// Or display a "Not found" error
} elseif (isset($_GET['answer_saved'])) {
   $message = __("The form has been successfully saved!");
   Html::displayTitle($CFG_GLPI['root_doc']."/pics/ok.png", $message, $message);
} else {
   Html::displayNotFoundError();
}

PluginFormcreatorForm::footer();
