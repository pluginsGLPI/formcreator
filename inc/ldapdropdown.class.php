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

use Glpi\Toolbox\Sanitizer;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorLdapDropdown extends CommonGLPI
{
   public static function getTable() {
       return '';
   }

   public function getForeignKeyField() {
       return '';
   }

   public function isField() {
       return false;
   }

   public static function dropdown($options = []) {
       $options['display'] = $options['display'] ?? false;
       $options['url'] = Plugin::getWebDir('formcreator') . '/ajax/getldapvalues.php';

       $out = Dropdown::show(self::class, $options);
      if (!$options['display']) {
          return $out;
      }
       echo $out;
   }

   public static function getDropdownValue($post, $json = true) {
      // Count real items returned
      $count = 0;

      if (isset($post['condition']) && !empty($post['condition']) && !is_array($post['condition'])) {
         // Retreive conditions from SESSION using its key
         $key = $post['condition'];
         $post['condition'] = [];
         if (isset($_SESSION['glpicondition']) && isset($_SESSION['glpicondition'][$key])) {
            $post['condition'] = $_SESSION['glpicondition'][$key];
         }
      }

      $questionId = $post['condition'][PluginFormcreatorQuestion::getForeignKeyField()];
      $question = PluginFormcreatorQuestion::getById($questionId);
      if (!is_object($question)) {
         return [];
      }

      $form = PluginFormcreatorCommon::getForm();
      $form = $form::getByItem($question);
      if (!$form->canViewForRequest()) {
         return [];
      }
      $post['searchText'] = $post['searchText'] ?? '';

      // Search values
      $ldap_values   = json_decode($question->fields['values'], JSON_OBJECT_AS_ARRAY);
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

      if ($post['searchText'] != '') {
         $ldap_values['ldap_filter'] = sprintf(
            "(& %s (%s))",
            $ldap_values['ldap_filter'],
            $attribute[0] . '=*' . $post['searchText'] . '*'
         );
      }

      $tab_values = [];
      try {
         $cookie = '';
         $ds = $config_ldap->connect();
         ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
         $foundCount = 0;
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
               trigger_error("LDAP size limit exceeded", E_USER_WARNING);
            }

            unset($entries['count']);

            foreach ($entries as $attr) {
               if (!isset($attr[$attribute[0]]) || in_array($attr[$attribute[0]][0], $tab_values)) {
                  continue;
               }

               $foundCount++;
               if ($foundCount < ((int) $post['page'] - 1) * (int) $post['page_limit'] + 1) {
                  // before the requested page
                  continue;
               }
               if ($foundCount > ((int) $post['page']) * (int) $post['page_limit']) {
                  // after the requested page
                  break;
               }

               $tab_values[] = [
                'id'   => $attr[$attribute[0]][0],
                'text' => $attr[$attribute[0]][0],
               ];
               $count++;
               if ($count >= $post['page_limit']) {
                  break;
               }
            }
         } while ($cookie !== null && $cookie != '' && $count < $post['page_limit']);
      } catch (Exception $e) {
         restore_error_handler();
         trigger_error($e->getMessage(), E_USER_WARNING);
      }

      restore_error_handler();

      $tab_values = Sanitizer::unsanitize($tab_values);
      usort($tab_values, function($a, $b) {
         return strnatcmp($a['text'], $b['text']);
      });
      $ret['results'] = $tab_values;
      $ret['count']   = $count;

      return ($json === true) ? json_encode($ret) : $ret;
   }

   public static function ldapErrorHandler($errno, $errstr, $errfile, $errline) {
      if (0 === error_reporting()) {
         return false;
      }
      throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
   }
}