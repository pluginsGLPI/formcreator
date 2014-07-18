<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class textField implements Field
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

      echo '<input type="text" class="form-control"
               name="formcreator_field_' . $field['id'] . '"
               id="formcreator_field_' . $field['id'] . '"
               value="' . $field['id'] . '" />';
      echo '</div>' . PHP_EOL;
	}

	public static function isValid($field, $input)
   {
      // Not required or not empty
      if($field['required'] && empty($input['formcreator_field_' . $field['id']])) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $field['name']);
         return false;

      // Min range not set or text length longer than min length
      } elseif(!is_null($field['range_min']) && strlen($input['formcreator_field_' . $field['id']] < $field['range_min'])) {
         Session::addMessageAfterRedirect(__('The text is too short:', 'formcreator') . ' ' . $field['name']);
         return false;

      // Max range not set or text length shorter than max length
      } elseif(!is_null($field['range_max']) && strlen($input['formcreator_field_' . $field['id']] > $field['range_max'])) {
         Session::addMessageAfterRedirect(__('The text is too long:', 'formcreator') . ' ' . $field['name']);
         return false;

      // Specific format not set or well match
      } elseif(!is_null($field['regex']) && !preg_match($field['regex'], $input['formcreator_field_' . $field['id']])) {
         Session::addMessageAfterRedirect(__('Specific format does not match:', 'formcreator') . ' ' . $field['name']);
         return false;

      // All is OK
		} else {
			return true;
		}
	}

   public static function getName()
   {
      return __('Text', 'formcreator');
   }

   public static function getJSFields()
   {
      $prefs = array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 0,
         'range'          => 1,
         'show_empty'     => 0,
         'regex'          => 1,
         'show_type'      => 1,
         'dropdown_value' => 0,
      );
      return "tab_fields_fields['text'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
