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

namespace GlpiPlugin\Formcreator\Field;

use Html;
use Session;
use Ticket;
use PluginFormcreatorFormAnswer;
use Dropdown;
use GlpiPlugin\Formcreator\Exception\ComparisonException;
use Glpi\Application\View\TemplateRenderer;
use PluginFormcreatorAbstractField;
class RequestTypeField extends SelectField
{

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
      $html = "";
      if (!$canEdit) {
         return Ticket::getTicketTypeName($this->value);
      }

      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;

      $options = [
         'value'     => $this->value,
         'rand'      => $rand,
         'display'   => false,
      ];
      if ($this->question->fields['show_empty'] != '0') {
         $options['toadd'] = [
            0  => Dropdown::EMPTY_VALUE,
         ];
      }
      $html .= Ticket::dropdownType($fieldName, $options);
      $html .=  PHP_EOL;
      $html .=  Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeRequestType('$fieldName', '$rand');
      });");

      return $html;
   }

   public static function getName(): string {
      return __('Request type', 'formcreator');
   }

   public function prepareQuestionInputForSave($input) {
      $this->value = $input['default_values'] != ''
         ? (int) $input['default_values']
         : '3';
      return $input;
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

   public function getAvailableValues(array $values = null): array {
      return Ticket::getTypes();
   }

   public function serializeValue(PluginFormcreatorFormAnswer $formanswer): string {
      if ($this->value === null || $this->value === '') {
         return '2';
      }

      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = ($value !== null && $value !== '')
         ? $value
         : '2';
   }

   public function getValueForDesign(): string {
      if ($this->value === null) {
         return '';
      }

      return $this->value;
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
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
            __('A required field is empty:', 'formcreator') . ' ' . $this->getTtranslatedLabel(),
            false,
            ERROR
         );
         return false;
      }

      // All is OK
      return $this->isValidValue($this->value);
   }

   public function isValidValue($value): bool {
      return in_array($value, array_keys($this->getAvailableValues()));
   }

   public function equals($value): bool {
      global $TRANSLATE;

      $oldLocale = $TRANSLATE->getLocale();
      $TRANSLATE->setLocale("en_GB");
      $_SESSION['glpilanguage'] = "en_GB";
      $available = $this->getAvailableValues();
      $TRANSLATE->setLocale($oldLocale);
      $_SESSION['glpilanguage'] = $oldLocale;

      if (!isset($available[$this->value])) {
         return false;
      }
      return strcasecmp($available[$this->value], $value) === 0;
   }

   public function notEquals($value): bool {
      global $TRANSLATE;

      $oldLocale = $TRANSLATE->getLocale();
      $TRANSLATE->setLocale("en_GB");
      $_SESSION['glpilanguage'] = "en_GB";
      $available = $this->getAvailableValues();
      $TRANSLATE->setLocale($oldLocale);
      $_SESSION['glpilanguage'] = $oldLocale;

      if (!isset($available[$this->value])) {
         return false;
      }
      return !$this->equals($value);
   }

   public function greaterThan($value): bool {
      global $TRANSLATE;

      $oldLocale = $TRANSLATE->getLocale();
      $TRANSLATE->setLocale("en_GB");
      $_SESSION['glpilanguage'] = "en_GB";
      $available = $this->getAvailableValues();
      $TRANSLATE->setLocale($oldLocale);
      $_SESSION['glpilanguage'] = $oldLocale;

      if (!isset($available[$this->value])) {
         return false;
      }
      return strcasecmp($available[$this->value], $value) > 0;
   }

   public function lessThan($value): bool {
      global $TRANSLATE;

      $oldLocale = $TRANSLATE->getLocale();
      $TRANSLATE->setLocale("en_GB");
      $_SESSION['glpilanguage'] = "en_GB";
      $available = $this->getAvailableValues();
      $TRANSLATE->setLocale($oldLocale);
      $_SESSION['glpilanguage'] = $oldLocale;

      if (!isset($available[$this->value])) {
         return false;
      }
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

   public function getTranslatableStrings(array $options = []) : array {
      return PluginFormcreatorAbstractField::getTranslatableStrings($options);
   }
}
