<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class datetimeField extends PluginFormcreatorField
{
   // public static function show($field, $datas, $edit = true)
   // {
   //    $rand = mt_rand();

   //    $value = (isset($datas['formcreator_field_' . $field['id']])
   //                 && ($datas['formcreator_field_' . $field['id']] != 'NULL'))
   //             ? $datas['formcreator_field_' . $field['id']]
   //             : $field['default_values'];

   //    if($field['required'])  $required = ' required';
   //    else $required = '';

   //    if (!$edit) {
   //       echo '<div class="form-group" id="form-group-field' . $field['id'] . '">';
   //       echo '<label>' . $field['name'] . '</label>';
   //       if (!empty($datas['formcreator_field_' . $field['id']])) {
   //          echo Html::convDateTime($datas['formcreator_field_' . $field['id']]);
   //       }
   //       echo '</div>' . PHP_EOL;echo '<script type="text/javascript">formcreatorAddValueOf(' . $field['id'] . ', "' . Html::convDateTime($datas['formcreator_field_' . $field['id']]) . '");</script>';
   //       return;
   //    }

   //    echo '<div class="form-group' . $required . '" id="form-group-field' . $field['id'] . '">';
   //       echo '<label>';
   //       echo  $field['name'];
   //       if($field['required'])  echo ' <span class="red">*</span>';
   //       echo '</label>';

   //       echo '<div>';
   //       Html::showDateTimeField('formcreator_field_' . $field['id'], array(
   //          'value' => $value,
   //          'rand'  => $rand,
   //       ));
   //       echo '</div>';
   //       echo '<script type="text/javascript">
   //                jQuery(document).ready(function($) {
   //                   $( "#showdate' . $rand . '" ).on("change", function() {
   //                      formcreatorChangeValueOf(' . $field['id'] . ', this.value);
   //                   });
   //                   $( "#resetdate' . $rand . '" ).on("click", function() {
   //                      formcreatorChangeValueOf(' . $field['id'] . ', "");
   //                   });
   //                });
   //             </script>';
   //       echo '<script type="text/javascript">formcreatorAddValueOf(' . $field['id'] . ', "' .$value . '");</script>';

   //       echo '<div class="help-block">' . html_entity_decode($field['description']) . '</div>';
   //    echo '</div>' . PHP_EOL;
   // }

   // public static function displayValue($value, $values)
   // {
   //    return Html::convDateTime($value);
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
      return __('Datetime', 'formcreator');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
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
      return "tab_fields_fields['datetime'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
