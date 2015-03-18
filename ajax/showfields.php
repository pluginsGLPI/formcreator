<?php
include ('../../../inc/includes.php');

$currentValues  = json_decode(stripslashes($_POST['values']), true);
foreach ($currentValues as &$value) {
   if (is_array($value)) {
      foreach ($value as &$sub_value) {
         if (Toolbox::seems_utf8($sub_value)) $sub_value = Toolbox::decodeFromUtf8($sub_value);
         $sub_value = str_replace("'", "&apos;", htmlentities(html_entity_decode(str_replace("&apos;", "'", $sub_value))));
      }
   } elseif (is_array(json_decode($value))) {
      $tab = json_decode($value);
      foreach ($tab as &$sub_value) {
         if (Toolbox::seems_utf8($sub_value)) $sub_value = Toolbox::decodeFromUtf8($sub_value);
         $sub_value = str_replace("'", "&apos;", htmlentities(html_entity_decode(str_replace("&apos;", "'", $sub_value))));
      }
      $value = json_encode($tab);
   } else {
         if (Toolbox::seems_utf8($value)) $value = Toolbox::decodeFromUtf8($value);
      $value = str_replace("'", "&apos;", htmlentities(html_entity_decode(str_replace("&apos;", "'", $value))));
   }
}
$questionToShow = array();
foreach ($currentValues as $id => $value) {
   $questionToShow[$id] = PluginFormcreatorFields::isVisible($id, $currentValues);
}
echo json_encode($questionToShow);
