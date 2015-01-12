<?php
require_once(realpath(dirname(__FILE__ ) . '/../../../../inc/includes.php'));
require_once('field.interface.php');

class ldapselectField extends PluginFormcreatorField
{
   // public static function show($field, $datas, $edit = true)
   // {
   //    $rand = mt_rand();

   //    $default_values = explode("\r\n", $field['default_values']);
   //    $default_value  = array_shift($default_values);
   //    $default_value = (!empty($datas['formcreator_field_' . $field['id']]))
   //             ? $datas['formcreator_field_' . $field['id']]
   //             : $default_value;

   //    if($field['required'])  $required = ' required';
   //    else $required = '';

   //    if (!$edit) {
   //       echo '<div class="form-group" id="form-group-field' . $field['id'] . '">';
   //       echo '<label>' . $field['name'] . '</label>';
   //       if (isset($datas['formcreator_field_' . $field['id']])) {
   //          $values = self::getValues($field['values'], $field['show_empty']);
   //          echo $values[$datas['formcreator_field_' . $field['id']]];
   //       } else {
   //          echo '&nbsp;';
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

   //       $tab_values = self::getValues($field['values'], $field['show_empty']);

   //       if (!empty($tab_values)) {
   //          echo '<div class="form_field">';
   //          Dropdown::showFromArray('formcreator_field_' . $field['id'],
   //                                  $tab_values,
   //                                  array(
   //                                     'value' => $default_value,
   //                                     'rand'  => $rand
   //                                  )
   //          );
   //          echo '</div>' . PHP_EOL;
   //       } else {
   //          echo '<b><i class="red">';
   //          echo __('Cannot recover LDAP informations!', 'formcreator');
   //          echo '</i></b>';
   //       }

   //       echo '<script type="text/javascript">
   //                jQuery(document).ready(function($) {
   //                   jQuery("#dropdown_formcreator_field_' . $field['id'] . $rand . '").on("select2-selecting", function(e) {
   //                      formcreatorChangeValueOf (' . $field['id']. ', e.val);
   //                   });
   //                });
   //             </script>';
   //       echo '<script type="text/javascript">formcreatorAddValueOf(' . $field['id'] . ', "' . $default_value . '");</script>';

   //       echo '<div class="help-block">' . html_entity_decode($field['description']) . '</div>';
   //    echo '</div>' . PHP_EOL;
   // }

   // public static function getValues($ldap_datas, $show_empty) {
   //    if (!empty($ldap_datas)) {
   //       $ldap_values   = json_decode($ldap_datas);
   //       $ldap_dropdown = new RuleRightParameter();
   //       $ldap_dropdown->getFromDB($ldap_values->ldap_attribute);
   //       $attribute     = array($ldap_dropdown->fields['value']);

   //       $config_ldap = new AuthLDAP();
   //       $config_ldap->getFromDB($ldap_values->ldap_auth);

   //       if (!function_exists('warning_handler')) {
   //          function warning_handler($errno, $errstr, $errfile, $errline, array $errcontext) {
   //             if (0 === error_reporting()) return false;
   //             throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
   //          }
   //       }
   //       set_error_handler("warning_handler", E_WARNING);

   //       try {
   //          $ds      = $config_ldap->connect();
   //          $sn      = ldap_search($ds, $config_ldap->fields['basedn'], $ldap_values->ldap_filter, $attribute);
   //          $entries = ldap_get_entries($ds, $sn);
   //          array_shift($entries);

   //          $tab_values = array();
   //          foreach($entries as $id => $attr) {
   //             if(isset($attr[$attribute[0]])
   //                && !in_array($attr[$attribute[0]][0], $tab_values)) {
   //                $tab_values[$id] = $attr[$attribute[0]][0];
   //             }
   //          }

   //          if($show_empty) $tab_values = array('' => '-----') + $tab_values;
   //          sort($tab_values);
   //          return $tab_values;
   //       } catch(Exception $e) {
   //          return array();
   //       }

   //       restore_error_handler();
   //    } else {
   //       return array();
   //    }
   // }

   // public static function displayValue($value, $values)
   // {
   //    if(!empty($values[$value])) {
   //       // $ldap_values = json_decode($values);

   //       // $ldap_dropdown = new RuleRightParameter();
   //       // $ldap_dropdown->getFromDB($ldap_values->ldap_attribute);
   //       // $attribute = array($ldap_dropdown->fields['value']);

   //       // $config_ldap = new AuthLDAP();
   //       // $config_ldap->getFromDB($ldap_values->ldap_auth);
   //       // $ds      = $config_ldap->connect();
   //       // $sn      = ldap_search($ds, $config_ldap->fields['basedn'], $ldap_values->ldap_filter, $attribute);
   //       // $entries = ldap_get_entries($ds, $sn);
   //       // array_shift($entries);

   //       // $tab_values = array();
   //       // foreach($entries as $id => $attr) {
   //       //    if(isset($attr[$attribute[0]])
   //       //       && !in_array($attr[$attribute[0]][0], $tab_values)) {
   //       //       $tab_values[$id] = $attr[$attribute[0]][0];
   //       //    }
   //       // }
   //       // sort($tab_values);

   //       return $values[$value];
   //    }
   //    return '';
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
         'glpi_objects'   => 0,
         'ldap_values'    => 1,
      );
   }

   public static function getJSFields()
   {
      $prefs = self::getPrefs();
      return "tab_fields_fields['ldapselect'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
