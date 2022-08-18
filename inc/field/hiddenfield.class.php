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
use Toolbox;
use PluginFormcreatorFormAnswer;
use Glpi\Application\View\TemplateRenderer;

class HiddenField extends PluginFormcreatorAbstractField
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

   public function show(string $domain, bool $canEdit = true): string {
      if (!$canEdit) {
         return '';
      }

      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $domId        = $fieldName . '_' . $rand;
      $defaultValue = Html::cleanInputText($this->question->fields['default_values']);
      return Html::hidden($fieldName, [
         'id'    => $domId,
         'value' => $defaultValue,
      ]);
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

   public function isValid(): bool {
      return true;
   }

   public function isValidValue($value): bool {
      return true;
   }

   public static function getName(): string {
      return _n('Hidden field', 'Hidden fields', 1);
   }

   public function getValueForTargetText($domain, $richText): ?string {
      return str_replace("\n", '\r\n', $this->value);
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function moveUploads() {
   }

   public function getDocumentsForTarget(): array {
      return [];
   }

   public static function canRequire(): bool {
      return false;
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!is_string($input[$key])) {
         return false;
      }

      $this->value = Toolbox::stripslashes_deep($input[$key]);
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
      return (preg_match($value, $this->value) === 1) ? true : false;
   }

   public function isPublicFormCompatible(): bool {
      return true;
   }

   public function getHtmlIcon() {
      return '<i class="fa fa-eye-slash" aria-hidden="true"></i>';
   }

   public function isVisibleField(): bool {
      return false;
   }

   public function isEditableField(): bool {
      return false;
   }

   public function getTranslatableStrings(array $options = []) : array {
      $strings = [
         'text' => [],
         'id'   => [],
      ];

      $params = [
         'searchText'      => '',
         'id'              => '',
         'is_translated'   => null,
         'language'        => '', // Mandatory if one of is_translated and is_untranslated is false
      ];
      $options = array_merge($params, $options);

      $searchString = Toolbox::stripslashes_deep(trim($options['searchText']));
      if ($searchString != '' && stripos($this->question->fields['default_values'], $searchString) === false) {
         return $strings;
      }
      $id = \PluginFormcreatorTranslation::getTranslatableStringId($this->question->fields['default_values']);
      if ($options['id'] != '' && $id != $options['id']) {
         return $strings;
      }

      if ($this->question->fields['default_values'] != '') {
         $strings['string'][$id] = $this->question->fields['default_values'];
         $strings['id'][$id] = 'string';
      }
      return $strings;
   }

   public function getValueForApi() {
      return $this->value;
   }
}
