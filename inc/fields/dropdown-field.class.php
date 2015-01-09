<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class dropdownField implements Field
{
   public static function show($field, $datas, $edit = true)
   {
      $rand = mt_rand();

      $default_values = explode("\r\n", $field['default_values']);
      $default_value  = array_shift($default_values);
      $default_value = (!empty($datas['formcreator_field_' . $field['id']]))
               ? $datas['formcreator_field_' . $field['id']]
               : $default_value;

      if($field['required'])  $required = ' required';
      else $required = '';

      if (!$edit) {
         echo '<div class="form-group" id="form-group-field' . $field['id'] . '">';
         echo '<label>' . $field['name'] . '</label>';
         if (!empty($datas['formcreator_field_' . $field['id']])) {
            echo self::displayValue($datas['formcreator_field_' . $field['id']], $field['values']);
         }
         echo '</div>' . PHP_EOL;
         echo '<script type="text/javascript">formcreatorAddValueOf(' . $field['id'] . ', "' . $datas['formcreator_field_' . $field['id']] . '");</script>';
         return;
      }

      echo '<div class="form-group liste' . $required . '" id="form-group-field' . $field['id'] . '">';
         echo '<label>';
         echo  $field['name'];
         if($field['required'])  echo ' <span class="red">*</span>';
         echo '</label>';

         echo '<div class="form_field">';
         if(!empty($field['values'])) {
            if ($field['values'] == 'User') {
               User::dropdown(array(
                  'name'                => 'formcreator_field_' . $field['id'],
                  'value'               => $default_value,
                  'comments'            => false,
                  'right'               => 'all',
                  'display_emptychoice' => $field['show_empty'],
                  'rand'                => $rand,
               ));
            } else {
               Dropdown::show($field['values'], array(
                  'name'                => 'formcreator_field_' . $field['id'],
                  'value'               => $default_value,
                  'comments'            => false,
                  'display_emptychoice' => $field['show_empty'],
                  'rand'                => $rand,
               ));
            }
         }
         echo '</div>' . PHP_EOL;
         echo '<script type="text/javascript">
                  jQuery(document).ready(function($) {
                     jQuery("#dropdown_formcreator_field_' . $field['id'] . $rand . '").on("select2-selecting", function(e) {
                        formcreatorChangeValueOf (' . $field['id']. ', e.val);
                     });
                  });
               </script>';
         echo '<script type="text/javascript">formcreatorAddValueOf(' . $field['id'] . ', "' . $default_value . '");</script>';

         echo PHP_EOL . '<div class="help-block">' . html_entity_decode($field['description']) . '</div>' . PHP_EOL;
      echo '</div>' . PHP_EOL;
   }

   public static function displayValue($value, $values)
   {
      if ($values == 'User') {
         return getUserName($value);
      } else {
         return Dropdown::getDropdownName(getTableForItemType($values), $value);
      }
   }

   public static function isValid($field, $value, $datas)
   {
      // If the field are hidden, don't test it
      if (($field['show_type'] == 'hide') && isset($datas['formcreator_field_' . $field['show_field']])) {
         $hidden = true;

         switch ($field['show_condition']) {
            case 'notequal':
               if ($field['show_value'] != $datas['formcreator_field_' . $field['show_field']])
                  $hidden = false;
               break;
            case 'lower':
               if ($field['show_value'] < $datas['formcreator_field_' . $field['show_field']])
                  $hidden = false;
               break;
            case 'greater':
               if ($field['show_value'] > $datas['formcreator_field_' . $field['show_field']])
                  $hidden = false;
               break;

            default:
               if ($field['show_value'] == $datas['formcreator_field_' . $field['show_field']])
                  $hidden = false;
               break;
         }

         if ($hidden) return true;
      }

      // Not required or not empty
      if($field['required'] && empty($value)) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $field['name'], false, ERROR);
         return false;

      // All is OK
      } else {
         return true;
      }
   }

   public static function getName()
   {
      return _n('Dropdown', 'Dropdowns', 1);
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 1,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 1,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['dropdown'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
