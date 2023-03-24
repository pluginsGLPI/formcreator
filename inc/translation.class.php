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

use Glpi\Toolbox\Sanitizer;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorTranslation
{
   /**
    * get a HTML dropdown of translatable strings
    *
    * @param array $options
    * @return string|void
    */
   public static function dropdown(array $options) {
      $params = [
         'id'              => '',
         'is_translated'   => null,
         'language'        => '', // Mandatory if one of is_translated and is_untranslated is false
      ];
      $options = array_merge($params, $options);

      $options['url'] = Plugin::getWebDir('formcreator') . '/ajax/gettranslationsvalues.php';
      $options['display'] = false;
      $options['display_emptychoice'] = true;
      $options['comments'] = false;
      $options['name'] = 'plugin_formcreator_translations_id';

      $out = Dropdown::show(PluginFormcreatorForm_Language::getType(), $options);
      if (!$options['display']) {
         return $out;
      }
      echo $out;
   }

   /**
    * Get dropdown value
    *
    * @param array   $post Posted values
    * @param boolean $json Encode to JSON, default to true
    *
    * @return string|array
    */
   public static function getDropdownValue($post, $json = true) {
      // Count real items returned
      $count = 0;

      if (isset($post['condition']) && !empty($post['condition']) && !is_array($post['condition'])) {
         // Retreive conditions from SESSION using its key
         $key = $post['condition'];
         $post['condition'] = [];
         if (isset($_SESSION['glpicondition']) && isset($_SESSION['glpicondition'][$key])) {
            $post['condition'] = $_SESSION['glpicondition'][$key];
         }
      }
      $formLanguageId = $post['condition'][PluginFormcreatorForm_Language::getForeignKeyField()];

      $formLanguage = new PluginFormcreatorForm_Language();
      if (!$formLanguage->getFromDB($formLanguageId)) {
         return [];
      }
      $post['searchText'] = $post['searchText'] ?? '';

      $form = PluginFormcreatorCommon::getForm();
      $form->getFromDB($formLanguage->fields['plugin_formcreator_forms_id']);
      $strings = $form->getTranslatableStrings([
         'language' => $formLanguage->fields['name'],
         'searchText' => $post['searchText'],
         'is_translated' => $post['condition']['is_translated'],
      ]);

      $foundCount = 0;
      $data = [];
      foreach (['itemlink', 'string', 'text'] as $stringType) {
         foreach ($strings[$stringType] as $id => $string) {
            $foundCount++;
            if ($foundCount < ((int) $post['page'] - 1) * (int) $post['page_limit']) {
               // before the requested page
               continue;
            }
            if ($foundCount > ((int) $post['page']) * (int) $post['page_limit']) {
               // after the requested page
               break;
            }
            $data[] = [
               // 'level' => 1,
               'id'    => $id,
               'text'  => strip_tags(html_entity_decode($string)),
            ];
            $count++;
            if ($count >= $post['page_limit']) {
               break 2;
            }
         }
      }

      $data = Sanitizer::unsanitize($data);
      $ret['results'] = $data;
      $ret['count']   = $count;

      return ($json === true) ? json_encode($ret) : $ret;
   }

   /**
    * get an HTML input for a translatable string
    *
    * @param PluginFormcreatorForm_Language $formLanguage
    * @param string $id
    * @return void
    */
   public static function getEditorFieldsHtml(PluginFormcreatorForm_Language $formLanguage, string $id = '') {
      $out = '';
      $form = PluginFormcreatorCommon::getForm();
      $form->getFromDB($formLanguage->fields['plugin_formcreator_forms_id']);

      // Find the strings to translate
      $translatableString = $form->getTranslatableStrings([
         'language'      => $formLanguage->fields['name'],
         'is_translated' => ($id != ''),
      ]);
      if (count($translatableString['id']) < 1) {
         $out .= '<td colspan="2">' . __('No more string to translate', 'formcreator') . '</td>';
         return $out;
      }
      if ($id == '') {
         // find the first string to translate
         reset($translatableString['id']);
         $id = key($translatableString['id']);
      }
      if (!isset($translatableString['id'][$id])) {
         // Show nothing if string definitively not found
         // Should not happen
         return '<td colspan="2">' . __('Iternal error : translatable string not found.', 'formcreator') . '</td>';;
      }

      $type = $translatableString['id'][$id] ?? 'string';
      $original = $translatableString[$type][$id];

      // Find the translation if any
      $translations = $form->getTranslations($formLanguage->fields['name']);
      $translatedString = $translations[$original] ?? '';

      switch ($type) {
         case 'itemlink':
         case 'string':
            $out .= '<td width="50%">' . $original . Html::hidden("id", ['value' => $id]) . '</td>';
            $out .= '<td>' . Html::input("value", ['value' => $translatedString]) . '</td>';
            break;

         case 'text':
            $out .= '<td width="50%">' . Html::entity_decode_deep($original) . Html::hidden("id", ['value' => $id]) . '</td>';
            $out .= '<td>' . Html::textarea([
               'name'  => "value",
               'value' => $translatedString,
               'enable_richtext' => true,
               'display' => false,
            ]) . '</td>';
      }
      $out .= Html::scriptBlock('$(\'input[name="value"]\').focus(); $(\'textarea[name="value"]\').focus();');

      return $out;
   }

   /**
    * Compute ID of a translatable string (using a hash function as there is no table in DB)
    *
    * @param string $string translatable string
    * @return string
    */
   public static function getTranslatableStringId($string) {
      return hash('md5', $string);
   }

   /**
    * Add a translation
    *
    * @param array $input
    * @return bool
    */
   public function add(array $input) : bool {
      global $TRANSLATE;

      $formLanguage = new PluginFormcreatorForm_Language();
      if (!$formLanguage->getFromDB($input['plugin_formcreator_forms_languages_id'])) {
         Session::addMessageAfterRedirect(__('Language not found.', 'formcreator'), false, ERROR);
         return false;
      }
      $form = PluginFormcreatorCommon::getForm();
      if (!$form->getFromDB($formLanguage->fields['plugin_formcreator_forms_id'])) {
         Session::addMessageAfterRedirect(__('Form not found.', 'formcreator'), false, ERROR);
         return false;
      }
      $translations = $form->getTranslations($formLanguage->fields['name']);
      $translatableStrings = $form->getTranslatableStrings([
         'id' => $input['id'],
      ]);
      $type = $translatableStrings['id'][$input['id']];
      $original = $translatableStrings[$type][$input['id']];

      $input['value'] = Sanitizer::unsanitize($input['value']);
      $input['value'] = str_replace('\r\n', '', $input['value']);
      $translations[$original] = Sanitizer::sanitize($input['value'], false);

      if (!$form->setTranslations($formLanguage->fields['name'], $translations)) {
         Session::addMessageAfterRedirect(__('Failed to add the translation.', 'formcreator'), false, ERROR);
         return false;
      }
      $domain = PluginFormcreatorForm::getTranslationDomain($form->getID(), $formLanguage->fields['name']);
      $TRANSLATE->clearCache($domain, $formLanguage->fields['name']);

      return true;
   }

   /**
    * Delete a translation
    *
    * @param array $input with keys domain and original
    * @return bool
    */
   public function delete(PluginFormcreatorForm_Language $formLanguage, $input) : bool {
      global $TRANSLATE;

      $form = PluginFormcreatorCommon::getForm();
      if (!$form->getFromDB($formLanguage->fields['plugin_formcreator_forms_id'])) {
         return false;
      }
      $translations = $form->getTranslations($formLanguage->fields['name']);
      $translated = $form->getTranslatableStrings([
         'id'       => $input['id'],
         'language' => $formLanguage->fields['name'],
      ]);
      $original = $translated[$translated['id'][$input['id']]][$input['id']];
      unset($translations[$original]);

      $form->setTranslations($formLanguage->fields['name'], $translations);
      $TRANSLATE->clearCache('formcreator', $formLanguage->fields['name']);
      return true;
   }
}