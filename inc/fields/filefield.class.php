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

class PluginFormcreatorFileField extends PluginFormcreatorField
{
   /**@var $uploadData array uploads saved as documents   */
   private $uploadData = [];

   /** @var $uploads array uploaded files on form submit */
   private $uploads = [
      '_filename' => [],
      '_prefix_filename' => [],
      '_tag_filename' => [],
   ];

   public function isPrerequisites() {
      return true;
   }

   public function displayField($canEdit = true) {
      if ($canEdit) {
         echo Html::file([
            'name'    => 'formcreator_field_' . $this->question->getID(),
            'display' => false,
            'multiple' => 'multiple',
            'uploads' => $this->uploads,
         ]);
      } else {
         $doc = new Document();
         $answer = $this->uploadData;
         if (!is_array($this->uploadData)) {
            $answer = [$this->uploadData];
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
      $this->value = __('No attached document', 'formcreator');;
      if (count($this->uploadData) > 0) {
         $this->value = __('Attached document', 'formcreator');
      }
   }

   public function getValueForDesign() {
      return '';
   }

   public function getValueForTargetText($richText) {
      return $this->value;
   }

   public function moveUploads()
   {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!is_array($this->uploads) || !isset($this->uploads["_$key"])) {
         return;
      }
      $answer_value = [];
      $index = 0;
      foreach ($this->uploads["_$key"] as $document) {
         $document = Toolbox::stripslashes_deep($document);
         if (is_file(GLPI_TMP_DIR . '/' . $document)) {
            $prefix = $this->uploads['_prefix_formcreator_field_' . $this->question->getID()][$index];
            $answer_value[] = $this->saveDocument($document, $prefix);
         }
         $index++;
      }
      $this->uploadData = $answer_value;
   }

   public function getDocumentsForTarget() {
      return $this->uploadData;
   }

   public function isValid() {
      if (!$this->isRequired()) {
         return true;
      }

      if (!$this->isValidValue($this->value)) {
         Session::addMessageAfterRedirect(__('A required file is missing:', 'formcreator') . ' ' . $this->question->fields['name'], false, ERROR);
         return false;
      }

      return true;
   }

   private function isValidValue($value) {
      // If the field is required it can't be empty
      $key = 'formcreator_field_' . $this->question->getID();
      return (count($this->uploads["_$key"]) > 0);
   }

   public static function getName() {
      return __('File');
   }

   public function prepareQuestionInputForSave($input) {
      return $input;
   }

   public static function canRequire() {
      return true;
   }

   public function saveUploads($input) {
      $key = 'formcreator_field_' . $this->question->getID();
      $index = 0;
      $answer_value = [];
      foreach ($input["_$key"] as $document) {
         $document = Toolbox::stripslashes_deep($document);
         if (is_file(GLPI_TMP_DIR . '/' . $document)) {
            $prefix = $input['_prefix_formcreator_field_' . $this->question->getID()][$index];
            $answer_value[] = $this->saveDocument($document, $prefix);
         }
         $index++;
      }
      $this->uploadData = $answer_value;
   }

   public function hasInput($input) {
      return isset($input['_formcreator_field_' . $this->question->getID()]);
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
           "$questionTable.id" => $this->question->getID(),
        ],
      ]);
      if ($form->isNewItem()) {
         // A problem occured while finding the form of the field
         return;
      }

      $doc                             = new Document();
      $file_data                       = [];
      $file_data["name"]               = Toolbox::addslashes_deep($form->getField('name'). ' - ' . $this->question->fields['name']);
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

   public function parseAnswerValues($input, $nonDestructive = false) {
      $key = 'formcreator_field_' . $this->question->getID();
      if (isset($input['_tag_' . $key]) && isset($input['_' . $key]) && isset($input['_prefix_' . $key])) {
         $this->uploads['_' . $key] = $input['_' . $key];
         $this->uploads['_prefix_' . $key] = $input['_prefix_' . $key];
         $this->uploads['_tag_' . $key] = $input['_tag_' . $key];
      }
      if (isset($input["_$key"])) {
         if (!is_array($input["_$key"])) {
            return false;
         }

         if ($this->hasInput($input)) {
            $this->value = __('Attached document', 'formcreator');
         }
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

   public function getHtmlIcon() {
      return '<i class="fa fa-file" aria-hidden="true"></i>';
   }
}
