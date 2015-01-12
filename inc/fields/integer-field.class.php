<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class integerField extends PluginFormcreatorField
{
   // public static function show($field, $datas, $edit = true)
   // {
   //    $value = (!empty($datas['formcreator_field_' . $field['id']]))
   //             ? $datas['formcreator_field_' . $field['id']]
   //             : $field['default_values'];

   //    if($field['required'])  $required = ' required';
   //    else $required = '';

   //    if (!$edit) {
   //       echo '<div class="form-group" id="form-group-field' . $field['id'] . '">';
   //       echo '<label>' . $field['name'] . '</label>';
   //       if (!empty($datas['formcreator_field_' . $field['id']])) {
   //          echo $datas['formcreator_field_' . $field['id']];
   //       }
   //       echo '</div>' . PHP_EOL;
   //       echo '<script type="text/javascript">formcreatorAddValueOf(' . $field['id'] . ', "' . $datas['formcreator_field_' . $field['id']] . '");</script>';
   //       return;
   //    }

   //    echo '<div class="form-group' . $required . '" id="form-group-field' . $field['id'] . '">';
   //       echo '<label>';
   //       echo  $field['name'];
   //       if($field['required'])  echo ' <span class="red">*</span>';
   //       echo '</label>';

   //       echo '<input type="text" class="form-control"
   //                name="formcreator_field_' . $field['id'] . '"
   //                id="formcreator_field_' . $field['id'] . '"
   //                value="' . $value . '"' . $required . '
   //                onchange="formcreatorChangeValueOf(' . $field['id'] . ', this.value);" />';
   //       echo '<script type="text/javascript">formcreatorAddValueOf(' . $field['id'] . ', "' . $value . '");</script>';

   //       echo '<div class="help-block">' . html_entity_decode($field['description']) . '</div>';
   //    echo '</div>' . PHP_EOL;
   // }

   // public static function displayValue($value, $values)
   // {
   //    return $value;
   // }

   // public static function isValid($field, $value, $datas)
   // {
   //    // If the field are hidden, don't test it
   //    if (($field['show_type'] == 'hide') && isset($datas['formcreator_field_' . $field['show_field']])) {
   //       $hidden = true;

   //       switch ($field['show_condition']) {
   //          case 'notequal':
   //             if ($field['show_value'] != $datas['formcreator_field_' . $field['show_field']])
   //                $hidden = false;
   //             break;
   //          case 'lower':
   //             if ($field['show_value'] < $datas['formcreator_field_' . $field['show_field']])
   //                $hidden = false;
   //             break;
   //          case 'greater':
   //             if ($field['show_value'] > $datas['formcreator_field_' . $field['show_field']])
   //                $hidden = false;
   //             break;

   //          default:
   //             if ($field['show_value'] == $datas['formcreator_field_' . $field['show_field']])
   //                $hidden = false;
   //             break;
   //       }

   //       if ($hidden) return true;
   //    }

   //    // Not required or not empty
   //    if($field['required'] && ($value == '')) {
   //       Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $field['name'], false, ERROR);
   //       return false;

   //    // Not an integer
   //    } elseif ($value != '') {
   //       if (!ctype_digit($value)) {
   //          Session::addMessageAfterRedirect(__('This is not an integer:', 'formcreator') . ' ' . $field['name'], false, ERROR);
   //          return false;

   //       // Min range not set or text length longer than min length
   //       } elseif (!empty($field['range_min']) && ($value < $field['range_min'])) {
   //          $message = sprintf(__('The following number must be greater than %d:', 'formcreator'), $field['range_min']);
   //          Session::addMessageAfterRedirect($message . ' ' . $field['name'], false, ERROR);
   //          return false;

   //       // Max range not set or text length shorter than max length
   //       } elseif (!empty($field['range_max']) && ($value > $field['range_max'])) {
   //          $message = sprintf(__('The following number must be lower than %d:', 'formcreator'), $field['range_max']);
   //          Session::addMessageAfterRedirect($message . ' ' . $field['name'], false, ERROR);
   //          return false;

   //       // Specific format not set or well match
   //       } elseif (!empty($field['regex']) && !preg_match('/' . trim($field['regex']) . '/', $value)) {
   //          Session::addMessageAfterRedirect(__('Specific format does not match:', 'formcreator') . ' ' . $field['name'], false, ERROR);
   //          return false;

   //       // All is OK
   //       } else {
   //          return true;
   //       }
   //    }
   // }

   public static function getName()
   {
      return __('Integer', 'formcreator');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 0,
         'range'          => 1,
         'show_empty'     => 0,
         'regex'          => 1,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['integer'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
