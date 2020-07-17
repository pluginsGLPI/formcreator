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

class PluginFormcreatorIpField extends PluginFormcreatorField
{
   public function isPrerequisites() {
      return true;
   }

   public function getDesignSpecializationField() {
      $common = $common = parent::getDesignSpecializationField();
      $additions = $common['additions'];

      return [
         'label' => '',
         'field' => '',
         'additions' => $additions,
         'may_be_empty' => false,
         'may_be_required' => false,
      ];
   }

   public function show($canEdit = true) {
      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $domId        = $fieldName . '_' . $rand;
      $ip = Toolbox::getRemoteIpAddress();
      $ip = Html::cleanInputText($ip);
      echo Html::hidden($fieldName, [
         'id'     => $domId,
         'value'  => $ip,
      ]);
   }

   public function serializeValue() {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = ($value !== null && $value !== '')
                  ? $value
                  : '';
   }

   public function getValueForDesign() {
      if ($this->value === null) {
         return '';
      }

      return $this->value;
   }

   public function getValueForTargetText($richText) {
      return Toolbox::addslashes_deep($this->value);
   }

   public function moveUploads() {}

   public function getDocumentsForTarget() {
      return [];
   }

   public function isValid() {
      return true;
   }

   public static function getName() {
      return _n('IP address', 'Adresses IP', 1);
   }

   public static function canRequire() {
      return false;
   }

   public function parseAnswerValues($input, $nonDestructive = false) {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!is_string($input[$key])) {
         return false;
      }

      $this->value = $input[$key];
      return true;
   }

   public function hasInput($input) {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function equals($value) {
      return $this->value == $value;
   }

   public function notEquals($value) {
      return !$this->equals($value);
   }

   public function greaterThan($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
   }

   public function lessThan($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
   }

   public function isAnonymousFormCompatible() {
      return true;
   }

   public function getHtmlIcon() {
      return '<i class="fa fa-desktop" aria-hidden="true"></i>';
   }
}
