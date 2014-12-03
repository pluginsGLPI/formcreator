<?php
include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   if (isset($_POST["profiles_id"]) && isset($_POST["form_id"])) {

      if(isset($_POST['access_rights'])) {
         $form = new PluginFormcreatorForm();
         $form->update(array(
            'id'            => $_POST['form_id'],
            'access_rights' => $_POST['access_rights']
         ));
      }

      $table = getTableForItemType('PluginFormcreatorFormprofiles');
      $DB->query("DELETE FROM $table WHERE plugin_formcreator_forms_id = " . (int) $_POST["form_id"]);

      foreach($_POST["profiles_id"] as $profile_id) {
         if ($profile_id != 0) {
            $query = "INSERT IGNORE INTO $table SET
                        `plugin_formcreator_forms_id` = " . (int) $_POST["form_id"] .",
                        `plugin_formcreator_profiles_id` = " . (int) $profile_id;
            $DB->query($query);
         }
      }
      Html::back();

   } else {
      Html::back();
   }

// Or display a "Not found" error
}else{
   Html::displayNotFoundError();
}
