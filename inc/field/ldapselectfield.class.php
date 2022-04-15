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

use AuthLDAP;
use Exception;
use Html;
use Session;
use RuleRightParameter;
use Glpi\Application\View\TemplateRenderer;
use PluginFormcreatorAbstractField;

class LdapselectField extends SelectField
{
   public function showForm(array $options): void {
      $template = '@formcreator/field/' . $this->question->fields['fieldtype'] . 'field.html.twig';

      $decodedValues = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $this->question->fields['_ldap_auth'] = $decodedValues['ldap_auth'] ?? '';
      $this->question->fields['_ldap_filter'] = $decodedValues['ldap_filter'] ?? '';
      $this->question->fields['_ldap_attribute'] = $decodedValues['ldap_attribute'] ?? '';

      $this->question->fields['default_values'] = Html::entities_deep($this->question->fields['default_values']);
      $this->deserializeValue($this->question->fields['default_values']);
      TemplateRenderer::getInstance()->display($template, [
         'item' => $this->question,
         'params' => $options,
      ]);
   }

   public function getAvailableValues() {
      if (empty($this->question->fields['values'])) {
         return [];
      }

      $ldap_values   = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      $ldap_dropdown = new RuleRightParameter();
      if (!$ldap_dropdown->getFromDB($ldap_values['ldap_attribute'])) {
         return [];
      }
      $attribute     = [$ldap_dropdown->fields['value']];

      $config_ldap = new AuthLDAP();
      if (!$config_ldap->getFromDB($ldap_values['ldap_auth'])) {
         return [];
      }

      set_error_handler([self::class, 'ldapErrorHandler'], E_WARNING);

      $tab_values = [];
      try {
         $cookie = '';
         $ds = $config_ldap->connect();
         ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
         do {
            if (AuthLDAP::isLdapPageSizeAvailable($config_ldap)) {
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
               $result = ldap_search($ds, $config_ldap->fields['basedn'], $ldap_values['ldap_filter'], $attribute, 0, -1, -1, LDAP_DEREF_NEVER, $controls);
               ldap_parse_result($ds, $result, $errcode, $matcheddn, $errmsg, $referrals, $controls);
               $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'] ?? '';
            } else {
               $result  = ldap_search($ds, $config_ldap->fields['basedn'], $ldap_values['ldap_filter'], $attribute);
            }

            $entries = ldap_get_entries($ds, $result);
            // openldap return 4 for Size limit exceeded
            $limitexceeded = in_array(ldap_errno($ds), [4, 11]);

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
         } while ($cookie !== null && $cookie != '');
      } catch (Exception $e) {
         restore_error_handler();
         trigger_error($e->getMessage(), E_USER_WARNING);
      }

      restore_error_handler();
      asort($tab_values);
      return $tab_values;
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
      // Get values already saved for the question
      $ldap_values = [];
      if (isset($this->question) && isset($this->question->fields['values'])) {
         $ldap_values = json_decode($this->question->fields['values'], JSON_OBJECT_AS_ARRAY);
      }

      // Get the directory from the browser then from the DB (if editing a question)
      $ldap_values['ldap_auth'] = $input['ldap_auth'] ?? ($ldap_values['ldap_auth'] ?? null);
      if ($ldap_values['ldap_auth'] === null) {
         Session::addMessageAfterRedirect(__('LDAP directory not defined!', 'formcreator'), false, ERROR);
         return [];
      }
      // Check the directory exists
      /** @var LdapAuth $config_ldap */
      $config_ldap = AuthLDAP::getById($ldap_values['ldap_auth']);
      if (!($config_ldap instanceof AuthLDAP)) {
         Session::addMessageAfterRedirect(__('LDAP directory not found!', 'formcreator'), false, ERROR);
         return [];
      }

      $ldap_values['ldap_attribute'] = $input['ldap_attribute'] ?? ($ldap_values['ldap_attribute'] ?? null);
      if (isset($ldap_values['ldap_attribute'])) {
         $ldap_dropdown = RuleRightParameter::getById((int) $ldap_values['ldap_attribute']);
         if (!($ldap_dropdown instanceof RuleRightParameter)) {
            return [];
         }
      }

      if (isset($input['ldap_filter'])) {
         $input['ldap_filter'] = html_entity_decode($input['ldap_filter']);
      }
      $ldap_values['ldap_filter'] = $input['ldap_filter'] ?? ($ldap_values['ldap_filter'] ?? '');

      $input['values'] = json_encode($ldap_values, JSON_UNESCAPED_UNICODE);
      unset($input['ldap_auth']);
      unset($input['ldap_filter']);
      unset($input['ldap_attribute']);

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

   public function getTranslatableStrings(array $options = []) : array {
      $strings = PluginFormcreatorAbstractField::getTranslatableStrings($options);

      $params = [
         'searchText'      => '',
         'id'              => '',
         'is_translated'   => null,
         'language'        => '', // Mandatory if one of is_translated and is_untranslated is false
      ];
      $options = array_merge($params, $options);

      return $strings;
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

   public function isPublicFormCompatible(): bool {
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

   public static function ldapErrorHandler($errno, $errstr, $errfile, $errline) {
      if (0 === error_reporting()) {
         return false;
      }
      throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
   }
}
