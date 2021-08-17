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
 * @copyright Copyright © 2011 - 2020 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

namespace GlpiPlugin\Formcreator\Field;

use PluginFormcreatorAbstractField;
use AuthLDAP;
use Dropdown;
use Exception;
use Html;
use Session;
use RuleRightParameter;

class LdapselectField extends SelectField
{
   public function getDesignSpecializationField(): array {
      $rand = mt_rand();

      $label = '<label for="dropdown_ldap_auth' . $rand . '">';
      $label .= _n('LDAP directory', 'LDAP directories', 1);
      $label .= '</label>';

      $ldap_values = json_decode(plugin_formcreator_decode($this->question->fields['values']), JSON_OBJECT_AS_ARRAY);
      if ($ldap_values === null) {
         $ldap_values = [];
      }
      $current_entity = $_SESSION['glpiactive_entity'];
      $auth_ldap_condition = '';
      if ($current_entity != 0) {
         $auth_ldap_condition = "glpi_authldaps.id = (select glpi_entities.authldaps_id from glpi_entities where id=${current_entity})";
      }
      $field = Dropdown::show(AuthLDAP::class, [
         'name'      => 'ldap_auth',
         'condition' => $auth_ldap_condition,
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
      $additions .= Html::input('ldap_filter', [
         'id'     => 'ldap_filter',
         'value'  => (isset($ldap_values['ldap_filter'])) ? $ldap_values['ldap_filter'] : '',
      ]);
      $additions .= '</td>';

      $additions .= '<td>';
      $additions .= '<label for="ldap_attribute">';
      $additions .= __('Attribute', 'formcreator');
      $additions .= '</label>';
      $additions .= '</td>';

      $additions .= '<td>';
      $additions .= Dropdown::show('RuleRightParameter', [
         'name'    => 'ldap_attribute',
         'rand'    => $rand,
         'value'   => (isset($ldap_values['ldap_attribute'])) ? $ldap_values['ldap_attribute'] : '',
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

      $common = PluginFormcreatorAbstractField::getDesignSpecializationField();
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
      if (empty($this->question->fields['values'])) {
         return [];
      }

      $ldap_values   = json_decode(plugin_formcreator_decode($this->question->fields['values']));
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

         $cookie = '';
         $ds = $config_ldap->connect();
         ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
         do {
            if (AuthLDAP::isLdapPageSizeAvailable($config_ldap)) {
               // phpcs:ignore Generic.PHP.DeprecatedFunctions
               if (version_compare(PHP_VERSION, '7.3') < 0) {
                  // phpcs:ignore Generic.PHP.DeprecatedFunctions
                  ldap_control_paged_result($ds, $config_ldap->fields['pagesize'], true, $cookie);
                  $result = ldap_search($ds, $config_ldap->fields['basedn'], $ldap_values->ldap_filter, $attribute);
               } else {
                  $controls = [
                     [
                        'oid'        => LDAP_CONTROL_PAGEDRESULTS,
                        'iscritical' => true,
                        'value'      => [
                           'size'    => $config_ldap->fields['pagesize'],
                           'cookie'  => $cookie
                        ]
                     ]
                  ];
                  $result = ldap_search($ds, $config_ldap->fields['basedn'], $ldap_values->ldap_filter, $attribute, 0, -1, -1, LDAP_DEREF_NEVER, $controls);
                  ldap_parse_result($ds, $result, $errcode, $matcheddn, $errmsg, $referrals, $controls);
                  $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'] ?? '';
               }
            } else {
               $result  = ldap_search($ds, $config_ldap->fields['basedn'], $ldap_values->ldap_filter, $attribute);
            }

            $limitexceeded = false;
            if (in_array(ldap_errno($ds), [4, 11])) {
               // openldap return 4 for Size limit exceeded
               $limitexceeded = true;
            }

            $entries = ldap_get_entries($ds, $result);
            if (in_array(ldap_errno($ds), [4, 11])) {
               // openldap return 4 for Size limit exceeded
               $limitexceeded = true;
            }

            if ($limitexceeded) {
               Session::addMessageAfterRedirect(__('LDAP size limit exceeded', 'formcreator'), true, WARNING);
            }
            array_shift($entries);

            $id = 0;
            foreach ($entries as $attr) {
               if (!isset($attr[$attribute[0]]) || in_array($attr[$attribute[0]][0], $tab_values)) {
                  continue;
               }
               $tab_values[$id] = $attr[$attribute[0]][0];
               $id++;
            }

            if (AuthLDAP::isLdapPageSizeAvailable($config_ldap) && version_compare(PHP_VERSION, '7.3') < 0) {
               // phpcs:ignore Generic.PHP.DeprecatedFunctions
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

   public static function getName(): string {
      return __('LDAP Select', 'formcreator');
   }

   public function serializeValue(): string {
      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = $value;
   }

   public function getValueForDesign(): string {
      return '';
   }

   public function isValid(): bool {
      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '0') {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR
         );
         return false;
      }

      // All is OK
      return true;
   }

   public function isValidValue($value): bool {
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
         $cookie = '';
         $ds = $config_ldap->connect();
         ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
         if (version_compare(PHP_VERSION, '7.3') < 0) {
            // phpcs:ignore Generic.PHP.DeprecatedFunctions
            ldap_control_paged_result($ds, 1, false, $cookie);
            $result = ldap_search($ds, $config_ldap->fields['basedn'], $input['ldap_filter'], $attribute);
         } else {
            $controls = [
               [
                  'oid'        =>LDAP_CONTROL_PAGEDRESULTS,
                  'iscritical' => false,
                  'value'      => [
                     'size'    => $config_ldap->fields['pagesize'],
                     'cookie'  => $cookie
                  ]
               ]
            ];
            $result = ldap_search($ds, $config_ldap->fields['basedn'], $input['ldap_filter'], $attribute, 0, -1, -1, LDAP_DEREF_NEVER, $controls);
            ldap_parse_result($ds, $result, $errcode, $matcheddn, $errmsg, $referrals, $controls);
            $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'] ?? '';
         }
         ldap_get_entries($ds, $result);
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

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public static function canRequire(): bool {
      return true;
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!isset($input[$key])) {
         $input[$key] = '';
      }
      if (!is_string($input[$key])) {
         return false;
      }

      $this->value = $input[$key];
      return true;
   }

   public function equals($value): bool {
      return $this->value == $value;
   }

   public function notEquals($value): bool {
      return !$this->equals($value);
   }

   public function greaterThan($value): bool {
      return $this->value > $value;
   }

   public function lessThan($value): bool {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function regex($value): bool {
      return (preg_grep($value, $this->value)) ? true : false;
   }

   public function isAnonymousFormCompatible(): bool {
      return false;
   }

   public function getHtmlIcon(): string {
      return '<i class="fa fa-sitemap" aria-hidden="true"></i>';
   }

   public function isVisibleField(): bool {
      return true;
   }

   public function isEditableField(): bool {
      return true;
   }
}
