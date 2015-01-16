<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $form = new PluginFormcreatorForm();

   // Add a new Form
   if(isset($_POST["add"])) {
      Session::checkRight("entity", UPDATE);
      $newID = $form->add($_POST);

      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $newID);

   // Edit an existinf form
   } elseif(isset($_POST["update"])) {
      Session::checkRight("entity", UPDATE);
      $form->update($_POST);
      Html::back();

   // Delete a form (is_deleted = true)
   } elseif(isset($_POST["delete"])) {
      Session::checkRight("entity", UPDATE);
      $form->delete($_POST);
      $form->redirectToList();

   // Restore a deleteted form (is_deleted = false)
   } elseif(isset($_POST["restore"])) {
      Session::checkRight("entity", UPDATE);
      $form->restore($_POST);
      $form->redirectToList();

   // Delete defenitively a form from DB and all its datas
   } elseif(isset($_POST["purge"])) {
      Session::checkRight("entity", UPDATE);
      $form->delete($_POST,1);
      $form->redirectToList();

   // Save form to target
   } elseif (isset($_POST['submit_formcreator'])) {
      if($form->getFromDB($_POST['formcreator_form'])) {

         // If user is not authenticated, create temporary user
         if(!isset($_SESSION['glpiname'])) {
            $_SESSION['glpiname'] = 'formcreator_temp_user';
         }

         // Save form
         $form->saveForm();

         // If user was not authenticated, remove temporary user
         if($_SESSION['glpiname'] == 'formcreator_temp_user') {
            unset($_SESSION['glpiname']);
            Html::back();
         } else {
            Html::redirect('formlist.php');
         }
      }


   // Show forms form
   } else {
      Session::checkRight("entity", UPDATE);

      Html::header(
         PluginFormcreatorForm::getTypeName(2),
         $_SERVER['PHP_SELF'],
         'admin',
         'PluginFormcreatorForm',
         'option'
      );

      $form->display($_GET);

      Html::footer();
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
