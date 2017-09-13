<?php
include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isActivated("formcreator")) {
   Html::displayNotFoundError();
}

if (isset($_POST["profiles_id"]) && isset($_POST["form_id"])) {

   if (isset($_POST['access_rights'])) {
      $form = new PluginFormcreatorForm();
      $form->update([
         'id'            => (int) $_POST['form_id'],
         'access_rights' => (int) $_POST['access_rights']
      ]);
   }

   $form_profile = new PluginFormcreatorForm_Profile();
   $form_profile->deleteByCriteria([
         'plugin_formcreator_forms_id'    => (int) $_POST["form_id"],
   ]);

   foreach ($_POST["profiles_id"] as $profile_id) {
      if ($profile_id != 0) {
         $form_profile = new PluginFormcreatorForm_Profile();
         $form_profile->add([
               'plugin_formcreator_forms_id' => (int) $_POST["form_id"],
               'profiles_id'                 => (int) $profile_id,
         ]);
      }
   }
   Html::back();
} else {
   Html::back();
}
