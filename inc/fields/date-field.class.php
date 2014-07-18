<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class dateField implements Field
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

      Html::showDateTimeField('formcreator_field_' . $field['id'], array(
         'value'   => (empty($field['default_values'])) ? date(('Y-m-d H:i:s')) : $field['default_values'],
         'mindate' => $field['range_min'],
         'maxdate' => $field['range_max'],
      ));

      echo '</div>' . PHP_EOL;
   }

   public static function isValid($field, $input)
   {
      return true;
   }

   public static function getName()
   {
      return __('Date', 'formcreator');
   }

   public static function getJSFields()
   {
      $prefs = array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 0,
         'range'          => 1,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
      );
      return "tab_fields_fields['date'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
