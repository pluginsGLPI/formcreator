<?php

header('Content-type: text/javascript');

// Parse inc/fields directory and include all specific validation rules
foreach(glob(dirname(dirname(__FILE__)) . '/inc/fields/*') as $filepath) {
   // Load *.class.php files and get the class name
   if(preg_match("/inc/fields.(.+)\.class.php/", $filepath, $matches)) {
      $classname = 'PluginFormcreatorField' . ucfirst($matches[1]);
      include_once($filepath);
      // If the install method exists, load it
      if(method_exists($classname, 'validationScripts')) {
         $classname::validationScripts();
      }
   }
}
