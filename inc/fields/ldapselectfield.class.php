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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class PluginFormcreatorLdapselectField extends PluginFormcreatorSelectField
{
   public function getDesignSpecializationField() {
      $rand = mt_rand();

      $label = '<label for="dropdown_ldap_auth'.$rand.'">';
      $label .= _n('LDAP directory', 'LDAP directories', 1);
      $label .= '</label>';

      $ldap_values = json_decode(plugin_formcreator_decode($this->fields['values']), JSON_OBJECT_AS_ARRAY);
      if ($ldap_values === null) {
         $ldap_values = [];
      }
      $field = Dropdown::show('AuthLDAP', [
         'name'      => 'ldap_auth',
         'rand'      => $rand,
         'value'     => (isset($ldap_values['ldap_auth'])) ? $ldap_values['ldap_auth'] : '',
         'on_change' => 'plugin_formcreator_changeLDAP(this)',
         'display'   => false,
      ]);

      $additions = '<tr class="plugin_formcreator_question_specific">';
      $additions .= '<td>';
      $additions .= '<label for="ldap_filter">';
      $additions .= __('Filter', 'formcreator');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '<input type="text" name="ldap_filter" id="ldap_filter" style="width:98%;"'
           .'value="'.(isset($ldap_values['ldap_filter']) ? $ldap_values['ldap_filter'] : '').'" />';
      $additions .= '</td>';

      $additions .= '<td>';
      $additions .= '<label for="ldap_attribute">';
      $additions .= __('Attribute', 'formcreator');
      $additions .= '</label>';
      $additions .= '</td>';

      $additions .= '<td>';
      $additions .= Dropdown::show('RuleRightParameter', [
         'name'  => 'ldap_attribute',
         'rand'  => $rand,
         'value' => (isset($ldap_values['ldap_attribute'])) ? $ldap_values['ldap_attribute'] : '',
         'display' => false,
      ]);
      $additions .= '</td>';
      $additions .= '</tr>';
      $additions .= '<tr class="plugin_formcreator_question_specific">';
      $additions .= '<td>';
      $additions .= '</td>';
      $additions .= '<td>';
      $additions .= '</td>';
      $additions .= '<td colspan="2">&nbsp;</td>';
      $additions .= '</tr>';

      $common = $common = parent::getDesignSpecializationField();
      $additions .= $common['additions'];

      return [
         'label' => $label,
         'field' => $field,
         'additions' => $additions,
         'may_be_empty' => true,
         'may_be_required' => true,
      ];
   }

   public function getAvailableValues() {
      if (empty($this->fields['values'])) {
         return [];
      }

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
   }

   public static function getName() {
      return __('LDAP Select', 'formcreator');
   }

   public function serializeValue() {
      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = $value;
   }

   public function getValueForDesign() {
      return '';
   }

   public function isValid() {
      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '0') {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public function prepareQuestionInputForSave($input) {
      // Fields are differents for dropdown lists, so we need to replace these values into the good ones
      if (!isset($input['ldap_auth'])) {
         Session::addMessageAfterRedirect(__('LDAP directory not defined!', 'formcreator'), false, ERROR);
         return [];
      }

      $config_ldap = new AuthLDAP();
      $config_ldap->getFromDB($input['ldap_auth']);
      if ($config_ldap->isNewItem()) {
         Session::addMessageAfterRedirect(__('LDAP directory not found!', 'formcreator'), false, ERROR);
         return [];
      }

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

      return $input;
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

   public function parseAnswerValues($input) {
      $key = 'formcreator_field_' . $this->fields['id'];
      if (!isset($input[$key])) {
         $input[$key] = '';
      }
      if (!is_string($input[$key])) {
         return false;
      }

       $this->value = $input[$key];
       return true;
   }

   public function equals($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
   }

   public function notEquals($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
   }

   public function greaterThan($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
   }

   public function lessThan($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
   }

   public function isAnonymousFormCompatible() {
      return false;
   }
}
