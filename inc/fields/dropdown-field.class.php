<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class dropdownField implements Field
{
   public static function show($field, $datas)
   {
      $default_values = explode("\r\n", $field['default_values']);
      $default_value  = array_shift($default_values);
      $default_value = (!empty($datas['formcreator_field_' . $field['id']]))
               ? $datas['formcreator_field_' . $field['id']]
               : $default_value;

      if($field['required'])  $required = ' required';
      else $required = '';

      $hide = ($field['show_type'] == 'hide') ? ' style="display: none"' : '';
      echo '<div class="form-group' . $required . '" id="form-group-field' . $field['id'] . '"' . $hide . '>';
      echo '<label>';
      echo  $field['name'];
      if($field['required'])  echo ' <span class="red">*</span>';
      echo '</label>';

      if(!empty($field['values'])) {
         Dropdown::show($field['values'], array(
            'name'                => 'formcreator_field_' . $field['id'],
            'value'               => $default_value,
            'comments'            => false,
            'display_emptychoice' => $field['show_empty'],
         ));
      }

      echo PHP_EOL . '<div class="help-block">' . html_entity_decode($field['description']) . '</div>' . PHP_EOL;

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
         $conditionnalField = new PluginFormcreatorQuestion();
         $conditionnalField->getFromDB($field['show_field']);

         switch ($conditionnalField->fields['fieldtype']) {
            case 'checkboxes' :
               echo '<script type="text/javascript">
                  var inputElements = document.getElementsByName("formcreator_field_' . $field['show_field'] . '[]");

                  for(var i=0; inputElements[i]; ++i) {
                     inputElements[i].addEventListener("change", function(){showFormGroup' . $field['id'] . '()});
                  }

                  function showFormGroup' . $field['id'] . '() {
                     var checkedValue = false;

                     for(var i=0; inputElements[i]; ++i) {
                        if (inputElements[i].value ' . $condition . ' ' . $field['show_value'] . ' && inputElements[i].checked) {
                           checkedValue = true;
                        }
                     }

                     if(checkedValue) {
                        document.getElementById("form-group-field' . $field['id'] . '").style.display = "block";
                     } else {
                        document.getElementById("form-group-field' . $field['id'] . '").style.display = "none";
                     }
                  }
                  showFormGroup' . $field['id'] . '();
               </script>';
               break;
            case 'multiselect' :
               echo '<script type="text/javascript">
                  var inputElements = document.getElementsByName("formcreator_field_' . $field['show_field'] . '[]")[1];
                  inputElements.addEventListener("change", function(){showFormGroup' . $field['id'] . '()});

                  function showFormGroup' . $field['id'] . '() {
                     var checkedValue = false;

                     for(var i=0; inputElements[i]; ++i) {
                        if (inputElements[i].value ' . $condition . ' ' . $field['show_value'] . ' && inputElements[i].selected) {
                           checkedValue = true;
                        }
                     }

                     if(checkedValue) {
                        document.getElementById("form-group-field' . $field['id'] . '").style.display = "block";
                     } else {
                        document.getElementById("form-group-field' . $field['id'] . '").style.display = "none";
                     }
                  }
                  showFormGroup' . $field['id'] . '();
               </script>';
               break;
            case 'radios' :
               echo '<script type="text/javascript">
                  var inputElements = document.getElementsByName("formcreator_field_' . $field['show_field'] . '");

                  for(var i=0; inputElements[i]; ++i) {
                     inputElements[i].addEventListener("change", function(){showFormGroup' . $field['id'] . '()});
                  }

                  function showFormGroup' . $field['id'] . '() {
                     var checkedValue = false;

                     for(var i=0; inputElements[i]; ++i) {
                        if (inputElements[i].value ' . $condition . ' ' . $field['show_value'] . ' && inputElements[i].checked) {
                           checkedValue = true;
                        }
                     }

                     if(checkedValue) {
                        document.getElementById("form-group-field' . $field['id'] . '").style.display = "block";
                     } else {
                        document.getElementById("form-group-field' . $field['id'] . '").style.display = "none";
                     }
                  }
                  showFormGroup' . $field['id'] . '();
               </script>';
               break;
            default :
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
      }

      echo '</div>' . PHP_EOL;
   }

   public static function displayValue($value, $values)
   {
      return Dropdown::getDropdownName(getTableForItemtype($values), $value);
   }

   public static function isValid($field, $value, $datas)
   {
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
      } else {
         $hidden = false;
      }

      // Not required or not empty
      if($field['required'] && empty($value) && !$hidden) {
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
         'ldap_values'    => 0,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['dropdown'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
