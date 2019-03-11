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
 * @copyright Copyright © 2011 - 2019 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

class PluginFormcreatorFileField extends PluginFormcreatorField
{
   private $uploadData = [];

   public function isPrerequisites() {
      return true;
   }

   public function displayField($canEdit = true) {
      if ($canEdit) {
         $required = $this->isRequired() ? ' required' : '';

         echo Html::file([
            'name'    => 'formcreator_field_' . $this->fields['id'],
            'display' => false,
            'multiple' => 'multiple',
         ]);
      } else {
         $doc = new Document();
         $answer = $this->value;
         if (!is_array($this->value)) {
            $answer = [$this->value];
         }
         foreach ($answer as $item) {
            if (is_numeric($item) && $doc->getFromDB($item)) {
               echo $doc->getDownloadLink();
            }
         }
      }
   }

   public function serializeValue() {
      return json_encode($this->uploadData, true);
   }

   public function deserializeValue($value) {
      $this->uploadData = json_decode($value, true);
      if ($this->uploadData === null) {
         $this->uploadData = [];
      }
      if (count($this->uploadData) === 0) {
         $this->value = __('Attached document', 'formcreator');
      } else {
         $this->value = '';
      }
   }

   public function getValueForDesign() {
      return '';
   }

   public function getValueForTargetText($richText) {
      return $this->value;
   }

   public function getDocumentsForTarget() {
      return $this->uploadData;
   }

   public function isValid() {
      if (!$this->isRequired()) {
         return true;
      }

      if (!$this->isValidValue($this->value)) {
         Session::addMessageAfterRedirect(__('A required file is missing:', 'formcreator') . ' ' . $this->fields['name'], false, ERROR);
         return false;
      }

      return true;
   }

   private function isValidValue($value) {
      // If the field is required it can't be empty
      return (count($this->uploadData) > 0);
   }

   public static function getName() {
      return __('File');
   }

   public static function getPrefs() {
      return [
         'required'       => 1,
         'default_values' => 0,
         'values'         => 0,
         'range'          => 0,
         'show_empty'     => 0,
         'regex'          => 0,
         'show_type'      => 1,
         'dropdown_value' => 0,
         'glpi_objects'   => 0,
         'ldap_values'    => 0,
      ];
   }

   public static function getJSFields() {
      $prefs = self::getPrefs();
      return "tab_fields_fields['file'] = 'showFields(" . implode(', ', $prefs) . ");';";
   }

   /**
    * Save an uploaded file into a document object, link it to the answers
    * and returns the document ID
    * @param PluginFormcreatorForm $form
    * @param PluginFormcreatorQuestion $question
    * @param array $file                         an item from $_FILES array
    *
    * @return integer|NULL
    */
   private function saveDocument($file, $prefix) {
      global $DB;

      $sectionTable = PluginFormcreatorSection::getTable();
      $sectionFk = PluginFormcreatorSection::getForeignKeyField();
      $questionTable = PluginFormcreatorQuestion::getTable();
      $formTable = PluginFormcreatorForm::getTable();
      $formFk = PluginFormcreatorForm::getForeignKeyField();
      $form = new PluginFormcreatorForm();
      $form->getFromDBByRequest([
        'LEFT JOIN' => [
           $sectionTable => [
              'FKEY' => [
                 $sectionTable => $formFk,
                 $formTable => 'id'
              ]
           ],
           $questionTable => [
              'FKEY' => [
                 $sectionTable => 'id',
                 $questionTable => $sectionFk
              ]
           ]
        ],
        'WHERE' => [
           "$questionTable.id" => $this->fields['id'],
        ],
      ]);
      if ($form->isNewItem()) {
         // A problem occured while finding the form of the field
         return;
      }

      $doc                             = new Document();
      $file_data                       = [];
      $file_data["name"]               = Toolbox::addslashes_deep($form->getField('name'). ' - ' . $this->fields['name']);
      $file_data["entities_id"]        = isset($_SESSION['glpiactive_entity'])
                                       ? $_SESSION['glpiactive_entity']
                                       : $form->getField('entities_id');
      $file_data["is_recursive"]       = $form->getField('is_recursive');
      $file_data['_filename']          = [$file];
      $file_data['_prefix_filename']   = [$prefix];
      if ($docID = $doc->add($file_data)) {
         return $docID;
      }
      return null;
   }

   public function parseAnswerValues($input) {
      $key = 'formcreator_field_' . $this->fields['id'];
      if (isset($input["_$key"])) {
         if (!is_array($input["_$key"])) {
            return false;
         }

         $answer_value = [];
         $index = 0;
         foreach ($input["_$key"] as $document) {
            if (is_file(GLPI_TMP_DIR . '/' . $document)) {
               $prefix = $input['_prefix_formcreator_field_' . $this->fields['id']][$index];
               $answer_value[] = $this->saveDocument($document, $prefix);
            }
            $index++;
         }
         $this->uploadData = $answer_value;
         $this->value = __('Attached document', 'formcreator');
         return true;
      }
      $this->uploadData = [];
      $this->value = '';
      return true;
   }

   public function equals($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
   }

   public function notEquals($value) {
      throw new PluginFormcreatorComparisonException('Meaningless comparison');
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
}
