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
use Session;
use Toolbox;
use PluginFormcreatorFormAnswer;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Toolbox\Sanitizer;

class RadiosField extends PluginFormcreatorAbstractField
{
   public function isPrerequisites(): bool {
      return true;
   }

   public function showForm(array $options): void {
      $template = '@formcreator/field/' . $this->question->fields['fieldtype'] . 'field.html.twig';

      $this->question->fields['values'] = json_decode($this->question->fields['values']);
      $this->question->fields['values'] = is_array($this->question->fields['values']) ? $this->question->fields['values'] : [];
      $this->question->fields['values'] = implode("\r\n", $this->question->fields['values']);
      $this->deserializeValue($this->question->fields['default_values']);
      TemplateRenderer::getInstance()->display($template, [
         'item' => $this->question,
         'params' => $options,
      ]);
   }

   public function getRenderedHtml($domain, $canEdit = true): string {
      if (!$canEdit) {
         return __($this->value, $domain);
      }
      $html         = '';
      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $domId        = $fieldName . '_' . $rand;

      $values = $this->getAvailableValues();
      if (!empty($values)) {
         $html .= '<div class="radios">';
         $i = 0;
         foreach ($values as $value) {
            if ((trim($value) != '')) {
               $i++;
               $checked = ($this->value == $value) ? ['checked' => ''] : [];
               $html .= '<div class="radio">';
               $html .= '<span class="form-group-radio">';
               $html .= Html::input($fieldName, [
                  'type'    => 'radio',
                  'class'   => 'form-check-input',
                  'id'      => $domId . '_' . $i,
                  'value'   => $value
               ] + $checked);
               $translated_value =  __($value, $domain);
               $html .= '<label for="' . $domId . '_' . $i . '" class="label-radio" title="' . $translated_value . '">';
               $html .= '<span class="box"></span>';
               $html .= '<span class="check"></span>';
               $html .= '&nbsp;' . $translated_value;
               $html .= '</label>';
               $html .= '</span>';
               $html .= '</div>';
            }
         }
         $html .= '</div>';
      }
      $html .= Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeRadios('$fieldName', '$rand');
      });");

      return $html;
   }

   public static function getName(): string {
      return __('Radios', 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      global $DB;

      if (!isset($input['values']) || empty($input['values'])) {
         Session::addMessageAfterRedirect(
            __('The field value is required.', 'formcreator'),
            false,
            ERROR
         );
         return [];
      }

      // trim values (actually there is only one value then no \r\n expected)
      $defaultValues = $this->trimValue($input['default_values'] ?? '');
      if (count($defaultValues) > 1) {
         Session::addMessageAfterRedirect(
            __('Only one default value is allowed.', 'formcreator'),
            false,
            ERROR
         );
         return [];
      }
      $values = $this->trimValue($input['values']);
      if (count($defaultValues) > 0) {
         $validDefaultValues = array_intersect($this->getAvailableValues($values), $defaultValues);
         if (count($validDefaultValues) != count($defaultValues)) {
            Session::addMessageAfterRedirect(
               __('The default value is not in the list of available values.', 'formcreator'),
               false,
               ERROR
            );
            return [];
         }
      }
      $input['values'] = $DB->escape(json_encode($values, JSON_UNESCAPED_UNICODE));
      $input['default_values'] = array_pop($defaultValues);

      return $input;
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      $key = 'formcreator_field_' . $this->question->getID();
      if (isset($input[$key])) {
         if (!is_string($input[$key])) {
            return false;
         }
      } else {
         $this->value = '';
         return true;
      }

      $this->value = Toolbox::stripslashes_deep($input[$key]);
      return true;
   }

   public function parseDefaultValue($defaultValue) {
      $this->value = explode('\r\n', $defaultValue);
      $this->value = array_filter($this->value, function ($value) {
         return ($value !== '');
      });
      $this->value = array_shift($this->value);
   }

   public function serializeValue(PluginFormcreatorFormAnswer $formanswer): string {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = ($value !== null && $value !== '')
         ? $value
         : '';
   }

   public function getValueForDesign(): string {
      if ($this->value === null) {
         return '';
      }

      return $this->value;
   }

   public function getValueForTargetText($domain, $richText): ?string {
      return Sanitizer::unsanitize(__($this->value, $domain));
   }

   public function moveUploads() {
   }

   public function getDocumentsForTarget(): array {
      return [];
   }

   public function isValid(): bool {
      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '') {
         Session::addMessageAfterRedirect(
            sprintf(__('A required field is empty: %s', 'formcreator'), $this->getTtranslatedLabel()),
            false,
            ERROR
         );
         return false;
      }

      // All is OK
      return $this->isValidValue($this->value);
   }

   public function isValidValue($value): bool {
      if ($value == '') {
         return true;
      }

      $value = trim($value);
      if (!in_array($value, $this->getAvailableValues())) {
         Session::addMessageAfterRedirect(
            sprintf(__('This value %1$s is not allowed: %2$s', 'formcreator'), $value, $this->getTtranslatedLabel()),
            false,
            ERROR
         );
         return false;
      }

      return true;
   }

   public static function canRequire(): bool {
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
      return preg_match($value, $this->value) ? true : false;
   }

   public function isPublicFormCompatible(): bool {
      return true;
   }

   public function getHtmlIcon(): string {
      return '<i class="fa fa-check-circle" aria-hidden="true"></i>';
   }

   public function isVisibleField(): bool {
      return true;
   }

   public function isEditableField(): bool {
      return true;
   }

   public function getTranslatableStrings(array $options = []) : array {
      $strings = parent::getTranslatableStrings($options);

      $params = [
         'searchText'      => '',
         'id'              => '',
         'is_translated'   => null,
         'language'        => '', // Mandatory if one of is_translated and is_untranslated is false
      ];
      $options = array_merge($params, $options);

      $searchString = Toolbox::stripslashes_deep(trim($options['searchText']));

      foreach ($this->getAvailableValues() as $value) {
         if ($searchString != '' && stripos($value, $searchString) === false) {
            continue;
         }
         $id = \PluginFormcreatorTranslation::getTranslatableStringId($value);
         if ($options['id'] != '' && $id != $options['id']) {
            continue;
         }
         $strings['string'][$id] = $value;
         $strings['id'][$id] = 'string';
      }

      return $strings;
   }

   public function getValueForApi() {
      return $this->value;
   }
}
