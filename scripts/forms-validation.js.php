<?php

include ('../../../inc/includes.php');

header('Content-type: text/javascript');

?>

function validateForm(form) {
   var requiredFields = document.getElementsByClassName('required');
   for (i = 0; i < requiredFields.length; i++) {
      if (requiredFields[i].value == "") {
         alert("<?php echo addslashes(__('A required field is empty:', 'formcreator')); ?>");
         requiredFields[i].focus();
         return false;
      }
   }

   return true;
}

function showDescription(id, img){
   if(img.alt == "+") {
     img.alt = "-";
     img.src = "<?php echo $GLOBALS['CFG_GLPI']['root_doc']; ?>/pics/moins.png";
     document.getElementById("desc" + id).style.display = "table-row";
   } else {
     img.alt = "+";
     img.src = "<?php echo $GLOBALS['CFG_GLPI']['root_doc']; ?>/pics/plus.png";
     document.getElementById("desc" + id).style.display = "none";
   }
}