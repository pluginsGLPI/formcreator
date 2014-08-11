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

      if(!empty($field['values'])) {
         $ldap_values = json_decode($field['values']);
         $config_ldap = new AuthLDAP();
         $config_ldap->getFromDB($ldap_values->ldap_auth);
         $ds      = $config_ldap->connect();
         $sn      = ldap_search($ds, $config_ldap->fields['basedn'], $ldap_values->ldap_filter, array($ldap_values->ldap_attribute));
         $entries = ldap_get_entries($ds, $sn);
         array_shift($entries);

         $tab_values = array();
         foreach($entries as $id => $attribute) {
            if(isset($attribute[$ldap_values->ldap_attribute])
               && !in_array($attribute[$ldap_values->ldap_attribute][0], $tab_values)) {
               $tab_values[$id] = $attribute[$ldap_values->ldap_attribute][0];
            }
         }

         if($field['show_empty']) $tab_values = array('' => '-----') + $tab_values;
         Dropdown::showFromArray('formcreator_field_' . $field['id'], $tab_values);
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

   public static function displayValue($value, $values)
   {
      if(!empty($values)) {
         $ldap_values = json_decode($values);
         $config_ldap = new AuthLDAP();
         $config_ldap->getFromDB($ldap_values->ldap_auth);
         $ds      = $config_ldap->connect();
         $sn      = ldap_search($ds, $config_ldap->fields['basedn'], $ldap_values->ldap_filter, array($ldap_values->ldap_attribute));
         $entries = ldap_get_entries($ds, $sn);
         array_shift($entries);

         $tab_values = array();
         foreach($entries as $id => $attribute) {
            if(isset($attribute[$ldap_values->ldap_attribute])
               && !in_array($attribute[$ldap_values->ldap_attribute][0], $tab_values)) {
               $tab_values[$id] = $attribute[$ldap_values->ldap_attribute][0];
            }
         }
      }
      return $tab_values[$value];
   }

   public static function isValid($field, $value)
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
