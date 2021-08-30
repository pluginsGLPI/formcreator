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

use PluginFormcreatorAbstractField;
use PluginFormcreatorCommon;
use Html;
use Session;
use Toolbox;

class TextareaField extends TextField
{
   /** @var array uploaded files on form submit */
   private $uploads = [
      '_filename' => [],
      '_prefix_filename' => [],
      '_tag_filename' => [],
   ];

   public function getDesignSpecializationField(): array {
      $rand = mt_rand();

      $label = '';
      $field = '';

      $additions = '<tr class="plugin_formcreator_question_specific">';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_default_values' . $rand . '">';
      $additions .= __('Default values');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td width="80%" colspan="3">';
      $additions .= Html::textarea([
         'name'             => 'default_values',
         'id'               => 'default_values',
         'value'            => $this->getValueForDesign(),
         'enable_richtext'  => true,
         'filecontainer'   => 'default_values_info',
         'display'          => false,
      ]);
      //$additions .= Html::initEditorSystem('default_values', '', false);
      $additions .= '</td>';
      $additions .= '</tr>';

      $common = PluginFormcreatorAbstractField::getDesignSpecializationField();
      $additions .= $common['additions'];

      return [
         'label' => $label,
         'field' => $field,
         'additions' => $additions,
         'may_be_empty' => false,
         'may_be_required' => true,
      ];
   }

   public function getRenderedHtml($domain, $canEdit = true): string {
      if (!$canEdit) {
         $value = Toolbox::convertTagToImage($this->value, $this->getQuestion());
         return Toolbox::getHtmlToDisplay($value);
      }

      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $value        = nl2br(__($this->value, $domain));
      $html = '';
      $html .= Html::textarea([
         'name'              => $fieldName,
         'editor_id'         => "$fieldName$rand",
         'rand'              => $rand,
         'value'             => $value,
         'rows'              => 5,
         'display'           => false,
         'enable_richtext'   => true,
         'enable_fileupload' => false,
         'uploads'           => $this->uploads,
      ]);
      // The following file upload area is needed to allow embedded pics in the tetarea
      $html .=  '<div style="display:none;">';
      Html::file(['editor_id'    => "$fieldName$rand",
                  'filecontainer' => "filecontainer$rand",
                  'onlyimages'    => true,
                  'showtitle'     => false,
                  'multiple'      => true,
                  'display'       => false]);
      $html .=  '</div>';
      $html .= Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeTextarea('$fieldName', '$rand');
      });");

      return $html;
   }

   public static function getName(): string {
      return __('Textarea', 'formcreator');
   }

   public function serializeValue(): string {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      $key = 'formcreator_field_' . $this->question->getID();
      if (isset($this->uploads['_' . $key])) {
         foreach ($this->uploads['_' . $key] as $id => $filename) {
            // TODO :replace PluginFormcreatorCommon::getDuplicateOf by Document::getDuplicateOf
            // when is merged https://github.com/glpi-project/glpi/pull/9335
            if ($document = PluginFormcreatorCommon::getDuplicateOf(Session::getActiveEntity(), GLPI_TMP_DIR . '/' . $filename)) {
               $this->value = str_replace('id="' .  $this->uploads['_tag_' . $key][$id] . '"', $document->fields['tag'], $this->value);
               $this->uploads['_tag_' . $key][$id] = $document->fields['tag'];
            }
         }
         $input = [$key => $this->value] + $this->uploads;
         $input = $this->question->addFiles(
            $input,
            [
               'force_update'  => true,
               // 'content_field' => $key,
               'content_field' => null,
               'name'          => $key,
            ]
         );
         $this->value = $input[$key];
         $this->value = Html::entity_decode_deep($this->value);
         foreach ($input['_tag'] as $tag) {
            $regex = '/<img[^>]+' . preg_quote($tag, '/') . '[^<]+>/im';
            $this->value = preg_replace($regex, "#$tag#", $this->value);
         }
         $this->value = Html::entities_deep($this->value);
      }

      return Toolbox::addslashes_deep($this->value);
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

   public function isValid(): bool {
      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '') {
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

   public function prepareQuestionInputForSave($input): array {
      $success = true;
      $fieldType = $this->getFieldTypeName();
      if (isset($input['_parameters'][$fieldType]['regex']['regex']) && !empty($input['_parameters'][$fieldType]['regex']['regex'])) {
         $regex = Toolbox::stripslashes_deep($input['_parameters'][$fieldType]['regex']['regex']);
         $success = PluginFormcreatorCommon::checkRegex($regex);
         if (!$success) {
            Session::addMessageAfterRedirect(__('The regular expression is invalid', 'formcreator'), false, ERROR);
         }
      }
      if (!$success) {
         return [];
      }
      $this->value = Toolbox::stripslashes_deep(str_replace('\r\n', "\r\n", $input['default_values']));

      // Handle uploads
      $key = 'formcreator_field_' . $this->question->getID();
      if (isset($input['_tag_default_values']) && isset($input['_default_values']) && isset($input['_prefix_default_values'])) {
         $this->uploads['_' . $key] = $input['_default_values'];
         $this->uploads['_prefix_' . $key] = $input['_prefix_default_values'];
         $this->uploads['_tag_' . $key] = $input['_tag_default_values'];
      }
      $input[$key] = $input['default_values'];

      return $input;
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      parent::parseAnswerValues($input, $nonDestructive);
      $key = 'formcreator_field_' . $this->question->getID();
      if (isset($input['_tag_' . $key]) && isset($input['_' . $key]) && isset($input['_prefix_' . $key])) {
         $this->uploads['_' . $key] = $input['_' . $key];
         $this->uploads['_prefix_' . $key] = $input['_prefix_' . $key];
         $this->uploads['_tag_' . $key] = $input['_tag_' . $key];
      }

      return true;
   }

   public function getValueForTargetText($domain, $richText): ?string {
      $value = $this->value;
      if (!$richText) {
         $value = Toolbox::unclean_cross_side_scripting_deep($value);
         $value = strip_tags($value);
      }
      // $value = Toolbox::convertTagToImage($this->value, $this->getQuestion());
      // $value = Toolbox::getHtmlToDisplay($value);

      return $value;
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
      return (preg_grep($value, $this->value)) ? true : false;
   }

   public function isAnonymousFormCompatible(): bool {
      return true;
   }

   public function getHtmlIcon(): string {
      return '<i class="far fa-comment-dots" aria-hidden="true"></i>';
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

      if ($searchString != '' && stripos($this->question->fields['default_values'], $searchString) === false) {
         return $strings;
      }
      $id = \PluginFormcreatorTranslation::getTranslatableStringId($this->question->fields['default_values']);
      if ($options['id'] != '' && $id != $options['id']) {
         return $strings;
      }
      if ($this->question->fields['default_values'] != '') {
         $strings['text'][$id] = $this->question->fields['default_values'];
         $strings['id'][$id] = 'text';
      }

      return $strings;
   }
}
