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

use Document;
use Toolbox;
use PluginFormcreatorForm;

trait Uploadable {

   /**@var array $uploadData uploads saved as documents   */
   protected $uploadData = [];

    /** @var array $uploads uploaded files on form submit */
   protected $uploads = [
      '_filename' => [],
      '_prefix_filename' => [],
      '_tag_filename' => [],
   ];

    /**
     * Save an uploaded file into a document object, link it to the answers
     * and returns the document ID
     * @param PluginFormcreatorForm     $form
     * @param PluginFormcreatorQuestion $question
     * @param array $file               an item from $_FILES array
     *
     * @return integer|null
     */
   protected function saveDocument($file, $prefix) {
      $form = PluginFormcreatorForm::getByItem($this->question);
      if ($form === null) {
         // A problem occured while finding the form of the field
         return;
      }

      $file_data = [
         'name'             => Toolbox::addslashes_deep($form->fields['name'] . ' - ' . $this->question->fields['name']),
         'entities_id'      => $_SESSION['glpiactive_entity'] ?? $form->getField('entities_id'),
         'is_recursive'     => $form->getField('is_recursive'),
         '_filename'        => [$file],
         '_prefix_filename' => [$prefix],
      ];
      $doc = new Document();
      if ($docID = $doc->add($file_data)) {
         return $docID;
      }
      return null;
   }

   public function moveUploads() {
      parent::moveUploads();

      $key = 'formcreator_field_' . $this->question->getID();
      if (!is_array($this->uploads) || !isset($this->uploads["_$key"])) {
         return;
      }
      $answer_value = [];
      foreach ($this->uploads["_$key"] as $index => $document) {
         $document = Toolbox::stripslashes_deep($document);
         if (is_file(GLPI_TMP_DIR . '/' . $document)) {
            $prefix = $this->uploads['_prefix_' . $key][$index];
            $answer_value[] = $this->saveDocument($document, $prefix);
         }
         $index++;
      }
      $this->uploadData = $answer_value;
   }

   public function getDocumentsForTarget(): array {
      return $this->uploadData;
   }
}
