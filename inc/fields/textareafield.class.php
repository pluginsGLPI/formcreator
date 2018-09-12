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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class PluginFormcreatorTextareaField extends PluginFormcreatorTextField
{
   public function displayField($canEdit = true) {
      global $CFG_GLPI;

      $id           = $this->fields['id'];
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $domId        = $fieldName . '_' . $rand;
      if ($canEdit) {
         $required = $this->fields['required'] ? ' required' : '';

         // echo '<textarea class="form-control"
         //          rows="5"
         //          name="' . $fieldName . '"
         //          id="' . $domId . '"
         //          onchange="formcreatorChangeValueOf('.$this->fields['id'].', this.value);">'
         //         .str_replace('\r\n', PHP_EOL, $this->getValue()).'</textarea>';
         // if ($CFG_GLPI["use_rich_text"]) {
         //    Html::initEditorSystem('formcreator_field_'.$this->fields['id']);
         // }
         echo Html::textarea([
            'name'            => $fieldName,
            'rand'            => $rand,
            'value'           => str_replace('\r\n', PHP_EOL, $this->getValue()),
            'rows'            => 5,
            'display'         => false,
            'enable_richtext' => true,
         ]);
         echo Html::scriptBlock("$(function() {
            pluginFormcreatorInitializeTextarea('$fieldName', '$rand');
         });");
      } else {
         if ($CFG_GLPI["use_rich_text"]) {
            echo plugin_formcreator_decode($this->getAnswer());
         } else {
            echo nl2br($this->getAnswer());
         }
      }
   }

   public static function getName() {
      return __('Textarea', 'formcreator');
   }

   public function prepareQuestionInputForTarget($input) {
      $input = str_replace("\r\n", '\r\n', addslashes($input));
      return $input;
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['textarea'] = 'showFields(".implode(', ', $prefs).");';";
   }
}
