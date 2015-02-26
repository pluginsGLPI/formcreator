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
      $target->update(array('id' => $found['id'], 'name' => $name));
      $targetticket->update($_POST);
      Html::back();

   } elseif (isset($_POST['actor_role'])) {
      Session::checkRight("entity", UPDATE);
      $id          = (int) $_POST['id'];
      $actor_value = isset($_POST['actor_value_' . $_POST['actor_type']])
                     ? $_POST['actor_value_' . $_POST['actor_type']]
                     : '';
      $use_notification = ($_POST['use_notification'] == 0) ? 0 : 1;
      $query = "INSERT INTO glpi_plugin_formcreator_targettickets_actors SET
                  `plugin_formcreator_targettickets_id` = $id,
                  `actor_role`                          = \"{$_POST['actor_role']}\",
                  `actor_type`                          = \"{$_POST['actor_type']}\",
                  `actor_value`                         = \"$actor_value\",
                  `use_notification`                    = \"$use_notification\"";
      $DB->query($query);
      Html::back();

   } elseif (isset($_GET['delete_actor'])) {
      $query = "DELETE FROM glpi_plugin_formcreator_targettickets_actors
                WHERE id = " . (int) $_GET['delete_actor'];
      $DB->query($query);
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
      $found    = $target->find("itemtype = '$itemtype' AND items_id = '" . $_REQUEST['id'] . "'");
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
