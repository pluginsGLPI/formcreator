<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class descriptionField implements Field
{
   public static function show($field)
   {
      echo '<div class="description_field form-group" id="description-field">';
      echo html_entity_decode($field['description']);
      echo '</div>';
   }

   public static function isValid($field, $input)
   {
      return true;
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
