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
use Html;
use Session;
use Toolbox;
class TextareaField extends TextField
{
   /** @var $uploads array uploaded files on form submit */
   private $uploads = [
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

   public function getRenderedHtml($canEdit = true) {
      if (!$canEdit) {
         return Toolbox::getHtmlToDisplay($this->value);
      }

      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $value        = nl2br($this->value);
      $html = '';
      $html .= Html::textarea([
         'name'              => $fieldName,
         'rand'              => $rand,
         'value'             => $value,
         'rows'              => 5,
         'display'           => false,
         'enable_richtext'   => true,
         'enable_fileupload' => false,
         'uploads'           => $this->uploads,
      ]);
      $html .= Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeTextarea('$fieldName', '$rand');
      });");

      return $html;
   }

   public static function getName() {
      return __('Textarea', 'formcreator');
   }

   public function serializeValue() {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      $key = 'formcreator_field_' . $this->question->getID();
      $this->value = $this->question->addFiles(
         [$key => $this->value] + $this->uploads,
         [
            'force_update'  => true,
            'content_field' => $key,
            'name'          => $key,
         ]
      )[$key];

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
      $success = true;
      $fieldType = $this->getFieldTypeName();
      if (isset($input['_parameters'][$fieldType]['regex']['regex']) && !empty($input['_parameters'][$fieldType]['regex']['regex'])) {
         $regex = Toolbox::stripslashes_deep($input['_parameters'][$fieldType]['regex']['regex']);
         $success = $this->checkRegex($regex);
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

   public function hasInput($input) {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function parseAnswerValues($input, $nonDestructive = false) {
      parent::parseAnswerValues($input, $nonDestructive);
      $key = 'formcreator_field_' . $this->question->getID();
      if (isset($input['_tag_' . $key]) && isset($input['_' . $key]) && isset($input['_prefix_' . $key])) {
         $this->uploads['_' . $key] = $input['_' . $key];
         $this->uploads['_prefix_' . $key] = $input['_prefix_' . $key];
         $this->uploads['_tag_' . $key] = $input['_tag_' . $key];
      }

      return true;
   }

   public function getValueForTargetText($richText) {
      $value = $this->value;
      if (!$richText) {
         $value = Toolbox::unclean_cross_side_scripting_deep($value);
         $value = strip_tags($value);
      }
      return $value;
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
      return '<i class="far fa-comment-dots" aria-hidden="true"></i>';
   }
}
