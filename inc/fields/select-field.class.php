<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class selectField implements Field
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
            echo $datas['formcreator_field_' . $field['id']];
         }
         echo '</div>' . PHP_EOL;
         echo '<script type="text/javascript">formcreatorAddValueOf(' . $field['id'] . ', "' . addslashes($datas['formcreator_field_' . $field['id']]) . '");</script>';
         return;
      }

      echo '<div class="form-group' . $required . '" id="form-group-field' . $field['id'] . '">';
         echo '<label>';
         echo  $field['name'];
         if($field['required'])  echo ' <span class="red">*</span>';
         echo '</label>';

         echo '<div class="form_field">';
         if(!empty($field['values'])) {
            $values         = explode("\r\n", $field['values']);
            $tab_values     = array();
            foreach ($values as $value) {
               if ((trim($value) != '')) $tab_values[$value] = $value;
            }

            if($field['show_empty'])
               array_unshift($values, array('' => '---'));

            if($field['show_empty']) $tab_values = array('' => '-----') + $tab_values;
            Dropdown::showFromArray('formcreator_field_' . $field['id'], $tab_values, array(
               'value'     => $default_value,
               'rand'      => $rand,
            ));
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

         echo '<div class="help-block">' . html_entity_decode($field['description']) . '</div>';
      echo '</div>' . PHP_EOL;
   }

   public static function displayValue($value, $values)
   {
      return $value;
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
      return __('Select', 'formcreator');
   }

   public static function getPrefs()
   {
      return array(
         'required'       => 1,
         'default_values' => 1,
         'values'         => 1,
         'range'          => 0,
         'show_empty'     => 1,
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
      return "tab_fields_fields['select'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
