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

use Dropdown;
use Html;
use Session;
use Toolbox;

class SelectField extends RadiosField
{
   public function isPrerequisites(): bool {
      return true;
   }

   public function getDesignSpecializationField(): array {
      $specialization = parent::getDesignSpecializationField();
      $specialization['may_be_empty'] = true;

      return $specialization;
   }

   public function getRenderedHtml($domain, $canEdit = true): string {
      if (!$canEdit) {
         return nl2br(__($this->value, $domain)) . PHP_EOL;
      }

      $html         = '';
      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $values       = $this->getAvailableValues();
      $translatedValues   = [];

      if (!empty($this->question->fields['values'])) {
         foreach ($values as $value) {
            if ((trim($value) != '')) {
               $translatedValues[$value] = __($value, $domain);
            }
         }

         $html .= Dropdown::showFromArray($fieldName, $translatedValues, [
            'display_emptychoice' => $this->question->fields['show_empty'] == 1,
            'value'     => $this->value,
            'values'    => [],
            'rand'      => $rand,
            'multiple'  => false,
            'display'   => false,
         ]);
      }
      $html .=  PHP_EOL;
      $html .=  Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeSelect('$fieldName', '$rand');
      });");

      return $html;
   }

   public static function getName(): string {
      return __('Select', 'formcreator');
   }

   public function isValid(): bool {
      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '0') {
         Session::addMessageAfterRedirect(
            sprintf(__('A required field is empty: %s', 'formcreator'), $this->getLabel()),
            false,
            ERROR
         );
         return false;
      }

      // All is OK
      return $this->isValidValue($this->value);
   }

   public function isValidValue($value): bool {
      if ($value == '0') {
         return true;
      }
      $value = Toolbox::stripslashes_deep($value);
      $value = trim($value);
      return in_array($value, $this->getAvailableValues());
   }

   public function equals($value): bool {
      if ($value == '') {
         // empty string means no selection
         $value = '0';
      }
      return $this->value == $value;
   }


   public function getHtmlIcon(): string {
      return '<i class="fas fa-caret-square-down" aria-hidden="true"></i>';
   }
}
