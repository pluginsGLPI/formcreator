<?php

include ('../../../inc/includes.php');

header('Content-type: text/javascript');

// Parse inc/fields directory and include all specific validation rules
// foreach (glob(dirname(dirname(__FILE__)) . '/inc/fields/*') as $filepath) {
//    // Load *.class.php files and get the class name
//    if (preg_match("/inc/fields.(.+)\.class.php/", $filepath, $matches)) {
//       $classname = 'PluginFormcreatorField' . ucfirst($matches[1]);
//       include_once($filepath);
//       // If the install method exists, load it
//       if (method_exists($classname, 'validationScripts')) {
//          $classname::validationScripts();
//       }
//    }
// }
?>

function validateForm(form) {
   var requiredFields = document.getElementsByClassName('required');
   for (i = 0; i < requiredFields.length; i++) {
      if (requiredFields[i].value == "") {
         alert("<?php echo addslashes(__('A required field is empty', 'formcreator')); ?>");
         requiredFields[i].focus();
         return false;
      }
   }

   return true;
}
