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
use Document;
use Html;
use Toolbox;
use Session;
use PluginFormcreatorFormAnswer;
use PluginFormcreatorForm;
use GlpiPlugin\Formcreator\Exception\ComparisonException;
use Glpi\Application\View\TemplateRenderer;
use PluginFormcreatorSection;
use PluginFormcreatorQuestion;
use PluginFormcreatorCommon;

class FileField extends PluginFormcreatorAbstractField
{
   /**@var array $uploadData uploads saved as documents   */
   private $uploadData = [];

   /** @var array $uploads uploaded files on form submit */
   private $uploads = [
      '_filename' => [],
      '_prefix_filename' => [],
      '_tag_filename' => [],
   ];

   public function showForm(array $options): void {
      $template = '@formcreator/field/' . $this->question->fields['fieldtype'] . 'field.html.twig';

      $this->question->fields['default_values'] = Html::entities_deep($this->question->fields['default_values']);
      $this->deserializeValue($this->question->fields['default_values']);
      TemplateRenderer::getInstance()->display($template, [
         'item' => $this->question,
         'params' => $options,
      ]);
   }

   public function isPrerequisites(): bool {
      return true;
   }

   public function getRenderedHtml($domain, $canEdit = true): string {
      if (!$canEdit) {
         $html = '';
         $doc = new Document();
         $answer = $this->uploadData;
         if (!is_array($this->uploadData)) {
            $answer = [$this->uploadData];
         }
         foreach ($answer as $item) {
            if (is_numeric($item) && $doc->getFromDB($item)) {
               $html .= $doc->getDownloadLink($this->form_answer);
            }
         }
         return $html;
      }

      return Html::file([
         'name'    => 'formcreator_field_' . $this->question->getID(),
         'display' => false,
         'multiple' => 'multiple',
         'uploads' => $this->uploads,
      ]);
   }

   public function serializeValue(PluginFormcreatorFormAnswer $formanswer): string {
      return json_encode($this->uploadData, true);
   }

   public function deserializeValue($value) {
      $this->uploadData = [];
      $this->value = __('No attached document', 'formcreator');
      if ($value === null) {
         return;
      }
      $this->uploadData = json_decode($value, true);
      if (!is_array($this->uploadData)) {
         $this->uploadData = [];
      }
      if (count($this->uploadData) > 0) {
         $this->value = __('Attached document', 'formcreator');
      }
   }

   public function getValueForDesign(): string {
      return '';
   }

   public function getValueForTargetText($domain, $richText): ?string {
      return $this->value;
   }

   public function moveUploads() {
      $key = 'formcreator_field_' . $this->question->getID();
      if (!is_array($this->uploads) || !isset($this->uploads["_$key"])) {
         return;
      }
      $answer_value = [];
      foreach ($this->uploads["_$key"] as $index => $document) {
         $document = Toolbox::stripslashes_deep($document);
         if (is_file(GLPI_TMP_DIR . '/' . $document)) {
            $prefix = $this->uploads['_prefix_formcreator_field_' . $this->question->getID()][$index];
            $answer_value[] = $this->saveDocument($document, $prefix);
         }
         $index++;
      }
      $this->uploadData = $answer_value;
   }

   public function getDocumentsForTarget(): array {
      return $this->uploadData;
   }

   public function isValid(): bool {
      if (!$this->isRequired()) {
         return true;
      }

      // If the field is required it can't be empty
      $key = '_formcreator_field_' . $this->question->getID();
      if (($this->isRequired() && (!isset($this->uploads[$key]) || count($this->uploads[$key]) < 1))) {
         Session::addMessageAfterRedirect(
            sprintf(__('A required file is missing: %s', 'formcreator'), $this->getLabel()),
            false,
            ERROR
         );
         return false;
      }

      return $this->isValidValue($this->value);
   }

   public function isValidValue($value): bool {
      // If the field is required it can't be empty
      $key = 'formcreator_field_' . $this->question->getID();
      return (count($this->uploads["_$key"]) > 0);
   }

   public static function getName(): string {
      return __('File');
   }

   public function prepareQuestionInputForSave($input) {
      return $input;
   }

   public static function canRequire(): bool {
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

   public function hasInput($input): bool {
      // key with unserscore when testing unput from a requester
      // key without underscore when testing data from DB (display a saved answer)
      $key = 'formcreator_field_' . $this->question->getID();
      return isset($input["_$key"])
         || isset($input[$key]);
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
      $form = PluginFormcreatorForm::getByItem($this->question);
      if ($form === null) {
         // A problem occured while finding the form of the field
         return;
      }

      $doc                             = new Document();
      $file_data                       = [];
      $file_data["name"]               = Toolbox::addslashes_deep($form->getField('name') . ' - ' . $this->question->fields['name']);
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

   public function parseAnswerValues($input, $nonDestructive = false): bool {
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
      if (isset($input[$key])) {
         // To restore input from database
         $this->uploadData = json_decode($input[$key]);
         $this->value = __('Attached document', 'formcreator');
         return true;

      }
      $this->uploadData = [];
      $this->value = '';
      return true;
   }

   public function equals($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function notEquals($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function greaterThan($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function lessThan($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function regex($value): bool {
      throw new ComparisonException('Meaningless comparison');
   }

   public function isPublicFormCompatible(): bool {
      return false;
   }

   public function getHtmlIcon() {
      return '<i class="fa fa-file" aria-hidden="true"></i>';
   }

   public function isVisibleField(): bool {
      return true;
   }

   public function isEditableField(): bool {
      return true;
   }

   public function getValueForApi() {
      $output = [];
      foreach ($this->uploadData as $documentId) {
         $output[] = [Document::class, $documentId];
      }
      return $output;
   }
}
