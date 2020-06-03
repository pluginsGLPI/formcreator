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

class PluginFormcreatorSelectField extends PluginFormcreatorRadiosField
{
   public function isPrerequisites() {
      return true;
   }

   public function getDesignSpecializationField() {
      $specialization = parent::getDesignSpecializationField();
      $specialization['may_be_empty'] = true;

      return $specialization;
   }

   public function getRenderedHtml($canEdit = true) {
      if (!$canEdit) {
         return nl2br($this->value) . PHP_EOL;
      }

      $html         = '';
      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $values       = $this->getAvailableValues();
      $tab_values   = [];

      if (!empty($this->question->fields['values'])) {
         foreach ($values as $value) {
            if ((trim($value) != '')) {
               $tab_values[$value] = $value;
            }
         }

         $html .= Dropdown::showFromArray($fieldName, $tab_values, [
            'display_emptychoice' => $this->question->fields['show_empty'] == 1,
            'value'     => $this->value,
            'values'    => [],
            'rand'      => $rand,
            'multiple'  => false,
            'display'   => false,
         ]);
      }
      $html .=  PHP_EOL;
      $html .=  Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeSelect('$fieldName', '$rand');
      });");

      return $html;
   }

   public static function getName() {
      return __('Select', 'formcreator');
   }

   public function isValid() {
      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '0') {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR);
         return false;
      }

      // All is OK
      return true;
   }

   public function equals($value) {
      if ($value == '') {
         // empty string means no selection
         $value = '0';
      }
      return $this->value == $value;
   }


   public function getHtmlIcon() {
      return '<img src="' . FORMCREATOR_ROOTDOC . '/pics/ui-select-field.png" title="" />';
   }
}
