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

class PluginFormcreatorTextareaField extends PluginFormcreatorTextField
{
   private $taggedUploads = [
      '_filename' => [],
      '_prefix_filename' => [],
      '_tag_filename' => [],
   ];

   public function getDesignSpecializationField() {
      $rand = mt_rand();

      $label = '';
      $field = '';

      $additions = '<tr class="plugin_formcreator_question_specific">';
      $additions .= '<td>';
      $additions .= '<label for="dropdown_default_values'.$rand.'">';
      $additions .= __('Default values');
      $additions .= '</label>';
      $additions .= '</td>';
      $additions .= '<td width="80%" colspan="3">';
      $additions .= Html::textarea([
         'name'             => 'default_values',
         'id'               => 'default_values',
         'value'            => $this->getValueForDesign(),
         'enable_rich_text' => true,
         'display'          => false,
      ]);
      $additions .= Html::initEditorSystem('default_values', '', false);
      $additions .= '</td>';
      $additions .= '</tr>';

      $common = $common = PluginFormcreatorField::getDesignSpecializationField();
      $additions .= $common['additions'];

      return [
         'label' => $label,
         'field' => $field,
         'additions' => $additions,
         'may_be_empty' => false,
         'may_be_required' => true,
      ];
   }

   public function displayField($canEdit = true) {
      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $useRichText = true;
      if ($canEdit) {
         if ($useRichText) {
            $value = nl2br($this->value);
         } else {
            $value = $this->value;
         }
         echo Html::textarea([
            'name'              => $fieldName,
            'rand'              => $rand,
            'value'             => $value,
            'rows'              => 5,
            'display'           => false,
            'enable_richtext'   => $useRichText,
            'enable_fileupload' => false,
         ]);
         // This area is filled by glpi : @see js/fileupload.js
         // it contains _filename[] hidden inputs required to properly handle
         // images pasted in the textarea
         echo '<div id="fileupload_info" class="fileupload_info">';
         foreach ($this->taggedUploads['_filename'] as $id => $upload) {
            echo '<p>';
            // echo '<img src="/pics/icones/png-dist.png" title="png">';
            // echo '<b>' . 'image_paste' . '</b>';
            echo HTML::hidden(
               '_filename[' . $id . ']',
               ['value' => $this->taggedUploads['_filename'][$id]]
            );
            echo HTML::hidden(
               '_prefix_filename[' . $id . ']',
               ['value' => $this->taggedUploads['_prefix_filename'][$id]]
            );
            echo HTML::hidden(
               '_tag_filename[' . $id . ']',
               ['value' => $this->taggedUploads['_tag_filename'][$id]]
            );
            // echo '<span class="fa fa-times-circle pointer"></span>';
            echo '</p>';
         }
         echo '</div>';
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeTextarea('$fieldName', '$rand');
         });");
      } else {
         if ($useRichText) {
            echo Toolbox::getHtmlToDisplay($this->value);
         } else {
            echo nl2br($this->value);
         }
      }
   }

   public static function getName() {
      return __('Textarea', 'formcreator');
   }

   public function serializeValue() {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      return Toolbox::addslashes_deep($this->value);
   }

   public function deserializeValue($value) {
      $this->value = ($value !== null && $value !== '')
                  ? $value
                  : '';
      //$this->value = str_replace('\r\n', "\r\n", $this->value);
   }

   public function getValueForDesign() {
      if ($this->value === null) {
         return '';
      }

      return $this->value;
   }

   public function isValid() {
      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '') {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public function prepareQuestionInputForSave($input) {
      $this->value = str_replace('\r\n', "\r\n", $input['default_values']);
      return $input;
   }

   public function saveUploads($input) {
      $input = $this->question->addFiles(
         $input,
         [
            'force_update'  => true,
            'content_field' => 'formcreator_field_' . $this->question->getID(),
         ]
      );

      // Uploads handling ma have changed the input, update the value if the field
      $this->parseAnswerValues($input);
   }

   public function parseAnswerValues($input, $nonDestructive = false) {
      parent::parseAnswerValues($input, $nonDestructive);
      if (isset($input['_tag_filename']) && isset($input['_filename']) && isset($input['_tag_filename'])) {
         $this->taggedUploads['_filename'] = $input['_filename'];
         $this->taggedUploads['_prefix_filename'] = $input['_prefix_filename'];
         $this->taggedUploads['_tag_filename'] = $input['_tag_filename'];
      }
   }

   public function getValueForTargetText($richText) {
      return $this->value;
   }

   public function equals($value) {
      return $this->value == $value;
   }

   public function notEquals($value) {
      return !$this->equals($value);
   }

   public function greaterThan($value) {
      return $this->value > $value;
   }

   public function lessThan($value) {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function isAnonymousFormCompatible() {
      return true;
   }

   public function getHtmlIcon() {
      global $CFG_GLPI;

      return '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/ui-textarea-field.png" title="" />';
   }
}
