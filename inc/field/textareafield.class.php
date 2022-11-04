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

use PluginFormcreatorCommon;
use PluginFormcreatorQuestion;
use PluginFormcreatorForm;
use PluginFormcreatorFormAnswer;
use Html;
use Session;
use Toolbox;
use Document;
use Glpi\Toolbox\Sanitizer;
use Glpi\RichText\RichText;
use Glpi\Application\View\TemplateRenderer;

class TextareaField extends TextField
{
   /**@var array $uploadData uploads saved as documents   */
   private $uploadData = [];

   /** @var array uploaded files on form submit */
   private $uploads = [];

   public function showForm(array $options): void {
      $template = '@formcreator/field/' . $this->question->fields['fieldtype'] . 'field.html.twig';
      $this->deserializeValue($this->question->fields['default_values']);
      $parameters = $this->getParameters();
      TemplateRenderer::getInstance()->display($template, [
         'item' => $this->question,
         'question_params' => $parameters,
         'params' => $options,
      ]);
   }

   public function getRenderedHtml($domain, $canEdit = true): string {
      global $CFG_GLPI;

      if (!$canEdit) {
         $value = $this->value;
         if (version_compare(GLPI_VERSION, '10.0.3') < 0) {
            // TODO: duplicates Toolbox::convertTagToImage() with simplification and change to build proper URL
            //       must improve GLPI code
            $document = new Document();
            $doc_data = Toolbox::getDocumentsFromTag($value);
            foreach ($doc_data as $id => $image) {
               if (!isset($image['tag'])) {
                  continue;
               }
               if (!$document->getFromDB($id) || strpos($document->fields['mime'], 'image/') === false) {
                  $value = preg_replace(
                     '/' . Document::getImageTag($image['tag']) . '/',
                     '',
                     $value
                  );
                  continue;
               }

               $itil_url_param = null !== $this->form_answer
                        ? sprintf("&itemtype=%s&items_id=%s", $this->form_answer->getType(), $this->form_answer->getID())
                        : "";
               $img = "<img alt='" . $image['tag'] . "' src='" . $CFG_GLPI['root_doc'] .
                  "/front/document.send.php?docid=" . $id . $itil_url_param . "'/>";

               // 1 - Replace direct tag (with prefix and suffix) by the image
               $value = preg_replace(
                  '/' . Document::getImageTag($image['tag']) . '/',
                  $img,
                  $value
               );

               // 2 - Replace img with tag in id attribute by the image
               $regex = '/<img[^>]+' . preg_quote($image['tag'], '/') . '[^<]+>/im';
               preg_match_all($regex, $value, $matches);
               foreach ($matches[0] as $match_img) {
                  //retrieve dimensions
                  $width = $height = null;
                  $attributes = [];
                  preg_match_all('/(width|height)="([^"]*)"/i', $match_img, $attributes);
                  if (isset($attributes[1][0])) {
                      ${$attributes[1][0]} = $attributes[2][0];
                  }
                  if (isset($attributes[1][1])) {
                      ${$attributes[1][1]} = $attributes[2][1];
                  }

                  if ($width == null || $height == null) {
                      $path = GLPI_DOC_DIR . "/" . $image['filepath'];
                      $img_infos  = getimagesize($path);
                      $width = $img_infos[0];
                      $height = $img_infos[1];
                  }

                  // replace image
                  $new_image =  Html::getImageHtmlTagForDocument(
                      $id,
                      $width,
                      $height,
                      true,
                      $itil_url_param
                  );
                  if (empty($new_image)) {
                        $new_image = '#' . $image['tag'] . '#';
                  }
                  $value = str_replace(
                      $match_img,
                      $new_image,
                      $value
                  );
               }

            }
         } else {
            $value = Toolbox::convertTagToImage($this->value, $this->form_answer);
         }

         return RichText::getEnhancedHtml($value);
      }

      $id           = $this->question->getID();
      $rand         = mt_rand();
      $fieldName    = 'formcreator_field_' . $id;
      $value = $this->value;
      $html = '';
      $html .= Html::textarea([
         'name'              => $fieldName,
         'editor_id'         => "$fieldName$rand",
         'rand'              => $rand,
         'value'             => $value,
         'rows'              => 5,
         'display'           => false,
         'enable_richtext'   => true,
         'enable_fileupload' => false,
         'uploads'           => $this->uploads,
      ]);
      // The following file upload area is needed to allow embedded pics in the textarea
      $html .=  '<div style="display:none;">';
      Html::file(['editor_id'    => "$fieldName$rand",
                  'filecontainer' => "filecontainer$rand",
                  'onlyimages'    => true,
                  'showtitle'     => false,
                  'multiple'      => true,
                  'display'       => false]);
      $html .=  '</div>';
      $html .= Html::scriptBlock("$(function() {
         pluginFormcreatorInitializeTextarea('$fieldName', '$rand');
      });");

