<?php
include ("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

// Check if plugin is activated...
$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
   $targetticket = new PluginFormcreatorTargetTicket();

   // Edit an existing target ticket
   if(isset($_POST["update"])) {
      Session::checkRight("entity", UPDATE);

      $target = new PluginFormcreatorTarget();
      $found  = $target->find('items_id = ' . (int) $_POST['id']);
      $found  = array_shift($found);
      $target->update(array('id' => $found['id'], 'name' => $_POST['name']));
      $targetticket->update($_POST);
      $targetticket->postSaveTarget($targetticket);
      Html::back();

   } elseif (isset($_POST['actor_role'])) {
      Session::checkRight("entity", UPDATE);
      $id          = (int) $_POST['id'];
      $actor_value = isset($_POST['actor_value_' . $_POST['actor_type']])
                     ? $_POST['actor_value_' . $_POST['actor_type']]
                     : '';
      $use_notification = ($_POST['use_notification'] == 0) ? 0 : 1;
      $targetTicket_actor = new PluginFormcreatorTargetTicket_Actor();
      $targetTicket_actor->add(array(
            'plugin_formcreator_targettickets_id'  => $id,
            'actor_role'                           => $_POST['actor_role'],
            'actor_type'                           => $_POST['actor_type'],
            'actor_value'                          => $actor_value,
            'use_notification'                     => $use_notification,
      ));

      if (isset($_POST['objets_glpi_assign']) ||
              isset($_POST['question_concernee_observer'])) {
          $select = "SELECT * FROM glpi_plugin_formcreator_targetsconditions WHERE plugin_formcreator_targets_id=" . $id . ";";
          $select_result = $DB->query($select);
          if ($select_result->num_rows > 0) {
              if (isset($_POST['objets_glpi_assign'])) {
                  if ($_POST['objets_glpi_assign'] == "0") {
                      $_POST['objets_glpi_assign'] = NULL;
                  }
                  $update = "UPDATE glpi_plugin_formcreator_targetsconditions SET `objets_glpi_assign` = '" . $_POST['objets_glpi_assign'] . "' WHERE  plugin_formcreator_targets_id=" . $id . ";";
                  $DB->query($update);
              }
              if (isset($_POST['objets_glpi_observer'])) {
                  if ($_POST['objets_glpi_observer'] == "0") {
                      $_POST['objets_glpi_observer'] = NULL;
                  }
                  $update = "UPDATE glpi_plugin_formcreator_targetsconditions SET `objets_glpi_observer` = '" . $_POST['objets_glpi_observer'] . "' WHERE  plugin_formcreator_targets_id=" . $id . ";";
                  $DB->query($update);
              }
              if (isset($_POST['gr_conditions'])) {
                  if ($_POST['gr_conditions'] == "0") {
                      $_POST['gr_conditions'] = NULL;
                  }
                  $update = "UPDATE glpi_plugin_formcreator_targetsconditions SET `gr_conditions` = '" . $_POST['gr_conditions'] . "' WHERE  plugin_formcreator_targets_id=" . $id . ";";
                  $DB->query($update);
              }
              if (isset($_POST['gv_conditions'])) {
                  if ($_POST['gv_conditions'] == "0") {
                      $_POST['gv_conditions'] = NULL;
                  }
                  $update = "UPDATE glpi_plugin_formcreator_targetsconditions SET `gv_conditions` = '" . $_POST['gv_conditions'] . "' WHERE  plugin_formcreator_targets_id=" . $id . ";";
                  $DB->query($update);
              }
              if (isset($_POST['question_concernee_observer'])) {
                  if ($_POST['question_concernee_observer'] == "0") {
                      $_POST['quesFtion_concernee_observer'] = NULL;
                  }
                  $update = "UPDATE glpi_plugin_formcreator_targetsconditions SET `question_concernee_observer` = '" . $_POST['question_concernee_observer'] . "' WHERE  plugin_formcreator_targets_id=" . $id . ";";
                  $DB->query($update);
              }
              if (isset($_POST['question_concernee_assign'])) {
                  if ($_POST['question_concernee_assign'] == "0") {
                      $_POST['question_concernee_assign'] = NULL;
                  }
                  $update = "UPDATE glpi_plugin_formcreator_targetsconditions SET `question_concernee_assign` = '" . $_POST['question_concernee_assign'] . "' WHERE  plugin_formcreator_targets_id=" . $id . ";";
                  $DB->query($update);
              }
          } else {
              // insert
              if ($_POST['question_concernee_observer'] == "0") {
                  $_POST['question_concernee_observer'] = NULL;
              }
              if ($_POST['question_concernee_assign'] == "0") {
                  $_POST['question_concernee_assign'] = NULL;
              }
              if ($_POST['gv_conditions'] == "0") {
                  $_POST['gv_conditions'] = NULL;
              }
              if ($_POST['gr_conditions'] == "0") {
                  $_POST['gr_conditions'] = NULL;
              }
              if ($_POST['objets_glpi_observer'] == "0") {
                  $_POST['objets_glpi_observer'] = NULL;
              }
              if ($_POST['objets_glpi_assign'] == "0") {
                  $_POST['objets_glpi_assign'] = NULL;
              }
              $insert = "INSERT INTO glpi_plugin_formcreator_targetsconditions "
                      . "(plugin_formcreator_targets_id, question_concernee_assign, question_concernee_observer, "
                      . "objets_glpi_assign, objets_glpi_observer, gr_conditions, gv_conditions) "
                      . "VALUES (" . $id . ", '" . $_POST['question_concernee_assign'] . "','" . $_POST['question_concernee_observer'] . "','"
                      . $_POST['objets_glpi_assign'] . "','" . $_POST['objets_glpi_observer'] . "','"
                      . $_POST['gr_conditions'] . "','" . $_POST['gv_conditions'] . "');";
              $GLOBALS['DB']->query($insert);
          }
      }

      Html::back();

   } elseif (isset($_GET['delete_actor'])) {
      $targetTicket_actor = new PluginFormcreatorTargetTicket_Actor();
      $targetTicket_actor->delete(array(
            'id'                                   => (int) $_GET['delete_actor']
      ));
      Html::back();

   // Show target ticket form
   } else {
      Html::header(
         __('Form Creator', 'formcreator'),
         $_SERVER['PHP_SELF'],
         'admin',
         'PluginFormcreatorForm'
      );

      $itemtype = "PluginFormcreatorTargetTicket";
      $target   = new PluginFormcreatorTarget;
      $found    = $target->find("itemtype = '$itemtype' AND items_id = " . (int) $_REQUEST['id']);
      $first    = array_shift($found);
      $form     = new PluginFormcreatorForm;
      $form->getFromDB($first['plugin_formcreator_forms_id']);

      $_SESSION['glpilisttitle'][$itemtype] = sprintf(__('%1$s = %2$s'),
                                                      $form->getTypeName(1), $form->getName());
      $_SESSION['glpilisturl'][$itemtype]   = $form->getFormURL()."?id=".$form->getID();

      $targetticket->display($_REQUEST);

      Html::footer();
   }

// Or display a "Not found" error
} else {
   Html::displayNotFoundError();
}
