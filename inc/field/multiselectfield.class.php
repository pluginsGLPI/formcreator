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
use Glpi\Toolbox\Sanitizer;

class MultiSelectField extends CheckboxesField
{
   public function getRenderedHtml($domain, $canEdit = true): string {
      $html         = '';
      $translatedValues = [];
      if (!$canEdit) {
         if (count($this->value)) {
            foreach ($this->value as $value) {
               $translatedValues[] = __($value, $domain);
            }
            $html .= implode('<br />', $translatedValues);
         }
         return $html;
      }

      $id        = $this->question->getID();
      $rand      = mt_rand();
      $fieldName = 'formcreator_field_' . $id;
      $values    = $this->getAvailableValues();
      $translatedValues = [];

      foreach ($values as $key => $value) {
         $unsanitized = Sanitizer::unsanitize(__($value, $domain));
         $translatedValues[$key] = $unsanitized;
      }
      if (!empty($values)) {
         $html .= Dropdown::showFromArray($fieldName, $translatedValues, [
            'display_emptychoice' => $this->question->fields['show_empty'] == 1,
            'values'    => $this->value,
            'rand'      => $rand,
            'multiple'  => true,
            'display'   => false,
         ]);
      }
      $html .= PHP_EOL;
      $html .= Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeMultiselect('$fieldName', '$rand');
      });");

      return $html;
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function moveUploads() {
   }

   public static function getName(): string {
      return __('Multiselect', 'formcreator');
   }

   public function getHtmlIcon(): string {
      return '<i class="fas fa-check-double" aria-hidden="true"></i>';
   }
}
