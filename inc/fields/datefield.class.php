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

class PluginFormcreatorDateField extends PluginFormcreatorField
{
   public function isPrerequisites() {
      return true;
   }

   public function displayField($canEdit = true) {
      if ($canEdit) {
         $id        = $this->question->getID();
         $rand      = mt_rand();
         $fieldName = 'formcreator_field_' . $id;

         Html::showDateField($fieldName, [
            'value' => (strtotime($this->value) != '') ? $this->value : '',
            'rand'  => $rand,
         ]);
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeDate('$fieldName', '$rand');
         });");

      } else {
         echo $this->value;
      }
   }

   public function serializeValue() {
      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = $value;
   }

   public function getValueForDesign() {
      return $this->value;
   }

   public function getValueForTargetText($richText) {
      return Toolbox::addslashes_deep(Html::convDate($this->value));
   }

   public function hasInput($input) {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function moveUploads() {}

   public function getDocumentsForTarget() {
      return [];
   }

   public function isValid() {
      // If the field is required it can't be empty
      if ($this->isRequired() && (strtotime($this->value) === false)) {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public static function getName() {
      return __('Date');
   }

   public static function canRequire() {
      return true;
   }

   public function equals($value) {
      if ($this->value === '') {
         $answer = '0000-00-00 00:00';
      } else {
         $answer = $this->value;
      }
      $answerDatetime = DateTime::createFromFormat("Y-m-d", $answer);
      $compareDatetime = DateTime::createFromFormat("Y-m-d", $value);
      return $answerDatetime == $compareDatetime;
   }

   public function notEquals($value) {
      return !$this->equals($value);
   }

   public function greaterThan($value) {
      if (empty($this->value)) {
         $answer = '0000-00-00 00:00';
      } else {
         $answer = $this->value;
      }
      $answerDatetime = DateTime::createFromFormat("Y-m-d", $answer);
      $compareDatetime = DateTime::createFromFormat("Y-m-d", $value);
      return $answerDatetime > $compareDatetime;
   }

   public function lessThan($value) {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function parseAnswerValues($input, $nonDestructive = false) {

      $key = 'formcreator_field_' . $this->question->getID();
      if (!is_string($input[$key])) {
         return false;
      }

      $this->value = $input[$key];
      return true;
   }

   public function isAnonymousFormCompatible() {
      return true;
   }

   public function getHtmlIcon() {
      return '<i class="fa fa-calendar" aria-hidden="true"></i>';
   }
}
