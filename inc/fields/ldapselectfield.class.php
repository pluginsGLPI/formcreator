<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class PluginFormcreatorLdapselectField extends PluginFormcreatorSelectField
{
   public function getAvailableValues() {
      if (!empty($this->fields['values'])) {
         $ldap_values   = json_decode(plugin_formcreator_decode($this->fields['values']));
         $ldap_dropdown = new RuleRightParameter();
         if (!$ldap_dropdown->getFromDB($ldap_values->ldap_attribute)) {
            return [];
         }
         $attribute     = [$ldap_dropdown->fields['value']];

         $config_ldap = new AuthLDAP();
         if (!$config_ldap->getFromDB($ldap_values->ldap_auth)) {
            return [];
         }

         set_error_handler('plugin_formcreator_ldap_warning_handler', E_WARNING);

         try {
            $tab_values = [];

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
            return [];
         }

         restore_error_handler();
      } else {
         return [];
      }
   }

   public static function getName() {
      return __('LDAP Select', 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      // Fields are differents for dropdown lists, so we need to replace these values into the good ones
      if (isset($input['ldap_auth'])
          && !empty($input['ldap_auth'])) {

         $config_ldap = new AuthLDAP();
         $config_ldap->getFromDB($input['ldap_auth']);

         if (!empty($input['ldap_attribute'])) {
            $ldap_dropdown = new RuleRightParameter();
            $ldap_dropdown->getFromDB($input['ldap_attribute']);
            $attribute     = [$ldap_dropdown->fields['value']];
         } else {
            $attribute     = [];
         }

         set_error_handler('plugin_formcreator_ldap_warning_handler', E_WARNING);

         try {
            $ds            = $config_ldap->connect();
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_control_paged_result($ds, 1);
            $sn            = ldap_search($ds, $config_ldap->fields['basedn'], $input['ldap_filter'], $attribute);
            $entries       = ldap_get_entries($ds, $sn);
         } catch (Exception $e) {
            Session::addMessageAfterRedirect(__('Cannot recover LDAP informations!', 'formcreator'), false, ERROR);
         }

         restore_error_handler();

         $input['values'] = json_encode([
            'ldap_auth'      => $input['ldap_auth'],
            'ldap_filter'    => $input['ldap_filter'],
            'ldap_attribute' => strtolower($input['ldap_attribute']),
         ]);
      }
      return $input;
   }

   public function getAnswer() {
      $values = $this->getAvailableValues();
      $value  = $this->getValue();
      return in_array($value, $values) ? $value : $this->fields['default_values'];
   }

   public static function getPrefs() {
      return [
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
      ];
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['ldapselect'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }
}
