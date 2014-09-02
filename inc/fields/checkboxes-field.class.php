<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class checkboxesField implements Field
{
   public static function show($field, $datas)
   {
      $default_values = explode("\r\n", $field['default_values']);
      $default_values = (!empty($datas['formcreator_field_' . $field['id']]))
               ? $datas['formcreator_field_' . $field['id']]
               : $default_values;

      if($field['required'])  $required = ' required';
      else $required = '';

      $hide = ($field['show_type'] == 'hide') ? ' style="display: none"' : '';
      echo '<div class="form-group' . $required . '" id="form-group-field' . $field['id'] . '"' . $hide . '>';
      echo '<label>';
      echo  $field['name'];
      if($field['required'])  echo ' <span class="red">*</span>';
      echo '</label>';

      if(!empty($field['values'])) {
         $values         = explode("\r\n", $field['values']);

         echo '<div class="checkbox">';

         echo '<input type="hidden" class="form-control"
                  name="formcreator_field_' . $field['id'] . '" value="" />' . PHP_EOL;

         $i = 0;
         foreach ($values as $value) {
            if (trim($value) != '') {
               $i++;
               $checked = (in_array($value, $default_values)) ? ' checked' : '';
               echo '<input type="checkbox" class="form-control"
                        name="formcreator_field_' . $field['id'] . '[]"
                        id="formcreator_field_' . $field['id'] . '_' . $i . '"
                        value="' . addslashes($value) . '"
                        ' . $checked . ' /> ' . PHP_EOL;
               echo '<label for="formcreator_field_' . $field['id'] . '_' . $i . '">';
               echo $value;
               echo '</label>' . PHP_EOL;
               if($i != count($values)) echo '<br />';
            }
         }

         switch ($field['show_condition']) {
            case 'notequal':
               $condition = '!=';
               break;
            case 'lower':
               $condition = '<';
               break;
            case 'greater':
               $condition = '>';
               break;

            default:
               $condition = '==';
               break;
         }

         if ($field['show_type'] == 'hide') {
            echo '<script type="text/javascript">
                     document.getElementsByName("formcreator_field_' . $field['show_field'] . '")[0].addEventListener("change", function(){showFormGroup' . $field['id'] . '()});
                     function showFormGroup' . $field['id'] . '() {
                        var field_value = document.getElementsByName("formcreator_field_' . $field['show_field'] . '")[0].value;

                        if(field_value ' . $condition . ' "' . $field['show_value'] . '") {
                           document.getElementById("form-group-field' . $field['id'] . '").style.display = "block";
                        } else {
                           document.getElementById("form-group-field' . $field['id'] . '").style.display = "none";
                        }
                     }
                     showFormGroup' . $field['id'] . '();
                  </script>';
         }

         echo '</div>' . PHP_EOL;

      }

      echo '<div class="help-block">' . html_entity_decode($field['description']) . '</div>';

      echo '</div>' . PHP_EOL;
   }

   public static function displayValue($value, $values)
   {
      return implode(', ', $value);
   }

	public static function isValid($field, $value)
   {
      // Not required or not empty
      if($field['required'] && empty($value)) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $field['name'], false, ERROR);
         return false;

      // Min range not set or number of selected item lower than min
      } elseif (!empty($field['range_min']) && (count($value) < $field['range_min'])) {
         $message = sprintf(__('The following question needs of at least %d answers', 'formcreator'), $field['range_min']);
         Session::addMessageAfterRedirect($message . ' ' . $field['name'], false, ERROR);
         return false;

      // Max range not set or number of selected item greater than max
      } elseif (!empty($field['range_max']) && (count($value) > $field['range_max'])) {
         $message = sprintf(__('The following question does not accept more than %d answers', 'formcreator'), $field['range_max']);
         Session::addMessageAfterRedirect($message . ' ' . $field['name'], false, ERROR);
         return false;

      // All is OK
      } else {
         return true;
      }
	}

   public static function getName()
   {
      return __('Checkboxes', 'formcreator');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
         'range'          => 1,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['checkboxes'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
