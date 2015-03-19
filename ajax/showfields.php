<?php
include ('../../../inc/includes.php');

$currentValues  = json_decode(stripslashes($_POST['values']), true);
foreach ($currentValues as &$value) {
   if (is_array($value)) {
      foreach ($value as &$sub_value) {
         $sub_value = plugin_formcreator_encode($sub_value);
      }
   } elseif (is_array(json_decode($value))) {
      $tab = json_decode($value);
      foreach ($tab as &$sub_value) {
         $sub_value = plugin_formcreator_encode($sub_value);
      }
      $value = json_encode($tab);
   } else {
      $value = plugin_formcreator_encode($value);
   }
}
$questionToShow = array();
foreach ($currentValues as $id => $value) {
   $questionToShow[$id] = PluginFormcreatorFields::isVisible($id, $currentValues);
}
echo json_encode($questionToShow);
