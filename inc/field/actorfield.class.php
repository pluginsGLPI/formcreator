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
use Html;
use User;
use Session;
use PluginFormcreatorFormAnswer;
use GlpiPlugin\Formcreator\Exception\ComparisonException;
use Glpi\Application\View\TemplateRenderer;

/**
 * Actors field is a field which accepts several users. Those users may be
 * users from the itemtype User or email addresses. Email addresses allows to
 * add actors who don't have an account in GLPI.
 */
class ActorField extends PluginFormcreatorAbstractField
{
   public function isPrerequisites(): bool {
      return true;
   }

   public function showForm(array $options): void {
      $template = '@formcreator/field/' . $this->question->fields['fieldtype'] . 'field.html.twig';

      // Convert default values to text
      $items = json_decode($this->question->fields['default_values'], true);
      $this->question->fields['default_values'] = [];
      if (is_array($items)) {
         foreach ($items as $item) {
            if (filter_var($item, FILTER_VALIDATE_EMAIL) !== false) {
               $this->question->fields['default_values'][] = $item;
            } else if (!empty($item)) {
               $user = new User();
               $user->getFromDB($item);
               if (!$user->isNewItem()) {
                  // A user known in the DB
                  $this->question->fields['default_values'][] = $user->fields['name'];
               }
            }
         }
      }
      $this->question->fields['default_values'] = implode('\r\n', $this->question->fields['default_values']);

      TemplateRenderer::getInstance()->display($template, [
         'item' => $this->question,
         'params' => $options,
      ]);
   }

   public static function getName(): string {
      return _n('Actor', 'Actors', 1, 'formcreator');
   }

