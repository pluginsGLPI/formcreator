<?php
class PluginFormcreatorLdapselectField extends PluginFormcreatorSelectField
{
   public function getAvailableValues() {
      if (!empty($this->fields['values'])) {
         $ldap_values   = json_decode(plugin_formcreator_decode($this->fields['values']));
         $ldap_dropdown = new RuleRightParameter();
         if (!$ldap_dropdown->getFromDB($ldap_values->ldap_attribute)) {
            return array();
         }
         $attribute     = array($ldap_dropdown->fields['value']);

         $config_ldap = new AuthLDAP();
         if (!$config_ldap->getFromDB($ldap_values->ldap_auth)) {
            return array();
         }

         if (!function_exists('warning_handler')) {
            function warning_handler($errno, $errstr, $errfile, $errline, array $errcontext) {
               if (0 === error_reporting()) {
                  return false;
               }
               throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
         }
         set_error_handler("warning_handler", E_WARNING);

         try {
            $tab_values = array();

            $ds      = $config_ldap->connect();
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

            $cookie = '';
            do {
               if (AuthLDAP::isLdapPageSizeAvailable($config_ldap)) {
                  ldap_control_paged_result($ds, $config_ldap->fields['pagesize'], true, $cookie);
               }

               $result  = ldap_search($ds, $config_ldap->fields['basedn'], $ldap_values->ldap_filter, $attribute);
               $entries = ldap_get_entries($ds, $result);
               array_shift($entries);

               foreach ($entries as $id => $attr) {
                  if (isset($attr[$attribute[0]])
                     && !in_array($attr[$attribute[0]][0], $tab_values)) {
                     $tab_values[$id] = $attr[$attribute[0]][0];
                  }
               }

               if (AuthLDAP::isLdapPageSizeAvailable($config_ldap)) {
                  ldap_control_paged_result_response($ds, $result, $cookie);
               }

            } while ($cookie !== null && $cookie != '');

            asort($tab_values);
            return $tab_values;
         } catch (Exception $e) {
            return array();
         }

         restore_error_handler();
      } else {
         return array();
      }
   }

   public static function getName() {
      return __('LDAP Select', 'formcreator');
   }

   public function getAnswer() {
      $values = $this->getAvailableValues();
      $value  = $this->getValue();
      return in_array($value, $values) ? $value : $this->fields['default_values'];
   }

   public static function getPrefs() {
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

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['ldapselect'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
