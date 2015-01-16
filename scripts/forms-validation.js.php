<?php
include ('../../../inc/includes.php');
header('Content-type: text/javascript');
?>

function validateForm(form) {
/*
   var requiredFields = document.getElementsByClassName('required');
   for (i = 0; i < requiredFields.length; i++) {
      if (requiredFields[i].value == "") {
         alert("<?php echo addslashes(__('A required field is empty:', 'formcreator')); ?>");
         requiredFields[i].focus();
         return false;
      }
   }
*/
   return true;
}
