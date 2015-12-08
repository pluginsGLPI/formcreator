<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();

if($plugin->isActivated("formcreator") && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
   $form = new PluginFormcreatorForm();
   if($form->getFromDB((int) $_REQUEST['id'])) {

      if($form->fields['access_rights'] != PluginFormcreatorForm::ACCESS_PUBLIC) {
         Session::checkLoginUser();
      }
      if($form->fields['access_rights'] == PluginFormcreatorForm::ACCESS_RESTRICTED) {
         $table = getTableForItemType('PluginFormcreatorFormprofiles');
         $query = "SELECT *
                   FROM $table
                   WHERE plugin_formcreator_profiles_id = {$_SESSION['glpiactiveprofile']['id']}
                   AND plugin_formcreator_forms_id = {$form->fields['id']}";
         $result = $DB->query($query);

         if($DB->numrows($result) == 0) {
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

      if (isset($_SESSION['glpiactiveprofile']['interface'])
            && ($_SESSION['glpiactiveprofile']['interface'] == 'helpdesk')) {
         Html::helpHeader(
            __('Form list', 'formcreator'),
            $_SERVER['PHP_SELF']
         );

         $form->displayUserForm($form);

         Html::helpFooter();

      } elseif(!empty($_SESSION['glpiactiveprofile'])) {
         Html::header(
            __('Form Creator', 'formcreator'),
            $_SERVER['PHP_SELF'],
            'helpdesk',
            'PluginFormcreatorFormlist'
         );

         $form->displayUserForm($form);

         Html::footer();

      } else {
         Html::nullHeader(
            __('Form Creator', 'formcreator'),
            $_SERVER['PHP_SELF']
         );

         Html::displayMessageAfterRedirect();

         $form->displayUserForm($form);

         Html::nullFooter();
      }

   } else {
      Html::displayNotFoundError();
   }

   // If user was not authenticated, remove temporary user
   if($_SESSION['glpiname'] == 'formcreator_temp_user') {
      unset($_SESSION['glpiname']);
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