      return $html;
   }

   public static function getName(): string {
      return __('Textarea', 'formcreator');
   }

   public function serializeValue(PluginFormcreatorFormAnswer $formanswer): string {
      if ($this->value === null || $this->value === '') {
         return '';
      }

      $key = 'formcreator_field_' . $this->question->getID();
      if (isset($this->uploads['_' . $key])) {
         $input = [$key => $this->value] + $this->uploads;
         // for each uploaded document, check if it already exists in DB
         foreach ($this->uploads['_tag_' . $key] as $docKey => $tag) {
            $document = new Document();
            $newTag = $tag;
            if ($document->getDuplicateOf($formanswer->fields['entities_id'], GLPI_TMP_DIR . '/' . $input['_' . $key][$docKey])) {
               $newTag = $document->fields['tag'];
            }
            $this->uploads['dedup'][$tag] = $newTag;
         }
         $input = $formanswer->addFiles(
            $input,
            [
               'force_update'  => false,
               'content_field' => null,
               'name'          => $key,
            ]
         );
         $input[$key] = $this->value; // Restore the text because we don't want image converted into A + IMG tags
         // $this->value = $input[$key];
         $this->value = Sanitizer::unsanitize($this->value);
         foreach ($input['_tag'] as $docKey => $tag) {
            $newTag = $this->uploads['dedup'][$tag];
            $regex = '/<img[^>]+' . preg_quote($tag, '/') . '[^<]+>/im';
            $this->value = preg_replace($regex, "#$newTag#", $this->value);
         }
         $this->value = Sanitizer::sanitize($this->value);
      }

      return $this->value;
   }

   public function deserializeValue($value) {
      $this->value = ($value !== null && $value !== '')
         ? $value
         : '';
   }

   public function getValueForDesign(): string {
      if ($this->value === null) {
         return '';
      }

      return $this->value;
   }

   public function getDocumentsForTarget(): array {
      return $this->uploadData;
   }

   public function isValid(): bool {
      // If the field is required it can't be empty
      if ($this->isRequired() && $this->value == '') {
         Session::addMessageAfterRedirect(
            __('A required field is empty:', 'formcreator') . ' ' . $this->getLabel(),
            false,
            ERROR
         );
         return false;
      }

      // All is OK
      return true;
   }

   public function prepareQuestionInputForSave($input): array {
      $success = true;
      $fieldType = $this->getFieldTypeName();
      if (isset($input['_parameters'][$fieldType]['regex']['regex']) && !empty($input['_parameters'][$fieldType]['regex']['regex'])) {
         $regex = Toolbox::stripslashes_deep($input['_parameters'][$fieldType]['regex']['regex']);
         $success = PluginFormcreatorCommon::checkRegex($regex);
         if (!$success) {
            Session::addMessageAfterRedirect(__('The regular expression is invalid', 'formcreator'), false, ERROR);
         }
      }
      if (!$success) {
         return [];
      }

      $this->value = $input['default_values'];

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

   public function hasInput($input): bool {
      return isset($input['formcreator_field_' . $this->question->getID()]);
   }

   public function parseAnswerValues($input, $nonDestructive = false): bool {
      parent::parseAnswerValues($input, $nonDestructive);
      $key = 'formcreator_field_' . $this->question->getID();
      if (isset($input['_tag_' . $key]) && isset($input['_' . $key]) && isset($input['_prefix_' . $key])) {
         $this->uploads['_' . $key] = $input['_' . $key];
         $this->uploads['_prefix_' . $key] = $input['_prefix_' . $key];
         $this->uploads['_tag_' . $key] = $input['_tag_' . $key];
      }

      return true;
   }

   public function getValueForTargetText($domain, $richText): ?string {
      $value = $this->value;
      if (!$richText) {
         $value = strip_tags($value);
      }

      return $value;
   }

   public function equals($value): bool {
      return $this->value == Sanitizer::unsanitize($value);
   }

   public function notEquals($value): bool {
      return !$this->equals($value);
   }

   public function greaterThan($value): bool {
      return $this->value > Sanitizer::unsanitize($value);
   }

   public function lessThan($value): bool {
      return !$this->greaterThan($value) && !$this->equals($value);
   }

   public function regex($value): bool {
      return (preg_match(Sanitizer::unsanitize($value), $this->value) === 1) ? true : false;
   }

   public function isPublicFormCompatible(): bool {
      return true;
   }

   public function getHtmlIcon(): string {
      return '<i class="far fa-comment-dots" aria-hidden="true"></i>';
   }

   public function getTranslatableStrings(array $options = []) : array {
      $strings = parent::getTranslatableStrings($options);

      $params = [
         'searchText'      => '',
         'id'              => '',
         'is_translated'   => null,
         'language'        => '', // Mandatory if one of is_translated and is_untranslated is false
      ];
      $options = array_merge($params, $options);

      $searchString = Toolbox::stripslashes_deep(trim($options['searchText']));

      if ($searchString != '' && stripos($this->question->fields['default_values'], $searchString) === false) {
         return $strings;
      }
      $id = \PluginFormcreatorTranslation::getTranslatableStringId($this->question->fields['default_values']);
      if ($options['id'] != '' && $id != $options['id']) {
         return $strings;
      }
      if ($this->question->fields['default_values'] != '') {
         $strings['text'][$id] = $this->question->fields['default_values'];
         $strings['id'][$id] = 'text';
      }

      return $strings;
   }
}
