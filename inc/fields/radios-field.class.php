<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class radiosField extends PluginFormcreatorField
{
   // public static function show($field, $datas, $edit = true)
   // {
   //    $default_values = explode("\r\n", $field['default_values']);
   //    $default_value  = array_shift($default_values);
   //    $default_value = (!empty($datas['formcreator_field_' . $field['id']]))
   //             ? $datas['formcreator_field_' . $field['id']]
   //             : $default_value;

   //    if($field['required'])  $required = ' required';
   //    else $required = '';

   //    if (!$edit) {
   //       echo '<div class="form-group line' . ($field['order'] % 2) . '" id="form-group-field' . $field['id'] . '">';
   //       echo '<label>' . $field['name'] . '</label>';
   //       if (!empty($datas['formcreator_field_' . $field['id']])) {
   //          echo $datas['formcreator_field_' . $field['id']];
   //       }
   //       echo '</div>' . PHP_EOL;
   //       echo '<script type="text/javascript">formcreatorAddValueOf(' . $field['id'] . ', "' . $datas['formcreator_field_' . $field['id']] . '");</script>';
   //       return;
   //    }

   //    echo '<div class="form-group' . $required . ' line' . ($field['order'] % 2) . '" id="form-group-field' . $field['id'] . '">';
   //       echo '<label>';
   //       echo  $field['name'];
   //       if($field['required'])  echo ' <span class="red">*</span>';
   //       echo '</label>';

   //       echo '<input type="hidden" class="form-control"
   //                name="formcreator_field_' . $field['id'] . '" value="" />' . PHP_EOL;

   //       if(!empty($field['values'])) {
   //          $values         = explode("\r\n", $field['values']);

   //          echo '<div class="checkbox">';
   //          $i = 0;
   //          foreach ($values as $value) {
   //             if ((trim($value) != '')) {
   //                $i++;
   //                $checked = ($value == $default_value) ? ' checked' : '';
   //                echo '<input type="radio" class="form-control"
   //                      name="formcreator_field_' . $field['id'] . '"
   //                      id="formcreator_field_' . $field['id'] . '_' . $i . '"
   //                      value="' . addslashes($value) . '"
   //                      ' . $checked . ' /> ';
   //                echo '<label for="formcreator_field_' . $field['id'] . '_' . $i . '">';
   //                echo $value;
   //                echo '</label>';
   //                if($i != count($values)) echo '<br />';
   //             }
   //          }
   //          echo '</div>';
   //       }
   //       echo '<script type="text/javascript">
   //                jQuery(document).ready(function($) {
   //                   jQuery("input[name=\'formcreator_field_' . $field['id']. '\']").on("change", function() {
   //                      jQuery("input[name=\'formcreator_field_' . $field['id']. '\']").each(function() {
   //                         if (this.checked == true) {
   //                            formcreatorChangeValueOf (' . $field['id']. ', this.value);
   //                         }
   //                      });
   //                   });
   //                });
   //             </script>';
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
   //    if($field['required'] && empty($value)) {
   //       Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $field['name'], false, ERROR);
   //       return false;

   //    // All is OK
   //    } else {
   //       return true;
   //    }
   // }

   public static function getName()
   {
      return __('Radios', 'formcreator');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['radios'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
