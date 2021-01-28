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

use Session;
use Toolbox;

class IntegerField extends FloatField
{
   public function serializeValue(): string {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      return strval((int) $this->value);
   }

   public function moveUploads() {
   }

   public function isValidValue($value): bool {
      if (strlen($value) == 0) {
         return true;
      }

      if (!empty($value) && !ctype_digit((string) $value)) {
         Session::addMessageAfterRedirect(sprintf(__('This is not an integer: %s', 'formcreator'), $this->getLabel()), false, ERROR);
         return false;
      }

      $parameters = $this->getParameters();

      // Check the field matches the format regex
      if (!$parameters['regex']->isNewItem()) {
         $regex = $parameters['regex']->fields['regex'];
         if ($regex !== null && strlen($regex) > 0) {
            if (!preg_match($regex, $value)) {
               Session::addMessageAfterRedirect(
                  sprintf(__('Specific format does not match: %s', 'formcreator'), $this->getLabel()),
                  false,
                  ERROR
               );
               return false;
            }
         }
      }

      // Check the field is in the range
      if (!$parameters['range']->isNewItem()) {
         $rangeMin = $parameters['range']->fields['range_min'];
         $rangeMax = $parameters['range']->fields['range_max'];
         if ($rangeMin > 0 && $value < $rangeMin) {
            $message = sprintf(__('The following number must be greater than %d: %s', 'formcreator'), $rangeMin, $this->getLabel());
            Session::addMessageAfterRedirect($message, false, ERROR);
            return false;
         }

         if ($rangeMax > 0 && $value > $rangeMax) {
            $message = sprintf(__('The following number must be lower than %d: %s', 'formcreator'), $rangeMax, $this->getLabel());
            Session::addMessageAfterRedirect($message, false, ERROR);
            return false;
         }
      }

      return true;
   }

   public static function getName(): string {
      return __('Integer', 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      $success = true;
      $fieldType = $this->getFieldTypeName();
      // Add leading and trailing regex marker automaticaly
      if (isset($input['_parameters'][$fieldType]['regex']['regex']) && !empty($input['_parameters'][$fieldType]['regex']['regex'])) {
         $regex = Toolbox::stripslashes_deep($input['_parameters'][$fieldType]['regex']['regex']);
         $success = $this->checkRegex($regex);
         if (!$success) {
            Session::addMessageAfterRedirect(__('The regular expression is invalid', 'formcreator'), false, ERROR);
         }
      }
      if (!$success) {
         return false;
      }

      if (isset($input['default_values'])) {
         if ($input['default_values'] != '') {
            $this->value = (int) $input['default_values'];
         } else {
            $this->value = '';
         }
      }
      $input['values'] = '';

      return $input;
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!is_string($input[$key])) {
         $this->value = '';
      }
      // $input[$key] = (int) $input[$key];

      $this->value = $input[$key];
      return true;
   }

   public function equals($value): bool {
      return ((int) $this->value) === ((int) $value);
   }

   public function greaterThan($value): bool {
      return ((int) $this->value) > ((int) $value);
   }

   public function regex($value): bool {
      return (preg_grep($value, (int) $this->value)) ? true : false;
   }

   public function getHtmlIcon() {
      return '<i class="fas fa-square-root-alt" aria-hidden="true"></i>';
   }
}
