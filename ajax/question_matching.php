<?php

include ('../../../inc/includes.php');

if (!isset($_POST['type']) || !in_array($_POST['type'], array('add', 'delete'))
      || (!isset($_POST['question']) || (-1 == $_POST['question']))) {
   header('HTTP/1.1 400 Bad Request');
   echo __("Invalid parameters", 'formcreator');
   exit();
}

// Add a matching line
if ('add' == $_POST['type']) {
   if (!isset($_POST['id']) || (!isset($_POST['field']) ||(-1 == $_POST['field']))) {
      header('HTTP/1.1 400 Bad Request');
      echo __("Invalid parameters", 'formcreator');
      exit();
   }

   $question = new PluginFormcreatorQuestion();
   $question->getFromDB((int) $_POST['id']);

   if ('user' == $question->fields['fieldtype']) {
      $class = 'User';
   } elseif ('glpiselect' == $question->fields['fieldtype']
         && (!empty($question->fields['values']))) {
      $class = $question->fields['values'];
   } else {
      header('HTTP/1.1 400 Bad Request');
      echo __("Invalid object", 'formcreator');
      exit();
   }
   $obj           = new $class();
   $searchOptions = $obj->getSearchOptions();
   array_shift($searchOptions);

   $target = new PluginFormcreatorQuestion();
   $target->getFromDB((int) $_POST['question']);

   $query  = "INSERT INTO `glpi_plugin_formcreator_matching_questions` SET
                 `questions_id` = " . (int) $_POST['id'] . ",
                 `targets_id` = " . (int) $_POST['question'] . ",
                 `fields` = " . (int) $_POST['field'];
   if (!$GLOBALS['DB']->query($query)) {
      header('HTTP/1.1 400 Bad Request');
      echo __("Cannot insert the values or dupplicate entries.", 'formcreator');
      exit();
   } else {
      header('HTTP/1.1 200 OK');

      $obj = new stdClass();
      $obj->targets_id   = (int) $_POST['question'];
      $obj->targets_name = $target->fields['name'];
      $obj->fields_id    = (int) $_POST['field'];
      $obj->fields_name  = $searchOptions[(int) $_POST['field']]['name'];

      echo json_encode($obj);
   }

// Remove a matching line
} else {
   if (!isset($_POST['question']) || !isset($_POST['target'])) {
      header('HTTP/1.1 400 Bad Request');
      echo __("Invalid parameters", 'formcreator');
      exit();
   }

   $query  = "DELETE FROM `glpi_plugin_formcreator_matching_questions`
              WHERE `questions_id` = " . (int) $_POST['question'] . "
              AND `targets_id` = " . (int) $_POST['target'];
              Toolbox::logDebug($query);
   if (!$GLOBALS['DB']->query($query)) {
      header('HTTP/1.1 400 Bad Request');
      echo __("Cannot delete the values.", 'formcreator');
      exit();
   } else {
      header('HTTP/1.1 200 OK');

      $target = new PluginFormcreatorQuestion();
      $target->getFromDB((int) $_POST['target']);

      $obj = new stdClass();
      $obj->targets_id   = (int) $_POST['target'];
      $obj->targets_name = $target->fields['name'];

      echo json_encode($obj);
   }
}
