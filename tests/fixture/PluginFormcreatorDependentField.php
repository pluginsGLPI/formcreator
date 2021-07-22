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
use PluginFormcreatorQuestionDependency;
use Toolbox;
use Html;
use Session;
use PluginFormcreatorTranslatable;

class DependentField extends PluginFormcreatorAbstractField
{

   use PluginFormcreatorTranslatable;

   public function isPrerequisites(): bool {
      return true;
   }

   public static function getName(): string {
      return _n('User ID', 'User IDs', 1, 'formcreator');
   }

   public static function canRequire(): bool {
      return true;
   }

   public function getEmptyParameters(): array {
      return [
         'firstname' => new PluginFormcreatorQuestionDependency(
            $this,
            [
               'fieldName' => 'firstname',
               'label'     => __('First name field', 'formcreator'),
               'fieldType' => ['text'],
            ]
         ),
         'lastname' => new PluginFormcreatorQuestionDependency(
            $this,
            [
               'fieldName' => 'lastname',
               'label'     => __('Last name field', 'formcreator'),
               'fieldType' => ['text'],
            ]
         ),
      ];
   }

   public function prepareQuestionInputForSave($input) {
      $success = true;
      $fieldType = $this->getFieldTypeName();
      if ($input['_parameters'][$fieldType]['firstname']['plugin_formcreator_questions_id_1'] === '0') {
         Session::addMessageAfterRedirect(__('No text field selected for firstname', 'formcreator'), false, ERROR);
         $success =  false;
      }
      if ($input['_parameters'][$fieldType]['lastname']['plugin_formcreator_questions_id_2'] === '0') {
         Session::addMessageAfterRedirect(__('No text field selected for lastname', 'formcreator'), false, ERROR);
         $success =  false;
      }
      if (!$success) {
         return false;
      }

      return $input;
   }

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function serializeValue(): string {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      return strval((int) $this->value);
   }

   public function deserializeValue($value) {
      $this->value = ($value !== null && $value !== '')
         ? $value
         : '';
   }

   public function show($domain, $canEdit = true) {
      parent::show($canEdit);
      $questionId = $this->fields['id'];
      $domId = "input[name=\"formcreator_field_$questionId\"]";
      $parameters = $this->getEmptyParameters();
      foreach ($parameters as $fieldName => $parameter) {
         $parameter->getFromDBByCrit([
            'plugin_formcreator_questions_id'   => $this->fields['id'],
            'fieldname'                         => $fieldName,
         ]);
      }
      $firstnameQuestionId = $parameters['firstname']->getField('plugin_formcreator_questions_id_1');
      $lastnameQuestionId = $parameters['lastname']->getField('plugin_formcreator_questions_id_2');
      echo Html::scriptBlock("$(function() {
         plugin_formcreator_field_$questionId()
         $('input[name=\"formcreator_field_$firstnameQuestionId\"]').on('input', plugin_formcreator_field_$questionId)
         $('input[name=\"formcreator_field_$lastnameQuestionId\"]').on('input', plugin_formcreator_field_$questionId)
      })
      function plugin_formcreator_field_$questionId() {
         var firstname = $('input[name=\"formcreator_field_$firstnameQuestionId\"]').val().toUpperCase()
         var lastname = $('input[name=\"formcreator_field_$lastnameQuestionId\"]').val().toUpperCase()
         if (firstname.length < 2 || lastname.length < 2) {
            $('$domId').val('')
            return
         }
         $('$domId').val(lastname.substring(0, 2) + firstname.substring(0, 2))
      }");
   }

   public function displayField($canEdit = true) {
      $id           = $this->fields['id'];
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $domId        = $fieldName . '_' . $rand;
      if ($canEdit) {
         echo '<input type="text" class="form-control" readonly="readonly"
                  name="' . $fieldName . '"
                  id="' . $domId . '"
                  value="' . $this->value . '" />';
      } else {
         echo $this->value;
      }
   }

   public function getValueForDesign(): string {
      if ($this->value === null) {
         return '';
      }

      return $this->value;
   }

   public function getValueForTargetText($domain, $richText): ?string {
      return Toolbox::addslashes_deep($this->value);
   }

   public function getDocumentsForTarget(): array {
      return [];
   }

   public function moveUploads() {
   }

   public function isValid(): bool {
      if ($this->isRequired() && $this->value === '') {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR
         );
         return false;
      }
      if (!$this->isValidValue($this->value)) {
         Session::addMessageAfterRedirect(
            __('A field does not match the expected format:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR
         );
         return false;
      }

      // All is OK
      return true;
   }

   /**
    * Checks the value of the field is in the expected form
    * @param string $value the value of the field
    */
   public function isValidValue($value): bool {
      // TODO: use all fields of the form to check the scheme of the string
      $parameters = $this->getEmptyParameters();
      foreach ($parameters as $fieldname => $parameter) {
         $parameter->getFromDBByCrit([
            'plugin_formcreator_questions_id'   => $this->fields['id'],
            'fieldname'                         => $fieldname,
         ]);
      }
      $firstnameQuestionId = $parameters['firstname']->getField('plugin_formcreator_questions_id_1');
      $lastnameQuestionId = $parameters['lastname']->getField('plugin_formcreator_questions_id_2');

      $firstname = strtoupper($this->fields['answer']["formcreator_field_$firstnameQuestionId"]);
      $lastname = strtoupper($this->fields['answer']["formcreator_field_$lastnameQuestionId"]);
      if (strlen($firstname) < 2 || strlen($lastname) < 2) {
         return false;
      }
      $expected = substr($lastname, 0, 2) . substr($firstname, 0, 2);
      return ($value === $expected);
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      $key = 'formcreator_field_' . $this->fields['id'];
      if (!is_string($input[$key])) {
         return false;
      }

      $this->value = $input[$key];
      return true;
   }

   public function equals($value): bool {
      return ($this->value) === ($value);
   }

   public function notEquals($value): bool {
      return !$this->equals($value);
   }

   public function greaterThan($value): bool {
      return ($this->value) > ($value);
   }

   public function lessThan($value): bool {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function regex($value): bool {
      return preg_match($value, $this->value) ? true : false;
   }

   public function isAnonymousFormCompatible(): bool {
      return true;
   }

   public function getHtmlIcon(): string {
      return '';
   }

   public function isEditableField(): bool {
      return true;
   }

   public function isVisibleField(): bool {
      return true;
   }
}
