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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

trait PluginFormcreatorTranslatable
{

   /**
    * Find search options of translatable fields
    *
    * @return array
    */
   public function getTranslatableSearchOptions() : array {
      $searchOptions = $this->searchOptions();
      $translatable = [];
      $table = $this::getTable();
      foreach ($searchOptions as $id => $searchOption) {
         if (!isset($searchOption['field'])) {
            continue;
         }
         if ($searchOption['table'] != $table) {
            continue;
         }
         if (!isset($searchOption['datatype'])) {
            continue;
         }
         if (!in_array($searchOption['datatype'], ['itemlink', 'text', 'string'])) {
            continue;
         }
         if ($searchOption['datatype'] == 'itemlink' && $id != '1') {
            continue;
         }
         $translatable[] = $searchOption;
      }

      return $translatable;
   }

   /**
    * get translatable strings of the item
    *
    * @param array $options
    * @return array
    */
   public function getMyTranslatableStrings(array $options) : array {
      $strings = [
         'itemlink' => [],
         'string'   => [],
         'text'     => [],
         'id'       => []
      ];
      $params = [
         'searchText'      => '',
         'id'              => '',
         'is_translated'   => null,
         'language'        => '', // Mandatory if one of is_translated and is_untranslated is false
      ];
      $options = array_merge($params, $options);

      $searchString = Toolbox::stripslashes_deep(trim($options['searchText']));

      foreach ($this->getTranslatableSearchOptions() as $searchOption) {
         if ($searchString != '' && stripos($this->fields[$searchOption['field']], $searchString) === false) {
            continue;
         }
         $id = PluginFormcreatorTranslation::getTranslatableStringId($this->fields[$searchOption['field']]);
         if ($options['id'] != '' && $id != $options['id']) {
            continue;
         }
         if ($this->fields[$searchOption['field']] != '') {
            $strings[$searchOption['datatype']][$id] = $this->fields[$searchOption['field']];
            $strings['id'][$id] = $searchOption['datatype'];
         }
      }

      return $strings;
   }

   protected function deduplicateTranslatable(array $strings) : array {
      foreach (array_keys($strings) as $type) {
         if ($type == 'id') {
            continue;
         }
         $strings[$type] = array_unique($strings[$type]);
         $strings[$type] = array_filter($strings[$type]);
      }

      return $strings;
   }
}