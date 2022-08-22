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
use GlpiPlugin\Formcreator\Exception\ComparisonException;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Toolbox\Sanitizer;

class DescriptionField extends PluginFormcreatorAbstractField
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
      $value = Toolbox::convertTagToImage(__($this->question->fields['description'], $domain), $this->getQuestion());
      $value = Sanitizer::unsanitize($value);
      $value = '<div class="plugin_formcreator_description">' . $value . '</div>';
      return $value;
   }

   public function serializeValue(PluginFormcreatorFormAnswer $formanswer): string {
      return '';
   }

   public function deserializeValue($value) {
      $this->value = '';
   }

   public function getValueForDesign(): string {
      return '';
   }

   public function getValueForTargetText($domain, $richText): ?string {
      $text = $this->question->fields['description'];
      if (!$richText) {
         $text = Sanitizer::unsanitize(strip_tags(html_entity_decode(__($text, $domain))));
      }

      return Sanitizer::unsanitize(__($text, $domain));
   }

   public function moveUploads() {
   }

   public function getDocumentsForTarget(): array {
      return [];
   }

   public function isValid(): bool {
      return true;
   }

   public function isValidValue($value): bool {
      return true;
   }

   public static function getName(): string {
      return __('Description');
   }

   public function prepareQuestionInputForSave($input) {
      if (isset($input['description'])) {
         if (strlen($input['description']) < 1) {
            Session::addMessageAfterRedirect(
               __('A description field should have a description:', 'formcreator') . ' ' . $input['name'],
               false,
               ERROR
            );
            return [];
         }
      }
      $this->value = '';

      return $input;
   }

   public function hasInput($input): bool {
      return false;
   }

   public static function canRequire(): bool {
      return false;
   }

   public function equals($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function notEquals($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function greaterThan($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function lessThan($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function regex($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      return true;
   }

   public function isPublicFormCompatible(): bool {
      return true;
   }

   public function getHtmlIcon() {
      return '<i class="fas fa-align-left" aria-hidden="true"></i>';
   }

   public function isVisibleField(): bool {
      return true;
   }

   public function isEditableField(): bool {
      return false;
   }

   public function getValueForApi() {
      return $this->question->fields['description'];
   }
}
