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
use Ticket;
use PluginFormcreatorFormAnswer;
use GlpiPlugin\Formcreator\Exception\ComparisonException;
use Glpi\Application\View\TemplateRenderer;

class UrgencyField extends PluginFormcreatorAbstractField
{
   public function isPrerequisites(): bool {
      return true;
   }

   public function showForm(array $options): void {
      $template = '@formcreator/field/' . $this->question->fields['fieldtype'] . 'field.html.twig';
      $this->question->fields['default_values'] = Html::entities_deep($this->question->fields['default_values']);
      $this->deserializeValue($this->question->fields['default_values']);
      TemplateRenderer::getInstance()->display($template, [
         'item' => $this->question,
         'params' => $options,
      ]);
   }

   public function getRenderedHtml($domain, $canEdit = true): string {
      if (!$canEdit) {
         return Ticket::getPriorityName($this->value);
      }

      $html         = '';
      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $html .= Ticket::dropdownUrgency([
         'name'                => $fieldName,
         'value'               => $this->value,
         'comments'            => false,
         'rand'                => $rand,
         'display'             => false,
         'display_emptychoice' => $this->question->fields['show_empty'] == 1,
      ]);
      $html .= PHP_EOL;
      $html .= Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeUrgency('$fieldName', '$rand');
      });");

      return $html;
   }

   public static function getName(): string {
      return __('Urgency');
   }

   public function prepareQuestionInputForSave($input) {
      $this->value = $input['default_values'] != ''
         ? (int) $input['default_values']
         : '3';
      return $input;
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!isset($input[$key])) {
         $input[$key] = '3';
      } else {
         if (!is_string($input[$key])) {
            return false;
         }
      }

      $this->value = $input[$key];
      return true;
   }

   public static function canRequire(): bool {
      return true;
   }

   public function getAvailableValues() {
      return [
         5 => _x('urgency', 'Very high'),
         4 => _x('urgency', 'High'),
         3 => _x('urgency', 'Medium'),
         2 => _x('urgency', 'Low'),
         1 => _x('urgency', 'Very low'),
      ];
   }

   public function serializeValue(PluginFormcreatorFormAnswer $formanswer): string {
      if ($this->value === null || $this->value === '') {
         return '3';
      }

      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = ($value !== null && $value !== '')
         ? $value
         : '3';
   }

   public function getValueForDesign(): string {
      if ($this->value === null) {
         return '';
      }

      return $this->value;
   }

   public function getValueForTargetText($domain, $richText): ?string {
      $available = $this->getAvailableValues();
      return $available[$this->value] ?? '';
   }

   public function moveUploads() {
   }

   public function getDocumentsForTarget(): array {
      return [];
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
      return in_array($value, array_keys($this->getAvailableValues()));
   }

   public function equals($value): bool {
      $available = $this->getAvailableValues();
      return strcasecmp($available[$this->value], $value) === 0;
   }

   public function notEquals($value): bool {
      return !$this->equals($value);
   }

   public function greaterThan($value): bool {
      $available = $this->getAvailableValues();
      return strcasecmp($available[$this->value], $value) > 0;
   }

   public function lessThan($value): bool {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function regex($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function isPublicFormCompatible(): bool {
      return true;
   }

   public function getHtmlIcon(): string {
      return '<i class="fa fa-exclamation" aria-hidden="true"></i>';
   }

   public function isVisibleField(): bool {
      return true;
   }

   public function isEditableField(): bool {
      return true;
   }

   public function getValueForApi() {
      return $this->value;
   }
}
