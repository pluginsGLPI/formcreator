<?php
include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   if (isset($_POST["profiles_id"]) && isset($_POST["form_id"])) {

      if (isset($_POST['access_rights'])) {
         $form = new PluginFormcreatorForm();
         $form->update(array(
            'id'            => $_POST['form_id'],
            'access_rights' => $_POST['access_rights']
         ));
      }

      $form_profile = new PluginFormcreatorForm_Profile();
      $form_profile->deleteByCriteria(array(
            'plugin_formcreator_forms_id'    => (int) $_POST["form_id"],
      ));
      $table = PluginFormcreatorForm_Profile::getTable();

      foreach ($_POST["profiles_id"] as $profile_id) {
         if ($profile_id != 0) {
            $form_profile = new PluginFormcreatorForm_Profile();
            $form_profile->add(array(
                  'plugin_formcreator_forms_id' => (int) $_POST["form_id"],
                  'profiles_id'                 => (int) $profile_id,
            ));
         }
      }
      Html::back();

   } else {
      Html::back();
   }

   // Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
