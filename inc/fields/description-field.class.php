<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.class.php');

class descriptionField extends Field
{
   public function show()
   {
      echo '<div class="description_field" id="description-field">';
      echo $this->_value;
      echo '</div>';
   }

   public function isValid()
   {
      return true;
   }

   public function getPost()
   {
      return '';
   }

   public static function getName()
   {
      return __('Description', 'formcreator');
   }

   public static function getJSFields()
   {
      $prefs = array(
         'required'       => 0,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
      );
      return "tab_fields_fields['description'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
