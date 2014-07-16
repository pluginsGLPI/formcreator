   <?php

   $tab_fied_types = array();
   $tab_fied_types[''] = '---';

   foreach(glob('./inc/fields/*-field.class.php') as $class_file) {
      preg_match("/inc.fields.(.+)-field\.class.php/", $class_file, $matches);
      $classname = $matches[1] . 'Field';
      include_once($class_file);
      if(method_exists($classname, 'getName')) {
         $tab_fied_types[strtolower($matches[1])] = $classname::getName();
      }
   }