   public function getRenderedHtml($domain, $canEdit = true): string {
      global $CFG_GLPI;

      $html = '';
      if (!$canEdit) {
         if (empty($this->value)) {
            return '';
         }

         $value = [];
         foreach ($this->value as $item) {
            if (filter_var($item, FILTER_VALIDATE_EMAIL) !== false) {
               $value[] = $item;
            } else {
               $user = new User();
               $user->getFromDB($item);
               $value[] = $user->getFriendlyName();
            }
         }
         $html .= implode('<br>', $value);
         return $html;
      }

      $value = $this->sanitizeValue($this->value);
      $initialValue = [];
      foreach ($value as $id => $item) {
         $initialValue[] = [
            'id'     => $id,
            'text'   => $item,
         ];
      }
      $initialValue = json_encode($initialValue);
      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $domId        = $fieldName . '_' . $rand;

      // Value needs to be non empty to allow execution of select2's initSelection
      $params = [
         'multiple' => 'multiple',
         'entity_restrict' => -1,
         'itemtype'        => User::getType(),
         'display_emptychoice' => false,
         'values'          => array_keys($value),
         'valuesnames'     => array_values($value),
         '_idor_token'     => Session::getNewIDORToken(User::getType()),
      ];
      $html .= \PluginFormcreatorCommon::jsAjaxDropdown(
         $fieldName . '[]',
         $domId,
         $CFG_GLPI['root_doc']."/ajax/getDropdownUsers.php",
         $params
      );
      $html .= Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeActor('$fieldName', '$rand');
      });");

      return $html;
   }

   public function serializeValue(PluginFormcreatorFormAnswer $formanswer): string {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      return json_encode($this->value);
   }

   public function deserializeValue($value) {
      $deserialized  = [];

      $serialized = ($value !== null && $value !== '')
         ? json_decode($value, JSON_OBJECT_AS_ARRAY)
         : [];
      if (!is_array($serialized)) {
         $serialized = [];
      }
      foreach ($serialized as $item) {
         $item = trim($item);
         if (filter_var($item, FILTER_VALIDATE_EMAIL) !== false) {
            $deserialized[] = $item;
         } else if (!empty($item) && ctype_digit($item) && intval($item)) {
            $deserialized[] = $item;
         }
      }

      $this->value = $deserialized;
   }

   public function getValueForDesign(): string {
      if ($this->value === null) {
         return '';
      }

      $value = [];
      foreach ($this->value as $item) {
         if (filter_var($item, FILTER_VALIDATE_EMAIL) !== false) {
            $value[] = $item;
         } else {
            $user = new User();
            $user->getFromDB($item);
            if (!$user->isNewItem()) {
               // A user known in the DB
               $value[] = $user->getField('name');
            }
         }
      }
      return implode("\r\n", $value);
   }

   public function getValueForTargetText($domain, $richText): ?string {
      $value = [];
      if (is_array($this->value)) {
         foreach ($this->value as $item) {
            if (filter_var($item, FILTER_VALIDATE_EMAIL) !== false) {
               $value[] = $item;
            } else {
               $user = new User();
               if (!$user->getFromDB($item)) {
                  continue;
               }
               $value[] = $user->getFriendlyName();
            }
         }
      }

      if ($richText) {
         $value = '<br />' . implode('<br />', $value);
      } else {
         $value = implode(', ', $value);
      }
      return $value;
   }

   public function moveUploads() {
   }

   public function getDocumentsForTarget(): array {
      return [];
   }

   /**
    * Sanitize the list of users or emails
    * @param array list of users and emails
    * @return array cleaned list of users and emails
    */
   protected function sanitizeValue($value): array {
      $unknownUsers = [];
      $knownUsers = [];
      foreach ($value as $item) {
         $item = trim($item);
         if (filter_var($item, FILTER_VALIDATE_EMAIL) !== false) {
            $unknownUsers[$item] = $item;
         } else if (strlen($item) > 0 && ctype_digit($item)) {
            $user = new User();
            $user->getFromDB($item);
            if (!$user->isNewItem()) {
               // A user known in the DB
               $knownUsers[$user->getID()] = $user->getFriendlyName();
            }
         }
      }
      return $knownUsers + $unknownUsers;
   }

   public function isValid(): bool {
      $sanitized = $this->sanitizeValue($this->value);

      // If the field is required it can't be empty
      if ($this->isRequired() && count($this->value) === 0) {
         Session::addMessageAfterRedirect(
            sprintf(__('A required field is empty: %s', 'formcreator'), $this->getLabel()),
            false,
            ERROR
         );
         return false;
      }

      // If an item has been removed by sanitization, then the data is not valid
      if (count($sanitized) != count($this->value)) {
         Session::addMessageAfterRedirect(
            sprintf(__('Invalid value: %s', 'formcreator'), $this->getLabel()),
            false,
            ERROR
         );
         return false;
      }

      return $this->isValidValue($this->value);
   }

   public function isValidValue($value): bool {
      if ($value === '') {
         return true;
      }

      foreach ($value as $item) {
         $item = trim($item);
         if (filter_var($item, FILTER_VALIDATE_EMAIL) !== false) {
            continue;
         } else if (!empty($item)) {
            $user = new User();
            if (!$user->getFromDB($item)) {
               Session::addMessageAfterRedirect(
                  sprintf(__('User not found or invalid email address: %s', 'formcreator'), $this->getLabel()),
                  false,
                  ERROR
               );
               return false;
            }
         }
      }

      return true;
   }

   public static function canRequire(): bool {
      return true;
   }

   public function prepareQuestionInputForSave($input) {
      $parsed  = [];
      $defaultValue = $input['default_values'];
      $serialized = ($defaultValue !== null && $defaultValue !== '')
         ? explode('\r\n', $defaultValue)
         : [];
      foreach ($serialized as $item) {
         $item = trim($item);
         if (filter_var($item, FILTER_VALIDATE_EMAIL) !== false) {
            $parsed[] = $item;
         } else if (!empty($item)) {
            $user = new User();
            $user->getFromDBByName($item);
            if (!$user->isNewItem()) {
               // A user known in the DB
               $parsed[] = $user->getID();
            }
         }
      }

      $this->value = $parsed;
      $input['default_values'] = '';
      if ($this->value !== null && $this->value != '') {
         $input['default_values'] = json_encode($this->value);
      }

      return $input;
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!isset($input[$key])) {
         $this->value = [];
         return true;
      }

      if (is_string($input[$key])) {
         // value from a saved answer
         $input[$key] = json_decode($input[$key], JSON_OBJECT_AS_ARRAY);
      }
      if (!is_array($input[$key])) {
         return false;
      }

      $this->value = $input[$key];
      return true;
   }

   public function equals($value): bool {
      $user = new User();
      if (!$user->getFromDBByName($value)) {
         // value does not match any user, test if it is an email address
         if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            // Not an email address,
            // no need to test against the values of the field
            return false;
         }

         if (!is_array($this->value)) {
            // No content in the field, but a email in the other operand
            return false;
         }

         // find the email address in the values of the field
         return in_array($value, $this->value);
      }
      if (!is_array($this->value)) {
         // No user selected
         return false;
      }

      return in_array($user->getID(), $this->value);
   }

   public function notEquals($value): bool {
      return !$this->equals($value);
   }

   public function greaterThan($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function lessThan($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function regex($value): bool {
      return (preg_grep($value, $this->value)) ? true : false;
   }

   public function isPublicFormCompatible(): bool {
      return false;
   }

   public function getHtmlIcon() {
      return '<i class="fa fa-user" aria-hidden="true"></i>';
   }

   public function isVisibleField(): bool {
      return true;
   }

   public function isEditableField(): bool {
      return true;
   }

   public function getValueForApi() {
      $value = [];
      if (is_array($this->value)) {
         foreach ($this->value as $item) {
            if (filter_var($item, FILTER_VALIDATE_EMAIL) !== false) {
               $value[] = $item;
            } else {
               $user = new User();
               if (!$user->getFromDB($item)) {
                  continue;
               }
               $value[] = [User::class, $item];
            }
         }
      }

      return $value;
   }
}
