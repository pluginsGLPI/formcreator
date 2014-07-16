<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.class.php');

class dropdownField extends Field
{
   public function show()
   {
      echo '<div class="dropdown_field" id="dropdown_field_' . $this->_id . '">';

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
      return __('Dropdown', 'formcreator');
   }

   public static function getJSFields()
   {
      $prefs = array(
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 1,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 1,
      );
      return "tab_fields_fields['dropdown'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
