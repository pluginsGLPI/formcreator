<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class checkboxesField implements Field
{
   public static function show($field)
   {
      if($field['required'])  $required = ' required';
      else $required = '';

      echo '<div class="form-group' . $required . '" id="form-group-field' . $field['id'] . '">';
      echo '<label>';
      echo  $field['name'];
      if($field['required'])  echo ' <span class="red">*</span>';
      echo '</label>';

      if(!empty($field['values'])) {
         $values         = explode("\r\n", $field['values']);
         $default_values = explode("\r\n", $field['default_values']);

         echo '<div class="checkbox">';
         $i = 0;
         foreach($values as $value) {
            $i++;
            $checked = (in_array($value, $default_values)) ? ' checked' : '';
            echo '<input type="checkbox" class="form-control"
                  name="formcreator_field_' . $field['id'] . '[]"
                  id="formcreator_field_' . $field['id'] . '_' . $i . '"
                  value="' . addslashes($value) . '"
                  ' . $checked . ' /> ';
            echo '<label for="formcreator_field_' . $field['id'] . '_' . $i . '">';
            echo $value;
            echo '</label>';
            if($i != count($values)) echo '<br />';
         }
         echo '</div>';
      }

      echo '</div>' . PHP_EOL;
   }

	public static function isValid($field, $input)
   {
		return true;
	}

   public static function getName()
   {
      return __('Checkboxes', 'formcreator');
   }

   public static function getJSFields()
   {
      $prefs = array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
         'range'          => 1,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
      );
      return "tab_fields_fields['checkboxes'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
