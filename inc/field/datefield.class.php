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
use DateTime;
use Session;
use PluginFormcreatorFormAnswer;
use GlpiPlugin\Formcreator\Exception\ComparisonException;
use Glpi\Application\View\TemplateRenderer;

class DateField extends PluginFormcreatorAbstractField
{
   const DATE_FORMAT = 'Y-m-d';

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
         return $this->value;
      }

      $html = '';
      $id        = $this->question->getID();
      $rand      = mt_rand();
      $fieldName = 'formcreator_field_' . $id;

      $html .= Html::showDateField($fieldName, [
         'value'   => (strtotime($this->value) != '') ? $this->value : '',
         'rand'    => $rand,
         'display' => false,
      ]);
      $html .= Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeDate('$fieldName', '$rand');
      });");

      return $html;
   }

   public function serializeValue(PluginFormcreatorFormAnswer $formanswer): string {
      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = $value;
   }

   public function getValueForDesign(): string {
      return $this->value;
   }

   public function getValueForTargetText($domain, $richText): ?string {
      return Html::convDate($this->value);
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function moveUploads() {
   }

   public function getDocumentsForTarget(): array {
      return [];
   }

   public function isValid(): bool {
      // If the field is required it can't be empty
      if ($this->isRequired() && (strtotime($this->value) == '')) {
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
      if (!$this->isRequired() && empty($value)) {
         return true;
      }

      $check = DateTime::createFromFormat(self::DATE_FORMAT, $value);
      return $check !== false;
   }

   public static function getName(): string {
      return __('Date');
   }

   public static function canRequire(): bool {
      return true;
   }

   public function equals($value): bool {
      if ($this->value === '') {
         $answer = '0000-00-00';
      } else {
         $answer = $this->value;
      }
      $answerDatetime = DateTime::createFromFormat(self::DATE_FORMAT, $answer);
      $answerDatetime->setTime(0, 0, 0, 0);
      $compareDatetime = DateTime::createFromFormat(self::DATE_FORMAT, $value);
      $compareDatetime->setTime(0, 0, 0, 0);
      return $answerDatetime == $compareDatetime;
   }

   public function notEquals($value): bool {
      return !$this->equals($value);
   }

   public function greaterThan($value): bool {
      if (empty($this->value)) {
         $answer = '0000-00-00';
      } else {
         $answer = $this->value;
      }
      $answerDatetime = DateTime::createFromFormat(self::DATE_FORMAT, $answer);
      $answerDatetime->setTime(0, 0, 0, 0);
      $compareDatetime = DateTime::createFromFormat(self::DATE_FORMAT, $value);
      $compareDatetime->setTime(0, 0, 0, 0);
      return $answerDatetime > $compareDatetime;
   }

   public function lessThan($value): bool {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function regex($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!isset($input[$key])) {
         $input[$key] = '';
      }

      if (!is_string($input[$key])) {
         return false;
      }

      if ($input[$key] != ''
         && DateTime::createFromFormat(self::DATE_FORMAT, $input[$key]) === false
      ) {
         return false;
      }

      $this->value = $input[$key];
      return true;
   }

   public function isPublicFormCompatible(): bool {
      return true;
   }

   public function getHtmlIcon(): string {
      return '<i class="fa fa-calendar" aria-hidden="true"></i>';
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
