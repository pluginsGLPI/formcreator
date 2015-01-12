<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class multiSelectField extends PluginFormcreatorField
{
   // public static function show($field, $datas, $edit = true)
   // {
   //    $rand = mt_rand();

   //    $default_values = explode("\r\n", $field['default_values']);
   //    $default_values = (!empty($datas['formcreator_field_' . $field['id']]))
   //             ? is_array($datas['formcreator_field_' . $field['id']])
   //                ? $datas['formcreator_field_' . $field['id']]
   //                : explode(',', $datas['formcreator_field_' . $field['id']])
   //             : $default_values;

   //    if($field['required'])  $required = ' required';
   //    else $required = '';

   //    if (!$edit) {
   //       echo '<div class="form-group line' . ($field['order'] % 2) . '" id="form-group-field' . $field['id'] . '">';
   //       echo '<label>' . $field['name'] . '</label>';
   //       if (!empty($datas['formcreator_field_' . $field['id']])) {
   //          echo str_replace(',', ', ', trim($datas['formcreator_field_' . $field['id']], ','));
   //       }
   //       echo '</div>' . PHP_EOL;
   //       echo '<script type="text/javascript">formcreatorAddValueOf(' . $field['id'] . ', "' . addslashes(json_encode(explode(',', $datas['formcreator_field_' . $field['id']]))) . '");</script>';
   //       return;
   //    }

   //    echo '<div class="form-group' . $required . ' line' . ($field['order'] % 2) . '" id="form-group-field' . $field['id'] . '">';
   //       echo '<label>';
   //       echo  $field['name'];
   //       if($field['required'])  echo ' <span class="red">*</span>';
   //       echo '</label>';
   //       echo '<input type="hidden" name="formcreator_field_' . $field['id'] . '[]" value="" />';

   //       echo '<div class="form_field">';
   //       if(!empty($field['values'])) {
   //          $values         = explode("\r\n", $field['values']);
   //          $tab_values     = array();
   //          foreach ($values as $value) {
   //             if ((trim($value) != '')) $tab_values[$value] = $value;
   //          }

   //          if($field['show_empty'])
   //             array_unshift($values, array('' => '---'));

   //          Dropdown::showFromArray('formcreator_field_' . $field['id'], $tab_values, array(
   //             'values'   => $default_values,
   //             'multiple' => true,
   //             'size'     => 5,
   //             'rand'     => $rand,
   //          ));
   //       }
   //       echo '</div>' . PHP_EOL;
   //       $values = json_encode($default_values);
   //       echo '<script type="text/javascript">
   //                jQuery(document).ready(function($) {
   //                   jQuery("#dropdown_formcreator_field_' . $field['id'] . $rand . '").on("change", function(e) {
   //                      var selectedValues = jQuery("#dropdown_formcreator_field_' . $field['id'] . $rand . '").val();
   //                      formcreatorChangeValueOf (' . $field['id']. ', selectedValues);
   //                   });
   //                });
   //             </script>';
   //       echo '<script type="text/javascript">formcreatorAddValueOf(' . $field['id'] . ', "' . addslashes($values) . '");</script>';

   //       echo '<div class="help-block">' . html_entity_decode($field['description']) . '</div>';
   //    echo '</div>' . PHP_EOL;
   // }

   // public static function displayValue($value, $values)
   // {
   //    return ($value != '') ? str_replace(',', ', ', trim($value, ',')) : '';
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
   //    if($field['required'] && (count($value) - 1 == 0)) {
   //       Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $field['name'], false, ERROR);
   //       return false;

   //    // Min range not set or number of selected item lower than min
   //    } elseif (!empty($field['range_min']) && (count($value) - 1 < $field['range_min'])) {
   //       $message = sprintf(__('The following question needs of at least %d answers', 'formcreator'), $field['range_min']);
   //       Session::addMessageAfterRedirect($message . ' ' . $field['name'], false, ERROR);
   //       return false;

   //    // Max range not set or number of selected item greater than max
   //    } elseif (!empty($field['range_max']) && (count($value) - 1 > $field['range_max'])) {
   //       $message = sprintf(__('The following question does not accept more than %d answers', 'formcreator'), $field['range_max']);
   //       Session::addMessageAfterRedirect($message . ' ' . $field['name'], false, ERROR);
   //       return false;

   //    // All is OK
   //    } else {
   //       return true;
   //    }
   // }

   public static function getName()
   {
      return __('Multiselect', 'formcreator');
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
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['multiselect'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
