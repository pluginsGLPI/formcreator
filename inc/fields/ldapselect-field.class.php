<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class ldapselectField implements Field
{
   public static function show($field, $datas)
   {
      if($field['required'])  $required = ' required';
      else $required = '';

      $hide = ($field['show_type'] == 'hide') ? ' style="display: none"' : '';
      echo '<div class="form-group' . $required . '" id="form-group-field' . $field['id'] . '"' . $hide . '>';
      echo '<label>';
      echo  $field['name'];
      if($field['required'])  echo ' <span class="red">*</span>';
      echo '</label>';

      if (!empty($field['values'])) {
         $ldap_values = json_decode($field['values']);
         $ldap_dropdown = new RuleRightParameter();
         $ldap_dropdown->getFromDB($ldap_values->ldap_attribute);
         $attribute = array($ldap_dropdown->fields['value']);

         $config_ldap = new AuthLDAP();
         $config_ldap->getFromDB($ldap_values->ldap_auth);

         if (!function_exists('warning_handler')) {
            function warning_handler($errno, $errstr, $errfile, $errline, array $errcontext) {
               if (0 === error_reporting()) return false;
               throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
         }
         set_error_handler("warning_handler", E_WARNING);

         try {
            $ds      = $config_ldap->connect();
            $sn      = ldap_search($ds, $config_ldap->fields['basedn'], $ldap_values->ldap_filter, $attribute);
            $entries = ldap_get_entries($ds, $sn);
            array_shift($entries);

            $tab_values = array();
            foreach($entries as $id => $attr) {
               if(isset($attr[$attribute[0]])
                  && !in_array($attr[$attribute[0]][0], $tab_values)) {
                  $tab_values[$id] = $attr[$attribute[0]][0];
               }
            }

            if($field['show_empty']) $tab_values = array('' => '-----') + $tab_values;
            sort($tab_values);
            Dropdown::showFromArray('formcreator_field_' . $field['id'], $tab_values);
         } catch(Exception $e) {
            echo '<b><i class="red">';
            echo __('Cannot recover LDAP informations!', 'formcreator');
            echo '</i></b>';
         }

         restore_error_handler();
      }

      echo '<div class="help-block">' . html_entity_decode($field['description']) . '</div>';

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
      if(!empty($values)) {
         $ldap_values = json_decode($values);

         $ldap_dropdown = new RuleRightParameter();
         $ldap_dropdown->getFromDB($ldap_values->ldap_attribute);
         $attribute = array($ldap_dropdown->fields['value']);

         $config_ldap = new AuthLDAP();
         $config_ldap->getFromDB($ldap_values->ldap_auth);
         $ds      = $config_ldap->connect();
         $sn      = ldap_search($ds, $config_ldap->fields['basedn'], $ldap_values->ldap_filter, $attribute);
         $entries = ldap_get_entries($ds, $sn);
         array_shift($entries);

         $tab_values = array();
         foreach($entries as $id => $attr) {
            if(isset($attr[$attribute[0]])
               && !in_array($attr[$attribute[0]][0], $tab_values)) {
               $tab_values[$id] = $attr[$attribute[0]][0];
            }
         }
      }
      return ($value != '') ? $tab_values[$value] : '';
   }

   public static function isValid($field, $value, $datas)
   {
      // Not required or not empty
      if($field['required'] && ($value == '')) {
         Session::addMessageAfterRedirect(__('A required field is empty:', 'formcreator') . ' ' . $field['name'], false, ERROR);
         return false;

      // All is OK
      } else {
         return true;
      }
   }

   public static function getName()
   {
      return __('LDAP Select', 'formcreator');
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
         'dropdown_value' => 0,
         'ldap_values'    => 1,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['ldapselect'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
