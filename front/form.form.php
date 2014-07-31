<?php
include ("../../../inc/includes.php");

// Session::checkRight("config", "w");

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $form = new PluginFormcreatorForm();

   // Add a new Form
   if(isset($_POST["add"])) {
      $form->check(-1,'w',$_POST);
      $newID = $form->add($_POST);

      // $newTarget = $form->createDefaultTarget($newID);
      // $newSection = $form->createDefaultSection($newID,$newTarget);

      Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $newID);

   // Edit an existinf form
   } elseif(isset($_POST["update"])) {
      $form->check($_POST['id'],'w');
      $form->update($_POST);
      Html::back();

   // Delete a form (is_deleted = true)
   } elseif(isset($_POST["delete"])) {
      $form->check($_POST['id'], 'd');
      $form->delete($_POST);
      $form->redirectToList();

   // Restore a deleteted form (is_deleted = false)
   } elseif(isset($_POST["restore"])) {
      $form->check($_POST['id'], 'd');
      $form->restore($_POST);
      $form->redirectToList();

   // Delete defenitively a form from DB and all its datas
   } elseif(isset($_POST["purge"])) {
      $form->check($_POST['id'], 'd');
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
         $form->saveToTargets($_POST);

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
      Html::header(
         __('Form Creator', 'formcreator'),
         $_SERVER['PHP_SELF'],
         'plugins',
         'formcreator',
         'options'
      );

      $form->showForm($_REQUEST);

      Html::footer();
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
